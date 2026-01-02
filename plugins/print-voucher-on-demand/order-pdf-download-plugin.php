<?php
/*
Plugin Name: Create Coupon For Location Voucher
Description: Plugin for make coupon and pdf for location voucher
Version: 1.5
Author: Mathesconsulting
*/

// Include the required libraries (TCPDF and FPDI)
require_once plugin_dir_path(__FILE__) . 'tcpdf/tcpdf.php';
require_once plugin_dir_path(__FILE__) . 'fpdi/src/autoload.php';

use setasign\Fpdi\TcpdfFpdi;

// Hook to intercept PDF requests and regenerate if needed
add_action('template_redirect', 'handle_pdf_regeneration_request');
function handle_pdf_regeneration_request() {
    $request_uri = $_SERVER['REQUEST_URI'];
    
    // Check if it's a coupon PDF request
    if (strpos($request_uri, '/wp-content/plugins/print-voucher-on-demand/coupons/') !== false && 
        strpos($request_uri, '.pdf') !== false) {
        
        // Extract order number from filename
        $filename = basename($request_uri);
        $filename_parts = explode('.', $filename);
        $filename_without_ext = $filename_parts[0];
        
        // Extract order number (assuming format: something_12345)
        $filename_parts = explode('_', $filename_without_ext);
        $order_number = end($filename_parts);
        
        // Clean order number (remove query string if any)
        $order_number = preg_replace('/\?.*/', '', $order_number);
        
        if (is_numeric($order_number)) {
            // Try to regenerate and serve the PDF
            serve_or_regenerate_pdf($order_number, $filename);
        }
    }
}

function serve_or_regenerate_pdf($order_number, $requested_filename) {
    $coupon_post = get_page_by_title($order_number, OBJECT, 'shop_coupon');
    if (!$coupon_post) {
        status_header(404);
        die('Voucher not found for order: ' . $order_number);
    }
    
    $coupon_id = $coupon_post->ID;
    $coupon_data = get_post_meta($coupon_id, '_fcpdf_coupon_data', true);
    
    if (empty($coupon_data)) {
        status_header(404);
        die('Voucher data not found for order: ' . $order_number);
    }
    
    // Check if we have the original product info
    if (!isset($coupon_data['original_product_id'])) {
        // Try to get it from the order
        $order = wc_get_order($order_number);
        if (!$order) {
            status_header(404);
            die('Order not found');
        }
        
        // Find downloadable product
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $files = get_post_meta($product_id, '_downloadable_files', true);
            if (!empty($files) && is_array($files)) {
                $coupon_data['original_product_id'] = $product_id;
                // Save for future use
                update_post_meta($coupon_id, '_fcpdf_coupon_data', $coupon_data);
                break;
            }
        }
    }
    
    // Regenerate the PDF
    $pdf_url = regenerate_pdf_for_order($order_number, $coupon_id, $coupon_data);
    
    if ($pdf_url) {
        // Extract the actual filename from the new URL
        $parsed_url = parse_url($pdf_url);
        $new_filename = basename($parsed_url['path']);
        
        // Get the file path
        $coupons_dir = plugin_dir_path(__FILE__) . 'coupons/';
        $pdf_path = $coupons_dir . $new_filename;
        
        // Serve the PDF
        if (file_exists($pdf_path)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $new_filename . '"');
            header('Content-Length: ' . filesize($pdf_path));
            readfile($pdf_path);
            exit;
        }
    }
    
    status_header(404);
    die('Could not generate voucher PDF. Please contact support.');
}

