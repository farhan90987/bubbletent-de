
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
          <h1 style='color:white; font-size:28px; margin-top:20px; font-family:Arial, sans-serif;'>Dein Abenteuer wartet auf dich, <?php echo esc_html($first_name); ?>!</h1>
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
        <div class='text-block' style='padding:10px 20px; margin-bottom:10px; background-color:#ffffff; border-radius:8px; line-height:1.6;'>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">Stell dir vor: Der Himmel über euch. Der Wald um euch. Die Ruhe der Natur. Und ihr – in eurem eigenen Bubble Tent. Kein Lärm, keine Termine – nur ihr zwei, die Natur und eine Nacht unter dem Sternenhimmel. Ihr habt euch etwas Besonderes gegönnt. Bereit für unvergessliche Momente zu zweit?</p>
        </div>

        <div class='info-box' style='background-color:#f2f2f2; padding:20px; border-radius:8px; margin-bottom:20px;'>
            <?php 
            $product_name = '';
            foreach ($order->get_items() as $item_id => $item) {
                $product_name = $item->get_name();
            }
            ?>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;text-align:left;"><img width="24px" style="width:24px;margin-right:5px;vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/marker-1.png" alt="Tent Address Icon"> <strong>Standort:</strong> <?php echo $product_name ?></p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;text-align:left;"> 
            <img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/calendar.png" alt="">	<strong>Check-In:</strong> <?php echo $arrival_fmt; ?> &nbsp;&nbsp;&nbsp; <img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/calendar.png" alt="Property Policies Icon"> <strong>Check-Out:</strong> <?php echo $departure_fmt; ?>
            </p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;text-align:left;"><img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/moon.png" alt=""> <strong>Nächte:</strong> <?php echo $nights; ?> &nbsp;&nbsp;&nbsp; <img width="24px" style="width:24px;margin-right:5px;vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/people-fill-1.png" alt=""> <strong>Erwachsene:</strong> <?php echo $adults; ?>
            &nbsp;&nbsp;&nbsp; <img width="24px" style="width:24px;margin-right:5px;vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/people-fill-1.png" alt=""> <strong>Kinder:</strong> <?php echo $kids; ?>
            </p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;text-align:left;"><img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/credit-card.png" alt=""> <strong>Zahlungsart:</strong> <?php echo $payment_method; ?></p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;text-align:left;">
                <img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/credit-card.png" alt=""> <strong>Betrag:</strong> <?php echo wc_price($order_price); ?>
            </p>
        </div>

        <div class='payment-box' style='background-color:#fef9f5; border-left:4px solid #54774e; padding:15px 20px; margin-bottom:20px;text-align:left;'>
            <?php if ( ($payment_method == 'Prepayment by bank transfer' || $payment_method == 'Vorkasse per Überweisung') && $order_status == 'on-hold'): ?>
                <p style='color:#b00020;font-size:16px;line-hight:1.3;margin:0;'><strong>Zahlung ausstehend</strong>
                <p style="color:#b00020;font-size:16px;line-hight:1.3;margin:0;">Bitte überweise den Betrag innerhalb von 5 Tagen auf folgendes Konto:</p>
                <p style="color:#b00020;font-size:16px;line-hight:1.3;margin:0;">Bubble Tent Deutschland FM UG:</p>
                <p style="color:#b00020;font-size:16px;line-hight:1.3;margin:0;">Bank: Sparkasse Nürnberg</p>
                <p style="color:#b00020;font-size:16px;line-hight:1.3;margin:0;">IBAN: DE59 7605 0101 0014 9772 27</p>
                </p>
            <?php else: ?>
                <p style='color:#54774e;font-size:16px;line-hight:1.3;'><strong>Zahlung bestätigt</strong> – alles ist bereit für dein Erlebnis!</p>
            <?php endif; ?>
        </div>

        <div class='button-container' style='text-align:center; margin:30px 0 10px 0;'>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">In deinem Buchungsportal findest du alle Infos zum Aufenthalt, zur Rechnung und zum Check-in:</p>
            <?php 
                $rand_password = get_post_meta( $order->get_id(), '_random_password', true );
            ?>
            <a style="display: inline-block;padding: 14px 24px;background-color: #54774e;color: #ffffff;text-decoration: none;border-radius: 6px;font-weight: bold;" href='<?php echo get_home_url(); ?>/order-detail-page/?order_id=<?php echo $order_id; ?>&password=<?php echo $rand_password; ?>' class='button' style='display:inline-block; padding:14px 24px; background-color:#54774e; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:bold;'>Zur Buchung</a>
            <p style='font-style:italic; margin-top:10px;font-size:16px;line-hight:1.3;color:#54774e;'>Du hast deinen Gutschein nicht einlösen können? Nutze das Formular auf der Bestellübersichtsseite.</p>
        </div>

        <div class='text-block' style='text-align:center; background-color:#f2f2f2; padding:20px; border-radius:8px;'>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">Falls ihr Fragen zu eurer Buchung oder eurem Aufenthalt habt, nutzt bitte das Formular auf der Bestellübersichtsseite.<br><strong>Bitte antwortet nicht auf diese E-Mail.</strong></p>
        </div>

        <div class='text-block' style='text-align:center;'>
            <p style='font-size:20px; margin-top:20px;color: #2e4034;'>Wir freuen uns, euch bald unter dem Sternenhimmel willkommen zu heißen.</p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">Bis bald!<br><span style='font-family:"Brush Script MT", cursive; font-size:22px;'>Markus Mathes</span><br><span style='font-size:16px; color:#555;'>Geschäftsführer</span></p>
        </div>
    </div>

    <div class='footer' style='text-align:center; color:#888888; padding:20px;font-size:16px;line-hight:1.3;'>
        <p>
            <a href='https://www.iubenda.com/nutzungsbedingungen/38916340' style='color:#54774e; text-decoration:none;font-size:16px;line-hight:1.3;'>AGB</a> |
            <a href='https://book-a-bubble.de/impressum/' style='color:#54774e; text-decoration:none;font-size:16px;line-hight:1.3;'>Impressum</a> |
            <a href='https://www.iubenda.com/privacy-policy/38916340' style='color:#54774e; text-decoration:none;font-size:16px;line-hight:1.3;'>Datenschutz</a>
        </p>
        <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">&copy; 2025 Book a Bubble</p>
    </div>
</div>


