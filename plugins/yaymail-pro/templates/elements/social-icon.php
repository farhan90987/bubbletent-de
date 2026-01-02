<?php

defined( 'ABSPATH' ) || exit;

use YayMail\Utils\TemplateHelpers;

if ( empty( $args['element'] ) ) {
    return;
}

$element = $args['element'];
$data    = $element['data'];

if ( empty( $data['icon_list'] ) ) {
    return;
}

$direction_rtl = yaymail_get_email_direction();

$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'text-align'       => $data['align'],
        'background-color' => $data['background_color'],
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

$theme = [
    'Colorful'   => 'colorful',
    'LineDark'   => 'line-dark',
    'LineLight'  => 'line-light',
    'SolidDark'  => 'solid-dark',
    'SolidLight' => 'solid-light',
];

ob_start();

foreach ( $data['icon_list'] as $key => $el ) {

    $img_url     = YAYMAIL_PLUGIN_URL . 'assets/images/social-icons/' . $el['icon'] . '/' . $theme[ $data['theme'] ] . '.png';
    $first_index = ( 'rtl' === $direction_rtl ) ? count( $data['icon_list'] ) - 1 : 0;
    $margin_left = ( $first_index === $key ) ? '0px' : $data['spacing'] . 'px';

    ?> 
        <a class="yaymail-social-icon-item" href="<?php echo esc_attr( $el['url'] ); ?>" target="_blank" style="border: none; text-decoration: none; display: inline-block !important;margin-left:<?php echo esc_attr( $margin_left ); ?>"><img border="0" tabindex="0" src="<?php echo esc_attr( $img_url ); ?>" height="<?php echo esc_attr( $data['width_icon'] ); ?>" style="margin-right: 0;width:<?php echo esc_attr( $data['width_icon'] ); ?>px; "/></a>
    <?php
}

$element_content = ob_get_clean();

TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
