<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title>🎉 You've won!</title>
  <style>
    body {
      margin:0;
      padding:0;
      font-family: Arial, sans-serif;
      background:#f8f8f8;
    }
    @media only screen and (max-width:480px){
      h1{font-size:22px!important;}
      .container{width:100%!important;}
    }
  </style>
</head>
<body style="margin:0;padding:0;background:#f8f8f8;">

  <!-- Wrapper -->
  <center style="width:100%;background:#f8f8f8;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center">

          <table width="600" class="container" cellspacing="0" cellpadding="0" style="background:#fff;width:600px;max-width:600px;">
            
            <!-- HEADER -->
            <tr>
              <td background="https://book-a-bubble.de/wp-content/uploads/2025/05/Bild-14.jpg" bgcolor="#54774e"
                  style="background-position:center;background-size:cover;text-align:center;padding:40px 20px;">
                  
                <!-- Outlook VML -->
                <!--[if gte mso 9]>
                <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false"
                  style="width:600px;height:200px;">
                  <v:fill type="tile"
                    src="https://book-a-bubble.de/wp-content/uploads/2025/05/Bild-14.jpg"
                    color="#54774e"/>
                  <v:textbox inset="0,0,0,0">
                <![endif]-->

                <img src="https://book-a-bubble.de/wp-content/uploads/2025/12/mail-logo.png"
                  width="160" height="auto"
                  style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;"
                  alt="Book a Bubble Logo">

                <h1 style="color:#ffffff;margin:20px 0 0;font-size:26px;line-height:1.3;">
                  🎉 Congratulations!
                </h1>

                <!--[if gte mso 9]>
                  </v:textbox>
                </v:rect>
                <![endif]-->

              </td>
            </tr>

            <!-- BODY -->
            <tr>
              <td style="padding:30px 20px;line-height:1.6;color:#333;font-size:16px;">
                <p>Hi <?php echo $first_name; ?>,</p>

                <p>You are the lucky winner on <strong><?php echo $formatted_date; ?></strong> in the <strong>Book a Bubble Advent Calendar</strong>! 🫧</p>

                <!-- HIGHLIGHT BOX -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:20px 0;">
                  <tr>
                    <td style="background:#f2f2f2;padding:20px;text-align:center;border-radius:8px;">
                      <p style="margin:0 0 10px 0;font-size:18px;"><strong>Your Prize:</strong></p>
                      <p style="font-size:20px;font-weight:bold;margin:10px 0;"><?php echo $prize_description; ?></p>
                      <p style="margin:15px 0;">🎁 <strong>Redemption Code:</strong></p>
                      <p style="font-size:22px;font-weight:bold;color:#54774e;margin:10px 0;"><?php echo $coupon_code; ?></p>
                    </td>
                  </tr>
                </table>

                <p>We're absolutely thrilled to surprise you with this gift. You can redeem your prize easily by using the code above or via the button below:</p>

                <!-- CTA BUTTON -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:30px auto;">
                  <tr>
                    <td bgcolor="#54774e" style="border-radius:6px;">
                      <a href="https://book-a-bubble.de/en/"
                        style="font-size:16px;font-weight:bold;color:#ffffff;text-decoration:none;
                        padding:14px 28px;display:inline-block;">
                        🎁 Redeem Your Prize
                      </a>
                    </td>
                  </tr>
                </table>

                <p>If you have any questions, feel free to reach us at: <a href="mailto:adventskalender@book-a-bubble.de" style="color:#54774e;text-decoration:none;">adventskalender@book-a-bubble.de</a></p>

                <p style="margin-top:30px;">
                  <strong>Validity Notice:</strong><br>
                  Your prize is valid until <strong>01 December 2026</strong>. The overnight stay must be booked by that date. Extensions or rescheduling beyond this period are not possible.
                </p>

                <p style="margin-top:40px;">We hope you enjoy your prize — and maybe we'll see you soon under the stars ✨</p>

                <p>Warm regards<br>
                  <strong>Markus Mathes</strong><br>
                  <span style="font-size:14px;color:#555;">Founder – Book a Bubble</span>
                </p>

                <p style="margin-top:20px;font-size:13px;color:#666;">
                  <strong>Please note:</strong> If your prize is specific to a location, it cannot be exchanged for another one.
                </p>
              </td>
            </tr>

            <!-- FOOTER -->
            <tr>
              <td style="text-align:center;padding:20px;font-size:12px;color:#888;line-height:1.5;">
                <p>
                  <a href="https://book-a-bubble.de/en/imprint/" style="color:#54774e;text-decoration:none;">Imprint</a> |
                  <a href="https://www.iubenda.com/privacy-policy/38916340" style="color:#54774e;text-decoration:none;">Privacy Policy</a>
                </p>
                <p>&copy; 2025 Book a Bubble</p>
              </td>
            </tr>

          </table>

        </td>
      </tr>
    </table>
  </center>

</body>
</html>