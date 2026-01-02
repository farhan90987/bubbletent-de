<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include_once plugin_dir_path(__FILE__) . 'includes/email-handler.php'; // Email handler

// Admin menu page
add_action('admin_menu', function() {
    add_menu_page(
        'Christmas Order',
        'Christmas Order',
        'manage_woocommerce',
        'christmas-order',
        'render_christmas_order',
        'dashicons-printer',
        56
    );
});

function render_christmas_order() {
    ?>
    <div class="wrap">
        <h1>Christmas Printer Email Send</h1>
        <form method="post">
            <?php wp_nonce_field('fetch_orders_action', 'fetch_orders_nonce'); ?>

            <?php
            // Save printer email
            if (isset($_POST['save_printer_email'])) {
                if (!empty($_POST['printer_email']) && is_email($_POST['printer_email'])) {
                    update_option('printer_email', sanitize_email($_POST['printer_email']));
                    echo "<div class='updated'><p>✅ Printer Email Updated!</p></div>";
                } else {
                    echo "<div class='error'><p>❌ Invalid email address.</p></div>";
                }
            }

            $printer_email = get_option('printer_email', '');

            // Save shipment period (for later use elsewhere)
            if (isset($_POST['save_shipment_period'])) {
                if (!empty($_POST['shipment_period'])) {
                    update_option('shipment_period', sanitize_text_field($_POST['shipment_period']));
                    echo "<div class='updated'><p>✅ Shipment Period Updated!</p></div>";
                } else {
                    echo "<div class='error'><p>❌ Please select a shipment period.</p></div>";
                }
            }

            // Save cart options
            if (isset($_POST['save_cart_options'])) {
                update_option(
                    'christmas_auto_add_to_cart',
                    isset($_POST['christmas_auto_add_to_cart']) ? 'yes' : 'no'
                );

                update_option(
                    'christmas_enable_cart_upsell',
                    isset($_POST['christmas_enable_cart_upsell']) ? 'yes' : 'no'
                );

                echo "<div class='updated'><p>✅ Cart options updated!</p></div>";
            }

            // Get saved values
            $auto_add_to_cart   = get_option('christmas_auto_add_to_cart', 'no');
            $enable_cart_upsell = get_option('christmas_enable_cart_upsell', 'no');


            $shipment_period = get_option('shipment_period', '');
            ?>

            

            <label for="printer_email"><strong>Printer Email:</strong></label>
            <input type="email" name="printer_email" value="<?php echo esc_attr($printer_email); ?>" placeholder="example@domain.com" style="min-width:260px;margin-right:10px;">
            <input type="submit" name="save_printer_email" class="button" value="Save Printer Email">

            <br><br>

            <!-- Shipment Period Selector (saved in options for other templates/use) -->
            <label for="shipment_period"><strong>Shipment Period:</strong></label>
            <select name="shipment_period" style="min-width:180px;margin-right:10px;">
                <option value="">Select Shipment Period</option>
                <option value="1st" <?php selected($shipment_period, '1st'); ?>>1st Wave</option>
                <option value="2nd" <?php selected($shipment_period, '2nd'); ?>>2nd Wave</option>
                <option value="3rd" <?php selected($shipment_period, '3rd'); ?>>3rd Wave</option>
            </select>
            <input type="submit" name="save_shipment_period" class="button" value="Save Shipment Period">

            <br><br>

            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date">
            <input type="submit" name="fetch_orders" class="button button-primary" value="Fetch Orders">
            <br><br>

            <strong>Cart Settings:</strong><br><br>

            <label>
                <input type="checkbox"
                    name="christmas_auto_add_to_cart"
                    value="yes"
                    <?php checked($auto_add_to_cart, 'yes'); ?>>
                Auto Add to Cart
            </label>

            <br>

            <label>
                <input type="checkbox"
                    name="christmas_enable_cart_upsell"
                    value="yes"
                    <?php checked($enable_cart_upsell, 'yes'); ?>>
                Enable Cart Upsell
            </label>

            <br><br>

            <input type="submit"
                name="save_cart_options"
                class="button"
                value="Save Cart Settings">

        </form>

        <?php
        if (isset($_POST['fetch_orders']) && check_admin_referer('fetch_orders_action', 'fetch_orders_nonce')) {
            $start = sanitize_text_field($_POST['start_date']);
            $end   = sanitize_text_field($_POST['end_date']);
            fetch_and_display_order($start, $end);
        }
        ?>
    </div>
    <?php
}

