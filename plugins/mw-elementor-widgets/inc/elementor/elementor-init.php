<?php
namespace MWEW\Inc\Elementor;

use MWEW\Inc\Elementor\Widgets\Listing_Grid\Listing_Grid_Widget;
use MWEW\Inc\Elementor\Widgets\Listing_Grid\Listing_Grid_Action;
use MWEW\Inc\Elementor\Widgets\Area_Map\Country_Map;
use MWEW\Inc\Elementor\Widgets\Hero_Slider\MW_Hero_Product_Fetch;
use MWEW\Inc\Elementor\Widgets\Loop_Carousel\Template_Loop_Carousel;
use MWEW\Inc\Elementor\Widgets\Hero_Slider\MW_Hero_Slider;
use MWEW\Inc\Logger\Logger;
class Elementor_Init{
    public function __construct(){
        
        add_action( 'elementor/widgets/register', [$this, 'register_widget'] );
        
        add_filter( 'wpml_elementor_widgets_to_translate', [$this, 'widget_translate'] );

        add_action('wp_enqueue_scripts', [$this, 'load_widget_styles']);
        add_action('wp_enqueue_scripts', [$this, 'load_widget_scripts']);

        new Listing_Grid_Action();
        new MW_Hero_Product_Fetch();

        do_action('wpml_register_single_string', 'mwew', 'Custom Content', 'Custom Content');
        do_action('wpml_register_single_string', 'mwew', 'Page URL Content', 'Page URL Content');
        do_action('wpml_register_single_string', 'mwew', 'WooCommerce Product', 'WooCommerce Product');

    }

    public function register_widget( $widgets_manager ) {
        $widgets_manager->register( new Template_Loop_Carousel() );
        $widgets_manager->register( new Country_Map() );
        $widgets_manager->register( new MW_Hero_Slider() );
        $widgets_manager->register( new Listing_Grid_Widget() );
    }


    public function widget_translate( $widgets_to_translate ) {

        $widgets_to_translate['book_hero_bg_slider'] = [
            'fields' => [
                [ 'field' => 'line_1_text', 'type' => 'Text 1', 'editor_type' => 'LINE' ],
                [ 'field' => 'line_2_text', 'type' => 'Text 2', 'editor_type' => 'LINE' ],
                [ 'field' => 'line_3_text', 'type' => 'Text 3', 'editor_type' => 'LINE' ],
                [ 'field' => 'line_4_text', 'type' => 'Text 4', 'editor_type' => 'LINE' ],
                [ 'field' => 'line_5_text', 'type' => 'Text 5', 'editor_type' => 'LINE' ],
                [ 'field' => 'line_6_text', 'type' => 'Text 6', 'editor_type' => 'LINE' ],
                [ 'field' => 'line_7_text', 'type' => 'Text 7', 'editor_type' => 'LINE' ],
                [ 'field' => 'check_in_label', 'type' => 'Check-in Label', 'editor_type' => 'LINE' ],
                [ 'field' => 'check_in_placeholder', 'type' => 'Check-in Placeholder', 'editor_type' => 'LINE' ],
                [ 'field' => 'check_out_label', 'type' => 'Check-out Label', 'editor_type' => 'LINE' ],
                [ 'field' => 'check_out_placeholder', 'type' => 'Check-out Placeholder', 'editor_type' => 'LINE' ],
                [ 'field' => 'search_button_text', 'type' => 'Search Button Text', 'editor_type' => 'LINE' ],
            ],
            'fields_in_item' => [
                'slides' => [
                    [ 'field' => 'image_alt', 'type' => 'Slide Image Alt Text', 'editor_type' => 'LINE' ],
                    [ 'field' => 'mobile_image_alt', 'type' => 'Slide Mobile Image Alt Text', 'editor_type' => 'LINE' ],
                ],
                'mobile_buttons' => [
                    [ 'field' => 'button_text', 'type' => 'Mobile Button Text', 'editor_type' => 'LINE' ],
                    [ 'field' => 'button_link.url', 'type' => 'Mobile Button Link URL', 'editor_type' => 'LINE' ],
                    [ 'field' => 'button_icon_label', 'type' => 'Mobile Button Icon Label', 'editor_type' => 'LINE' ],
                    [ 'field' => 'modal_title', 'type' => 'Modal Title', 'editor_type' => 'LINE' ],
                    [ 'field' => 'modal_content', 'type' => 'Modal Content', 'editor_type' => 'VISUAL' ],
                    [ 'field' => 'modal_page_url.url', 'type' => 'Modal Page URL', 'editor_type' => 'LINE' ],
                    [ 'field' => 'modal_product', 'type' => 'Modal Product ID or URL', 'editor_type' => 'LINE' ],
                ]
            ],
        ];


        $widgets_to_translate['mw_country_map'] = [
            'fields' => [
                [
                    'field'       => 'section_title',
                    'type'        => 'Section Title',
                    'editor_type' => 'LINE',
                ],
            ],
            'fields_in_item' => [
                'tabs_list' => [
                    [
                        'field'       => 'tab_title',
                        'type'        => 'Map Tab Title',
                        'editor_type' => 'LINE',
                    ],
                    [
                        'field'       => 'title',
                        'type'        => 'Map Title',
                        'editor_type' => 'LINE',
                    ],
                    [
                        'field'       => 'description',
                        'type'        => 'Map Description',
                        'editor_type' => 'AREA',
                    ],
                ],
            ],
        ];

        $widgets_to_translate['mw_loop_carousel'] = [
            'fields' => [
                [
                    'field'       => 'section_title',
                    'type'        => 'Section Title',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'button_text',
                    'type'        => 'Button Text',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'button_link.url',
                    'type'        => 'Button Link URL',
                    'editor_type' => 'LINE',
                ],
            ],
        ];


        $widgets_to_translate['listing_grid_widget'] = [
            'fields' => [
                [
                    'field'       => 'title_text',
                    'type'        => 'Section Title',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'all_text',
                    'type'        => 'All button text',
                    'editor_type' => 'LINE',
                ],
            ],
        ];

        return $widgets_to_translate;
    }

    public function load_widget_styles(){

        wp_register_style( 'mw-hero-slider', MWEW_PATH_URL . 'inc/elementor/widgets/hero-slider/css/mw-slider.css', [], wp_rand(), 'all' );
        
        wp_register_style('mw-country-map', MWEW_PATH_URL . 'inc/elementor/widgets/area-map/css/styles.css', [], wp_rand(), 'all');
        
        wp_register_style('mw-listing-grid', MWEW_PATH_URL . 'inc/elementor/widgets/listing-grid/css/listing-grid.css', [], wp_rand(), 'all');
    }


    public function load_widget_scripts() {

        wp_register_script('mw-hero-slider', MWEW_PATH_URL . 'inc/elementor/widgets/hero-slider/js/widgets.js', ['jquery'], wp_rand(), false);
        wp_register_script('mw-hero-modal', MWEW_PATH_URL . 'inc/elementor/widgets/hero-slider/js/modal.js', ['jquery'], wp_rand(), false);

        wp_register_script('mw-country-map', MWEW_PATH_URL . 'inc/elementor/widgets/area-map/js/main.js', [], wp_rand());
        
        wp_register_script('mw-listing-grid', MWEW_PATH_URL . 'inc/elementor/widgets/listing-grid/js/listing-grid.js', [], wp_rand());
    }


}





