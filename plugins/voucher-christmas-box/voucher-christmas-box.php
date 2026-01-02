<?php
/*
Plugin Name: Voucher Product Christmas Box with Email Printing
Description: Sends an email to a printer with order details after creating a coupon.
Version: 3.0
Author: Mathesconsulting
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('plugins_loaded', function () {
    // Ensure WooCommerce email base class is loaded early
    if ( class_exists( 'WooCommerce' ) && !class_exists('WC_Email') ) {
        include_once WC()->plugin_path() . '/includes/emails/class-wc-email.php';
    }
}, 1);

$auto_add_to_cart   = get_option('christmas_auto_add_to_cart', 'no');
$enable_cart_upsell = get_option('christmas_enable_cart_upsell', 'no');

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/config.php'; // Configuration
include_once plugin_dir_path(__FILE__) . 'includes/helpers.php'; // Helpers file
include_once plugin_dir_path(__FILE__) . 'includes/coupon-handler.php'; // Coupon handler
include_once plugin_dir_path(__FILE__) . 'includes/email-handler.php'; // Email handler
if ($enable_cart_upsell === 'yes') {
    include_once plugin_dir_path(__FILE__) . 'includes/cart-upsell.php';
}
if ($auto_add_to_cart === 'yes') {
    include_once plugin_dir_path(__FILE__) . 'includes/auto-add-christmas-box.php';
}
include_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';



// Enqueue assets for the pop-up
// add_action('wp_enqueue_scripts', function () {
//     wp_enqueue_script(
//         'christmas-box-popup-js',
//         plugin_dir_url(__FILE__) . 'assets/js/christmas-box-popup.js',
//         ['jquery'],
//         '1.0',
//         true
//     );

//     wp_enqueue_style(
//         'christmas-box-popup-css',
//         plugin_dir_url(__FILE__) . 'assets/css/christmas-box-popup.css',
//         [],
//         '1.0'
//     );

//     wp_localize_script('christmas-box-popup-js', 'christmasBoxPopup', [
//         'ajax_url' => admin_url('admin-ajax.php'),
//     ]);
// });

// Stop the plugin if no voucher codes are available
if (!has_available_voucher_codes()) {
    log_to_debug("No available voucher codes. Disabling plugin features.");
    return; // Stop execution
}

// Hook to track products added to the cart
add_action('woocommerce_add_to_cart', function ($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    $product = wc_get_product($product_id);

    // Check if the product is eligible (downloadable or has PDF voucher meta)
    if ($product->is_downloadable() || get_post_meta($product_id, '_wpdesk_pdf_coupons', true) === 'yes') {
        WC()->session->set('show_christmas_box_popup', true);
        log_to_debug("Eligible product added to cart. Popup will trigger.");
    } else {
        log_to_debug("Non-eligible product added to cart. Popup will not trigger.");
    }
}, 10, 6);

// Render the pop-up container for dynamic content
add_action('wp_footer', function () {
    echo '<div id="christmas-box-popup" style="display: none;"></div>';
});

// Initialization hook if needed
function voucher_christmas_box_init() {
    log_to_debug("Voucher Christmas Box Plugin Initialized.");
}
add_action('init', 'voucher_christmas_box_init');

/**
 * One-click coupon creation (no email) + success page + secure PDF download link.
 * Replace your previous fcpdf_generate init handler with this full code.
 *
 * Usage:
 *  https://example.com/?fcpdf_generate=1&order_id=123&key=SECRETKEY
 *
 * Place in theme functions.php or a small custom plugin.
 */

/**
 * MAIN: generate coupons (no email) and show success page with download button(s)
 */


