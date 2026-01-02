<?php
namespace YayMail\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * OrderDetails Elements
 */
class OrderDetails extends BaseElement {

    use SingletonTrait;

    protected static $type = 'order_details';

    public $available_email_ids = [ YAYMAIL_WITH_ORDER_EMAILS ];

    public static function get_data( $attributes = [] ) {
        self::$icon = '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 20 20">
  <path d="M17.5,2.5v15H2.5V2.5h15M18,1H2c-.55,0-1,.45-1,1v16c0,.55.45,1,1,1h16c.55,0,1-.45,1-1V2c0-.55-.45-1-1-1h0Z"/>
  <path d="M18.05,8.1H1.82c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h16.23c.41,0,.75.34.75.75s-.34.75-.75.75Z"/>
  <path d="M18.05,13.55H1.82c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h16.23c.41,0,.75.34.75.75s-.34.75-.75.75Z"/>
  <path d="M18.05,18.99H1.82c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h16.23c.41,0,.75.34.75.75s-.34.75-.75.75Z"/>
  <path d="M12.75,18.8c-.41,0-.75-.34-.75-.75V1.82c0-.41.34-.75.75-.75s.75.34.75.75v16.23c0,.41-.34.75-.75.75Z"/>
  <path d="M7.27,18.8c-.41,0-.75-.34-.75-.75V1.82c0-.41.34-.75.75-.75s.75.34.75.75v16.23c0,.41-.34.75-.75.75Z"/>
</svg>';

        return [
            'id'        => uniqid(),
            'type'      => self::$type,
            'name'      => __( 'Order Details', 'yaymail' ),
            'icon'      => self::$icon,
            'group'     => 'woocommerce',
            'available' => true,
            'position'  => 190,
            'data'      => [
                'padding'              => [
                    'value_path'    => 'padding',
                    'component'     => 'Spacing',
                    'title'         => __( 'Padding', 'yaymail' ),
                    'default_value' => isset( $attributes['padding'] ) ? $attributes['padding'] : [
                        'top'    => '15',
                        'right'  => '50',
                        'bottom' => '15',
                        'left'   => '50',
                    ],
                    'type'          => 'style',
                ],
                'background_color'     => [
                    'value_path'    => 'background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Background color', 'yaymail' ),
                    'default_value' => isset( $attributes['background_color'] ) ? $attributes['background_color'] : '#fff',
                    'type'          => 'style',
                ],
                'title_color'          => [
                    'value_path'    => 'title_color',
                    'component'     => 'Color',
                    'title'         => __( 'Title color', 'yaymail' ),
                    'default_value' => isset( $attributes['title_color'] ) ? $attributes['title_color'] : YAYMAIL_COLOR_WC_DEFAULT,
                    'type'          => 'style',
                ],
                'text_color'           => [
                    'value_path'    => 'text_color',
                    'component'     => 'Color',
                    'title'         => __( 'Text color', 'yaymail' ),
                    'default_value' => isset( $attributes['text_color'] ) ? $attributes['text_color'] : YAYMAIL_COLOR_TEXT_DEFAULT,
                    'type'          => 'style',
                ],
                'border_color'         => [
                    'value_path'    => 'border_color',
                    'component'     => 'Color',
                    'title'         => __( 'Border color', 'yaymail' ),
                    'default_value' => isset( $attributes['border_color'] ) ? $attributes['border_color'] : YAYMAIL_COLOR_BORDER_DEFAULT,
                    'type'          => 'style',
                ],
                'font_family'          => [
                    'value_path'    => 'font_family',
                    'component'     => 'FontFamilySelector',
                    'title'         => __( 'Font family', 'yaymail' ),
                    'default_value' => isset( $attributes['font_family'] ) ? $attributes['font_family'] : YAYMAIL_DEFAULT_FAMILY,
                    'type'          => 'style',
                ],
                'rich_text'            => [
                    'value_path'    => 'rich_text',
                    'component'     => '',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => $attributes['rich_text'] ?? '[yaymail_order_details]',
                    'type'          => 'content',
                ],
                'payment_instructions' => [
                    'value_path'    => 'payment_instructions',
                    'component'     => '',
                    'title'         => __( 'Payment instructions', 'yaymail' ),
                    'default_value' => '[yaymail_payment_instructions]',
                    'type'          => 'content',
                ],
                'title'                => [
                    'value_path'    => 'title',
                    'component'     => 'RichTextEditor',
                    'title'         => __( 'Order item title', 'yaymail' ),
                    'default_value' => isset( $attributes['title'] ) ? $attributes['title'] : '<span style="font-size: 20px;">Order #[yaymail_order_number] <b>([yaymail_order_date])</b></span>',
                    'type'          => 'content',
                ],
                'product_title'        => [
                    'value_path'    => 'product_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Product title', 'yaymail' ),
                    'default_value' => esc_html__( 'Product', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'cost_title'           => [
                    'value_path'    => 'cost_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Cost title', 'yaymail' ),
                    'default_value' => esc_html__( 'Cost', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'quantity_title'       => [
                    'value_path'    => 'quantity_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Quantity title', 'yaymail' ),
                    'default_value' => esc_html__( 'Quantity', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'price_title'          => [
                    'value_path'    => 'price_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Price title', 'yaymail' ),
                    'default_value' => esc_html__( 'Price', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'cart_subtotal_title'  => [
                    'value_path'    => 'cart_subtotal_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Subtotal title', 'yaymail' ),
                    'default_value' => esc_html__( 'Subtotal:', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'payment_method_title' => [
                    'value_path'    => 'payment_method_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Payment method title', 'yaymail' ),
                    'default_value' => esc_html__( 'Payment method:', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'order_total_title'    => [
                    'value_path'    => 'order_total_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Total title', 'yaymail' ),
                    'default_value' => esc_html__( 'Total:', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'order_note_title'     => [
                    'value_path'    => 'order_note_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Note title', 'yaymail' ),
                    'default_value' => esc_html__( 'Note:', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'shipping_title'       => [
                    'value_path'    => 'shipping_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Shipping title', 'yaymail' ),
                    'default_value' => esc_html__( 'Shipping:', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'discount_title'       => [
                    'value_path'    => 'discount_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Discount title', 'yaymail' ),
                    'default_value' => esc_html__( 'Discount:', 'woocommerce' ),
                    'type'          => 'content',
                ],
            ],
        ];
    }
}
