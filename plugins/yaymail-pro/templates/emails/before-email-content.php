<?php
$dir                    = is_rtl() ? 'rtl' : 'ltr';
$template_exclude_style = apply_filters( 'yaymail_template_exclude_style', [] );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo esc_attr( $dir ); ?>">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta name="x-apple-disable-message-reformatting" />
        <?php if ( ! in_array( $template->get_name(), $template_exclude_style ) ) : ?>
            <style>
                h1{ font-family:inherit;text-shadow:unset;text-align:inherit;}
                h2,h3{ font-family:inherit;color:inherit;text-align:inherit;}
                .yaymail-inline-block {display: inline-block;}
            .yaymail-customizer-email-template-container a {color: <?php echo esc_attr( $template->get_text_link_color() ); ?>}
            </style>
        <?php endif; ?>
    </head>
    <body style="background: <?php echo esc_attr( $template->get_background_color() ); ?>" <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
