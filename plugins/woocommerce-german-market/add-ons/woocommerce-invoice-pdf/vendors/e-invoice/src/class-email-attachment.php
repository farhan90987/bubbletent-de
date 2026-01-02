<?php

namespace MarketPress\German_Market\E_Invoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class E_Invoice_Email_Attachment {

	/**
	* Add XML as an attachement to chosen e-mails
	*
	* @hook woocommerce_email_attachments
	* @param Array $attachments
	* @param String $status
	* @param WC_Order $order
	* @return Array
	*/
	public static function add_attachment( $attachments, $status, $order ) {

		if ( ! ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) ) {
			return $attachments;
		}

		$add_attachment = false;
		
		if ( \WGM_Helper::is_email_status_for_refund( $status ) ) {
			$status = 'customer_refunded_order';
		}

		// check if we need to add the invoice
		$selected_mails = get_option( 'german_market_einvoice_recipients_xml_emails', array() );
		if ( in_array( $status, $selected_mails ) ) {
			$add_attachment = true;
		}

		if ( ! $add_attachment ) {
			return $attachments;
		}

		if ( 'customer_refunded_order' === $status ) {
			$used_order = \WGM_Helper::get_current_refund_by_order( $order );
		} else {
			$used_order = $order;
		}

		$e_invoice_order_conditions = new E_Invoice_Order_Conditions();
		if ( ! $e_invoice_order_conditions->order_needs_e_invoice( $order ) ) {
			return $attachments;	
		}

		$is_frontend = true;
		$e_invoice = new E_Invoice_Order( $used_order, $is_frontend );
		$attachments[] = $e_invoice->save_xml_temp_and_get_path();

		return $attachments;
	}
}
