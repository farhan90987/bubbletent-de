<?php

namespace YayMail\PreviewEmail;

use YayMail\Integrations\TranslationModule;
use YayMail\Utils\SingletonTrait;
use YayMail\YayMailTemplate;
use YayMail\SupportedPlugins;
use YayMail\Models\TemplateModel;

/**
 *
 * @method static PreviewEmailWoo get_instance()
 */
class PreviewEmailWoo {
    use SingletonTrait;

    public static $recipient;

    private function __construct() {}

    public static function email_preview_output( $order_id, $email_id, $email_address = '' ) {
        $current_email = yaymail_get_email( $email_id );
        $current_email = ! empty( $current_email ) && method_exists( $current_email, 'get_root_email' ) ? $current_email->get_root_email() : '';

        if ( empty( $current_email ) ) {
            return [
                'html'                  => yaymail_get_content( '/templates/preview-email/notice.php' ),
                'subject'               => str_replace( '(N/A)', '', str_replace( '{blogname}', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), '[' . wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) . '] - ' . self::get_email_title_by_id( $email_id ) ) ),
                'is_disabled_send_mail' => true,
            ];
        }

        $email_class = get_class( $current_email );

        $order         = wc_get_order( $order_id );
        $language      = TranslationModule::get_instance()->get_active_language();
        $template_data = new YayMailTemplate( $email_id, TranslationModule::checked_language( $language ) );

        if ( ! $template_data->is_enabled() ) {
            return self::render_default_preview( $current_email, $email_class, $order );
        }

        self::$recipient = is_email( $email_address ) ? $email_address : '';

        WC()->payment_gateways();
        WC()->shipping();

        add_filter( 'woocommerce_email_recipient_' . $current_email->id, [ __CLASS__, 'no_recipient' ] );
        add_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );

        $supported_template_ids = SupportedPlugins::get_instance()->get_template_ids_from_core();

        if ( in_array( $email_id, $supported_template_ids, true ) ) {
                // Sample order fallback
            if ( empty( $order ) ) {
                return self::render_sample_preview( $template_data, $current_email );
            }

            self::trigger_email( $email_class, $current_email, $order_id );
            $content               = self::get_email_content( $current_email );
            $is_disabled_send_mail = false;
        } else {
            $content               = yaymail_get_content( '/templates/preview-email/notice.php', [ 'template_data' => $template_data ] );
            $is_disabled_send_mail = true;
        }

        remove_filter( 'woocommerce_new_order_email_allows_resend', '__return_true', 10 );

        return [
            'html'                  => yaymail_kses_post( $content ),
            'subject'               => self::get_subject( $current_email, $order ),
            'is_disabled_send_mail' => $is_disabled_send_mail,
        ];
    }

    public static function get_email_title_by_id( $email_id ) {
        $templates = TemplateModel::get_instance()->find_all();
        foreach ( $templates as $template ) {
            if ( $template['name'] === $email_id ) {
                return $template['template_title'];
            }
        }
        return __( 'Email Subject', 'yaymail' );
    }

    private static function get_subject( $current_email, $order ) {
        $text_replace = [
            '{order_number}' => ! empty( $order ) ? '#' . $order->get_order_number() : '#1',
            '{order_date}'   => ! empty( $order ) ? gmdate( get_option( 'date_format' ), strtotime( $order->get_date_created() ) ) : gmdate( get_option( 'date_format' ) ),
            '{order_id}'     => ! empty( $order ) ? '#' . $order->get_id() : '#1',
            '{blogname}'     => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
        ];

        $subject = ! empty( $current_email->subject ) ? str_replace( array_keys( $text_replace ), $text_replace, $current_email->subject ) : $current_email->get_subject();
        return $subject;
    }

    private static function render_sample_preview( $template_data, $current_email ) {
        $render_data = [
            'is_sample'             => true,
            'is_customized_preview' => true,
        ];
        $content     = $template_data->get_content( $render_data );

        return [
            'html'    => yaymail_kses_post( $content ),
            'subject' => self::get_subject( $current_email, '' ),
        ];
    }

    private static function render_default_preview( $current_email, $email_class, $order ) {
        add_filter(
            'woocommerce_email_preview_dummy_order',
            function ( $dummy_order ) use ( $order ) {
                return ! empty( $order ) ? $order : $dummy_order;
            },
            10,
            2
        );
        try {
            $email_preview = wc_get_container()->get( \Automattic\WooCommerce\Internal\Admin\EmailPreview\EmailPreview::class );
            $email_preview->set_email_type( $email_class );
            $message               = $email_preview->render();
            $message               = $email_preview->ensure_links_open_in_new_tab( $message );
            $content               = $message;
            $is_disabled_send_mail = false;
        } catch ( \Throwable $e ) {
            ob_end_clean();
            $content               = yaymail_get_content( '/templates/preview-email/notice.php' );
            $is_disabled_send_mail = true;
        }

        return [
            'html'                  => yaymail_kses_post( $content ),
            'subject'               => self::get_subject( $current_email, $order ),
            'is_disabled_send_mail' => $is_disabled_send_mail,
        ];
    }

    private static function trigger_email( $email_class, $email, $order_id ) {
        try {
            if ( $email_class === 'WC_Email_Customer_New_Account' ) {
                $email->trigger( get_current_user_id() );
            } else {
                $order = wc_get_order( $order_id );
                $email->set_object( $order );
                $email->trigger( $order_id );
            }
        } catch ( \Exception $e ) {
            return [ 'error' => $e ];
        }//end try
    }

    private static function get_email_content( $email ) {
        $email->email_type = 'html';
        $content           = $email->get_content();
        return apply_filters( 'woocommerce_mail_content', $email->style_inline( $content ) );
    }

    public static function no_recipient( $recipient ): string {
        if ( self::$recipient !== '' ) {
            $recipient = self::$recipient;
        } else {
            $recipient = '';
        }
        return $recipient;
    }
}
