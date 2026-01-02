<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

use WC_Order;

// Exit on direct access.
defined( 'ABSPATH' ) || exit;

class Internetmarke_Label_Meta {

        /**
         * Order Object to handle saved content.
         *
         * @var WC_Order
         */
        protected $order;

        /**
         * Indicates if the database table is installed.
         *
         * @var bool|null
         */
        public static $db_is_installed = null;

        /**
         * Simple construct.
         *
         * @param WC_Order $order Order object.
         */
        public function __construct( WC_Order $order ) {
                $this->order = $order;
        }

        /**
         * Creates database table.
         *
         * @return void
         */
        public static function create_table() {
                global $wpdb;

                $wpdb->hide_errors();
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';

                dbDelta( self::get_schema() );
        }

        /**
         * Creates table if table does not exist.
         *
         * @return bool
         */
        public static function maybe_create_table() {
                global $wpdb;

                $created_table = false;

                if ( true !== self::$db_is_installed ) {

                        $table_name   = self::get_table_name();
                        $prefixed_name = $wpdb->prefix . $table_name;
                        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$prefixed_name}'" );

                        if ( ! $table_exists ) {
                                self::create_table();
                                $created_table = true;
                        }

                        $columns = $wpdb->get_col( "DESC {$prefixed_name}", 0 );

                        if ( ! in_array( 'meta_key', $columns, true ) ) {
                                $wpdb->query( "ALTER TABLE {$prefixed_name} ADD `meta_key` varchar(191) NOT NULL AFTER `order_id`;" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        }

                        if ( ! in_array( 'compression', $columns, true ) ) {
                                $wpdb->query( "ALTER TABLE {$prefixed_name} CHANGE `saved_content` `saved_content` LONGBLOB NOT NULL;" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                                $wpdb->query( "ALTER TABLE {$prefixed_name} ADD `compression` tinyint(1) NOT NULL DEFAULT '0' AFTER `saved_content`;" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        }

                        // Ensure indexes exist.
                        $indexes         = $wpdb->get_results( "SHOW INDEX FROM {$prefixed_name}" );
                        $has_order_index = false;
                        $has_meta_index  = false;
                        $has_unique_key  = false;

                        if ( ! empty( $indexes ) ) {
                                foreach ( $indexes as $index ) {
                                        if ( 'order_id' === $index->Column_name ) {
                                                $has_order_index = true;
                                        }
                                        if ( 'meta_key' === $index->Column_name ) {
                                                $has_meta_index = true;
                                        }
                                        if ( 'order_meta' === $index->Key_name ) {
                                                $has_unique_key = true;
                                        }
                                }
                        }

                        if ( ! $has_order_index ) {
                                $wpdb->query( "ALTER TABLE {$prefixed_name} ADD INDEX `order_id` (`order_id`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        }

                        if ( ! $has_meta_index ) {
                                $wpdb->query( "ALTER TABLE {$prefixed_name} ADD INDEX `meta_key` (`meta_key`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        }

                        if ( ! $has_unique_key ) {
                                $wpdb->query( "ALTER TABLE {$prefixed_name} ADD UNIQUE KEY `order_meta` (`order_id`,`meta_key`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        }

                        self::$db_is_installed = true;
                }

                return $created_table;
        }

        /**
         * Get database table name.
         *
         * @return string
         */
        private static function get_table_name() {
                return 'gm_internetmarke_label_meta';
        }

        /**
         * Get sql schema for database table.
         *
         * @return string
         */
        private static function get_schema() {
                global $wpdb;
                $table_name = self::get_table_name();
                $collate    = '';

                if ( $wpdb->has_cap( 'collation' ) ) {
                        $collate = $wpdb->get_charset_collate();
                }

                $table = "
CREATE TABLE {$wpdb->prefix}{$table_name} (
  id bigint(20) unsigned auto_increment,
  order_id bigint(20) unsigned NOT NULL,
  meta_key varchar(191) NOT NULL,
  saved_content LONGBLOB NOT NULL,
  compression tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (id),
  KEY order_id (order_id),
  KEY meta_key (meta_key),
  UNIQUE KEY order_meta (order_id, meta_key)
) $collate;
                ";

                return $table;
        }

        /**
         * Drop table.
         *
         * @return void
         */
        public static function drop_table() {
                global $wpdb;
                $table_name = self::get_table_name();
                $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        }

        /**
         * Add meta data to db table.
         *
         * @param string $key     Meta key.
         * @param string $content Meta value.
         *
         * @return int|bool
         */
        public function add_meta( $key, $content ) {
                global $wpdb;

                self::maybe_create_table();
                $table_name = self::get_table_name();

                $compression = 0;
                if ( function_exists( 'gzcompress' ) ) {
                        $use_compression = apply_filters( 'wgm_internetmarke_label_use_compression_for_saved_content', true, $key, $this->order );
                        if ( $use_compression ) {
                                $maybe_compressed = gzcompress( $content, 9 );
                                if ( false !== $maybe_compressed ) {
                                        $compression = 1;
                                        $content     = $maybe_compressed;
                                }
                        }
                }

                // Ensure legacy post meta is removed.
                delete_post_meta( $this->order->get_id(), $key );
                $this->order->delete_meta_data( $key );
                $this->order->save_meta_data();

                $result = $wpdb->replace(
                        $wpdb->prefix . $table_name,
                        array(
                                'order_id'      => $this->order->get_id(),
                                'meta_key'      => $key,
                                'saved_content' => $content,
                                'compression'   => $compression,
                        ),
                        array( '%d', '%s', '%s', '%d' )
                );

                return $result;
        }

        /**
         * Returns if meta data exists without getting it (much faster).
         *
         * @param string $key Meta key.
         *
         * @return bool
         */
        public function has_meta( $key ) {
                global $wpdb;

                self::maybe_create_table();
                $table_name = self::get_table_name();

                $post_meta = $this->order->get_meta( $key, true );

                if ( ! empty( $post_meta ) ) {
                        return true;
                }

                $count = $wpdb->get_var(
                        $wpdb->prepare(
                                "SELECT id FROM {$wpdb->prefix}{$table_name} WHERE order_id = %d AND meta_key = %s LIMIT 1;",
                                $this->order->get_id(),
                                $key
                        )
                );

                return ! is_null( $count );
        }

        /**
         * Get meta from db table and migrate from post meta if required.
         *
         * @param string $key Meta key.
         *
         * @return string
         */
        public function get_meta( $key ) {
                global $wpdb;

                self::maybe_create_table();
                $table_name    = self::get_table_name();
                $saved_content = '';

                $post_meta = $this->order->get_meta( $key, true );

                if ( ! empty( $post_meta ) ) {
                        $decoded_content = base64_decode( $post_meta, true );

                        if ( false !== $decoded_content ) {
                                $saved_content = $decoded_content;
                        } else {
                                $saved_content = $post_meta;
                        }

                        delete_post_meta( $this->order->get_id(), $key );
                        $this->order->delete_meta_data( $key );
                        $this->order->save_meta_data();
                        $this->add_meta( $key, $saved_content );

                        return $saved_content;
                }

                $maybe_saved_content_object = $wpdb->get_results(
                        $wpdb->prepare(
                                "SELECT saved_content, compression FROM {$wpdb->prefix}{$table_name} WHERE order_id = %d AND meta_key = %s LIMIT 1;",
                                $this->order->get_id(),
                                $key
                        ),
                        ARRAY_A
                );

                if ( isset( $maybe_saved_content_object[0] ) ) {
                        $maybe_saved_content = $maybe_saved_content_object[0]['saved_content'];
                        $compression         = intval( $maybe_saved_content_object[0]['compression'] );

                        if ( ! empty( $maybe_saved_content ) ) {
                                if ( 0 === $compression ) {
                                        $saved_content = $maybe_saved_content;
                                } else {
                                        $decompressed = gzuncompress( $maybe_saved_content );
                                        if ( false !== $decompressed ) {
                                                $saved_content = $decompressed;
                                        }
                                }
                        }
                }

                return $saved_content;
        }

        /**
         * Delete meta of this order from db table.
         *
         * @param string $key Meta key.
         *
         * @return int|bool
         */
        public function delete_meta( $key ) {
                global $wpdb;

                self::maybe_create_table();
                $table_name = self::get_table_name();

                delete_post_meta( $this->order->get_id(), $key );
                $this->order->delete_meta_data( $key );
                $this->order->save_meta_data();

                $result = $wpdb->query(
                        $wpdb->prepare(
                                "DELETE FROM {$wpdb->prefix}{$table_name} WHERE `order_id` = %d AND `meta_key` = %s;",
                                $this->order->get_id(),
                                $key
                        )
                );

                return $result;
        }
}
