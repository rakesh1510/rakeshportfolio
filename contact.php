<?php
 
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$to = "info@trustnovatech.de";
$site = "TrustNova Tech / Rakesh Prajapati Portfolio";

function clean($v): string {
    $v = trim((string) ($v ?? ""));
    $v = str_replace(["\r", "\n"], " ", $v);
    return $v;
}

if (!empty($_POST["company"])) {
    http_response_code(200);
    exit;
} // honeypot
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header("Location: contact.html");
    exit;
}

$name = clean($_POST["name"] ?? "");
$email = clean($_POST["email"] ?? "");
$msg = trim((string) ($_POST["message"] ?? ""));

if ($name === "" || $email === "" || $msg === "") {
    header("Location: contact.html?sent=0");
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: contact.html?sent=0");
    exit;
}

// ===== SMTP creds (use your Gmail + App Password) =====
//$gmailUser = "rp151090@gmail.com";
//$gmailPass = "gkxdviavowwugiio"; // <-- replace this
$hostingerUser = 'info@trustnovatech.de';
$hostingerPass = 'Q!Ls$Ay76Bzf#jQQ';
try {
    // 1) Send to you
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = $hostingerUser;
    $mail->Password = $hostingerPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($hostingerUser, $site);
    $mail->addAddress($to);
    $mail->addReplyTo($email, $name);

    $mail->Subject = "New inquiry from {$name} ({$site})";
    $mail->Body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$msg}\n\n---\nSent from {$site}";

    $mail->send();

    // 2) Auto-reply to visitor (reuse SMTP, new instance is fine)
    $auto = new PHPMailer(true);
    $auto->isSMTP();
    $auto->Host = 'smtp.hostinger.com';
    $auto->SMTPAuth = true;
    $auto->Username = $hostingerUser;
    $auto->Password = $hostingerPass;
    $auto->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $auto->Port = 587;
    $auto->CharSet = 'UTF-8';

    $auto->setFrom($hostingerUser, 'TrustNova Tech');
    $auto->addAddress($email, $name);

    $auto->isHTML(true);
    $auto->Subject = "Vielen Dank für Ihre Nachricht";

    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

    $auto->Body = "
<div style='font-family:Arial,Helvetica,sans-serif;line-height:1.6;color:#333;'>


  <!-- GERMAN -->
  <p><strong>Hallo {$safeName},</strong></p>
  <p>vielen Dank für Ihre Nachricht. Ich habe Ihre Anfrage erhalten und melde mich so schnell wie möglich.</p>

  <p>Mit freundlichen Grüßen,</p>

  <!-- SIGNATURE AGAIN -->
  <table cellpadding='0' cellspacing='0' style='font-family:Arial,Helvetica,sans-serif; color:#111; margin-top:10px;'>
    <tr>
      
      <td style='vertical-align:middle; padding-right:18px; border-right:2px solid #e5e5e5;'>
        <img src='https://trustnovatech.com/assets/images/logo.png' width='120' alt='TrustNova Tech' style='display:block;'>
      </td>

      <td style='vertical-align:middle; padding-left:18px; font-size:13px; line-height:1.6;'>

        <div style='font-size:17px; font-weight:bold; color:#002b5c;'>Rakesh Prajapati</div>
        <div style='color:#333;'>Founder &amp; Software Engineer</div>
        <div style='font-weight:bold; color:#0a66c2;'>TrustNova Tech</div>

        <div style='margin:8px 0; color:#555;'>
          Neuenstadt am Kocher · Heilbronn Region · Germany
        </div>

        <div>
          <a href='https://trustnovatech.com' style='color:#0a66c2; text-decoration:none;'>www.trustnovatech.com</a>
        </div>

        <div>
          <a href='mailto:info@trustnovatech.de' style='color:#0a66c2; text-decoration:none;'>info@trustnovatech.de</a>
        </div>

        <div style='color:#000; font-weight:bold;'>
          +49 176 66145047
        </div>

        <div style='margin-top:10px; padding-top:8px; border-top:1px solid #eeeeee; font-size:12px; color:#777;'>
          Custom Software · SaaS Platforms · Web Applications · Cloud Solutions
        </div>

        <div style='margin-top:10px; color:#333;'>
          Gerne können wir auch kurz telefonieren, um Ihre Anforderungen unverbindlich zu besprechen.
        </div>

      </td>
    </tr>
  </table>

  <p style='font-size:12px;color:#777;margin-top:18px;'>
    (Automatic confirmation / Automatische Bestätigung)
  </p>

</div>
";

    $auto->send();

    header("Location: contact.html?sent=1");
    exit;
} catch (Exception $e) {
    // Log exact reason
    @file_put_contents(__DIR__ . '/mail_error.log',
                    date('c') . " | EX=" . $e->getMessage() . " | INFO=" . ($mail->ErrorInfo ?? '') . "\n",
                    FILE_APPEND
    );
    header("Location: contact.html?sent=0");
    exit;
}