add_action('init', function () {

    if (!isset($_GET['download_xmas_pdf']) || !isset($_GET['order_id']) || !isset($_GET['pass'])) {
        return;
    }

    $order_id = intval($_GET['order_id']);
    $password = sanitize_text_field($_GET['pass']);

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_die('Invalid Order');
    }

    // ✅ Validate password
    $saved_pass = get_post_meta($order_id, '_random_password', true);

    if (!$saved_pass || $password !== $saved_pass) {
        wp_die('<h3 style="color:red;">Unauthorized Access: Incorrect Password</h3>');
    }

    // ✅ Load voucher code from order meta
    $voucher_code = get_post_meta($order_id, '_voucher_code', true);

    if (!$voucher_code) {
        wp_die('Voucher code missing');
    }

    // ✅ Text extraction from order items
    $recipient_name = '';
    $sender_name = '';
    $message = '';

    foreach ($order->get_items() as $item) {
        $item_id     = $item->get_id();
        $product_id  = $item->get_product_id();

        $pdf_flag = get_post_meta( $product_id, '_wpdesk_pdf_coupons', true );
        if ( empty( $pdf_flag ) || $pdf_flag !== 'yes' ) {
            continue;
        }

        $qty = max(1, (int) $item->get_quantity());
        $line_total = (float) $item->get_total();
        $unit_price = $qty ? ($line_total / $qty) : $line_total;
        $coupon_amount += $unit_price * $qty;
        $coupon_amount = $coupon_amount . '€ ';
        $convertedText = iconv('UTF-8', 'windows-1252', $coupon_amount);

		$message = wc_get_order_item_meta($item_id, 'flexible_coupon_recipient_message', true);
        $recipient_name = wc_get_order_item_meta( $item_id, 'flexible_coupon_recipient_name', true );

    }

    $sender_name = $order->get_billing_first_name();

    // ✅ Use FPDF class
    if (!class_exists('FPDF')) {
        require_once plugin_dir_path(__FILE__) . '/fpdf/fpdf.php';
    }

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    // ✅ Background Image
    $background_image = plugin_dir_path(__FILE__) . '/wertgutschein-2-1.png';
    if (file_exists($background_image)) {
        $pdf->Image($background_image, 0, 0, 210, 297);
    }

    // ✅ Add Voucher Code Overlay
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->SetXY(0 , 266);
    $pdf->Cell(110, 0, $voucher_code, 0, 0, 'C');

    // ✅ Add Receiver Name
    if ($recipient_name) {
        $pdf->SetFont('Arial', '', 20);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->SetXY(35, 155);
        $pdf->Cell(170, 10, normalize_message_for_pdf($recipient_name) , 0, 0, 'L');
    }

    // ✅ Add Sender Name
    if ($sender_name) {
        $pdf->SetFont('Arial', '', 18);
        $pdf->SetXY(35, 130);
        $pdf->Cell(170, 10, normalize_message_for_pdf($sender_name) , 0, 0, 'L');
    }

    // ✅ Add Sender Name
    if ($coupon_amount) {
        $pdf->SetFont('Arial', '', 18);
        $pdf->SetXY(35, 180);
        $pdf->Cell(170, 10, $convertedText , 0, 0, 'L');
    }

	// fetch $message (from order/item meta)
	if (empty($message)) {
		$message = wc_get_order_item_meta($item_id, '_flexible_coupon_recipient_message', true);
	}
	
	// Output on PDF
	$pdf->SetFont('Arial', '', 13);
	$pdf->SetTextColor(0,0,0);
	$pdf->SetXY(32, 202);
	$pdf->MultiCell(110, 6, normalize_message_for_pdf($message) , 0, 'L');
    // ✅ Force Download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="voucher-' . $voucher_code . '.pdf"');
    $pdf->Output();
    exit;
});

function normalize_message_for_pdf($message)
{
    // normalize encoding to UTF-8 (best-effort)
    if (!mb_check_encoding($message, 'UTF-8')) {
        $message = mb_convert_encoding($message, 'UTF-8', 'auto');
    }

    // normalize newlines
    $message = str_replace(["\r\n", "\r"], "\n", $message);

    // convert to PDF-friendly charset (Windows-1252) with transliteration
    $safe_message = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $message);

    // fallback if iconv fails
    if ($safe_message === false) {
        $safe_message = utf8_decode($message);
    }

    return $safe_message;
}


/**
 * Daily cron – create 1 Christmas coupon per completed order,
 * using order meta + item meta rules requested.
 */

add_action('init', function() {
    // Define Christmas product IDs
    $GLOBALS['christmas_box_product_ids'] = array(222556, 222557);

    if (!wp_next_scheduled('generate_christmas_coupon_daily')) {
        wp_schedule_event(time(), 'daily', 'generate_christmas_coupon_daily');
    }
});

add_action('generate_christmas_coupon_daily', 'generate_christmas_coupons_for_orders');

