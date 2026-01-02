<?php
/**
 * Plugin Name: Custom WC Invoice Override
 * Plugin URI:  https://example.com
 * Description: Override WP WC Invoice PDF view-order download/button and provide public download by order_id (survives plugin updates).
 * Version:     1.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * License:     GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CUSTOM_WC_Invoice_Override {

	/**
	 * Initialize: wait until plugins_loaded to be able to remove plugin hooks.
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ), 20 );
	}

	/**
	 * Called on plugins_loaded: remove plugin hooks and register our own.
	 */
	public static function plugins_loaded() {
		// Try to remove original plugin hooks if class exists.
		$orig_class = 'WP_WC_Invoice_Pdf_View_Order_Download';

		if ( class_exists( $orig_class ) ) {
			// Remove original download button action (if plugin added it)
			// Original doc said it's hooked to: woocommerce_order_details_after_order_table
			// Try remove with multiple possible priorities (common default 10)
			remove_action( 'woocommerce_order_details_after_order_table', array( $orig_class, 'make_download_button' ), 10 );
			// If plugin adds instance-based action we can't always remove — but most register static method like above.

			// Remove original AJAX actions (both logged and not logged)
			remove_action( 'wp_ajax_woocommerce_wp_wc_invoice_pdf_view_order_invoice_download', array( $orig_class, 'download_pdf' ) );
			remove_action( 'wp_ajax_nopriv_woocommerce_wp_wc_invoice_pdf_view_order_invoice_download', array( $orig_class, 'download_pdf' ) );

			// Also attempt to remove refund action if exists
			remove_action( 'wp_ajax_woocommerce_wp_wc_invoice_pdf_view_order_refund_download', array( $orig_class, 'download_refund_pdf' ) );
			remove_action( 'wp_ajax_nopriv_woocommerce_wp_wc_invoice_pdf_view_order_refund_download', array( $orig_class, 'download_refund_pdf' ) );
		}

		// Now add our overrides
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'make_download_button' ), 10, 1 );

		// Register AJAX handlers for logged-in and guests
		add_action( 'wp_ajax_woocommerce_wp_wc_invoice_pdf_view_order_invoice_download', array( __CLASS__, 'download_pdf' ) );
		add_action( 'wp_ajax_nopriv_woocommerce_wp_wc_invoice_pdf_view_order_invoice_download', array( __CLASS__, 'download_pdf' ) );

		// Refund download (optional) — we'll add same-public behavior if plugin used refund downloads
		add_action( 'wp_ajax_woocommerce_wp_wc_invoice_pdf_view_order_refund_download', array( __CLASS__, 'download_refund_pdf' ) );
		add_action( 'wp_ajax_nopriv_woocommerce_wp_wc_invoice_pdf_view_order_refund_download', array( __CLASS__, 'download_refund_pdf' ) );
	}

	/**
	 * Output download button on order detail page. This mirrors plugin placement but uses our public link.
	 *
	 * @param WC_Order $order
	 */
	public static function make_download_button( $order ) {
		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		// Option checks from original plugin are intentionally omitted/kept minimal.
		// If you want to keep plugin options (e.g. status check), you can add them here.

		$order_id = $order->get_id();

		// Generate admin-ajax URL with nonce.
		$ajax_action = 'woocommerce_wp_wc_invoice_pdf_view_order_invoice_download';
		$url = admin_url( 'admin-ajax.php?action=' . rawurlencode( $ajax_action ) . '&order_id=' . rawurlencode( $order_id ) );
		// Add nonce param (name 'security' since we check with check_ajax_referer)
		$nonce_url = wp_nonce_url( $url, 'wp-wc-invoice-pdf-download-view-order' );

		// Button text: allow filtering
		$button_text = apply_filters( 'custom_wc_invoice_override_button_text', __( 'Download Invoice Pdf', 'custom-wc-invoice-override' ), $order );

		// Target / download attributes (mimic plugin defaults)
		$target_attr = ( get_option( 'wp_wc_invoice_pdf_view_order_link_behaviour', 'new' ) == 'new' ) ? ' target="_blank"' : '';
		$download_attr = ( get_option( 'wp_wc_invoice_pdf_view_order_download_behaviour', 'inline' ) == 'inline' ) ? '' : ' download';
		$a_attributes = trim( $target_attr . $download_attr );
		$inline_style = apply_filters( 'wp_wc_invoice_pdf_download_buttons_inline_style', 'margin: 0.15em 0;' );

		// Allow other code to override completely
		if ( has_action( 'wp_wc_invoice_pdf_view_order_button' ) ) {
			do_action( 'wp_wc_invoice_pdf_view_order_button', esc_url( $nonce_url ), $target_attr, $a_attributes, $button_text, $order );
			return;
		}

		// Print button
		?>
		<p class="download-invoice-pdf">
			<a href="<?php echo esc_url( $nonce_url ); ?>" class="button"<?php echo ( $a_attributes ? ' ' . wp_kses_post( $a_attributes ) : '' ); ?> style="<?php echo esc_attr( $inline_style ); ?>"><?php echo esc_html( $button_text ); ?></a>
		</p>
		<?php
	}

	/**
	 * AJAX handler to create/download invoice PDF using only order_id (no login, no order key).
	 * Uses check_ajax_referer for nonce, but nonces are not identity checks for guests.
	 */
	public static function download_pdf() {
		// Check nonce — name 'security' because wp_nonce_url attaches _wpnonce param
		if ( ! check_ajax_referer( 'wp-wc-invoice-pdf-download-view-order', 'security', false ) ) {
			wp_die( __( 'Security check failed.', 'custom-wc-invoice-override' ), '', array( 'response' => 403 ) );
		}

		// Get order_id from request (allow GET/POST)
		$order_id = isset( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : 0;
		if ( ! $order_id ) {
			wp_die( __( 'Missing order ID.', 'custom-wc-invoice-override' ), '', array( 'response' => 400 ) );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_die( __( 'Invalid order.', 'custom-wc-invoice-override' ), '', array( 'response' => 404 ) );
		}

		// Optionally restrict by status: e.g., only allow 'completed' or 'processing'
		// Uncomment and edit statuses if you want to restrict:
		// $allowed_statuses = array( 'completed', 'processing' );
		// if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
		//     wp_die( __( 'Invoice download not allowed for this order status.', 'custom-wc-invoice-override' ), '', array( 'response' => 403 ) );
		// }

		// Trigger action for other hooked code
		do_action( 'wp_wc_invoice_pdf_before_frontend_download', $order );

		$args = array(
			'order'         => $order,
			'output_format' => 'pdf',
			'output'        => get_option( 'wp_wc_invoice_pdf_view_order_download_behaviour' ), // e.g. inline or download
			'filename'      => apply_filters(
				'wp_wc_invoice_pdf_frontend_filename',
				get_option( 'wp_wc_invoice_pdf_file_name_frontend', get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-german-market' ) ),
				$order
			),
			'frontend'      => 'yes',
		);

		// Create PDF - relies on original plugin class WP_WC_Invoice_Pdf_Create_Pdf being present
		if ( class_exists( 'WP_WC_Invoice_Pdf_Create_Pdf' ) ) {
			new WP_WC_Invoice_Pdf_Create_Pdf( $args );
		} else {
			// If the original PDF generator class isn't available, notify
			wp_die( __( 'PDF generator not available. Make sure the invoice plugin is active.', 'custom-wc-invoice-override' ), '', array( 'response' => 500 ) );
		}

		exit;
	}

	/**
	 * Refund download override (mirrors plugin behavior but public via refund_id).
	 * If you don't need refund downloads, this can be left as-is.
	 */
	public static function download_refund_pdf() {
		if ( ! check_ajax_referer( 'wp-wc-refund-pdf-download-view-order', 'security', false ) ) {
			wp_die( __( 'Security check failed.', 'custom-wc-invoice-override' ), '', array( 'response' => 403 ) );
		}

		$refund_id = isset( $_REQUEST['refund_id'] ) ? absint( $_REQUEST['refund_id'] ) : 0;
		if ( ! $refund_id ) {
			wp_die( __( 'Missing refund ID.', 'custom-wc-invoice-override' ), '', array( 'response' => 400 ) );
		}

		// Refunds are stored as orders of type 'refund' in WooCommerce
		$refund = wc_get_order( $refund_id );
		if ( ! $refund ) {
			wp_die( __( 'Invalid refund.', 'custom-wc-invoice-override' ), '', array( 'response' => 404 ) );
		}

		$order_id = $refund->get_parent_id();
		$order    = $order_id ? wc_get_order( $order_id ) : false;

		// Trigger actions and create PDF using original class
		do_action( 'wp_wc_invoice_pdf_before_refund_backend_download', $refund_id );

		$args = array(
			'order'         => $order,
			'refund'        => $refund,
			'output_format' => 'pdf',
			'output'        => get_option( 'wp_wc_invoice_pdf_view_order_download_behaviour' ),
			'filename'      => apply_filters( 'wp_wc_invoice_pdf_refund_backend_filename', 'Refund-' . $refund_id ),
			'frontend'      => 'yes',
		);

		if ( class_exists( 'WP_WC_Invoice_Pdf_Create_Pdf' ) ) {
			new WP_WC_Invoice_Pdf_Create_Pdf( $args );
		} else {
			wp_die( __( 'PDF generator not available. Make sure the invoice plugin is active.', 'custom-wc-invoice-override' ), '', array( 'response' => 500 ) );
		}

		exit;
	}
}

// Initialize plugin
CUSTOM_WC_Invoice_Override::init();
