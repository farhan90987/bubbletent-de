<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title>🎉 Du hast gewonnen!</title>
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
                  🎉 Herzlichen Glückwunsch!
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
                <p>Hallo <?php echo $first_name; ?>,</p>

                <p>du hast am <strong><?php echo $formatted_date; ?></strong> beim <strong>Book a Bubble Adventskalender</strong> gewonnen! 🫧</p>

                <!-- HIGHLIGHT BOX -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:20px 0;">
                  <tr>
                    <td style="background:#f2f2f2;padding:20px;text-align:center;border-radius:8px;">
                      <p style="margin:0 0 10px 0;font-size:18px;"><strong>Dein Gewinn:</strong></p>
                      <p style="font-size:20px;font-weight:bold;margin:10px 0;"><?php echo $prize_description; ?></p>
                      <p style="margin:15px 0;">🎁 <strong>Gewinncode:</strong></p>
                      <p style="font-size:22px;font-weight:bold;color:#54774e;margin:10px 0;"><?php echo $coupon_code; ?></p>
                    </td>
                  </tr>
                </table>

                <p>Wir freuen uns riesig, dir eine Freude machen zu dürfen. Du kannst deinen Gewinn ganz einfach einlösen – mit dem Code oben oder direkt über folgenden Button:</p>

                <!-- CTA BUTTON -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:30px auto;">
                  <tr>
                    <td bgcolor="#54774e" style="border-radius:6px;">
                      <a href="https://book-a-bubble.de/"
                        style="font-size:16px;font-weight:bold;color:#ffffff;text-decoration:none;
                        padding:14px 28px;display:inline-block;">
                        🎁 Jetzt Gewinn einlösen
                      </a>
                    </td>
                  </tr>
                </table>

                <p>Bei Fragen erreichst du uns jederzeit über: <a href="mailto:adventskalender@book-a-bubble.de" style="color:#54774e;text-decoration:none;">adventskalender@book-a-bubble.de</a>.</p>
                
                <p style="margin-top:30px;">
                  <strong>Hinweis zur Gültigkeit:</strong><br>
                  Der Gewinn ist gültig bis zum <strong>01.12.2026</strong>. Die Übernachtung muss bis zu diesem Datum gebucht sein. Eine Verlängerung oder Verschiebung über diesen Zeitraum hinaus ist leider nicht möglich.
                </p>

                <p style="margin-top:40px;">Viel Freude mit deinem Gewinn – und vielleicht sehen wir uns schon bald unter dem Sternenhimmel ✨</p>

                <p>Herzliche Grüße<br>
                  <strong>Markus Mathes</strong><br>
                  <span style="font-size:14px;color:#555;">Geschäftsführer – Book a Bubble</span>
                </p>
                
                <p style="margin-top:20px;font-size:13px;color:#666;">
                  <strong>Bitte beachte:</strong> Handelt es sich bei deinem Gewinn um einen Standortgutschein, so ist ein Tausch zu einem anderen Standort leider nicht möglich.
                </p>
              </td>
            </tr>

            <!-- FOOTER -->
            <tr>
              <td style="text-align:center;padding:20px;font-size:12px;color:#888;line-height:1.5;">
                <p>
                  <a href="https://book-a-bubble.de/impressum/" style="color:#54774e;text-decoration:none;">Impressum</a> |
                  <a href="https://www.iubenda.com/privacy-policy/38916340" style="color:#54774e;text-decoration:none;">Datenschutz</a>
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