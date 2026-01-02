<?php 

namespace MWEW\Inc\Orders;

use MWEW\Inc\Logger\Logger;
use MWEW\Inc\Services\Order_Repo;
use WC_Order;
use WC_Coupon;
use WC_Order_Item_Fee;

class Order_Coupon {
    public function __construct() {
        add_action('woocommerce_before_order_object_save', [$this, 'coupon_change'], 10, 1);
    }

    public function coupon_change($order) {
        if (!$order instanceof WC_Order) return;

        if ( ! is_admin()) {
            return;
        }

        $current_coupons = $order->get_coupon_codes();

        if (empty($current_coupons)) return;

        $extra_item_amt = Order_Repo::get_extra_order_items_amt($order);
        $subtotal = floatval($order->get_subtotal());
        $order_total = $subtotal + $extra_item_amt;
        $discount_amt = 0;
        $discount_types = [];

        $remaining_total = $subtotal;

        foreach ($current_coupons as $coupon_code) {
            $coupon = new WC_Coupon($coupon_code);
            $discount_type = $coupon->get_discount_type();
            $coupon_amount = floatval($coupon->get_amount());
            $discount_types[] = $discount_type;
            if ($discount_type === 'fixed_cart') {
                $discount_amt += min( $coupon_amount, ($subtotal + $extra_item_amt));
            }
            else if($discount_type === 'percent') {
                if($remaining_total <= 0) continue;
                $discount = ($remaining_total * $coupon_amount) / 100;
                $discount_amt += $discount;
                $remaining_total -= $discount;
            }
        }
    
        
        Logger::debug("Total discount to apply: $discount_amt");
        if($order_total < $discount_amt){
            $discount_amt = $order_total;
        }
        // Logger::debug("Total discount to apply: $discount_amt");
        $order->set_discount_total( $discount_amt);
        
        $order->set_total($order_total - $discount_amt);
    }

}
