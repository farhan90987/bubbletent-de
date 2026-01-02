<?php
// auto-add-christmas-box.php

add_action('woocommerce_add_to_cart', 'add_christmas_box_when_product_added', 10, 6);

function add_christmas_box_when_product_added($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    $christmas_box_id = get_christmas_box_product_id();

    if (!$christmas_box_id) {
        log_to_debug("❌ No Christmas Box ID found, exiting function.");
        return;
    }

    // Make sure cart is available
    if (!WC()->cart) {
        log_to_debug("❌ WC()->cart not initialized yet.");
        return;
    }

    log_to_debug("🛒 add_christmas_box_when_product_added called. Product ID added: $product_id");
    log_to_debug("🎁 Christmas Box Product ID: $christmas_box_id");

    // Prevent adding duplicate Christmas Boxes
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ((int)$cart_item['product_id'] === (int)$christmas_box_id) {
            log_to_debug("🎄 Christmas Box already in cart, skipping.");
            return;
        }
    }

    $product = wc_get_product($product_id);

    // Check if product qualifies
    if (get_post_meta($product_id, '_wpdesk_pdf_coupons', true) === 'yes') {
        $added = WC()->cart->add_to_cart($christmas_box_id);
        if ($added) {
            log_to_debug("✅ Christmas Box added to cart successfully.");
        } else {
            log_to_debug("⚠️ Failed to add Christmas Box to cart.");
        }
    } else {
        log_to_debug("🚫 Product does not qualify for Christmas Box.");
    }
}
