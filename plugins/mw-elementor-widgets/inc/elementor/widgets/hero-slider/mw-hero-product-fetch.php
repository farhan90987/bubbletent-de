<?php
namespace MWEW\Inc\Elementor\Widgets\Hero_Slider;

use MWEW\Inc\Logger\Logger;

class MW_Hero_Product_Fetch {

    public function __construct() {
        add_action('wp_ajax_mwew_fetch_product', [$this, 'fetch_product_callback']);
        add_action('wp_ajax_nopriv_mwew_fetch_product', [$this, 'fetch_product_callback']);

        add_filter('woocommerce_add_to_cart_redirect', [$this, 'redirect_to_checkout_if_buy_now']);
        add_filter('woocommerce_add_to_cart_validation', [$this, 'mwai_popup_form_validation'], 10, 3);
    }

    public function mwai_popup_form_validation($passed, $product_id, $quantity) {
        if (isset($_REQUEST['mwew_buy_now']) && $_REQUEST['mwew_buy_now'] == '1') {
            if (!empty(get_post_meta($product_id, '_wpdesk_pdf_coupons', true))) {

                $name = isset($_REQUEST['flexible_coupon_recipient_name']) ? trim($_REQUEST['flexible_coupon_recipient_name']) : '';
                if (strlen($name) < 2) {
                    wc_add_notice(__('Please enter a valid name (at least 2 characters).', 'mwew'), 'error');
                    return false;
                }

                $message = isset($_REQUEST['flexible_coupon_recipient_message']) ? trim($_REQUEST['flexible_coupon_recipient_message']) : '';
                if (strlen($message) < 5) {
                    wc_add_notice(__('Please enter a valid message (at least 5 characters).', 'mwew'), 'error');
                    return false;
                }
            }
        }

        return $passed;
    }

    public function redirect_to_checkout_if_buy_now($url) {
        if (isset($_REQUEST['mwew_buy_now']) && $_REQUEST['mwew_buy_now'] == '1') {
            return wc_get_cart_url();
        }
        return $url;
    }

