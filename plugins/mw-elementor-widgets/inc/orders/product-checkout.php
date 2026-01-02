<?php 

namespace MWEW\Inc\Orders;

use MWEW\Inc\Logger\Logger;
use MWEW\Inc\Services\Order_Repo;

class Product_Checkout {

    public function __construct() {
        add_filter('woocommerce_cart_calculate_fees', [$this, 'apply_discount_to_cart_total'], 10, 1);
    }


    public function apply_discount_to_cart_total($cart) {
        if (!defined('DOING_AJAX')) {
            return;
        }

        $applied_coupons = $cart->get_applied_coupons();
        if (empty($applied_coupons)) {
            return;
        }

        $cart_subtotal = $cart->get_subtotal();

        $extra_item_amt = Order_Repo::get_extra_items_amt();

        foreach ($applied_coupons as $coupon_code) {
            $coupon          = new \WC_Coupon($coupon_code);
            $discount_type   = $coupon->get_discount_type();
            $discount_amount = $coupon->get_amount();

            if($discount_amount < $cart_subtotal || $discount_type == 'percent') continue;

            $applied_discount = 0;
            $eligible_total = max(0, $cart_subtotal);

            $item_discount = $discount_amount - $cart_subtotal;
            $applied_discount = min($item_discount, $eligible_total);

            $extra_discount = min($applied_discount, $extra_item_amt);

            if ($extra_discount > 0) {
                

                $discount_total = $cart->get_discount_total();
                $cart->set_discount_total($discount_total + $extra_discount);


                $cart_total = ($cart_subtotal + $extra_item_amt) - ($discount_total + $extra_discount);

                add_filter('woocommerce_cart_get_total', function($total) use($cart_total){

                    return $cart_total;
                });
            }
        }
    }

}







