<?php 
namespace MWEW\Inc\Database;

class Listing_Maps_DB {

    private static $table_name = 'mw_listing_maps';
    private static $version = MWEW_DB_VERSION;

    private static function create_table() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            region_id BIGINT(20) UNSIGNED NOT NULL,
            map_data LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        maybe_create_table($table,$sql);

        update_option('listing_maps_db_version', self::$version);
    }

    public static function insert($data) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        $wpdb->insert($table, [
            'region_id' => $data['region_id'],
            'map_data'  => maybe_serialize($data['map_data']),
        ]);

        return $wpdb->insert_id;
    }

    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        return $wpdb->update(
            $table,
            [
                'region_id' => $data['region_id'],
                'map_data'  => maybe_serialize($data['map_data']),
            ],
            ['id' => $id]
        );
    }

    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);
    }

    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
        if ($row) {
            $row['map_data'] = maybe_unserialize($row['map_data']);
        }

        return $row;
    }

    public static function delete_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        return $wpdb->delete($table, ['id' => $id]);
    }

    public static function drop_table() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        $wpdb->query("DROP TABLE IF EXISTS $table");
        delete_option('listing_maps_db_version');
    }

    public static function maybe_upgrade() {
        $installed_version = get_option('listing_maps_db_version', '0.0.0');
        if ($installed_version !== self::$version) {
            self::create_table();
        }
    }
}
