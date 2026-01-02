<?php

namespace YayMail\Integrations\AdvancedShipmentTrackingByZorem;

use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Elements\TrackingInformationElement;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Shortcodes\ZoremTrackingInformation;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Shortcodes\AstProTrackingInformation;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Emails\CustomerPartialShippedOrder;
use YayMail\Utils\SingletonTrait;

/**
 * AdvancedShipmentTracking
 * * @method static AdvancedShipmentTracking get_instance()
 */
class AdvancedShipmentTracking {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_emails();
            $this->initialize_elements();
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'Zorem_Woocommerce_Advanced_Shipment_Tracking' ) || ( class_exists( 'Ast_Pro' ) && ast_pro()->license && method_exists( ast_pro()->license, 'check_subscription_status' ) && ast_pro()->license->check_subscription_status() );
    }

    private function initialize_elements() {
        add_action(
            'yaymail_register_elements',
            function ( $element_service ) {
                $element_service->register_element( TrackingInformationElement::get_instance() );
            }
        );
    }

    private function initialize_shortcodes() {

        add_action(
            'yaymail_register_shortcodes',
            function () {
                if ( class_exists( 'Zorem_Woocommerce_Advanced_Shipment_Tracking' ) ) {
                    ZoremTrackingInformation::get_instance();
                }
                if ( class_exists( 'Ast_Pro' ) ) {
                    AstProTrackingInformation::get_instance();
                }
            }
        );
    }

    private function initialize_emails() {
        add_action(
            'yaymail_register_emails',
            function ( $email_service ) {
                $email_service->register( CustomerPartialShippedOrder::get_instance() );
            }
        );
    }
}
