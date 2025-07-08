<?php
require 'functions.php';
$message = '';

// Step 1: Send Unsubscribe Code
if (isset($_POST['send_code']) && !empty($_POST['unsubscribe_email'])) {
    $email = trim($_POST['unsubscribe_email']);
    $code = generateVerificationCode();

    if (!is_dir('codes')) {
        mkdir('codes', 0755, true);
    }

    file_put_contents("codes/unsub_{$email}.txt", $code);
    sendUnsubscribeCode($email, $code);
    $message = "Unsubscribe verification code sent to your email.";
}

// Step 2: Verify Unsubscribe Code
if (isset($_POST['verify_code']) && !empty($_POST['verification_code'])) {
    $email = trim($_POST['unsubscribe_email']);
    $inputCode = trim($_POST['verification_code']);
    $file = "codes/unsub_{$email}.txt";

    if (file_exists($file)) {
        $storedCode = trim(file_get_contents($file));
        if ($inputCode === $storedCode) {
            unsubscribeEmail($email);
            unlink($file);
            $message = "You have been unsubscribed successfully.";
        } else {
            $message = "Invalid verification code.";
        }
    } else {
        $message = "No unsubscribe code found. Please request again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Unsubscribe from XKCD Comics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 30px;
            max-width: 500px;
            margin: 40px auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-top: 20px;
            font-weight: bold;
        }

        input[type="email"],
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #cc0000;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #a10000;
        }

        p {
            margin-top: 20px;
            color: green;
            font-weight: bold;
        }

        hr {
            margin: 30px 0;
            border: none;
            border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h2>Unsubscribe from XKCD Comics</h2>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <!-- Unsubscribe form -->
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="unsubscribe_email" required><br>
        <button type="submit" name="send_code" id="submit-unsubscribe">Unsubscribe</button>
    </form>

    <hr>

    <!-- Verification form -->
    <form method="POST">
        <label>Verification Code:</label><br>
        <input type="text" name="verification_code" maxlength="6" required><br>
        <input type="hidden" name="unsubscribe_email" value="<?= isset($_POST['unsubscribe_email']) ? htmlspecialchars($_POST['unsubscribe_email']) : '' ?>">
        <button type="submit" name="verify_code" id="submit-verification">Verify</button>
    </form>
</body>
</html>
