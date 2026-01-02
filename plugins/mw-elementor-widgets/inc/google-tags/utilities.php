<?php 

namespace MWEW\Inc\Google_Tags;

class Utilities {
    public static function is_tracking_enabled() {
        $settings = get_option( 'mwew_gtm_ga4_settings', [] );
        return !empty($settings['enable_event_tracking']);
    }

    public static function is_thank_you_page() {
        return is_page('thank-you') || is_wc_endpoint_url('order-received');
    }

    // Other reusable helpers...
}
