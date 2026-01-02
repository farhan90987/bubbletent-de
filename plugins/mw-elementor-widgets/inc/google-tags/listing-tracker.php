<?php 

namespace MWEW\Inc\Google_Tags;

class Listing_Tracker {
    public function track() {
        if (!Utilities::is_tracking_enabled() || !is_singular('listing')) return;

        $bubble = $this->get_bubble_data();
        ?>
        <script>
        dataLayer.push({
            event: 'accommodation_viewed',
            bubble_id: '<?php echo esc_js($bubble['id']); ?>',
            bubble_name: '<?php echo esc_js($bubble['name']); ?>',
            location: '<?php echo esc_js($bubble['location']); ?>',
            price_per_night: <?php echo esc_js($bubble['price']); ?>
        });

        (function() {
            var viewedRaw = sessionStorage.getItem('mwew_browsing_titles');
            var viewed = [];

            if (viewedRaw) {
                try {
                    viewed = JSON.parse(viewedRaw);
                } catch (e) {
                    viewed = [];
                }
            }

            var currentTitle = <?php echo json_encode(get_the_title($bubble['id'])); ?>;

            if (!viewed.includes(currentTitle)) viewed.push(currentTitle);

            var uniqueViewed = Array.from(new Set(viewed));
            sessionStorage.setItem('mwew_browsing_titles', JSON.stringify(uniqueViewed));

            if (uniqueViewed.length >= 3 && !sessionStorage.getItem('mwew_fired_browsing_depth')) {
                dataLayer.push({ event: 'listing_browsing_depth', bubble_titles: uniqueViewed });
                sessionStorage.setItem('mwew_fired_browsing_depth', '1');
            }
        })();
        </script>
        <?php
    }

    private function get_bubble_data() {
        global $post;
        $woo_product_id = get_post_meta($post->ID, 'product_id', true);
        $base_price = get_post_meta($woo_product_id, 'sa_cfw_cog_amount', true);

        return [
            'id' => $post->ID,
            'name' => get_the_title($post),
            'location' => get_post_meta($post->ID, '_friendly_address', true) ?: 'Unknown',
            'price' => $base_price ?: 0
        ];
    }
}
