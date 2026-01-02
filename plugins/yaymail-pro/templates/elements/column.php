<?php
defined( 'ABSPATH' ) || exit;

use YayMail\Elements\ElementsLoader;
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
        'width'          => "{$data['width']}%",
        'max-width'      => "{$data['width']}%",
        'vertical-align' => 'top',
    ]
);

$content_style = TemplateHelpers::get_style(
    [
        'min-height' => '50px',
    ]
);

?>

<td class="yaymail-customizer-element-column" style="<?php echo esc_attr( $wrapper_style ); ?>">
    <div class="yaymail-customizer-element-nested-column-content" style="<?php echo esc_attr( $content_style ); ?>">
        <?php
        if ( ! empty( $element['children'] ) ) {
            $args['is_nested'] = true;
            ElementsLoader::render_elements(
                $element['children'],
                $args
            );
        }
        ?>
    </div>
</td>