function fetch_and_display_order($start_date, $end_date) {
    global $wpdb;

    if (empty($start_date) || empty($end_date)) {
        echo "<p>Please select date</p>";
        return;
    }

    $printer_email = get_option('printer_email', '');
    if (empty($printer_email)) {
        echo "<div class='error'><p><strong>⚠ Please set Printer Email first!</strong></p></div>";
        return;
    }

    $product_ids = array(222556, 222557, 222558);
    $statuses = 'wc-completed';

    $start_dt = date('Y-m-d H:i:s', strtotime($start_date));
    $end_dt   = date('Y-m-d H:i:s', strtotime($end_date . ' 23:59:59'));

    $query = $wpdb->prepare("
        SELECT DISTINCT p.ID
        FROM {$wpdb->prefix}posts p
        INNER JOIN {$wpdb->prefix}woocommerce_order_items oi ON p.ID = oi.order_id
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
        WHERE p.post_type = 'shop_order'
        AND p.post_status IN ('". $statuses . "')
        AND p.post_date BETWEEN %s AND %s
        AND oim.meta_key = '_product_id'
        AND oim.meta_value IN (" . implode(',', $product_ids) . ")
    ", $start_dt, $end_dt);

    $order_ids = $wpdb->get_col($query);

    if (empty($order_ids)) {
        echo "<p>No orders found.</p>";
        return;
    }

    echo "<table class='widefat striped'>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Email</th>
                <th>Customer Name</th>
                <th>Status</th>
                <th>Create date</th>
                <th>Voucher code</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>";

    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) continue;

        $order_email   = $order->get_billing_email();
        $status        = wc_get_order_status_name($order->get_status());
        $create_date   = $order->get_date_created()->date('d-m-Y');
        $user_name     = $order->get_formatted_billing_full_name();
        $voucher_code  = get_post_meta($order_id, '_voucher_code', true);

        echo "<tr>
            <td>{$order_id}</td>
            <td>{$order_email}</td>
            <td>{$user_name}</td>
            <td>{$status}</td>
            <td>{$create_date}</td>
            <td>{$voucher_code}</td>
            <td>
                <a href='#' class='button resend-email' 
                    data-email='". esc_attr($printer_email) ."' 
                    data-voucher='". esc_attr($voucher_code) ."'
                    data-order-id='{$order_id}'>
                    Send Printer Email
                </a>
            </td>
        </tr>";
    }

    echo "</tbody></table>";

    $nonce = wp_create_nonce('send_all_emails_ajax_printer');
    echo "<br><div style='display:none' id='progress-container'><div id='progress-bar'>0%</div></div>";
    echo "<button id='send-all-emails' class='button button-primary' data-nonce='{$nonce}' data-orders='" . esc_attr(json_encode($order_ids)) . "'>Send Printer Emails</button>";
}

