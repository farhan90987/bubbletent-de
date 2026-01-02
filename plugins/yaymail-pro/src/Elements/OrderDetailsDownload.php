<?php
namespace YayMail\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;
use YayMail\Emails\CustomerCompletedOrder;

/**
 * OrderDetailsDownload Elements
 */
class OrderDetailsDownload extends BaseElement {

    use SingletonTrait;

    protected static $type = 'order_details_download';

    protected function __construct() {
        $this->available_email_ids = CustomerCompletedOrder::get_instance()->get_id();
    }

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
            'name'      => __( 'Order Details Download', 'yaymail' ),
            'icon'      => self::$icon,
            'group'     => 'woocommerce',
            'available' => true,
            'position'  => 200,
            'data'      => [
                'padding'          => [
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
                'background_color' => [
                    'value_path'    => 'background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Background color', 'yaymail' ),
                    'default_value' => isset( $attributes['background_color'] ) ? $attributes['background_color'] : '#fff',
                    'type'          => 'style',
                ],
                'title_color'      => [
                    'value_path'    => 'title_color',
                    'component'     => 'Color',
                    'title'         => __( 'Title color', 'yaymail' ),
                    'default_value' => isset( $attributes['title_color'] ) ? $attributes['title_color'] : YAYMAIL_COLOR_WC_DEFAULT,
                    'type'          => 'style',
                ],
                'text_color'       => [
                    'value_path'    => 'text_color',
                    'component'     => 'Color',
                    'title'         => __( 'Text color', 'yaymail' ),
                    'default_value' => isset( $attributes['text_color'] ) ? $attributes['text_color'] : YAYMAIL_COLOR_TEXT_DEFAULT,
                    'type'          => 'style',
                ],
                'border_color'     => [
                    'value_path'    => 'border_color',
                    'component'     => 'Color',
                    'title'         => __( 'Border color', 'yaymail' ),
                    'default_value' => isset( $attributes['border_color'] ) ? $attributes['border_color'] : YAYMAIL_COLOR_BORDER_DEFAULT,
                    'type'          => 'style',
                ],
                'font_family'      => [
                    'value_path'    => 'font_family',
                    'component'     => 'FontFamilySelector',
                    'title'         => __( 'Font family', 'yaymail' ),
                    'default_value' => isset( $attributes['font_family'] ) ? $attributes['font_family'] : YAYMAIL_DEFAULT_FAMILY,
                    'type'          => 'style',
                ],
                'rich_text'        => [
                    'value_path'    => 'rich_text',
                    'component'     => '',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => isset( $attributes['rich_text'] ) ? $attributes['rich_text'] : '[yaymail_order_details_download_product]',
                    'type'          => 'content',
                ],
                'title'            => [
                    'value_path'    => 'title',
                    'component'     => 'RichTextEditor',
                    'title'         => __( 'Header title', 'yaymail' ),
                    'default_value' => isset( $attributes['title'] ) ? $attributes['title'] : '<span style="font-size: 20px;font-weight:600;">Downloads</span>',
                    'type'          => 'content',
                ],
                'product_title'    => [
                    'value_path'    => 'product_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Product title', 'yaymail' ),
                    'default_value' => esc_html__( 'Product', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'expires_title'    => [
                    'value_path'    => 'expires_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Expires title', 'yaymail' ),
                    'default_value' => esc_html__( 'Expires', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'download_title'   => [
                    'value_path'    => 'download_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Download title', 'yaymail' ),
                    'default_value' => esc_html__( 'Download', 'woocommerce' ),
                    'type'          => 'content',
                ],
            ],
        ];
    }
}
