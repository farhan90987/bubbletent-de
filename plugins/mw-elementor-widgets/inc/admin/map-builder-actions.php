<?php 
namespace MWEW\Inc\Admin;

use MWEW\Inc\Database\Listing_Maps_DB;
use WP_Query;

class Map_Builder_Actions {
    public function __construct() {
        add_action('wp_ajax_get_locations_by_region', [$this, 'get_locations_by_region']);
        
        add_action('wp_ajax_save_map_data', [$this, 'save_map_location']);

        add_action('wp_ajax_mw_delete_map', [$this, 'delete_map_data']);
        
    }

    public function get_locations_by_region() {
        $term_id = intval($_GET['term_id']);

        $args = [
            'post_type' => 'listing',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'region',
                    'field' => 'term_id',
                    'terms' => $term_id,
                ],
            ],
        ];

        $query = new WP_Query($args);
        $results = [];

        foreach ($query->posts as $post) {
            $results[] = [
                'id' => $post->ID,
                'title' => html_entity_decode(get_the_title($post->ID)),
            ];
        }

        wp_send_json($results);
    }

    public function save_map_location(){
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $region_id = isset($_POST['region_id']) ? intval($_POST['region_id']) : 0;
        $map_data = isset($_POST['map_data']) ? wp_unslash($_POST['map_data']) : '';
        $map_id = isset($_POST['map_id']) ? intval($_POST['map_id']) : 0;

        if (!$region_id || !$map_data) {
            wp_send_json_error('Missing data');
        }
        

        if($map_id == 0){
            $id = Listing_Maps_DB::insert([
                'region_id' => $region_id,
                'map_data'  => json_decode($map_data, true),
            ]);
        }else{
            $id = Listing_Maps_DB::update($map_id, [
                'region_id' => $region_id,
                'map_data'  => json_decode($map_data, true),
            ]);
        }
        wp_send_json_success(['id' => $id, 'map_data' => $map_data]);
    }


    public function delete_map_data() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $map_id = intval($_POST['map_id'] ?? 0);

        if (!$map_id) {
            wp_send_json_error('No map ID provided.');
        }

        $deleted = Listing_Maps_DB::delete_by_id($map_id);

        if ($deleted) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete map.');
        }
    }

}