// AJAX batch email
add_action('wp_ajax_send_all_emails_printer', function() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'send_all_emails_ajax_printer')) {
        wp_send_json_error(['message' => 'Invalid security token.']);
    }

    $printer_email = get_option('printer_email', '');

    if (empty($printer_email)) {
        wp_send_json_error(['message' => 'Printer Email not configured!']);
    }

    if (empty($_POST['order_ids']) || !is_array($_POST['order_ids'])) {
        wp_send_json_error(['message' => 'No orders received.']);
    }

    $order_ids = array_map('intval', $_POST['order_ids']);
    $batch = isset($_POST['batch']) ? intval($_POST['batch']) : 0;
    $batch_size = 50;

    $batch_orders = array_slice($order_ids, $batch * $batch_size, $batch_size);
    $sent_count = 0;

    foreach ($batch_orders as $order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            $voucher_code = get_post_meta($order_id, '_voucher_code', true);
            // Note: send_email_to_printer signature remains ( $order, $voucher_code, $printer_email )
            send_email_to_printer($order, $voucher_code, $printer_email);
            $sent_count++;
        }
    }

    $has_more = (($batch + 1) * $batch_size) < count($order_ids);
    wp_send_json_success([
        'message'   => "Batch {$batch} - {$sent_count} emails sent.",
        'has_more'  => $has_more,
        'processed' => ($batch + 1) * $batch_size > count($order_ids) ? count($order_ids) : ($batch + 1) * $batch_size,
        'total'     => count($order_ids)
    ]);
});

// AJAX Single email
add_action('wp_ajax_resend_order_email_printer', function () {
    if (!isset($_POST['order_id']) || !check_ajax_referer('resend_email_nonce_printer', 'nonce', false)) {
        wp_send_json_error('Invalid request');
    }

    $order_id = intval($_POST['order_id']);
    $printer_email = sanitize_email($_POST['email']);
    $voucher = sanitize_text_field($_POST['voucher']);

    $order = wc_get_order($order_id);

    if ($order) {
        // Keep send_email_to_printer signature unchanged
        send_email_to_printer($order, $voucher, $printer_email);
        wp_send_json_success('Email sent');
    } else {
        wp_send_json_error('Order not found');
    }
});

// Admin JS
add_action('admin_footer', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'christmas-order') {
        ?>
        <style>
            #progress-container {
                width: 100%;
                background: #eee;
                margin-top: 15px;
                display: none;
                margin-bottom: 10px;
            }
            #progress-bar {
                width: 0%;
                height: 20px;
                background: #0073aa;
                color: #fff;
                text-align: center;
                line-height: 20px;
                font-size: 12px;
            }
        </style>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#send-all-emails').on('click', function(e) {
                e.preventDefault();
                let button = $(this);
                let nonce = button.data('nonce');
                let orders = button.data('orders');
                let total = orders.length;
                let batch = 0;

                $('#progress-container').show();
                $('#progress-bar').css('width', '0%').text('0%');

                button.prop('disabled', true).text('Sending batch 1...');

                function sendBatch() {
                    $.post(ajaxurl, {
                        action: 'send_all_emails_printer',
                        nonce: nonce,
                        order_ids: orders,
                        batch: batch
                    }, function(response) {
                        if (response.success) {
                            let processed = response.data.processed;
                            let percent = Math.round((processed / total) * 100);
                            $('#progress-bar').css('width', percent + '%').text(percent + '%');

                            if (response.data.has_more) {
                                batch++;
                                button.text('Sending batch ' + (batch + 1) + '...');
                                sendBatch();
                            } else {
                                $('#progress-bar').css('width', '100%').text('100%');
                                alert('✅ All emails have been sent!');
                                location.reload();
                            }
                        } else {
                            alert('Error: ' + response.data.message);
                            button.prop('disabled', false).text('Send All Emails in Batches');
                        }
                    });
                }

                sendBatch();
            });

            $('.resend-email').on('click', function(e) {
                e.preventDefault();
                let button = $(this);
                let orderId = button.data('order-id');
                let voucher = button.data('voucher');
                let email = button.data('email');
                button.text('Sending...');

                $.post(ajaxurl, {
                    action: 'resend_order_email_printer',
                    order_id: orderId,
                    voucher: voucher,
                    email: email,
                    nonce: '<?php echo wp_create_nonce("resend_email_nonce_printer"); ?>'
                }, function(response) {
                    alert(response.data || 'Something went wrong');
                    button.text('Send Printer Email');
                });
            });
        });
        </script>
        <?php
    }
});
