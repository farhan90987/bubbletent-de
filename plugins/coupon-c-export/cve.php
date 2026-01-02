<?php
/*
Plugin Name: Voucher Code Exporter
Description: Exports voucher codes, descriptions, and redemption status to an XLSX file.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include Composer autoloader from the other plugin
if (file_exists(WP_PLUGIN_DIR . '../bulk-alt-text/vendor/autoload.php')) {
    require_once WP_PLUGIN_DIR . '../bulk-alt-text/vendor/autoload.php';
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Add a custom admin menu
add_action('admin_menu', function () {
    add_menu_page(
        'Voucher Exporter',        // Page title
        'Voucher Exporter',        // Menu title
        'manage_options',          // Capability
        'voucher-exporter',        // Menu slug
        'voucher_exporter_page',   // Callback function
        'dashicons-download',      // Icon
        20                         // Position
    );
});

// Callback to render the admin page
function voucher_exporter_page()
{
    ?>
    <div class="wrap">
        <h1>Voucher Code Exporter</h1>
        <p>Click the button below to export all voucher codes, their descriptions, and redemption status.</p>
        <form method="post" action="">
            <input type="hidden" name="export_vouchers" value="1">
            <?php submit_button('Export Vouchers'); ?>
        </form>
    </div>
    <?php

    // Check if the export form was submitted
    if (isset($_POST['export_vouchers']) && current_user_can('manage_options')) {
        export_voucher_codes_to_xlsx(); // Corrected function name
    }
}

// Function to export voucher codes to an XLSX file
function export_voucher_codes_to_xlsx()
{
    global $wpdb;

    // Prevent unexpected output
    ob_clean();

    // Table containing voucher codes
    $table_name = $wpdb->prefix . 'custom_excel_table';
    $voucher_codes = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    if (!$voucher_codes) {
        wp_die('No voucher codes found in the database.');
    }

    // Query WooCommerce coupons
    $args = [
        'post_type'      => 'shop_coupon',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ];
    $coupon_query = new WP_Query($args);

    if (!$coupon_query->have_posts()) {
        wp_die('No coupons found in WooCommerce.');
    }

    // Initialize Spreadsheet
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Header Row
    $sheet->setCellValue('A1', 'Voucher Code');
    $sheet->setCellValue('B1', 'Coupon Value');
    $sheet->setCellValue('C1', 'Description');
    $sheet->setCellValue('D1', 'Status');

    // Populate Data Rows
    $row = 2; // Start from the second row
    foreach ($voucher_codes as $voucher) {
        $code = $voucher['vouchercode'];
        $coupon_value = 'N/A';
        $description = 'N/A';
        $status = 'Not Created';

        foreach ($coupon_query->posts as $coupon) {
            $coupon_code = $coupon->post_title;

            // Match voucher code with WooCommerce coupon
            if (strcasecmp($coupon_code, $code) === 0) {
                // Fetch coupon value directly from meta
                $coupon_value = get_post_meta($coupon->ID, 'coupon_amount', true);
                if (!$coupon_value || !is_numeric($coupon_value)) {
                    $coupon_value = '0.00'; // Default to 0.00 if no value found
                }

                $description = $coupon->post_excerpt ?: 'No description provided';

                // Check if the coupon is redeemed
                $used_by = get_post_meta($coupon->ID, '_used_by', true); // Array of user IDs
                $status = (!empty($used_by)) ? 'Redeemed' : 'Not Redeemed';

                break;
            }
        }

        // Write Data to XLSX
        $sheet->setCellValue("A{$row}", $code);
        $sheet->setCellValue("B{$row}", $coupon_value); // Use raw numeric value
        $sheet->setCellValue("C{$row}", $description);
        $sheet->setCellValue("D{$row}", $status);
        $row++;
    }

    // File Output
    $filename = 'voucher_codes_' . date('Y-m-d_H-i-s') . '.xlsx';

    // Set appropriate headers for downloading the file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');

    // Stop execution to prevent corrupt output
    exit;
}


