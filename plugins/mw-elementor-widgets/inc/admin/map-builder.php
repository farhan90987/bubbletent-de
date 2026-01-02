<?php 
namespace MWEW\Inc\Admin;

use MWEW\Inc\Admin\Templates\Map_Builder_Template;
use MWEW\Inc\Admin\Templates\Map_Builder_List;
use WP_Query;

class Map_Builder {
    public function __construct() {
        add_action('admin_menu', [ $this, 'register_admin_menu' ]);
    }

    public function register_admin_menu() {
        add_menu_page(
            'MW Map Builder',
            'MW Map Builder',
            'manage_options',
            'mw-map-builder',
            [ $this, 'render_admin_page' ],
            'dashicons-location-alt',
            80
        );
    }

    public function render_admin_page() {
        echo '<div class="wrap">';
        echo '<div class="flex flex-row justify-between item-center mb-4">';
            echo '<h1>Map Builder</h1>';
            echo '<a href="' . admin_url('admin.php?page=mw-map-builder&action=add') . '" class="inline-block px-4 py-2 border border-blue-500 text-blue-500 rounded hover:bg-blue-500 hover:text-white transition">+ Add New Map</a>';
        echo '</div>';
        echo '<hr>';
        Map_Builder_List::render();
        echo '</div>';
    }

}