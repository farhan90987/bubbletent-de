<?php
/*
Plugin Name: Order Coupon Creator
Description: Generates coupons from WooCommerce orders. Supports manual trigger and automatic 12-hour coupon generation. Now includes product names as coupon description.
Version: 5.0
Author: Mathesconsulting
*/

// Add custom bulk action for manual coupon creation
function occ_register_custom_bulk_action($bulk_actions) {
    $bulk_actions['create_coupons'] = 'Create Coupons';
    return $bulk_actions;
}
add_filter('bulk_actions-edit-shop_order', 'occ_register_custom_bulk_action');

// Handle custom bulk action via AJAX
function occ_handle_custom_bulk_action() {
    if (!isset($_POST['post_ids']) || !is_array($_POST['post_ids'])) {
        error_log('Invalid request: No post_ids provided.');
        wp_send_json_error('Invalid request');
    }

    $post_ids = array_map('intval', $_POST['post_ids']);
    $log = array();

    foreach ($post_ids as $post_id) {
        $order = wc_get_order($post_id);
        if (!$order) {
            $log[] = "Invalid order ID: $post_id";
            error_log("Invalid order ID: $post_id");
            continue;
        }

        if ($order->get_status() !== 'completed') {
            $log[] = "Skipped order #$post_id: Order status is not completed.";
            error_log("Skipped order #$post_id: Order status is not completed.");
            continue;
        }
        
        // Skip if order has a voucher code
        if (get_post_meta($order->get_id(), '_voucher_code', true)) {
            $log[] = "Skipped order #$post_id: Order already has a voucher code.";
            error_log("Skipped order #$post_id: Order already has a voucher code.");
            continue;
        }   

        if (!occ_has_downloadable_products($order)) {
            $log[] = "Skipped order #$post_id: No voucher found in this order.";
            error_log("Skipped order #$post_id: No voucher found in this order");
            continue;
        }

        $order_id = $order->get_id();
        $item_subtotal = 0;
        $product_names = array();

        foreach ($order->get_items() as $item) {
            $item_subtotal += $item->get_subtotal();
            $product_names[] = $item->get_name(); // Collect product names
        }

        if ($item_subtotal <= 0) {
            $log[] = "Skipped order #$order_id: Order value is 0.";
            error_log("Skipped order #$order_id: Order value is 0.");
            continue;
        }

        $expiry_date = date('Y-m-d', strtotime('+3 years', strtotime($order->get_date_created())));
        $coupon_code = (string) $order_id;

        if (!wc_get_coupon_id_by_code($coupon_code)) {
            $coupon = new WC_Coupon();
            $coupon->set_code($coupon_code);
            $coupon->set_discount_type('fixed_cart');
            $coupon->set_amount($item_subtotal);
            $coupon->set_date_expires($expiry_date);
            $coupon->set_usage_limit(1);
            $coupon->set_description(implode(', ', $product_names)); // Set product names as description
            $coupon->save();
            $log[] = "Coupon created for order #$order_id with code $coupon_code.";
            error_log("Coupon created for order #$order_id with code $coupon_code.");
        } else {
            $log[] = "Coupon already exists for order #$order_id.";
            error_log("Coupon already exists for order #$order_id.");
        }
    }

    wp_send_json_success($log);
}
add_action('wp_ajax_occ_handle_custom_bulk_action', 'occ_handle_custom_bulk_action');

// Check if an order contains downloadable products
function occ_has_downloadable_products($order) {
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product && $product->is_downloadable()) {
            return true;
        }
    }
    return false;
}

// Enqueue JavaScript with versioning to disable caching
function occ_enqueue_custom_admin_script($hook_suffix) {
    if ($hook_suffix === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'shop_order') {
        $version = time(); // Generate a new version string each time
        wp_enqueue_script('occ-custom-admin-script', plugin_dir_url(__FILE__) . 'custom-admin-script.js?v=' . $version, array('jquery'), null, true);
        wp_localize_script('occ-custom-admin-script', 'occ_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
    }
}
add_action('admin_enqueue_scripts', 'occ_enqueue_custom_admin_script');

// Add a custom 12-hour interval for production
function occ_custom_cron_schedules($schedules) {
    $schedules['twicedaily'] = array(
        'interval' => 43200, // 12 hours
        'display' => __('Every 12 Hours')
    );
    return $schedules;
}
add_filter('cron_schedules', 'occ_custom_cron_schedules');

// Schedule event for automatic coupon creation every 12 hours
if (!wp_next_scheduled('occ_automatic_coupon_creation')) {
    wp_schedule_event(time(), 'twicedaily', 'occ_automatic_coupon_creation'); // Use 12-hour interval for production
}

// Hook the cron job to the coupon creation function
add_action('occ_automatic_coupon_creation', 'occ_auto_create_coupons');

// Automatic coupon creation for orders in the last 24 hours
function occ_auto_create_coupons() {
    error_log('Coupon creation cron started.');

    // Fetch completed orders created within the last 24 hours, limit to 100 per batch for performance
    $args = array(
        'limit'        => 100, // Limit to 100 orders per run for performance
        'status'       => 'completed',
        'date_created' => '>' . strtotime('-24 hours'), // Fetch orders from the last 24 hours
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
                $product_names = array(); // Collect product names

                foreach ($order->get_items() as $item) {
                    $item_subtotal += $item->get_subtotal();
                    $product_names[] = $item->get_name(); // Collect product names
                }

                if ($item_subtotal <= 0) {
                    error_log("Order #$order_id: Order value is 0, skipping coupon creation.");
                    continue; // Skip orders with no value
                }

                $expiry_date = date('Y-m-d', strtotime('+3 years', strtotime($order->get_date_created())));
                $coupon = new WC_Coupon();
                $coupon->set_code($coupon_code);
                $coupon->set_discount_type('fixed_cart');
                $coupon->set_amount($item_subtotal);
                $coupon->set_date_expires($expiry_date);
                $coupon->set_usage_limit(1);
                $coupon->set_description(implode(', ', $product_names)); // Set product names as description
                $coupon->save();

                error_log("Coupon created for order #$order_id with code $coupon_code.");
            } else {
                error_log("Coupon already exists for order #$order_id.");
            }
        } else {
            error_log("Order #$order_id does not contain voucher products.");
        }
    }

    error_log('Coupon creation cron completed.');
}

// Add manual trigger button to the coupon page above the coupon list
function occ_add_manual_trigger_button() {
    $screen = get_current_screen();
    if ($screen->id === 'edit-shop_coupon') {
        ?>
        <div class="alignleft actions">
            <a href="<?php echo admin_url('admin-post.php?action=manual_coupon_creation'); ?>" class="button button-primary">Generate Coupons Now</a>
        </div>
        <?php
    }
}
add_action('restrict_manage_posts', 'occ_add_manual_trigger_button');

// Handle manual coupon creation trigger for orders created in the last 24 hours
function occ_manual_coupon_creation_trigger() {
    error_log('Manual coupon creation triggered.');

    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied.');
    }

    occ_auto_create_coupons(); // Trigger the automatic function manually

    error_log('Coupon creation function executed.');

    wp_redirect(admin_url('edit.php?post_type=shop_coupon&message=coupons_generated'));
    exit;
}
add_action('admin_post_manual_coupon_creation', 'occ_manual_coupon_creation_trigger');
