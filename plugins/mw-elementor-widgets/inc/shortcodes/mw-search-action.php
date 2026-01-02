<?php

namespace MWEW\Inc\Shortcodes;


class MW_Search_Action {

    public function __construct(){
        add_action('wp_ajax_nopriv_mwew_get_listings', [$this, 'ajax_get_listings']);
        add_action('wp_ajax_mwew_get_listings', [$this, 'ajax_get_listings']);
    }

    public function ajax_get_listings() {
        check_ajax_referer('mwew_plugin_nonce', 'security');

        $page = isset($_REQUEST['page']) ? max(1, intval($_REQUEST['page'])) : 1;
        $posts_per_page = 5;

        $radius     = isset($_REQUEST['search_radius']) ? sanitize_text_field(stripslashes($_REQUEST['search_radius'])) : '';
        $check_in   = isset($_REQUEST['check_in']) ? sanitize_text_field($_REQUEST['check_in']) : '';
        $check_out  = isset($_REQUEST['check_out']) ? sanitize_text_field($_REQUEST['check_out']) : '';
        $country_id = isset($_REQUEST['country_id']) ? sanitize_text_field($_REQUEST['country_id']) : '';

        $query_args = [
            'search_radius' => $radius,
            'check_in'      => $check_in,
            'check_out'     => $check_out,
            'country_id'    => $country_id,
            'posts_per_page'=> $posts_per_page,
            'paged'         => $page,
        ];

        $taxonomy_objects = get_object_taxonomies('listing', 'objects');
        foreach ($taxonomy_objects as $tax) {
            $key = 'tax-' . $tax->name;
            if (isset($_REQUEST[$key])) {
                $query_args[$key] = $_REQUEST[$key];
            }
        }

        $available_query_vars = $this->build_available_query_vars();
        foreach ($available_query_vars as $meta_key) {
            if (isset($_REQUEST[$meta_key]) && $_REQUEST[$meta_key] != -1) {
                $query_args[$meta_key] = $_REQUEST[$meta_key];
            }
        }

        $listings = Listeo_Core_Listing::get_real_listings($query_args);

        $listing_count = $listings->found_posts ?? 0;

        ob_start();

        if ($listings->have_posts()) {
            $style = 'list';
            $grid_columns = 2;
            $style_data = [
                'style' => $style,
                'grid_columns' => $grid_columns,
                'max_num_pages' => $listings->max_num_pages,
                'counter' => $listings->found_posts,
            ];
            ?>
            <div class="loader-ajax-container"> <div class="loader-ajax"></div> </div>
            <?php
            while ($listings->have_posts()) {
                $listings->the_post();
                $post_id = get_the_ID();

                // Render listing template
                $template_loader = new \Listeo_Core_Template_Loader;
                $template_loader->set_template_data($style_data)->get_template_part('content-listing', $style);
            }
            ?>
            <div class="clearfix"></div>
            </div>
            <?php
            wp_reset_postdata();
        } else {
            ?>
            <div class="loader-ajax-container"> <div class="loader-ajax"></div></div>
            <div id="listeo-listings-container">
                <div class="loader-ajax-container"> <div class="loader-ajax"></div> </div>
                <section id="listings-not-found" class="margin-bottom-50 col-md-12">
                    <h2><?php esc_html_e('Nothing Found', 'mwew'); ?></h2>
                    <p><?php _e('Unfortunately, we didn\'t find any results matching your search. Please try changing your search settings.', 'mwew'); ?></p>
                </section>
            </div>
            <div class="clearfix"></div>
            <?php
        }

        $result = [
            'found_listings' => $listing_count > 0,
            'max_num_pages' => $listings->max_num_pages,
            'html' => ob_get_clean(),
            'pagination' => $listing_count > 0 ? listeo_core_ajax_pagination($listings->max_num_pages, $page) : '',
        ];

        wp_send_json($result);
    }

    private static function build_available_query_vars() {
        $query_vars = [];
        $taxonomy_objects = get_object_taxonomies('listing', 'objects');
        foreach ($taxonomy_objects as $tax) {
            $query_vars[] = 'tax-' . $tax->name;
        }

        // Collect all meta box fields
        $meta_boxes = [
            \Listeo_Core_Meta_Boxes::meta_boxes_service(),
            \Listeo_Core_Meta_Boxes::meta_boxes_location(),
            \Listeo_Core_Meta_Boxes::meta_boxes_event(),
            \Listeo_Core_Meta_Boxes::meta_boxes_prices(),
            \Listeo_Core_Meta_Boxes::meta_boxes_contact(),
            \Listeo_Core_Meta_Boxes::meta_boxes_rental(),
            \Listeo_Core_Meta_Boxes::meta_boxes_custom(),
        ];

        foreach ($meta_boxes as $box) {
            foreach ($box['fields'] as $field) {
                $query_vars[] = $field['id'];
            }
        }

        $query_vars[] = '_price_range';
        $query_vars[] = '_listing_type';
        $query_vars[] = '_price';
        $query_vars[] = '_max_guests';
        $query_vars[] = '_min_guests';
        $query_vars[] = '_instant_booking';

        return $query_vars;
    }
}