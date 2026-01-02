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

$element = $args['element'];
$data    = $element['data'];

$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'background-color' => $data['background_color'],
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

$text_style = TemplateHelpers::get_style(
    [
        'color'       => $data['text_color'],
        'font-family' => isset( $data['font_family'] ) ? $data['font_family'] : '',
    ]
);

ob_start();
?>

    <div style="<?php echo esc_attr( $text_style ); ?>"><?php echo wp_kses_post( do_shortcode( $data['rich_text'] ) ); ?></div>

<?php
$element_content = ob_get_clean();

TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