// Shortcode to display the order number and email form
add_action('woocommerce_order_status_completed', 'generate_pdf_on_order_complete', 10, 1);
function generate_pdf_on_order_complete($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) {
        error_log("Order not found for ID: $order_id");
        return;
    }

    $downloadable_product_id = false;
    $downloadable_files      = [];

    // ✅ Find the first downloadable product
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();

        $files = get_post_meta($product_id, '_downloadable_files', true);
        if (!empty($files) && is_array($files)) {
            $downloadable_product_id = $product_id;
            $downloadable_files      = $files;
            break; // stop at first downloadable product
        }
    }

    if (!$downloadable_product_id) {
        error_log("Order $order_id skipped — no downloadable products found");
        return;
    }

    // ✅ Make sure coupon exists
    if (!check_coupon_exists($order_id)) {
        create_coupon($order_id, $order, $downloadable_product_id);
    }

    // ✅ Get coupon post ID
    $coupon_post = get_page_by_title($order_id, OBJECT, 'shop_coupon');
    if (!$coupon_post) {
        error_log("Coupon post not found for order: $order_id");
        return;
    }
    $coupon_id = $coupon_post->ID;

    // ✅ Process only the first downloadable product
    foreach ($downloadable_files as $file_data) {
        if (!empty($file_data['file'])) {
            $download_url = $file_data['file'];
            $file_path    = convert_url_to_path($download_url);

            $pdf_link     = copy_and_modify_pdf($file_path, $order_id);

            if ($pdf_link) {
                $coupon_data = array(
                    'hash'         => md5($coupon_id . AUTH_SALT),
                    'order_id'     => $order_id,
                    'coupon_id'    => $coupon_id,
                    'coupon_code'  => $order->get_order_number(),
                    'coupon_url'   => $pdf_link,
                    'original_product_id' => $downloadable_product_id,
                    'original_file_path' => $file_path,
                    'generated_at' => current_time('mysql')
                );
                update_post_meta($coupon_id, '_fcpdf_coupon_data', $coupon_data);

                error_log("Generated coupon PDF for coupon: $coupon_id using product $downloadable_product_id");
            } else {
                error_log("Failed to generate coupon PDF for product $downloadable_product_id (order $order_id)");
            }

            break;
        }
    }
}

function order_download_form() {
    ob_start(); ?>
    <form method="post" id="order-download-form">
        <label for="order_number">Order Number:</label>
        <input type="text" name="order_number" id="order_number" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <input type="submit" name="submit_order" value="Get Download Link">
    </form>

    <?php
    if (isset($_POST['submit_order'])) {
        $order_number = sanitize_text_field($_POST['order_number']);
        $email = sanitize_email($_POST['email']);
        echo handle_order_download($order_number, $email);
    }

    return ob_get_clean();
}
add_shortcode('order_download', 'order_download_form');

// Function to handle the order download and PDF modification
function handle_order_download($order_number, $email) {
    error_log("Handling download for order: $order_number with email: $email");

    $order = wc_get_order($order_number);
    if (!$order) {
        error_log("Failed to retrieve order with number: $order_number");
        return 'Invalid order number or email.';
    }

    if ($order->get_billing_email() !== $email) {
        error_log("Email mismatch: provided $email, expected " . $order->get_billing_email());
        return 'Invalid order number or email.';
    }

    // Get or create coupon
    if (!check_coupon_exists($order_number)) {
        create_coupon($order_number, $order);
    }

    // Get coupon data
    $coupon_post = get_page_by_title($order_number, OBJECT, 'shop_coupon');
    if (!$coupon_post) {
        return 'No voucher found for this order.';
    }
    
    $coupon_id = $coupon_post->ID;
    $coupon_data = get_post_meta($coupon_id, '_fcpdf_coupon_data', true);
    
    // If we have a stored URL, use it
    if (!empty($coupon_data) && isset($coupon_data['coupon_url'])) {
        $pdf_url = $coupon_data['coupon_url'];
        
        // Check if file still exists
        $pdf_path = convert_url_to_path($pdf_url);
        if (!file_exists($pdf_path)) {
            // Regenerate it
            $pdf_url = regenerate_pdf_for_order($order_number, $coupon_id, $coupon_data);
        }
    } else {
        // Generate new PDF
        $downloads = $order->get_downloadable_items();
        if (empty($downloads)) {
            return 'No downloadable items found for this order.';
        }
        
        $download_url = isset($downloads[0]['file']['file']) ? $downloads[0]['file']['file'] : '';
        if (empty($download_url)) {
            return 'Downloadable file not found.';
        }
        
        $file_path = convert_url_to_path($download_url);
        $pdf_url = copy_and_modify_pdf($file_path, $order_number);
    }
    
    if (!$pdf_url) {
        return 'Failed to generate voucher PDF. Please try again.';
    }
    
    return '<a href="' . esc_url($pdf_url) . '" target="_blank">Download Your Voucher</a>';
}

