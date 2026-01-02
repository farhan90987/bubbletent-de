<?php
$gutschein_typ = 'Standortgutschein'; // Default
$is_voucher = false;
$zahlungsart = $order->get_payment_method_title();
if ($cats[0] == 'Wertgutschein' || $cats[0] == 'Value voucher') {
    $gutschein_typ = 'Wertgutschein';
    $is_voucher = true;
} else{
    $is_voucher = true;
}

// Set voucher-specific variables
if ($gutschein_typ == 'Wertgutschein') {
    $betrag = wc_price($order->get_total());
    $waehrung = $order->get_currency();
    $link_wert = get_home_url() . '/order-detail-page/?order_id=' . $order_id . '&password=' . $rand_password;
} else {
    $standort = $products[0]['name'];
    $anzahl_naechte = $products[0]['quantity'];
    $link_standort = get_home_url() . '/order-detail-page/?order_id=' . $order_id . '&password=' . $rand_password;
}

$coupon_amount= null;
foreach ($order->get_items() as $item) {
$product_id = $item->get_product_id();
$pdf_flag = get_post_meta($product_id, '_wpdesk_pdf_coupons', true);
if ($pdf_flag !== 'yes' || get_post_meta($product_id, 'christmas_location', true) === '1') continue;
$qty = max(1, (int) $item->get_quantity());
$line_total = (float) $item->get_total();
$unit_price = $qty ? ($line_total / $qty) : $line_total;
$coupon_amount += $unit_price * $qty;
}                        

$bestellnummer = $order->get_order_number();
$ausstellungsdatum = date_i18n('d.m.Y', time());
$gueltig_bis = date_i18n('d.m.Y', strtotime('+3 years'));
$bestellsumme = wc_price($order->get_total());
?>
<style>
    body{
        font-size:16px;
        line-height:1.3;
    }
</style>
<div class='container' style="max-width:800px;margin:0 auto;">
   <div class='header' style='background-color: #1a1a1a;'>
  <!--[if gte mso 9]>
    <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;height:300px;">
      <v:fill type="tile" src="https://book-a-bubble.de/wp-content/uploads/2025/05/Bild-14.jpg" color="#1a1a1a" />
      <v:textbox inset="0,0,0,0"><div>
  <![endif]-->
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td style="background-image: url(https://book-a-bubble.de/wp-content/uploads/2025/05/Bild-14.jpg); background-size: cover; background-position: center; background-color: #1a1a1a; padding:40px 20px; text-align:center;">
        <div style="display:inline-block; background:rgba(0, 0, 0, 0); padding:30px; border-radius:4px;">
          <img src='https://book-a-bubble.de/wp-content/uploads/2025/06/Nice-Tense-7-1.png' alt='Logo' style='max-width:180px; height:auto;'>
          <h1 style='color:white; font-size:28px; margin-top:20px; font-family:Arial, sans-serif;'>Ein Geschenk, das Erinnerungen schafft.</h1>
        </div>
      </td>
    </tr>
  </table>
  <!--[if gte mso 9]>
      </div></v:textbox>
    </v:rect>
  <![endif]-->
