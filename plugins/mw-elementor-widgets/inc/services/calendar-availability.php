<?php 

namespace MWEW\Inc\Services;
use DateTime;
use MWEW\Inc\Logger\Logger;

class Calendar_Availability{

    private static function get_busy_days($listing_id, $is_product_id = false) {
		global $wpdb;

        if($is_product_id){
            $woocommerce_id = $listing_id;
        } else {
            $woocommerce_id = get_post_meta($listing_id, 'product_id', true);
        }

        $product = wc_get_product($woocommerce_id);
        if (!$product) {
            return null;
        }

        $property_id = wc_get_product($woocommerce_id)->get_meta('custom_property_id_field');

		if ( ! empty( $property_id ) ) {
			$days = wp_cache_get( 'smoobu_busy_days_' . $property_id );
			if ( false === $days ) {
				// phpcs:ignore
				$days = json_decode(
					$wpdb->get_col(
						$wpdb->prepare(
							"SELECT busy_dates FROM {$wpdb->prefix}smoobu_calendar_availability WHERE property_id = %d ORDER BY busy_dates ASC",
							$property_id
						)
					)[0],
					true
				);
				wp_cache_set( 'smoobu_busy_days_' . $property_id, $days );
			}  

			$days = self::apply_lead_time_to_busy_days($days, $woocommerce_id);

			$today = gmdate('Y-m-d');

            $days = array_filter($days, function($d) use ($today) {
                return $d >= $today;
            });

            $days = array_values($days);
            sort($days);

            return $days;

		}
	}
    private static function get_date_range($start_date, $end_date) {
        $dates = [];
        $current = strtotime($start_date);
        $end = strtotime('-1 day', strtotime($end_date));

        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        return $dates;
    }


    public static function is_available($listing_id, $check_in, $check_out){

         if (get_post_status($listing_id) !== 'publish') {
            return false;
        }

        $checkin_date = isset($check_in) ? sanitize_text_field($check_in) : '';
        $checkout_date = isset($check_out) ? sanitize_text_field($check_out) : '';

        if (!self::validate_date($checkin_date) || !self::validate_date($checkout_date)) {
            return false;
        }

        $date_range = self::get_date_range($checkin_date, $checkout_date);
        
        $all_available = true;

        $all_busy_dates = self::get_busy_days($listing_id);

        if($all_busy_dates == null) return $all_available;

        foreach ($date_range as $date) {
            if (in_array($date, $all_busy_dates)) {
                $all_available = false;
                break;
            }
        }

        return $all_available;
    }

    public static function validate_date($date) {
        $formats = [
            'd-m-Y',
            'd.m.Y',
            'Y-m-d',
            'Y.m.d',
            'm-d-Y',
            'm.d.Y'
        ];


        foreach ($formats as $format) {
            $d = DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) === $date) {
                return true;
            }
        }

        return false;
    }

    public static function get_first_avail_date($listing_id) {

        $booked_dates = self::get_busy_days($listing_id);

        if($booked_dates == null) return date_i18n(get_option('date_format'), current_time('timestamp'));

        $booked_dates = array_unique($booked_dates);
        sort($booked_dates);

        $today = new DateTime();

        $start_date = $today;

        $max_days = 365;
        $booked_set = array_flip($booked_dates);

        for ($i = 0; $i < $max_days; $i++) {
            $check_date = (clone $start_date)->modify("+$i day")->format('Y-m-d');
            if (!isset($booked_set[$check_date])) {
                return $check_date;
            }
        }

        return date_i18n(get_option('date_format'), current_time('timestamp'));
    }


    private static function apply_lead_time_to_busy_days($days, $woo_product_id) {
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
			return $days;
		}

        if(!empty(WC()->cart)){
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product = $cart_item['data'];
                if ($product->is_type('listing_booking')) {
                    $lead_time = intval($product->get_meta('min_lead_time'));
                    if ($lead_time > 0) {
                        $lead_time_dates = array_map(
                            function ($x) {
                                return gmdate('Y-m-d', strtotime("+$x days"));
                            },
                            range(0, $lead_time)
                        );

                        $days = array_unique(array_merge($lead_time_dates, $days));
                        sort($days);
                        break;
                    }
                }
            }
        }

        $listing_product = wc_get_product($woo_product_id);

        if (!empty($listing_product)) {
            if ($listing_product->is_type('listing_booking')) {
                $lead_time = intval($listing_product->get_meta('min_lead_time'));

                if ($lead_time > 0) {
                    $lead_time_dates = array_map(
                        function ($x) {
                            return gmdate('Y-m-d', strtotime("+$x days"));
                        },
                        range(0, $lead_time)
                    );

                    $days = array_unique(array_merge($lead_time_dates, $days));
                    sort($days);
                }
            }
        } 

		return $days;
    }

    public static function get_busy_dates_by_order_id($order_id){

        if ( ! $order_id || ! is_numeric( $order_id ) ) {
            return [];
        }

        $order = wc_get_order( $order_id );

        if ( ! $order || ! $order->get_items() ) {
            return [];
        }

        $busy_dates = [];
        foreach ( $order->get_items() as $item_id => $item ) {
            $product_id = $item->get_product_id();
            $busy_dates[] = self::get_busy_days($product_id, true);
        }

        return array_unique(array_merge(...$busy_dates));
    }

}