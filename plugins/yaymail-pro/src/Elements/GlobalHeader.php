<?php
namespace YayMail\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * GlobalHeader Elements
 */
class GlobalHeader extends BaseElement {

    use SingletonTrait;

    protected static $type = 'global_header';

    public $available_email_ids = [ YAYMAIL_ALL_EMAILS ];

    public static function get_data( $attributes = [] ) {

        self::$icon = '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 20 20">
  <path d="M17.5,2.5v15H2.5V2.5h15M18,1H2c-.55,0-1,.45-1,1v16c0,.55.45,1,1,1h16c.55,0,1-.45,1-1V2c0-.55-.45-1-1-1h0Z"/>
  <rect x="3.02" y="2.89" width="13.95" height="4" rx=".24" ry=".24"/>
</svg>';

        if ( isset( $attributes['text'] ) ) {
            $content = $attributes['text'];
        } else {
            $content = __( 'Email Heading', 'yaymail' );
            $content = '<h1 style="font-size: 30px; font-weight: 300; line-height: normal; margin: 0px; color: inherit; text-align: left;">' . $content . '</h1>';
        }

        return [
            'id'          => uniqid(),
            'type'        => self::$type,
            'name'        => __( 'Global Header', 'yaymail' ),
            'icon'        => self::$icon,
            'group'       => 'basic',
            'available'   => true,
            'status_info' => 'new',
            'position'    => 40,
            'data'        => [
                'rich_text'     => [
                    'value_path'    => 'rich_text',
                    'component'     => 'RichTextEditor',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => $content,
                    'type'          => 'content',
                ],
                'global_header' => [
                    'value_path'    => 'global_header',
                    'component'     => 'GlobalHeaderFooterCustomizerLink',
                    'title'         => __( 'Global header', 'yaymail' ),
                    'default_value' => '',
                    'type'          => 'content',
                ],
            ],
        ];
    }
}
