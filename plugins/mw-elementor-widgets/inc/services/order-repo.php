<?php 

namespace MWEW\Inc\Services;


class Order_Repo{

    public static function get_extra_items_amt(){
        $extra_charges = 0;
        foreach (WC()->cart->get_fees() as $key => $fee) {
			if ('Shipping' !== $fee->name) {
                $extra_charges += $fee->amount;
			}
		}

        return $extra_charges;
    }


    public static function get_extra_order_items_amt( $order ) {
        if ( ! $order instanceof \WC_Order ) {
            return 0;
        }

        $total_fees      = 0;
        $line_items_fee  = $order->get_items( 'fee' );

        foreach ( $line_items_fee as $fee_item ) {

            $total_fees += $fee_item->get_total();
        }

        return $total_fees;
    }
}