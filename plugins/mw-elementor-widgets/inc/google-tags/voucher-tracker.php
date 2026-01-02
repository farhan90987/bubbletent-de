<?php

namespace MWEW\Inc\Google_Tags;

class Voucher_Tracker {
    public function track() {
        if (!Utilities::is_tracking_enabled()) return;

        if ($this->is_voucher_viewed()) {
            $voucher = $this->get_voucher_data();
            ?>
            <script>
            dataLayer.push({
                event: 'voucher_viewed',
                voucher_type: '<?php echo esc_js($voucher['type']); ?>',
                bubble_name: '<?php echo esc_js($voucher['bubble_name']); ?>',
                current_page: '<?php echo esc_url($voucher['page']); ?>'
            });
            </script>
            <?php
        }
    }

    private function is_voucher_viewed() {
        return $this->is_voucher_page_is_product() || $this->is_voucher_page_is_page();
    }

    private function is_voucher_page_is_product() {
        global $post;
        $settings = get_option('mwew_gtm_ga4_settings', []);
        $voucher_category_ids = $settings['voucher_category_ids'] ?? [];

        if (is_singular('product') && !empty($voucher_category_ids)) {
            $terms = wp_get_post_terms($post->ID, 'product_cat', ['fields' => 'ids']);
            foreach ($terms as $term_id) {
                if (in_array($term_id, $voucher_category_ids)) return true;
            }
        }
        return false;
    }

    private function is_voucher_page_is_page() {
        return is_page() && isset($_GET['listing_id']) && intval($_GET['listing_id']) > 0;
    }

    private function get_voucher_data() {
        if ($this->is_voucher_page_is_page()) {
            $listing_id = intval($_GET['listing_id']);
            $listing_type = 'listing-specific';
        } else {
            $listing_id = get_the_ID();
            $listing_type = 'global';
        }

        $name = get_the_title($listing_id);
        $page_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        return [
            'type' => $listing_type,
            'bubble_name' => $name ?: '',
            'page' => $page_url
        ];
    }
}
