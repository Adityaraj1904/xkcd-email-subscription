<?php
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/PHPMailer-master/src/Exception.php';

function generateVerificationCode($length = 6) {
    $digits = '0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $digits[rand(0, strlen($digits) - 1)];
    }
    return $code;
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) file_put_contents($file, '');
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    if (file_exists($file)) {
        $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $filtered = array_filter($emails, fn($e) => trim($e) !== trim($email));
        file_put_contents($file, implode(PHP_EOL, $filtered) . PHP_EOL);
    }
}

function sendVerificationEmail($email, $code) {
    sendEmail($email, 'Your Verification Code', "<p>Your verification code is: <strong>$code</strong></p>");
}

function sendUnsubscribeCode($email, $code) {
    sendEmail($email, 'Confirm Un-subscription', "<p>To confirm un-subscription, use this code: <strong>$code</strong></p>");
}

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        // Mailpit SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->SMTPSecure = false;

        // Email headers
        $mail->setFrom('no-reply@example.com', 'XKCD Bot');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}

function verifyCode($email, $code, $type = 'sub') {
    $prefix = ($type === 'unsub') ? 'unsub_' : '';
    $file = __DIR__ . "/codes/{$prefix}{$email}.txt";
    if (file_exists($file)) {
        return trim(file_get_contents($file)) === $code;
    }
    return false;
}

function getRandomXKCDComic() {
    $latest = json_decode(file_get_contents('https://xkcd.com/info.0.json'), true)['num'];
    $random = rand(1, $latest);
    return json_decode(file_get_contents("https://xkcd.com/$random/info.0.json"), true);
}

function fetchAndFormatXKCDData() {
    $comic = getRandomXKCDComic();
    $img = htmlspecialchars($comic['img']);
    return "<h2>XKCD Comic</h2>
            <img src=\"$img\" alt=\"XKCD Comic\">
            <p><a href=\"http://localhost:8000/unsubscribe.php\" id=\"unsubscribe-button\">Unsubscribe</a></p>";
}

function sendXKCDUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $body = fetchAndFormatXKCDData();

    foreach ($emails as $email) {
        sendEmail($email, 'Your XKCD Comic', $body);
    }
}