    public function fetch_product_callback() {
        // if (!empty($_GET['lang']) && function_exists('wpml_switch_language')) {
        //     do_action('wpml_switch_language', sanitize_text_field($_GET['lang']));
        // }

        if (empty($_GET['product_id'])) {
            wp_send_json_error(__('No product ID provided', 'mwew'));
            wp_die();
        }

        $product_id = intval($_GET['product_id']);

        // if (function_exists('wpml_object_id')) {
        //     $product_id = apply_filters('wpml_object_id', $product_id, 'product', true, apply_filters('wpml_current_language', null));
        // }

        $product = wc_get_product($product_id);

        if (!$product) {
            echo '<p>' . esc_html__('Product not found.', 'mwew') . '</p>';
            wp_die();
        }

        $pdf_coupone = get_post_meta($product->get_id(), '_wpdesk_pdf_coupons', true);

        $title = $product->get_name();
        $price_html = $product->get_price_html();
        $description = $product->get_short_description();
        $featured_img_url = get_the_post_thumbnail_url($product_id, 'large');

        ob_start();
        ?>
        <div id="mwew-popup" class="custom-product-popup" style="display:flex; gap:30px;">
            <div class="popup-image" style="flex:1; min-width:200px;">
                <?php if ($featured_img_url): ?>
                    <div style="background-image: url(<?php echo esc_url($featured_img_url); ?>); width:100%; height: 100%; border-radius: 8px; background-size: cover; background-position: center;"></div>
                <?php endif; ?>
            </div>

            <div class="popup-details" style="flex:1;">
                <h2 class="popup-title" style="font-size:24px; margin-bottom:10px;"><?php echo esc_html($title); ?></h2>
                <div class="popup-price" style="font-weight:bold; font-size:18px; color:#3D6B50; margin-bottom:15px;">
                    <?php echo wp_kses_post($price_html); ?>
                </div>
                <div class="popup-description" style="font-size:16px; line-height:1.4; color:#555; margin-bottom:20px;">
                    <?php echo wp_kses_post($description); ?>
                </div>

                <?php if ($product->is_type('variable')): ?>
                    <?php
                    $available_variations = $product->get_available_variations();
                    $attributes = $product->get_variation_attributes();
                    ?>
                    <form class="variations_form cart" action="<?php echo esc_url($product->get_permalink()); ?>" method="post" enctype="multipart/form-data" data-product_id="<?php echo esc_attr($product_id); ?>" data-product_variations="<?php echo esc_attr(json_encode($available_variations)); ?>" data-alert="<?php echo esc_attr(__('Please fill in all required fields correctly.', 'mwew')); ?>">
                        <table class="variations" cellspacing="0" style="width:100%; margin-bottom: 15px;">
                            <tbody>
                                <?php foreach ($attributes as $attribute_name => $options): ?>
                                    <tr>
                                        <td class="label" style="padding:8px 10px; font-weight:bold;"><?php echo esc_html(wc_attribute_label($attribute_name)); ?></td>
                                        <td class="value" style="padding:8px 10px;">
                                            <select name="attribute_<?php echo esc_attr(sanitize_title($attribute_name)); ?>" style="width:100%; padding:6px;">
                                                <option value=""><?php esc_html_e('Choose an option', 'woocommerce'); ?></option>
                                                <?php
                                                foreach ($options as $option) {
                                                    $term = get_term_by('slug', $option, $attribute_name);
                                                    $label = $term ? apply_filters('wpml_translate_single_string', $term->name, 'woocommerce', $term->name) : ucfirst($option);
                                                    echo '<option value="' . esc_attr($option) . '">' . esc_html($label) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="single_variation_wrap">
                            <div class="woocommerce-variation single_variation" role="alert" aria-relevant="additions">
                                <div class="woocommerce-variation-description"></div>
                                <div class="woocommerce-variation-price">
                                    <span class="price">
                                        <span class="woocommerce-Price-amount amount">
                                            <bdi><?php echo esc_html($product->get_price()); ?>&nbsp;<span class="woocommerce-Price-currencySymbol"><?php echo esc_html(get_woocommerce_currency_symbol()); ?></span></bdi>
                                        </span>
                                    </span>
                                </div>
                                <div class="woocommerce-variation-availability"></div>
                            </div>

                            <div class="woocommerce-variation-add-to-cart variations_button woocommerce-variation-add-to-cart-enabled">
                                <?php if (!empty($pdf_coupone) && $pdf_coupone === 'yes'): ?>
                                    <div class="pdf-coupon-fields" style="clear: both;">
                                        <p class="form-row validate-required">
                                            <label for="flexible_coupon_recipient_name" class="required_field"><?php _e('For', 'mwew'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
                                            <span class="woocommerce-input-wrapper">
                                                <input type="text" class="input-text" name="flexible_coupon_recipient_name" id="flexible_coupon_recipient_name" required="1" aria-required="true" maxlength="100" minlength="2">
                                            </span>
                                        </p>
                                        <p class="form-row validate-required">
                                            <label for="flexible_coupon_recipient_message" class="required_field"><?php _e('Text', 'mwew'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
                                            <span class="woocommerce-input-wrapper">
                                                <textarea name="flexible_coupon_recipient_message" class="input-text" id="flexible_coupon_recipient_message" rows="1" maxlength="250" minlength="5" required="1" aria-required="true"></textarea>
                                            </span>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <div class="e-atc-qty-button-holder">
                                    <div class="quantity" style="display: none;">
                                        <label class="screen-reader-text" for="quantity_popup_<?php echo esc_attr($product_id); ?>"><?php esc_html_e('Voucher quantity', 'mwew'); ?></label>
                                        <input type="number" id="quantity_popup_<?php echo esc_attr($product_id); ?>" class="input-text qty text" name="quantity" value="1" min="1" step="1">
                                    </div>
                                    <button type="submit" class="single_add_to_cart_button button alt"><?php esc_html_e('Add to Cart', 'mwew'); ?></button>
                                </div>

                                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
                                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                                <input type="hidden" name="variation_id" class="variation_id" value="">
                                <input type="hidden" name="mwew_buy_now" value="1" />
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <form class="cart" action="<?php echo esc_url($product->get_permalink()); ?>" method="post" enctype='multipart/form-data'>
                        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>" />
                        <input type="hidden" name="mwew_buy_now" value="1" />
                        <button type="submit" class="single_add_to_cart_button button alt" style="background:#3D6B50; color:#fff; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">
                            <?php esc_html_e('Add to Cart', 'mwew'); ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php
        echo ob_get_clean();
        wp_die();
    }
}
