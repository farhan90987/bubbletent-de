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
        'background-color' => $data['background_color'],
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

$margin               = isset( $data['align'] ) && 'center' === $data['align'] ? '0 auto' : 'auto';
$float                = isset( $data['align'] ) && ( 'left' === $data['align'] || 'right' === $data['align'] ) ? $data['align'] : 'unset';
$divider_holder_style = TemplateHelpers::get_style(
    [
        'width'            => "{$data['width']}%",
        'margin'           => $margin,
        'float'            => $float,
        'border-top-width' => "{$data['height']}px",
        'border-top-color' => $data['divider_color'],
        'border-top-style' => $data['divider_type'],
    ]
);

ob_start();
?>

    <div style="<?php echo esc_attr( $divider_holder_style ); ?>"></div>

<?php
$element_content = ob_get_clean();

TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
