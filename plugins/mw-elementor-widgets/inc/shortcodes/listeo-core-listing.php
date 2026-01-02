<?php 
namespace MWEW\Inc\Shortcodes;

use MWEW\Inc\Services\Calendar_Availability;

class Listeo_Core_Listing {

    private static $_instance = null;

    public function __construct() {
        add_filter('query_vars', [$this, 'add_query_vars']);
    }

    public function add_query_vars($vars) {
        $new_vars = ['date_range', 'keyword_search', 'location_search', 'listeo_core_order', 'search_radius', 'radius_type'];
        return array_merge($new_vars, $vars);
    }

    /**
     * Get listings with availability filtering and pagination applied.
     *
     * @param array $args Query arguments and filters.
     * @return \WP_Query Filtered and paginated WP_Query object.
     */
    public static function get_real_listings($args) {
        global $wpdb;

        $paged = isset($args['paged']) ? max(1, intval($args['paged'])) : max(1, get_query_var('paged', 1));
        $posts_per_page = isset($args['posts_per_page']) ? intval($args['posts_per_page']) : 10;

        $ordering_args = self::get_listings_ordering_args($args['listeo_orderby'] ?? '');

        $query_args = [
            'post_type'           => 'listing',
            'post_status'         => 'publish',
            'ignore_sticky_posts' => 1,
            'posts_per_page'      => -1,   // fetch all posts for filtering availability
            'orderby'             => $ordering_args['orderby'],
            'order'               => $ordering_args['order'],
            'tax_query'           => [],
            'meta_query'          => [],
        ];

        if (isset($args['offset'])) {
            $query_args['offset'] = intval($args['offset']);
        }
        if (!empty($ordering_args['meta_type'])) {
            $query_args['meta_type'] = $ordering_args['meta_type'];
        }
        if (!empty($ordering_args['meta_key']) && $ordering_args['meta_key'] !== '_featured') {
            $query_args['meta_key'] = $ordering_args['meta_key'];
        }

        if (!empty($args['country_id'])) {
            $country_id = absint($args['country_id']);
            $query_args['tax_query'][] = [
                'taxonomy' => 'region',
                'field'    => 'term_id',
                'terms'    => $country_id,
            ];
        }

        $query = new \WP_Query($query_args);

        $check_in = $args['check_in'] ?? null;
        $check_out = $args['check_out'] ?? null;

        $available_posts = [];
        foreach ($query->posts as $post) {
            if (Calendar_Availability::is_available($post->ID, $check_in, $check_out)) {
                $available_posts[] = $post;
            }
        }

        $total_available = count($available_posts);

        $offset = ($paged - 1) * $posts_per_page;
        $paged_posts = array_slice($available_posts, $offset, $posts_per_page);

        $query->posts = $paged_posts;
        $query->post_count = count($paged_posts);
        $query->found_posts = $total_available;
        $query->max_num_pages = max(1, ceil($total_available / $posts_per_page));
        $query->query_vars['paged'] = $paged;
        $query->query_vars['posts_per_page'] = $posts_per_page;

        return $query;
    }

    /**
     * Get ordering arguments for listings query.
     *
     * @param string $orderby Optional orderby string.
     * @param string $order Optional order direction.
     * @return array Associative array with 'orderby', 'order', and optionally 'meta_key' and 'meta_type'.
     */
    private static function get_listings_ordering_args($orderby = '', $order = '') {
        if ($orderby) {
            $orderby_value = $orderby;
        } else {
            $orderby_value = isset($_GET['listeo_core_order']) ? (string) $_GET['listeo_core_order'] : get_option('listeo_sort_by', 'date');
        }

        $parts = explode('-', $orderby_value);
        $orderby = esc_attr($parts[0]);
        $order = !empty($parts[1]) ? $parts[1] : $order;

        $args = [
            'orderby'  => 'date ID',
            'order'    => ('desc' === strtolower($order)) ? 'DESC' : 'ASC',
            'meta_key' => '',
        ];

        switch ($orderby) {
            case 'rand':
                $args['orderby'] = 'rand';
                break;
            case 'featured':
                $args['orderby']  = 'meta_value_num date';
                $args['meta_key'] = '_featured';
                break;
            case 'verified':
                $args['orderby']  = 'meta_value_num';
                $args['meta_key'] = '_verified';
                break;
            case 'date':
                $args['orderby'] = 'date';
                $args['order']   = ('asc' === strtolower($order)) ? 'ASC' : 'DESC';
                break;
            case 'highest-rated':
            case 'highest':
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                $args['meta_type'] = 'NUMERIC';
                $args['meta_key'] = 'listeo-avg-rating';
                break;
            case 'views':
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                $args['meta_type'] = 'NUMERIC';
                $args['meta_key'] = '_listing_views_count';
                break;
            case 'upcoming':
            case 'upcoming-event':
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                $args['meta_key'] = '_event_date_timestamp';
                break;
            case 'reviewed':
                $args['orderby'] = 'comment_count';
                $args['order']   = 'DESC';
                break;
            case 'title':
                $args['orderby'] = 'title';
                $args['order']   = ('desc' === strtolower($order)) ? 'DESC' : 'ASC';
                break;
            case 'near':
                $args['orderby'] = 'post__in';
                break;
            default:
                $args['orderby'] = 'date ID';
                $args['order']   = ('asc' === strtolower($order)) ? 'ASC' : 'DESC';
                break;
        }

        return apply_filters('listeo_core_get_listings_ordering_args', $args);
    }
}
