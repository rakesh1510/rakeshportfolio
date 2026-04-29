<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$to   = "rp151090@gmail.com";
$site = "Rakesh Prajapati Portfolio";

function clean($v): string {
    $v = trim((string)($v ?? ""));
    $v = str_replace(["\r", "\n"], " ", $v);
    return $v;
}

if (!empty($_POST["company"])) { http_response_code(200); exit; } // honeypot
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header("Location: contact.html"); exit; }

$name  = clean($_POST["name"] ?? "");
$email = clean($_POST["email"] ?? "");
$msg   = trim((string)($_POST["message"] ?? ""));

if ($name === "" || $email === "" || $msg === "") { header("Location: contact.html?sent=0"); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { header("Location: contact.html?sent=0"); exit; }

// ===== SMTP creds (use your Gmail + App Password) =====
$gmailUser = "rp151090@gmail.com";
$gmailPass = "gkxdviavowwugiio"; // <-- replace this

try {
    // 1) Send to you
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $gmailUser;
    $mail->Password = $gmailPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($gmailUser, $site);
    $mail->addAddress($to);
    $mail->addReplyTo($email, $name);

    $mail->Subject = "New inquiry from {$name} ({$site})";
    $mail->Body =
        "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$msg}\n\n---\nSent from {$site}";

    $mail->send();

    // 2) Auto-reply to visitor (reuse SMTP, new instance is fine)
    $auto = new PHPMailer(true);
    $auto->isSMTP();
    $auto->Host = 'smtp.gmail.com';
    $auto->SMTPAuth = true;
    $auto->Username = $gmailUser;
    $auto->Password = $gmailPass;
    $auto->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $auto->Port = 587;
    $auto->CharSet = 'UTF-8';

    $auto->setFrom($gmailUser, 'Rakesh Prajapati');
    $auto->addAddress($email, $name);

    $auto->isHTML(true);
    $auto->Subject = "Thank you for contacting me | Vielen Dank für Ihre Nachricht";

    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

    $auto->Body = "
      <div style='font-family:Arial,Helvetica,sans-serif;line-height:1.6;color:#333;'>
        <p><strong>Dear {$safeName},</strong></p>
        <p>Thank you for reaching out to me. I have received your message and will reply as soon as possible.</p>
        <p>Best regards,<br><strong>Rakesh Prajapati</strong><br>
         Senior Software Engineer/ Software Architect  / Project Manager</p>

        <hr style='margin:22px 0;'>

        <p><strong>Hallo {$safeName},</strong></p>
        <p>vielen Dank für Ihre Nachricht. Ich habe Ihre Anfrage erhalten und melde mich so schnell wie möglich.</p>
        <p>Mit freundlichen Grüßen<br><strong>Rakesh Prajapati</strong><br>
         Senior Software Engineer / Software Architekt / Projektmanager</p>

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