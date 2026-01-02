<?php

defined( 'ABSPATH' ) || exit;
?>

<div class="yaymail-product-name <?php echo $show_hyper_links ? 'yaymail-product-name__hyper-link' : ''; ?>">
    <?php
    if ( empty( $item ) ) {
        echo wp_kses_post( $product_name );
    } else {
        echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', ! $show_hyper_links ? $product_name : $product_hyper_link, $item, false ) );
    }
    ?>
</div>


