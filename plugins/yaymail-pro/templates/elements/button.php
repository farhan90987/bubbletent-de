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
        'width'            => '100%',
        'text-align'       => 'center',
        'background-color' => $data['background_color'],
    ]
);

$margin_value        = isset( $data['margin'] ) && 'center' === $data['margin'] ? '0 auto' : 'auto';
$float_value         = in_array( $data['align'], [ 'left', 'right' ], true ) ? $data['align'] : 'unset';
$button_holder_style = TemplateHelpers::get_style(
    [
        'width'          => $data['width'] . '%',
        'min-width'      => $data['width'] . '%',
        'margin'         => $margin_value,
        'padding'        => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
        'float'          => $float_value,
        'border-spacing' => '0',
    // Make sure this will work when inject css not working
    ]
);


$border_radius = $data['border_radius'];
$link_style    = TemplateHelpers::get_style(
    [
        'text-decoration'  => 'none',
        'padding'          => '12px 20px',
        'display'          => 'block',
        'box-sizing'       => 'border-box',
        'border-radius'    => TemplateHelpers::get_border_radius_value( $border_radius, 'px' ),
        'font-size'        => "{$data['font_size']}px",
        'font-weight'      => $data['weight'],
        'background-color' => $data['button_background_color'],
        'word-break'       => 'break-word',
    ]
);

$text_style = TemplateHelpers::get_style(
    [
        'font-family' => TemplateHelpers::get_font_family_value( $data['font_family'] ),
        'line-height' => "{$data['height']}px",
        'color'       => $data['text_color'],
    ]
);

ob_start();
?>

    <table style="<?php echo esc_attr( $button_holder_style ); ?>">
        <tbody>
            <tr>
                <td style="padding: 0;">
                    <a
                        href="<?php echo esc_url( do_shortcode( $data['url'] ) ); ?>"
                        style="<?php echo esc_attr( $link_style ); ?>"
                        target="_blank"
                        rel="noreferrer"
                    >
                        <span style="<?php echo esc_attr( $text_style ); ?>"><?php yaymail_kses_post_e( do_shortcode( $data['text'] ) ); ?></span>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>

<?php
$element_content = ob_get_clean();
TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
