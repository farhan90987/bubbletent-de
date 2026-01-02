<?php 

namespace MWEW\Inc\Orders;


class Tax_Exempt {
    public function __construct() {
        add_filter('woocommerce_order_is_vat_exempt', [$this, 'handle_tax'], 10, 2);
    }

    public function handle_tax($is_exempt, $order) {
        return true;
    }
}