<?php
defined( 'ABSPATH' ) || exit;

use YayMail\Utils\TemplateHelpers;

/**
 * $args includes
 * $element
 * $render_data
 * $is_nested
 */
if ( empty( $args['element'] ) ) {
    return;
}

$yaymail_settings     = yaymail_settings();
$payment_display_mode = isset( $yaymail_settings['payment_display_mode'] ) ? $yaymail_settings['payment_display_mode'] : false;

$element       = $args['element'];
$data          = $element['data'];
$template_name = isset( $args['template']->get_data()['name'] ) ? $args['template']->get_data()['name'] : '';

$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'background-color' => $data['background_color'],
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

$table_title_style = TemplateHelpers::get_style(
    [
        'text-align'    => yaymail_get_text_align(),
        'color'         => isset( $data['title_color'] ) ? $data['title_color'] : 'inherit',
        'margin-top'    => '0',
        'font-family'   => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'margin-bottom' => '7px',
    ]
);

$payment_instructions_style = TemplateHelpers::get_style(
    [
        'text-align'    => yaymail_get_text_align(),
        'font-size'     => '14px',
        'color'         => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
        'font-family'   => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'margin-bottom' => '10px',
    ]
);

ob_start();
?>
<div class="yaymail-order-details-title" style="<?php echo esc_attr( $table_title_style ); ?>" > <?php echo wp_kses_post( do_shortcode( $data['title'] ) ); ?> </div>
<?php
$element_content = ob_get_contents();
ob_end_clean();
$element_content .= do_shortcode( isset( $data['rich_text'] ) ? $data['rich_text'] : '' );

TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
