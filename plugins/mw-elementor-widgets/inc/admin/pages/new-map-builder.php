<?php 
namespace MWEW\Inc\Admin\Pages;

use MWEW\Inc\Admin\Templates\Map_Builder_Template;

class New_Map_Builder {
    public function __construct() {
        add_action('admin_menu', [ $this, 'register_admin_menu' ]);
    }

    public function register_admin_menu() {
        add_submenu_page(
            'mw-map-builder',
            'New Map',
            'New Map',
            'manage_options',
            'mw-new-map-builder',
            [ $this, 'render_admin_page' ],
            80
        );
    }

    public function render_admin_page() {
        $regions = $this->get_all_regions();
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        $map_id = isset($_GET['map_id']) ? $_GET['map_id'] : 0;


        echo '<div class="wrap">';
        echo '<div class="flex flex-row justify-between item-center mb-4">';
            echo '<h1>Map Builder</h1>';
            echo '<a href="' . admin_url('admin.php?page=mw-map-builder') . '" class="inline-block px-4 py-2 border border-red-500 text-red-500 rounded hover:bg-red-500 hover:text-white transition">← Back to Map List</a>';
        echo '</div>';
        echo '<hr>';
        echo '<div id="map-loader">';
        echo '<div class="map-loader-inner">';
        echo '</div>';
        echo '</div>';
            Map_Builder_Template::render($regions, $map_id);
        echo '</div>';
    }

    private function get_all_regions() {
        $args = [
            'taxonomy'   => 'region',
            'hide_empty' => false,
        ];

        $terms = get_terms($args);
        $regions = [];

        foreach ($terms as $term) {
            $image_id = get_term_meta($term->term_id, 'map_image', true);
            $image_url = $image_id ? wp_get_attachment_url($image_id) : '';

            $regions[] = [
                'id'    => $term->term_id,
                'name'  => $term->name,
                'slug'  => $term->slug,
                'image' => $image_url,
            ];
        }

        return $regions;
    }

}