</div>

    <div class='content' style='padding:30px 0;'>
        <!-- Introtext je Typ -->
        <?php if ($gutschein_typ == 'Standortgutschein'): ?>
        <div class='text-block' style='padding:10px 20px; margin-bottom:10px; background-color:#ffffff; border-radius:8px; line-height:1.6;'>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">Sternenhimmel verschenken? Geht nicht? Oh doch! Mit diesem Gutschein verschenkst du nicht nur eine Nacht in einem Hotel, sondern in einem 1000-Sterne-Hotel. In dieser E-Mail findest du alle wichtigen Informationen zu deinem Gutschein.</p>
        </div>
        <?php elseif ($gutschein_typ == 'Wertgutschein'): ?>
        <div class='text-block' style='padding:10px 20px; margin-bottom:10px; background-color:#ffffff; border-radius:8px; line-height:1.6;'>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">Sternenhimmel verschenken? Geht nicht? Oh doch! Mit diesem Gutschein verschenkst du ein ganz besonderes Erlebnis für eine Nacht in einem Hotel unter vielen Sternen. Mit dem Wertgutschein kann die beschenkte Person den Standort des Bubble Tents aus unserem Angebot frei wählen. In dieser E-Mail findest du alle wichtigen Informationen zu deinem Gutschein.</p>
        </div>
        <?php endif; ?>

        <div class='info-box' style='background-color:#f2f2f2; padding:20px; border-radius:8px; margin-bottom:20px;'>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034; margin: 5px 0;"><img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/voucher.png" alt=""> <strong>Gutschein-Typ:</strong> <?php echo $gutschein_typ; ?></p>

            <?php if ($gutschein_typ == 'Standortgutschein'): ?>
                <p style="font-size: 16px;line-height: 1.3;color: #2e4034; margin: 5px 0;"><img width="24px" style="width:24px;margin-right:5px;vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/marker-1.png" alt="Tent Address Icon"> <strong>Standort:</strong> <?php echo $standort; ?></p>
                <p style="font-size: 16px;line-height: 1.3;color: #2e4034; margin: 5px 0;"><img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/moon.png" alt=""> <strong>Anzahl der Nächte:</strong> <?php echo $anzahl_naechte; ?></p>
            <?php elseif ($gutschein_typ == 'Wertgutschein'): ?>
                <p style="font-size: 16px;line-height: 1.3;color: #2e4034; margin: 5px 0;"><img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/credit-card.png" alt=""> <strong>Betrag:</strong> <?php echo ($enable_cart_upsell === 'yes' && $contains_excluded_product) ?  $coupon_amount : $betrag ; ?> <?php echo $waehrung; ?></p>
                <p style="font-size: 16px;line-height: 1.3;color: #2e4034; margin: 5px 0;"><img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/lock.jpg" alt=""> <strong>Gutscheincode:</strong> <?php echo $gutscheincode ? $gutscheincode : get_post_meta($order_id, '_voucher_code', true) ?></p>
            <?php endif; ?>

            <p style="font-size: 16px;line-height: 1.3;color: #2e4034; margin: 5px 0;"><img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/page.jpg" alt=""> <strong>Bestellnummer/Gutscheinnummer:</strong> <?php echo $bestellnummer; ?></p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034; margin: 5px 0;"><img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/calendar.png" alt=""> <strong>Ausstellungsdatum:</strong> <?php echo $ausstellungsdatum; ?> &nbsp;&nbsp; <img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/calendar.png" alt=""> <strong>Gültig bis:</strong> <?php echo $gueltig_bis; ?></p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034; margin: 5px 0;"><img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/credit-card.png" alt=""> <strong>Zahlungsart:</strong> <?php echo $zahlungsart; ?></p>
        </div>

        <div class='payment-box' style='background-color:#fef9f5; border-left:4px solid #54774e; padding:15px 20px; margin-bottom:20px;text-align:left;'>
            <?php if (($zahlungsart == 'Vorkasse per Überweisung' || $zahlungsart == 'Prepayment by bank transfer') && $order_status == 'on-hold'): ?>
                <p style='color:#b00020;font-size:16px;line-height:1.3;margin:5px 0;'><strong>Zahlung ausstehend</strong></p>
                <p style="color:#b00020;font-size:16px;line-height:1.3;margin:5px 0;">Wir aktivieren deinen Gutschein nach Zahlungseingang.</p>
                <p style="color:#b00020;font-size:16px;line-height:1.3;margin:5px 0;"><strong>Summe der Bestellung:</strong> <?php echo $bestellsumme; ?></p>
                <p style="color:#b00020;font-size:16px;line-height:1.3;margin:5px 0;">Bitte überweise den Betrag innerhalb von 5 Tagen auf folgendes Konto:<br>Bubble Tent Deutschland FM UG:<br>Bank: Sparkasse Nürnberg<br>IBAN: DE59 7605 0101 0014 9772 27</p>
            <?php else: ?>
                <p style='color:#54774e;font-size:16px;line-height:1.3;margin:5px 0;'><strong>Zahlung bestätigt</strong> – dein Gutschein ist aktiv und kann eingelöst werden.</p>
                <p style='color:#54774e;font-size:16px;line-height:1.3;margin:5px 0;'><strong>Summe der Bestellung:</strong> <?php echo $bestellsumme; ?></p>
            <?php endif; ?>
        </div>

        <div class='button-container' style='text-align:center; margin:30px 0 10px 0;'>
            <?php 
                $rand_password = get_post_meta( $order->get_id(), '_random_password', true );
            ?>
            <?php
            if ($enable_cart_upsell === 'yes' && $contains_excluded_product) {
                ?>
                    <a href="<?php echo get_home_url().'?download_xmas_pdf=1&order_id='. $order_id.'&pass=' .$rand_password ?>" target="_blank" rel="noopener" class="btn" style="padding:14px 22px; background:#54774e; color:#ffffff; border-radius:6px; font-weight:bold; text-decoration:none; display:inline-block;">Gutschein jetzt drucken</a>
                <?php
            }else{

            if($order_status == 'completed'){
            ?>
                <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">Lade jetzt deinen Gutschein herunter:</p>
            <?php
                if ($gutschein_typ == 'Wertgutschein'): 
                $pdf_link = get_coupon_pdf_url_from_code( $gutscheincode );
            ?>
            <a style="display: inline-block;padding: 14px 24px;background-color: #54774e;color: #ffffff;text-decoration: none;border-radius: 6px;font-weight: bold;" href='<?php echo $pdf_link; ?>' class='button'>👉 Gutschein herunterladen</a>
            <?php else: ?>
                <?php $pdf_link = get_coupon_pdf_url_from_code( $bestellnummer ); ?>
            <a style="display: inline-block;padding: 14px 24px;background-color: #54774e;color: #ffffff;text-decoration: none;border-radius: 6px;font-weight: bold;" href='<?php echo $pdf_link; ?>' class='button'>👉 Gutschein herunterladen</a>
            <?php 
                endif;
            }
            }
        ?>

        </div>

        <div class='text-block' style='text-align:left; background-color:#f2f2f2; padding:20px; border-radius:8px; margin-bottom:20px;'>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034; margin-bottom: 8px;margin-top:0;"><strong>Wichtige Hinweise</strong></p>
            <ul style="font-size: 16px;line-height: 1.6;color: #2e4034; padding-left: 20px; margin: 0;">
                <li>Gültigkeit: 3 Jahre ab Ausstellungsdatum.</li>
                <li>Keine Barauszahlung, keine Rückerstattung von Restguthaben.</li>
                <li>Übertragbar ohne Namensbindung.</li>
                <?php if ($gutschein_typ == 'Wertgutschein'): ?>
                    <li>Wertgutschein: keine Standortbindung, flexible Einlösung.</li>
                <?php else: ?>
                    <li>Standortgutschein: strenge Bindung an den ausgewiesenen Standort.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class='text-block' style='text-align:center;'>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">Bitte antworte nicht auf diese E-Mail. Du hast Fragen? Schreib an <a href="mailto:support@book-a-bubble.de" style="color:#54774e;">support@book-a-bubble.de</a>.</p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034; margin-top: 10px;">Viel Freude beim Verschenken.</p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">Bis bald unter dem Sternenhimmel.</p>
        </div>
    </div>

    <div class='footer' style='text-align:center; color:#888888; padding:20px;font-size:16px;line-height:1.3;'>
        <p>
            <a href='https://www.iubenda.com/nutzungsbedingungen/38916340' style='color:#54774e; text-decoration:none;font-size:16px;line-height:1.3;'>AGB</a> |
            <a href='https://book-a-bubble.de/impressum/' style='color:#54774e; text-decoration:none;font-size:16px;line-height:1.3;'>Impressum</a> |
            <a href='https://www.iubenda.com/privacy-policy/38916340' style='color:#54774e; text-decoration:none;font-size:16px;line-height:1.3;'>Datenschutz</a>
        </p>
        <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">&copy; 2025 Book a Bubble</p>
    </div>
</div>