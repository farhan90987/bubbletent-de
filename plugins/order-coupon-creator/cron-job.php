<?php
// Ensure this file is being accessed through WordPress
if ( !defined('ABSPATH') ) {
    exit;
}

// Automatic coupon creation function
function occ_auto_create_coupons() {
    error_log('Coupon creation cron started.');

    // Fetch completed orders created within the last 24 hours, limit to 100 per batch for performance
    $args = array(
        'limit'        => 100,
        'status'       => 'completed',
        'date_created' => '>' . strtotime('-24 hours'),
    );

    $orders = wc_get_orders($args);

    if (empty($orders)) {
        error_log('No completed orders found within the last 24 hours.');
        return; // No orders to process
    }

    foreach ($orders as $order) {
        $order_id = $order->get_id();
        error_log("Processing order #$order_id.");

        // Skip if order has a voucher code
        if (get_post_meta($order_id, '_voucher_code', true)) {
            error_log("Skipped order #$order_id: Order already has a voucher code.");
            continue;
        }

        if (occ_has_downloadable_products($order)) {
            $coupon_code = (string) $order_id;

            if (!wc_get_coupon_id_by_code($coupon_code)) {
                $item_subtotal = 0;

                foreach ($order->get_items() as $item) {
                    $item_subtotal += $item->get_subtotal();
                }

                if ($item_subtotal <= 0) {
                    error_log("Order #$order_id: Order value is 0, skipping coupon creation.");
                    continue;
                }

                $expiry_date = date('Y-m-d', strtotime('+3 years', strtotime($order->get_date_created())));
                $coupon = new WC_Coupon();
                $coupon->set_code($coupon_code);
                $coupon->set_discount_type('fixed_cart');
                $coupon->set_amount($item_subtotal);
                $coupon->set_date_expires($expiry_date);
                $coupon->set_usage_limit(1);
                $coupon->save();

                error_log("Coupon created for order #$order_id with code $coupon_code.");
            } else {
                error_log("Coupon already exists for order #$order_id.");
            }
        } else {
            error_log("Order #$order_id does not contain downloadable products.");
        }
    }

    error_log('Coupon creation cron completed.');
}



// Ensure the function is included in the main plugin file
if ( file_exists(plugin_dir_path(__FILE__) . 'order-coupon-creator.php') ) {
    include_once(plugin_dir_path(__FILE__) . 'order-coupon-creator.php');
}
