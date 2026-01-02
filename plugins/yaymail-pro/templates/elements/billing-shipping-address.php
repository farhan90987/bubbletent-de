<?php
defined( 'ABSPATH' ) || exit;

use YayMail\Utils\TemplateHelpers;

if ( empty( $args['element'] ) ) {
    return;
}

$element = $args['element'];
$data    = $element['data'];

$billing_address_html  = wp_kses_post( do_shortcode( '[yaymail_billing_address]' ) );
$shipping_address_html = wp_kses_post( do_shortcode( '[yaymail_shipping_address]' ) );
$width                 = ! empty( $billing_address_html ) & ! empty( $shipping_address_html ) ? '50%' : '100%';

if ( empty( $billing_address_html ) && empty( $shipping_address_html ) ) :
    return '';
endif;

$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'background-color' => $data['background_color'],
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

$table_style = TemplateHelpers::get_style(
    [
        'width'           => '100%',
        'text-align'      => yaymail_get_text_align(),
        'border-collapse' => 'separate',
        'border-spacing'  => '5px !important',
    ]
);

$column_wrap_style = TemplateHelpers::get_style(
    [
        'border' => 'solid 1px ' . $data['border_color'],
    ]
);

$column_style = TemplateHelpers::get_style(
    [
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
        'padding'     => '12px',
        'font-size'   => '14px',
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
    ]
);

$title_style = TemplateHelpers::get_style(
    [
        'text-align'    => yaymail_get_text_align(),
        'color'         => isset( $data['title_color'] ) ? $data['title_color'] : 'inherit',
        'margin-top'    => '0',
        'font-family'   => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'margin-bottom' => '7px',
    ]
);

ob_start();
?>
<table class="yaymail-table-billing-shipping-address" style="<?php echo esc_attr( $table_style ); ?>">
    <tbody>
        <tr>
            <?php if ( ! empty( $billing_address_html ) ) : ?>
            <td style="width: <?php echo esc_attr( $width ); ?>; vertical-align: top;">
                <div class="yaymail-billing-title" style="<?php echo esc_attr( $title_style ); ?>" > <?php echo wp_kses_post( do_shortcode( $data['billing_title'] ) ); ?> </div>
                <div class="yaymail-billing-address-wrap" style="<?php echo esc_attr( $column_wrap_style ); ?>">
                    <div style="<?php echo esc_attr( $column_style ); ?>">
                        <?php echo wp_kses_post( do_shortcode( '[yaymail_billing_address]' ) ); ?>
                    </div>
                </div>
            </td>
            <?php endif; ?>
            <?php if ( ! empty( $shipping_address_html ) ) : ?>
            <td style="width: <?php echo esc_attr( $width ); ?>; vertical-align: top;">
                <div class="yaymail-shipping-title" style="<?php echo esc_attr( $title_style ); ?>" > <?php echo wp_kses_post( do_shortcode( $data['shipping_title'] ) ); ?> </div>
                <div class="yaymail-shipping-address-wrap" style="<?php echo esc_attr( $column_wrap_style ); ?>">
                    <div style="<?php echo esc_attr( $column_style ); ?>">
                        <?php echo wp_kses_post( do_shortcode( '[yaymail_shipping_address]' ) ); ?>
                    </div>
                </div>
            </td>
            <?php endif; ?>
        </tr>
    </tbody>
</table>
<?php
$element_content = ob_get_clean();
TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );

