<?php
namespace YayMail\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * GlobalFooter Elements
 */
class GlobalFooter extends BaseElement {

    use SingletonTrait;

    protected static $type = 'global_footer';

    public $available_email_ids = [ YAYMAIL_ALL_EMAILS ];

    public static function get_data( $attributes = [] ) {
        self::$icon = '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 20 20">
  <path d="M17.5,2.5v15H2.5V2.5h15M18,1H2c-.55,0-1,.45-1,1v16c0,.55.45,1,1,1h16c.55,0,1-.45,1-1V2c0-.55-.45-1-1-1h0Z"/>
  <rect x="3.02" y="13.01" width="13.95" height="4" rx=".24" ry=".24"/>
</svg>';

        return [
            'id'          => uniqid(),
            'type'        => self::$type,
            'name'        => __( 'Global Footer', 'yaymail' ),
            'icon'        => self::$icon,
            'group'       => 'basic',
            'available'   => true,
            'status_info' => 'new',
            'position'    => 50,
            'data'        => [
                'global_footer' => [
                    'value_path'    => 'global_footer',
                    'component'     => 'GlobalHeaderFooterCustomizerLink',
                    'title'         => __( 'Global footer', 'yaymail' ),
                    'default_value' => '',
                    'type'          => 'content',
                ],
            ],
        ];
    }
}