// Function to regenerate PDF for an order
function regenerate_pdf_for_order($order_number, $coupon_id, $coupon_data) {
    error_log("Regenerating PDF for order: $order_number");
    
    // Try to get the original product
    if (isset($coupon_data['original_product_id'])) {
        $product_id = $coupon_data['original_product_id'];
        $files = get_post_meta($product_id, '_downloadable_files', true);
        
        if (!empty($files) && is_array($files)) {
            foreach ($files as $file_data) {
                if (!empty($file_data['file'])) {
                    $file_path = convert_url_to_path($file_data['file']);
                    if (file_exists($file_path)) {
                        $pdf_url = copy_and_modify_pdf($file_path, $order_number);
                        
                        if ($pdf_url) {
                            // Update coupon data
                            $coupon_data['coupon_url'] = $pdf_url;
                            $coupon_data['regenerated_at'] = current_time('mysql');
                            $coupon_data['regeneration_count'] = isset($coupon_data['regeneration_count']) ? 
                                $coupon_data['regeneration_count'] + 1 : 1;
                            update_post_meta($coupon_id, '_fcpdf_coupon_data', $coupon_data);
                            
                            error_log("Successfully regenerated PDF for order: $order_number");
                            return $pdf_url;
                        }
                    }
                }
            }
        }
    }
    
    // Fallback: Get from order items
    $order = wc_get_order($order_number);
    if ($order) {
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $files = get_post_meta($product_id, '_downloadable_files', true);
            
            if (!empty($files) && is_array($files)) {
                foreach ($files as $file_data) {
                    if (!empty($file_data['file'])) {
                        $file_path = convert_url_to_path($file_data['file']);
                        if (file_exists($file_path)) {
                            $pdf_url = copy_and_modify_pdf($file_path, $order_number);
                            
                            if ($pdf_url) {
                                // Update coupon data with product info
                                $coupon_data['original_product_id'] = $product_id;
                                $coupon_data['coupon_url'] = $pdf_url;
                                $coupon_data['regenerated_at'] = current_time('mysql');
                                $coupon_data['regeneration_count'] = isset($coupon_data['regeneration_count']) ? 
                                    $coupon_data['regeneration_count'] + 1 : 1;
                                update_post_meta($coupon_id, '_fcpdf_coupon_data', $coupon_data);
                                
                                return $pdf_url;
                            }
                        }
                    }
                }
            }
        }
    }
    
    error_log("Failed to regenerate PDF for order: $order_number");
    return false;
}

// Check if a coupon exists
function check_coupon_exists($coupon_code) {
    global $wpdb;
    $result = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_title = %s AND post_type = 'shop_coupon'", $coupon_code));
    return $result > 0;
}

// Create a new coupon
function create_coupon($coupon_code, $order, $product_id = null) {
    $product = wc_get_product($product_id);
    $price = '';
    if ($product) {
        $price = $product->get_price();
    }
    $coupon = new WC_Coupon();
    $coupon->set_code($coupon_code);
    $coupon->set_discount_type('fixed_cart');
    $coupon->set_amount($price);
    $coupon->set_date_expires(strtotime('+3 years'));
    $coupon->set_usage_limit(1);
    if ($product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $coupon->set_description($product->get_name());
        }
    }
    $coupon->save();
    error_log("Created coupon for order number: $coupon_code");
}

// Convert URL to local file path
function convert_url_to_path($url) {
    $parsed_url = parse_url($url, PHP_URL_PATH);
    $local_path = ABSPATH . ltrim($parsed_url, '/');
    return $local_path;
}

