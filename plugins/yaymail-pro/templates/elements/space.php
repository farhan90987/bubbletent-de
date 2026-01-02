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
    ]
);

$space_style = TemplateHelpers::get_style(
    [
        'height' => "{$data['height']}px",
    ]
);

ob_start();
?>
    <div style="<?php echo esc_attr( $space_style ); ?>"></div>
<?php
$element_content = ob_get_clean();

TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
