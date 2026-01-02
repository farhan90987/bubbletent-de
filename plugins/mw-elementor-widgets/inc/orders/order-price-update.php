<?php 

namespace MWEW\Inc\Orders;

use MWEW\Inc\Logger\Logger;
use MWEW\Inc\Services\Order_Repo;
use WC_Order;
use WC_Order_Item_Fee;
use WC_Coupon;

class Order_Price_Update {
    public function __construct() {
        add_action('wp_ajax_mwew_save_price_data', [$this, 'save_price_data']);
    }

    public function save_price_data() {
        if (!current_user_can('edit_shop_orders')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'mwew'));
        }

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $price    = isset($_POST['price']) ? floatval($_POST['price']) : 0;

        if (!$order_id || $price < 0) {
            wp_send_json_error(__('Invalid order ID or price.', 'mwew'));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(__('Order not found.', 'mwew'));
        }

        try {
            $applied_coupons = $order->get_coupon_codes();
            foreach ($applied_coupons as $coupon_code) {
                $order->remove_coupon($coupon_code);
            }

            foreach ($order->get_items() as $item_id => $item) {
                if ($item->get_type() === 'coupon') continue; 
                $quantity = $item->get_quantity(); 
                $item->set_subtotal($price * $quantity); 
                $item->set_total($price * $quantity); 
                $item->save();
            }

            foreach ($applied_coupons as $coupon_code) {
                $coupon = new WC_Coupon($coupon_code);
                if ($coupon->get_amount() > 0) {
                    $order->apply_coupon($coupon_code);
                }
            }
            
            $order->add_order_note(sprintf(
                __('Order item price updated to %s. Coupons preserved and reapplied.', 'mwew'),
                wc_price($price)
            ));

            wp_send_json_success(__('Price updated successfully, coupons preserved.', 'mwew'));
        } catch (\Exception $e) {
            Logger::debug('Error updating order price: ' . $e->getMessage());
            wp_send_json_error(__('Failed to update price.', 'mwew'));
        }
    }
}
