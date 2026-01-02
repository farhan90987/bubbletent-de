<?php
namespace YayMail\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;
/**
 * Button Elements
 */
class Button extends BaseElement {

    use SingletonTrait;

    protected static $type = 'button';

    public $available_email_ids = [ YAYMAIL_ALL_EMAILS ];

    public static function get_data( $attributes = [] ) {
        self::$icon = '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1" viewBox="0 0 20 20">
  <path d="M10,1C5,1,1,5,1,10s4,9,9,9,9-4,9-9S15,1,10,1ZM10,17.5c-4.1,0-7.5-3.4-7.5-7.5s3.4-7.5,7.5-7.5,7.5,3.4,7.5,7.5-3.4,7.5-7.5,7.5Z"/>
  <path d="M12.8,9.2h-2.1v-2.1c0-.4-.3-.8-.8-.8s-.8.3-.8.8v2.1h-2.1c-.4,0-.8.3-.8.8s.3.8.8.8h2.1v2.1c0,.4.3.8.8.8s.8-.3.8-.8v-2.1h2.1c.4,0,.8-.3.8-.8s-.3-.8-.8-.8Z"/>
</svg>';

        return [
            'id'        => uniqid(),
            'type'      => self::$type,
            'name'      => __( 'Button', 'yaymail' ),
            'icon'      => self::$icon,
            'group'     => 'basic',
            'available' => true,
            'position'  => 60,
            'data'      => [
                'button_type'             => ElementsHelper::get_button_type_selector( $attributes ),
                'align'                   => ElementsHelper::get_align( $attributes ),
                'padding'                 => ElementsHelper::get_spacing(
                    $attributes,
                    [
                        'title' => __( 'Container padding', 'yaymail' ),
                    ]
                ),
                'border_radius'           => ElementsHelper::get_spacing(
                    $attributes,
                    [
                        'value_path'    => 'border_radius',
                        'title'         => __( 'Border radius', 'yaymail' ),
                        'default_value' => [
                            'top_left'     => '5',
                            'top_right'    => '5',
                            'bottom_right' => '5',
                            'bottom_left'  => '5',
                        ],
                        'extras_data'   => [
                            'is_border_radius' => true,
                            'class_name'       => 'yaymail-border-radius',
                        ],
                    ]
                ),
                'text'                    => ElementsHelper::get_text_input(
                    $attributes,
                    [
                        'value_path'    => 'text',
                        'title'         => __( 'Button text', 'yaymail' ),
                        'default_value' => __( 'Click me', 'yaymail' ),
                    ]
                ),
                'url'                     => ElementsHelper::get_text_input( $attributes ),
                'background_color'        => ElementsHelper::get_color(
                    $attributes,
                    [
                        'default_value' => '#fff',
                    ]
                ),
                'button_background_color' => ElementsHelper::get_color(
                    $attributes,
                    [
                        'value_path'    => 'button_background_color',
                        'title'         => __( 'Button background color', 'yaymail' ),
                        'default_value' => YAYMAIL_COLOR_WC_DEFAULT,
                    ]
                ),
                'text_color'              => ElementsHelper::get_color(
                    $attributes,
                    [
                        'value_path'    => 'text_color',
                        'title'         => __( 'Text color', 'yaymail' ),
                        'default_value' => '#ffffff',
                    ]
                ),
                'font_size'               => ElementsHelper::get_dimension(
                    $attributes,
                    [
                        'value_path'    => 'font_size',
                        'title'         => __( 'Font size', 'yaymail' ),
                        'default_value' => '13',
                        'extras_data'   => [
                            'min' => 10,
                            'max' => 40,
                        ],
                    ]
                ),
                'height'                  => ElementsHelper::get_dimension(
                    $attributes,
                    [
                        'value_path'    => 'height',
                        'title'         => __( 'Height', 'yaymail' ),
                        'default_value' => '21',
                        'extras_data'   => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ]
                ),
                'width'                   => ElementsHelper::get_dimension(
                    $attributes,
                    [
                        'default_value' => '50',
                        'extras_data'   => [
                            'min'  => 0,
                            'max'  => 100,
                            'unit' => '%',
                        ],
                    ]
                ),
                'weight'                  => ElementsHelper::get_font_weight_selector( $attributes ),
                'font_family'             => ElementsHelper::get_font_family_selector( $attributes ),
            ],
        ];
    }
}
