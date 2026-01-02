<?php

namespace YayMail\Utils;

use YayMail\Integrations\TranslationModule;

defined( 'ABSPATH' ) || exit;

use YayMail\Models\TemplateModel;

/**
 * Localize Classes
 */
class Localize {

    public static function get_list_orders() {
        if ( yaymail_is_wc_installed() ) {
            $data_orders   [] = [
                'id'           => 'sample_order',
                'order_number' => 'sample_order',
                'email'        => '',
                'first_name'   => '',
                'last_name'    => '',
                'title'        => esc_html__( 'Sample order', 'yaymail' ),
            ];

            $wc_list_orders = wc_get_orders(
                [
                    'limit' => 50,
                ]
            );

            foreach ( $wc_list_orders as $order ) {
                if ( method_exists( $order, 'get_id' ) && method_exists( $order, 'get_order_number' ) ) {
                    $order_id     = strval( $order->get_id() );
                    $order_number = $order->get_order_number();
                    $email        = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : '';
                    $first_name   = method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : '';
                    $last_name    = method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : '';
                    $title        = $order_number . ' - ' . $first_name . $last_name . ' (' . ( $email ? $email : __( 'Unknown', 'yaymail' ) ) . ')';

                    $data_orders[] = [
                        'id'           => $order_id,
                        'order_number' => $order_number,
                        'email'        => $email,
                        'first_name'   => $first_name,
                        'last_name'    => $last_name,
                        'title'        => $title,
                    ];
                }
            }
        }//end if
        return $data_orders;
    }

    public static function get_translate_integrations() {
        $current_integration = TranslationModule::get_instance()->current_integration;
        $integrations_data   = array_map(
            function( $integration_instance ) {
                return $integration_instance->get_data();
            },
            array_values( TranslationModule::get_instance()->available_integrations )
        );
        $integrations        = [
            'available'           => $integrations_data,
            'current_translation' => is_null( $current_integration ) ? null : $current_integration->get_data(),
        ];
        return $integrations;
    }

    public static function get_social_icons_data() {
        $social_icons_folder = YAYMAIL_PLUGIN_PATH . 'assets/images/social-icons/';

        $images = [];

        if ( is_dir( $social_icons_folder ) ) {
            $subfolders = array_diff( scandir( $social_icons_folder ), [ '.', '..' ] );

            foreach ( $subfolders as $sub ) {
                $sub_folder = $social_icons_folder . $sub;

                if ( is_dir( $sub_folder ) ) {
                    $png_files = array_diff( scandir( $sub_folder ), [ '.', '..' ] );

                    $pngs = [];

                    foreach ( $png_files as $file ) {
                        $file_path = $sub_folder . '/' . $file;

                        if ( is_file( $file_path ) ) {
                            $base64 = base64_encode( file_get_contents( $file_path ) );

                            $pngs[] = [
                                'theme' => self::kebab_to_pascal( str_replace( '.png', '', $file ) ),
                                'src'   => 'data: image/png;base64,' . $base64,
                            ];
                        }
                    }

                    $images[] = [
                        'name' => str_replace( '.png', '', $sub ),
                        'data' => $pngs,
                    ];
                }//end if
            }//end foreach
        }//end if

        return [
            'icons'  => [ 'discord', 'facebook', 'instagram', 'linkedin', 'messenger', 'pinterest', 'telegram', 'tiktok', 'twitter', 'vimeo', 'website', 'whatsapp', 'youtube' ],
            'themes' => [ 'Colorful', 'LineDark', 'LineLight', 'SolidDark', 'SolidLight' ],
            'images' => $images,
        ];
    }

    public static function get_global_headers_footers() {
        $template_model         = TemplateModel::get_instance();
        $global_headers_footers = $template_model->get_global_headers_and_footers_for_all_available_languages();
        return $global_headers_footers;
    }

    public static function get_activated_addons() {
        $result = apply_filters( 'yaymail_activated_addons', [] );

        return $result;
    }

    private static function kebab_to_pascal( $input ) {
        // Remove hyphens and split the string into words
        $words = explode( '-', $input );
        // Capitalize the first letter of each word
        $pascal_words = array_map( 'ucfirst', $words );
        // Join the words back together
        $pascal_case = implode( '', $pascal_words );

        return $pascal_case;
    }
}
