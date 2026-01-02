<?php 

namespace MWEW\Inc\Orders;

use MWEW\Inc\Logger\Logger;

class Order_Meta_Save {
    public function __construct() {
        add_action('wp_ajax_mwew_save_smoobu_data', [$this, 'save_smoobu_data']);
    }

    public function save_smoobu_data() {
        if ( ! current_user_can( 'edit_shop_orders' ) ) {
            wp_send_json_error( __( 'You do not have permission to perform this action.', 'smoobu-calendar' ) );
        }

        $order_id       = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $check_in_date  = isset($_POST['check_in_date']) ? sanitize_text_field($_POST['check_in_date']) : '';
        $check_out_date = isset($_POST['check_out_date']) ? sanitize_text_field($_POST['check_out_date']) : '';
     
        if ( ! $order_id ) {
            wp_send_json_error( __( 'Invalid order ID.', 'smoobu-calendar' ) );
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            wp_send_json_error( __( 'Order not found.', 'smoobu-calendar' ) );
        }

        try{
            $order->update_meta_data('smoobu_calendar_start', $check_in_date);
            $order->update_meta_data('smoobu_calendar_end', $check_out_date);


            $nights = 1;
            if ( $check_in_date && $check_out_date ) {
                $start = new \DateTime( $check_in_date );
                $end   = new \DateTime( $check_out_date );
                $diff  = $start->diff( $end );
                $nights = max( 1, (int) $diff->days ); 
            }

            foreach ( $order->get_items() as $item_id => $item ) {
                $product = $item->get_product();
                if ( ! $product ) {
                    continue;
                }

                $product_id = $item->get_product_id();

                $base_price = (float) get_post_meta( $product_id, 'sa_cfw_cog_amount', true );

                if ( $base_price <= 0 ) {
                    $base_price = (float) $product->get_price();
                }

                $item->set_quantity( $nights );

                $item->set_subtotal( $base_price * $nights );
                $item->set_total( $base_price * $nights );

            }

            $order->calculate_totals();
            $order->save();

             wp_send_json_success( __( 'Booking data saved successfully.', 'smoobu-calendar' ) );
        }catch (\Exception $e) {
            Logger::error('Error saving Smoobu data: ' . $e->getMessage());
            wp_send_json_error( __( 'Failed to save booking data.', 'smoobu-calendar' ) );
        }
       
    }
}