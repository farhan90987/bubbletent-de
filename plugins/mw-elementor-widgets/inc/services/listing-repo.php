<?php 

namespace MWEW\Inc\Services;

class Listing_Repo{
    public static function get_countries_by_region() {
        $terms = get_terms([
            'taxonomy' => 'region',
            'hide_empty' => true,
        ]);

        $countries = [];

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $countries[$term->term_id] = $term->name;
            }
        }

        return $countries;
    }


    public static function get_listing_features_posts() {
        $options = [];

        $current_lang = function_exists('apply_filters') ? apply_filters('wpml_current_language', null) : null;

        $args = [
            'post_type'        => 'features',
            'posts_per_page'   => -1,
            'post_status'      => 'publish',
            'orderby'          => 'title',
            'order'            => 'ASC',
            'suppress_filters' => false,
        ];

        $posts = get_posts($args);

        foreach ($posts as $post) {
            $translated_id = function_exists('wpml_object_id')
                ? apply_filters('wpml_object_id', $post->ID, 'features', false, $current_lang)
                : $post->ID;

            $translated_post = get_post($translated_id);
            if ($translated_post) {
                $options[$translated_id] = $translated_post->post_title;
            }
        }

        return $options;
    }

}