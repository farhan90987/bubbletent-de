<?php 

namespace MWEW\Inc\Orders;

class Order_Meta_Init {
    public function __construct() {
        new Order_Meta();
        new Order_Price_Update();
        new Order_Meta_Save();
        new Product_Checkout();
        new Order_Coupon();
        new Tax_Exempt();
    }
}
