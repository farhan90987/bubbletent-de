<?php
namespace MWEW\Inc\Elementor\Widgets\Listing_Grid;

use MWEW\Inc\Logger\Logger;

class Listing_Grid_Action {

    public function __construct() {
        add_action( 'wp_ajax_filter_listing_by_feature_and_country', [ $this, 'handle_filter_listing_by_feature_and_country' ] );
        add_action( 'wp_ajax_nopriv_filter_listing_by_feature_and_country', [ $this, 'handle_filter_listing_by_feature_and_country' ] );
    }

    public function handle_filter_listing_by_feature_and_country() {
        $feature_id = sanitize_text_field($_GET['feature_id'] ?? 'all');
        $country_id = sanitize_text_field($_GET['country_id'] ?? '');
        $max        = absint($_GET['max'] ?? 6);

        $args = [
            'post_type'      => 'listing',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'suppress_filters' => false,
        ];

        if ($feature_id !== 'all') {
            $feature_id = absint($feature_id);
            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key'     => 'highlight_features',
                    'value'   => '"' . $feature_id . '"',
                    'compare' => 'LIKE',
                ],
                [
                    'key'     => 'listing_features',
                    'value'   => '"' . $feature_id . '"',
                    'compare' => 'LIKE',
                ],
            ];

            $args['posts_per_page'] = $max;
        }

        if (!empty($country_id)) {
            $country_id = absint($country_id);

            if (function_exists('icl_object_id')) {
                $country_id = apply_filters('wpml_object_id', $country_id, 'region', true);
            }

            $args['tax_query'] = [
                [
                    'taxonomy' => 'region',
                    'field'    => 'term_id',
                    'terms'    => $country_id,
                ],
            ];
        }

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $post_id = get_the_ID();

                if (function_exists('wpml_object_id')) {
                    $post_id = apply_filters('wpml_object_id', $post_id, 'listing', true);
                }

                $img       = get_the_post_thumbnail_url($post_id, 'large');
                $permalink = get_permalink($post_id);
                $title     = get_the_title($post_id);

                echo '<a href="' . esc_url($permalink) . '" class="listing-card">';
                echo '<div class="listing-image" style="background:url(' . esc_url($img) . ')"></div>';
                echo '<div class="listing-card-location">';
                echo '<img src="' . esc_url(MWEW_PATH_URL . 'assets/images/Location.svg') . '" /> <span class="listing-title">' . esc_html($title) . '</span>';
                echo '</div></a>';
            }
        } else {
            echo '<p class="mwew-not-found">' . esc_html__('No listings found.', 'mwew') . '</p>';
        }

        wp_die();
    }


}