// Copy and modify the original PDF and add the order number
function copy_and_modify_pdf($file_path, $order_number) {
    error_log("Copying and modifying original PDF for order: $order_number");

    if (!file_exists($file_path)) {
        error_log("Original file not found: $file_path");
        return false;
    }

    $coupons_dir = plugin_dir_path(__FILE__) . 'coupons/';
    if (!file_exists($coupons_dir)) {
        mkdir($coupons_dir, 0755, true);
        error_log("Created directory for coupons: $coupons_dir");
    }

    $original_filename = basename($file_path);
    $file_name_without_ext = pathinfo($original_filename, PATHINFO_FILENAME);
    $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
    $unique_pdf_filename = "{$file_name_without_ext}_{$order_number}.{$file_extension}";
    $unique_pdf_file = $coupons_dir . $unique_pdf_filename;

    $pdf = new TcpdfFpdi();
    $page_count = $pdf->setSourceFile($file_path);

    for ($i = 1; $i <= $page_count; $i++) {
        $tplIdx = $pdf->importPage($i);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx, 0, 0, 210);

        $x_position = 43;
        $y_position = 258;
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Text($x_position, $y_position, $order_number);
    }

    $pdf->Output($unique_pdf_file, 'F');
    error_log("Generated unique PDF: $unique_pdf_file");

    schedule_pdf_cleanup($unique_pdf_file);

    $theme_url = plugins_url('coupons', __FILE__);
    return $theme_url . '/' . $unique_pdf_filename . '?t=' . time();
}

add_filter('cron_schedules', function($schedules) {
    $schedules['monthly'] = [
        'interval' => 30 * 24 * 60 * 60, // 30 days
        'display'  => __('Once Monthly')
    ];
    return $schedules;
});

// Schedule cleanup for temporary PDFs
function schedule_pdf_cleanup($file_path) {
    $cleanup_files = get_option('temp_pdf_files', []);
    $cleanup_files[] = $file_path;
    update_option('temp_pdf_files', $cleanup_files);

    if (!wp_next_scheduled('delete_temp_pdf_files')) {
        wp_schedule_event(time() + 30 * DAY_IN_SECONDS, 'monthly', 'delete_temp_pdf_files');
    }
}

// Cleanup expired temporary PDFs
add_action('delete_temp_pdf_files', 'cleanup_temp_pdf_files');
function cleanup_temp_pdf_files() {
    $cleanup_files = get_option('temp_pdf_files', []);

    if (!empty($cleanup_files)) {
        foreach ($cleanup_files as $key => $file_path) {
            if (file_exists($file_path)) {
                unlink($file_path);
                error_log("Deleted temporary PDF: $file_path");
            }
            unset($cleanup_files[$key]);
        }
        update_option('temp_pdf_files', $cleanup_files);
    }
}

// Add a button for downloading flexible PDF coupon in the admin order screen
add_action('woocommerce_admin_order_actions_end', 'custom_order_screen_coupon_button', 10, 1);
function custom_order_screen_coupon_button($order) {
    $order_id = $order->get_id();
    $coupon_post = get_page_by_title($order_id, OBJECT, 'shop_coupon');
    
    if ($coupon_post) {
        $coupon_id = $coupon_post->ID;
        $coupon_data = get_post_meta($coupon_id, '_fcpdf_coupon_data', true);
        
        // Always regenerate on admin click to ensure fresh PDF
        $regenerate_url = add_query_arg(array(
            'action' => 'regenerate_and_download_pdf',
            'order_id' => $order_id,
            'nonce' => wp_create_nonce('admin_download_' . $order_id)
        ), admin_url('admin-ajax.php'));
        
        echo '<a class="button button-primary" href="' . esc_url($regenerate_url) . '" target="_blank" title="Generate and Download Voucher PDF">Download Voucher PDF</a>';
    }
}

// AJAX handler for admin PDF download (always regenerates)
add_action('wp_ajax_regenerate_and_download_pdf', 'handle_admin_pdf_download');
function handle_admin_pdf_download() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Unauthorized');
    }

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';

    if (!wp_verify_nonce($nonce, 'admin_download_' . $order_id)) {
        wp_die('Security check failed');
    }

    if ($order_id) {
        $coupon_post = get_page_by_title($order_id, OBJECT, 'shop_coupon');
        if ($coupon_post) {
            $coupon_id = $coupon_post->ID;
            $coupon_data = get_post_meta($coupon_id, '_fcpdf_coupon_data', true);
            
            $pdf_url = regenerate_pdf_for_order($order_id, $coupon_id, $coupon_data);
            
            if ($pdf_url) {
                // Redirect to the PDF
                wp_redirect($pdf_url);
                exit;
            }
        }
    }
    
    wp_die('Failed to generate PDF');
}