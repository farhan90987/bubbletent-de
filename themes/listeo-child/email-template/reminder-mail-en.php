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
          <h1 style='color:white; font-size:28px; margin-top:20px; font-family:Arial, sans-serif;'>Tomorrow is the day!</h1>
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
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">Your adventure in the Bubble Tent begins tomorrow. If you still need travel information, please click on the button: There you will find the link to the location, the address and our one-pager with all the important details. In your booking portal, you will also find all the information about your stay, the invoice and check-in.</p>
        </div>

        <div class='info-box' style='background-color:#f2f2f2; padding:20px; border-radius:8px; margin-bottom:20px;'>
            <?php 
            $product_name = '';
            foreach ($order->get_items() as $item_id => $item) {
                $product_name = $item->get_name();
            }
            ?>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;text-align:left;"><img width="24px" style="width:24px;margin-right:5px;vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/marker-1.png" alt="Tent Address Icon"> <strong>Location:</strong> <?php echo $product_name ?></p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;text-align:left;"> 
            <img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/calendar.png" alt="">	<strong>Check-In:</strong> <?php echo $arrival_fmt; ?> &nbsp;&nbsp;&nbsp; <img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/calendar.png" alt="Property Policies Icon"> <strong>Check-Out:</strong> <?php echo $departure_fmt; ?>
            </p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;text-align:left;"><img width="24px" style="width:24px; margin-right:5px; vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/moon.png" alt=""> <strong>Nights:</strong> <?php echo $nights; ?> &nbsp;&nbsp;&nbsp; <img width="24px" style="width:24px;margin-right:5px;vertical-align:middle;" src="<?php echo get_stylesheet_directory_uri();?>/assets/images/people-fill-1.png" alt=""> <strong>Adults:</strong> <?php echo $adults; ?>
            </p>
        </div>

        <div class='button-container' style='text-align:center; margin:30px 0 10px 0;'>
            <?php 
                $rand_password = get_post_meta( $order->get_id(), '_random_password', true );
               	$lang_link = '/en/booking-details/?order_id='.$order_id;
            ?>
            <a style="display: inline-block;padding: 14px 24px;background-color: #54774e;color: #ffffff;text-decoration: none;border-radius: 6px;font-weight: bold;" href='<?php echo get_home_url().$lang_link; ?>&password=<?php echo $rand_password; ?>' class='button' style='display:inline-block; padding:14px 24px; background-color:#54774e; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:bold;'>View Booking</a>
        </div>

        <div class='text-block' style='text-align:center; background-color:#f2f2f2; padding:20px; border-radius:8px;'>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">if you have questions about your booking or stay, please use the form on the order overview page.<br><strong>Do not reply to this email.</strong></p>
        </div>

        <div class='text-block' style='text-align:center;'>
            <p style='font-size:20px; margin-top:20px;color: #2e4034;'>We look forward to welcoming you under the starry sky soon.</p>
            <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">See you soon!<br><span style='font-family:"Brush Script MT", cursive; font-size:22px;'>Markus Mathes</span><br><span style='font-size:16px; color:#555;'>Managing Director</span></p>
        </div>
    </div>

    <div class='footer' style='text-align:center; color:#888888; padding:20px;font-size:16px;line-hight:1.3;'>
        <p>
            <a href='https://www.iubenda.com/nutzungsbedingungen/38916340' style='color:#54774e; text-decoration:none;font-size:16px;line-hight:1.3;'>Terms & Conditions</a> |
            <a href='https://book-a-bubble.de/en/imprint/' style='color:#54774e; text-decoration:none;font-size:16px;line-hight:1.3;'>Legal Notice</a> |
            <a href='https://www.iubenda.com/privacy-policy/38916340' style='color:#54774e; text-decoration:none;font-size:16px;line-hight:1.3;'>Privacy Policy</a>
        </p>
        <p style="font-size: 16px;line-height: 1.3;color: #2e4034;">&copy; 2025 Book a Bubble</p>
    </div>
</div>


