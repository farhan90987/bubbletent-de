<?php

namespace YayMail\Models;

use YayMail\Utils\SingletonTrait;

/**
 * Setting Model
 *
 * @method static SettingModel get_instance()
 */
class SettingModel {
    use SingletonTrait;

    const OPTION_NAME = 'yaymail_settings';

    // TODO: change variable name to be more meaning in db ( when initialize )
    const DEFAULT = [
        'direction'                  => 'ltr',
        'container_width'            => 605,
        'payment_display_mode'       => 'yes',
        'show_product_image'         => false,
        'product_image_position'     => 'top',
        'product_image_height'       => 30,
        'product_image_width'        => 30,
        'show_product_sku'           => true,
        'show_product_description'   => false,
        'show_product_hyper_links'   => false,
        'show_product_regular_price' => false,
        'show_product_item_cost'     => false,
        'enable_custom_css'          => false,
        'custom_css'                 => '',
    ];

    public static function find_by_name( $name ) {
        $settings = self::find_all();
        if ( isset( $settings[ $name ] ) && ! empty( $settings[ $name ] ) ) {
            return $settings[ $name ];
        }
        return null;
    }

    public static function find_all() {
        $default_settings = self::DEFAULT;
        $settings         = get_option( self::OPTION_NAME, [] );
        if ( ! is_array( $settings ) ) {
            return $default_settings;
        }
        return wp_parse_args( $settings, $default_settings );
    }

    public static function update( $settings ) {
        $settings_option = get_option( self::OPTION_NAME );
        if ( ! empty( $settings ) && is_array( $settings ) ) {
            update_option( self::OPTION_NAME, wp_parse_args( $settings, $settings_option ) );
        }
    }

    public static function delete() {
        delete_option( self::OPTION_NAME );
    }
}
