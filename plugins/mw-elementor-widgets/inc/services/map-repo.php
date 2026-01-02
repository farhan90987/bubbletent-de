<?php 

namespace MWEW\Inc\Services;

use MWEW\Inc\Database\Listing_Maps_DB;
use MWEW\Inc\Logger\Logger;

class Map_Repo{

    public static function get_map_id_title(){
        $listing_map = Listing_Maps_DB::get_all();
        
        $map_data = [];

        foreach($listing_map as $map){
            $term = get_term($map['region_id'], 'region');

            if (is_wp_error($term) || !$term) {
                continue;
            }

            $image_id = get_term_meta($term->term_id, 'map_image', true);
            $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
            
            if($image_url){
                $term = get_term($map['region_id'], 'region');
                $region_name = $term ? $term->name : '—';
                $map_data[$map['id']] = $region_name;
            }
            
        }

        return $map_data;
    }

    public static function get_region_by_id($id) {
        $term = get_term($id, 'region');

        if (is_wp_error($term) || !$term) {
            return null;
        }

        $image_id = get_term_meta($term->term_id, 'map_image', true);
        $image_url = $image_id ? wp_get_attachment_url($image_id) : '';

        return [
            'id'    => $term->term_id,
            'name'  => $term->name,
            'slug'  => $term->slug,
            'image' => $image_url,
        ];
    }

    public static function get_listing_info_by_id($map_id) {
        $listing_map = Listing_Maps_DB::get_by_id($map_id);
        $listing_list = [];

        if (!$listing_map || empty($listing_map['map_data']) || !is_array($listing_map['map_data'])) {
            return $listing_list;
        }
        foreach ($listing_map['map_data'] as $point) {
            if (!isset($point['dataId'])) continue;

            $listing_id = intval($point['dataId']);
            if (!$listing_id || get_post_type($listing_id) !== 'listing') continue;

            $woocommerce_id = get_post_meta($listing_id, 'product_id', true);
            
            $date_range = Calendar_Availability::get_first_avail_date($listing_id);

            $listing_list[] = [
                'id' => strval($listing_id),
                'name' => __(get_the_title($listing_id), 'mwew'),
                'location' => __(get_post_meta($listing_id, '_friendly_address', true), 'mwew') ?: '',
                'price' => __(get_post_meta($woocommerce_id, 'sa_cfw_cog_amount', true), 'mwew') ?: '',
                'currency' => __(get_option('woocommerce_currency', '€'), 'mwew'),
                'unit' => __('Night', 'mwew'),
                'date_range' => $date_range,
                'image' => get_the_post_thumbnail_url($listing_id, 'full') ?: '',
                'url' => get_the_permalink($listing_id),
            ];
        }

        return $listing_list;
    }

    public static function get_listing_title_by_id($data) {
        foreach ($data['map_data'] as $point) {
            if (!isset($point['dataId'])) continue;

            $listing_id = intval($point['dataId']);
            if (!$listing_id || get_post_type($listing_id) !== 'listing') continue;

            $listing_list[] = [
                'id' => (string) $listing_id,
                'regionId' => (string) $data['region_id'],
                'name' => __(get_the_title($listing_id), 'mwew'),
            ];
        }

        return $listing_list;
    }

}