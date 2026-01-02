<?php

namespace YayMail\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMail\Utils\SingletonTrait;

/**
 * GlobalHeaderFooter Class
 *
 * This is an YayMail element, not an email template. But its customizer page (for editing, saving, etc...) shares the same logic as email template customizer.
 *
 * @method static GlobalHeaderFooter get_instance()
 */
class GlobalHeaderFooter extends BaseEmail {
    use SingletonTrait;

    public $email_types = [ YAYMAIL_GLOBAL_HEADER_FOOTER_ID ];

    protected function __construct() {
        $this->id        = 'yaymail_global_header_footer';
        $this->title     = __( 'Global header footer', 'yaymail' );
        $this->recipient = __( 'Global header footer recipient placeholder', 'yaymail' );
    }

    public function get_default_elements() {
        $default_elements = ElementsLoader::load_elements(
            [
                [
                    'type'       => 'Heading',
                    'attributes' => [
                        'rich_text'        => __( 'Email Heading', 'yaymail' ),
                        'hide_text_editor' => true,
                    ],
                ],
                [
                    'type' => 'SkeletonDivider',
                ],
                [
                    'type'       => 'Footer',
                    'attributes' => [
                        'hide_text_editor' => true,
                    ],
                ],
            ]
        );

        return $default_elements;
    }

    public function get_all_elements() {
        $elements = parent::get_elements();
        /**
         * Add flag to notify front-end to remove text editor of headers and footers
         */
        if ( isset( $elements['heading']['data'] ) ) {
            $elements['heading']['data']['hide_text_editor'] = true;
        }
        return $elements;
    }

    public function get_template_file( $located, $template_name, $args ) {
    }

    public function get_template_path() {
    }
}
