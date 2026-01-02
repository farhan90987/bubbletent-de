<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPWOOF_Logger {

	const LOG_TYPE_GENERAL = 'general';
	const LOG_TYPE_FEED = 'feed';
	const LOG_TYPE_MIGRATION = 'migration';

	const LOG_LEVEL_INFO = 'info';
	const LOG_LEVEL_WARNING = 'warning';
	const LOG_LEVEL_ERROR = 'error';

	private static $instance = null;

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
	}

	public function log( $message, $type = self::LOG_TYPE_GENERAL, $feed_id = null, $level = self::LOG_LEVEL_INFO ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wpwoof_logs';

		$wpdb->insert(
			$table_name,
			array(
				'log_type' => $type,
				'feed_id' => $feed_id,
				'message' => $message,
				'log_level' => $level,
				'created_at' => current_time( 'mysql' )
			),
			array( '%s', '%d', '%s', '%s', '%s' )
		);
	}

	public function get_logs( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'type' => null,
			'feed_id' => null,
			'level' => null,
			'limit' => 100,
			'offset' => 0,
			'order_by' => 'created_at',
			'order' => 'DESC'
		);

		$args = wp_parse_args( $args, $defaults );

		$table_name = $wpdb->prefix . 'wpwoof_logs';
		$where = array( '1=1' );

		if ( $args['type'] ) {
			$where[] = $wpdb->prepare( 'log_type = %s', $args['type'] );
		}

		if ( $args['feed_id'] ) {
			$where[] = $wpdb->prepare( 'feed_id = %d', $args['feed_id'] );
		}

		if ( $args['level'] ) {
			$where[] = $wpdb->prepare( 'log_level = %s', $args['level'] );
		}

		$where_clause = implode( ' AND ', $where );
		$order_by = in_array( $args['order_by'], array( 'id', 'log_type', 'feed_id', 'log_level', 'created_at' ) ) 
			? $args['order_by'] 
			: 'created_at';
		$order = $args['order'] === 'ASC' ? 'ASC' : 'DESC';

		$sql = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY {$order_by} {$order} LIMIT %d OFFSET %d";

		return $wpdb->get_results( $wpdb->prepare( $sql, $args['limit'], $args['offset'] ), ARRAY_A );
	}

	public function count_logs( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'type' => null,
			'feed_id' => null,
			'level' => null
		);

		$args = wp_parse_args( $args, $defaults );

		$table_name = $wpdb->prefix . 'wpwoof_logs';
		$where = array( '1=1' );

		if ( $args['type'] ) {
			$where[] = $wpdb->prepare( 'log_type = %s', $args['type'] );
		}

		if ( $args['feed_id'] ) {
			$where[] = $wpdb->prepare( 'feed_id = %d', $args['feed_id'] );
		}

		if ( $args['level'] ) {
			$where[] = $wpdb->prepare( 'log_level = %s', $args['level'] );
		}

		$where_clause = implode( ' AND ', $where );

		$sql = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";

		return (int) $wpdb->get_var( $sql );
	}

	public function clear_feed_generation_logs( $feed_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wpwoof_logs';

		$generation_count_key = 'wpwoof_feed_generation_count_' . $feed_id;
		$generation_count = (int) get_transient( $generation_count_key );
		$generation_count++;

		set_transient( $generation_count_key, $generation_count, WEEK_IN_SECONDS );

		if ( $generation_count >= 3 ) {
			$wpdb->delete(
				$table_name,
				array(
					'log_type' => self::LOG_TYPE_FEED,
					'feed_id' => $feed_id
				),
				array( '%s', '%d' )
			);
			delete_transient( $generation_count_key );
		}
	}

	public function clear_old_general_logs() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wpwoof_logs';
		$one_year_ago = date( 'Y-m-d H:i:s', strtotime( '-1 year' ) );

		$wpdb->query( 
			$wpdb->prepare( 
				"DELETE FROM {$table_name} WHERE log_type IN (%s, %s) AND created_at < %s",
				self::LOG_TYPE_GENERAL,
				self::LOG_TYPE_MIGRATION,
				$one_year_ago
			)
		);
	}

	public function clear_all_logs( $type = null, $feed_id = null ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wpwoof_logs';

		$where = array();
		$where_format = array();

		if ( $type ) {
			$where['log_type'] = $type;
			$where_format[] = '%s';
		}

		if ( $feed_id ) {
			$where['feed_id'] = $feed_id;
			$where_format[] = '%d';
		}

		if ( empty( $where ) ) {
			$wpdb->query( "TRUNCATE TABLE {$table_name}" );
		} else {
			$wpdb->delete( $table_name, $where, $where_format );
		}
	}

	public static function create_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wpwoof_logs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			log_type varchar(50) NOT NULL,
			feed_id bigint(20) unsigned DEFAULT NULL,
			message text NOT NULL,
			log_level varchar(20) NOT NULL DEFAULT 'info',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY log_type (log_type),
			KEY feed_id (feed_id),
			KEY log_level (log_level),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