function generate_christmas_coupons_for_orders() {
    $product_ids = $GLOBALS['christmas_box_product_ids'] ?? array();
    if (empty($product_ids)) return;

    $orders = wc_get_orders([
        'status' => 'completed',
        'limit'  => -1,
        'meta_query' => [
            [
                'key'     => '_voucher_code',
                'value'   => '',
                'compare' => '!=',
            ],
        ],
        'date_created' => '>=' . '2025-10-01 00:00:00',
    ]);

    $result = [];

    foreach ($orders as $order) {
        $order_id = $order->get_id();
        $voucher_code = get_post_meta($order_id, '_voucher_code', true);

        if (!$voucher_code) continue;

        // ✅ Skip if coupon title already exists
        if (get_page_by_title($voucher_code, OBJECT, 'shop_coupon')) {
            $result[] = [
                'order_id' => $order_id,
                'skipped'  => "Coupon already exists: {$voucher_code}",
            ];
            continue;
        }

        $coupon_amount = 0;
        $coupon_message = '';

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $pdf_flag = get_post_meta($product_id, '_wpdesk_pdf_coupons', true);
            if ($pdf_flag !== 'yes') continue;

            $qty = max(1, (int) $item->get_quantity());
            $line_total = (float) $item->get_total();
            $unit_price = $qty ? ($line_total / $qty) : $line_total;
            $coupon_amount += $unit_price * $qty;

            if (empty($coupon_message)) {
                $coupon_message = wc_get_order_item_meta(
                    $item->get_id(),
                    'flexible_coupon_recipient_message',
                    true
                );
            }
        }

        if ($coupon_amount <= 0) {
            $result[] = [
                'order_id' => $order_id,
                'skipped'  => 'Coupon amount 0'
            ];
            continue;
        }

        // ✅ Set expiry date: 3 years from order creation date
        $expiry_timestamp = strtotime('+3 years', strtotime($order->get_date_created()));

        $coupon_id = wp_insert_post([
            'post_title'   => $voucher_code,
            'post_excerpt' => $coupon_message,
            'post_status'  => 'publish',
            'post_type'    => 'shop_coupon',
        ]);

        if (is_wp_error($coupon_id) || !$coupon_id) continue;

        update_post_meta($coupon_id, 'discount_type', 'fixed_cart');
        update_post_meta($coupon_id, 'coupon_amount', wc_format_decimal($coupon_amount));
        update_post_meta($coupon_id, 'individual_use', 'yes');
        update_post_meta($coupon_id, 'usage_limit', 1);
        update_post_meta($coupon_id, 'usage_limit_per_user', 1);
        update_post_meta($coupon_id, 'date_expires', $expiry_timestamp); // ✅ NEW
        update_post_meta($coupon_id, 'christmas_coupon', 'yes');
        update_post_meta($coupon_id, '_christmas_parent_order', $order_id);

        $result[] = [
            'order_id' => $order_id,
            'coupon'   => $coupon_id,
            'expires'  => date('Y-m-d', $expiry_timestamp),
        ];
    }

    return $result;
}


// ✅ Add custom column header
add_filter( 'manage_edit-shop_coupon_columns', function( $columns ) {
    $columns['christmas_parent_order'] = __('Order', 'woocommerce');
    return $columns;
});

// ✅ Show value for each coupon row
add_action( 'manage_shop_coupon_posts_custom_column', function( $column, $post_id ) {
    if ( $column === 'christmas_parent_order' ) {
        $order_id = get_post_meta( $post_id, '_christmas_parent_order', true );
        
        if ( $order_id ) {
            $edit_link = admin_url( 'post.php?post=' . $order_id . '&action=edit' );
            echo '<a href="' . esc_url( $edit_link ) . '">#' . esc_html( $order_id ) . '</a>';
        } else {
            echo '-';
        }
    }
}, 10, 2);

// ✅ Make the column sortable
add_filter( 'manage_edit-shop_coupon_sortable_columns', function( $columns ) {
    $columns['christmas_parent_order'] = 'christmas_parent_order';
    return $columns;
});

// ✅ Sorting logic
add_action( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() || $query->get('post_type') !== 'shop_coupon' ) return;

    if ( $query->get('orderby') === 'christmas_parent_order' ) {
        $query->set('meta_key', '_christmas_parent_order');
        $query->set('orderby', 'meta_value_num');
    }
});
