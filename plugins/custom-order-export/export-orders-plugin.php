<?php
/**
 * Plugin Name: Custom Order Export by Mathesconsulting
 * Description: Export WooCommerce orders in a specific format for IBAN, Stripe, and PayPal payments.
 * Version: 5.0
 * Author: Mathesconsulting
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Konto Mapping Based on Last Name's First Letter
function get_konto($last_name) {
    $konto_mapping = [
        "A" => 11000, "B" => 11100, "C" => 11200, "D" => 11300, "E" => 11400,
        "F" => 11500, "G" => 11600, "H" => 11700, "I" => 11800, "J" => 11900,
        "K" => 12000, "L" => 12100, "M" => 12200, "N" => 12300, "O" => 12400,
        "P" => 12500, "Q" => 12600, "R" => 12700, "S" => 12800, "SCH" => 12900,
        "ST" => 13000, "T" => 13100, "U" => 13200, "V" => 13300, "W" => 13400,
        "X" => 13500, "Y" => 13600, "Z" => 13700
    ];
    $first_letter = strtoupper(substr($last_name, 0, 1));
    return $konto_mapping[$first_letter] ?? "Unknown";
}

// Format Date to dd.mm.yyyy
function format_date($date) {
    return date("d.m.Y", strtotime($date));
}

// Format Price with Symbols and Decimal Separator
function format_price($amount, $soll_haben) {
    $formatted_amount = number_format($amount, 2, ",", ".");
    return ($soll_haben === "H" ? "+" : "-") . $formatted_amount;
}

// Add a menu item in WooCommerce
add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'Custom Order Export',
        'Custom Order Export',
        'manage_woocommerce',
        'custom-orders-export',
        'export_orders_page'
    );
});

// Display the Export Orders page with Date Filters
function export_orders_page() {
    $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : '';
    $date_to = isset($_POST['date_to']) ? $_POST['date_to'] : '';

    ?>
    <div class="wrap">
        <h1>Export Orders</h1>
        <form method="POST">
            <label for="date_from">Date From:</label>
            <input type="date" name="date_from" id="date_from" value="<?php echo esc_attr($date_from); ?>" required>
            
            <label for="date_to">Date To:</label>
            <input type="date" name="date_to" id="date_to" value="<?php echo esc_attr($date_to); ?>" required>

            <input type="hidden" name="export_orders" value="1" />
            <button type="submit" class="button button-primary">Export Orders to Excel</button>
        </form>
    </div>
    <?php

    if (isset($_POST['export_orders']) && $_POST['export_orders'] == '1') {
        export_orders_to_excel($date_from, $date_to);
    }
}

// Export orders to Excel based on selected date range
function export_orders_to_excel($date_from, $date_to) {
    require_once plugin_dir_path(__FILE__) . 'libs/vendor/autoload.php';

    try {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $tabs = [
            'Zahlung IBAN' => ['bacs'],
            'Zahlung PayPal' => ['ppcp-gateway'],
            'Zahlung Stripe-Klarna' => [
                'stripe', 'eh_klarna_stripe', 'stripe_klarna', 'stripe_sepa',
                'eh_stripe_pay', 'eh_giropay_stripe', 'eh_sofort_stripe', 'stripe_sofort',
                'eh_stripe_applepay', 'eh_stripe_ideal'
            ],
            'Free Orders' => [] // Renamed from "Orders Without Payment Gateway"
        ];

        foreach ($tabs as $tab => $payment_methods) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($tab);

            // Set headers only once
            $headers = ['Umsatz', 'Soll/Haben', 'Gegenkonto', 'Belegfeld 1', 'Datum', 'Konto', 'Buchungstext', 'Belegverknüpfung'];
            $sheet->fromArray($headers, NULL, 'A1');

            $data = ($tab === 'Free Orders') 
                ? fetch_orders_without_payment_methods($date_from, $date_to) 
                : fetch_orders_for_payment_methods($payment_methods, $tab, $date_from, $date_to);
            
            $sheet->fromArray($data, NULL, 'A2');
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        if (headers_sent()) {
            die('Headers already sent. Cannot export file.');
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="orders-export-' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        ob_end_clean();
        $writer->save('php://output');
        exit;
    } catch (Exception $e) {
        wp_die('Error generating the Excel file: ' . $e->getMessage());
    }
}

// Fetch orders for specific payment methods with date filter
function fetch_orders_for_payment_methods($payment_methods, $tab_name, $date_from, $date_to) {
    global $wpdb;

    $query = $wpdb->prepare("
        SELECT ID, post_date
        FROM {$wpdb->prefix}posts
        WHERE post_type = 'shop_order'
        AND post_status = 'wc-completed'
        AND DATE(post_date) BETWEEN %s AND %s
    ", $date_from, $date_to);

    $results = $wpdb->get_results($query);
    $final_results = [];

    foreach ($results as $result) {
        $order = wc_get_order($result->ID);
        if (!$order) continue;

        $last_name = $order->get_billing_last_name();
        $konto = get_konto($last_name);

        $formatted_date = format_date($order->get_date_created()->date('Y-m-d'));
        $customer_name = $order->get_billing_first_name() . ", " . $last_name;
        $total_amount = format_price($order->get_total(), "H");
        $fee = format_price(fetch_order_fee($order, $tab_name), "S");

        $final_results[] = [$total_amount, "H", "4200", $order->get_order_number(), $formatted_date, $konto, $customer_name, ""];
        $final_results[] = [$fee, "S", "6855", $order->get_order_number(), $formatted_date, "Diverse-W", "$customer_name, Gebühr $tab_name", ""];
    }

    return $final_results;
}
// Fetch orders without payment methods with date filter
function fetch_orders_without_payment_methods($date_from, $date_to) {
    global $wpdb;

    $query = $wpdb->prepare("
        SELECT ID, post_date
        FROM {$wpdb->prefix}posts
        WHERE post_type = 'shop_order'
        AND post_status = 'wc-completed'
        AND DATE(post_date) BETWEEN %s AND %s
    ", $date_from, $date_to);

    $results = $wpdb->get_results($query);
    $final_results = [];

    foreach ($results as $result) {
        $order = wc_get_order($result->ID);
        if (!$order) continue;

        // Orders with 0 price in IBAN should be moved to Free Orders
        if ($order->get_total() == 0) {
            $last_name = $order->get_billing_last_name();
            $konto = get_konto($last_name);
            $formatted_date = format_date($order->get_date_created()->date('Y-m-d'));
            $customer_name = $order->get_billing_first_name() . ", " . $last_name;

            $total_amount = format_price($order->get_total(), "H");
            $fee = format_price(fetch_order_fee($order, "Free Orders"), "S");

            $final_results[] = [$total_amount, "H", "4200", $order->get_order_number(), $formatted_date, $konto, $customer_name, ""];
            $final_results[] = [$fee, "S", "6855", $order->get_order_number(), $formatted_date, "Diverse-W", "$customer_name, Gebühr", ""];
        }
    }

    return $final_results;
}


// Fetch Fee Amount
function fetch_order_fee($order, $tab_name) {
    $fee_total = 0.00;
    if ($tab_name === 'Zahlung PayPal') {
        $paypal_fee = $order->get_meta('_ppcp_paypal_fees', true);
        $fee_total = $paypal_fee ? floatval($paypal_fee) : 0.00;
    } elseif ($tab_name === 'Zahlung Stripe-Klarna') {
        $stripe_fee = $order->get_meta('_stripe_fee', true);
        $fee_total = $stripe_fee ? floatval($stripe_fee) : 0.00;
    }
    return $fee_total;
}
