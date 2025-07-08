<?php
require_once 'functions.php';  // ← only include your helper functions once

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Send verification code
    if (isset($_POST['send_code']) && !empty($_POST['email'])) {
        $email = trim($_POST['email']);
        $code = generateVerificationCode();
        if (!is_dir('codes')) {
            mkdir('codes', 0755, true);
        }
        file_put_contents("codes/{$email}.txt", $code);
        sendVerificationEmail($email, $code);
        $message = "Verification code sent to your email.";
    }

    // Verify the code
    if (isset($_POST['verify_code']) && !empty($_POST['verification_code'])) {
        $email = trim($_POST['email'] ?? '');
        $inputCode = trim($_POST['verification_code']);
        $codeFile = "codes/{$email}.txt";

        if (file_exists($codeFile)) {
            $storedCode = trim(file_get_contents($codeFile));
            if ($inputCode === $storedCode) {
                registerEmail($email);
                unlink($codeFile);
                $message = "Email verified and registered successfully!";
            } else {
                $message = "Invalid code. Please try again.";
            }
        } else {
            $message = "No code found for that email. Please request a new one.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>XKCD Comic Subscription</title>
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
            background-color: #0077cc;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #005fa3;
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
    <h2>Subscribe to XKCD Comics</h2>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <!-- Step 1: Request verification code -->
    <form method="post">
        <label for="email">Email:</label><br>
        <input id="email" type="email" name="email" required><br>
        <button id="submit-email" type="submit" name="send_code">Submit</button>
    </form>

    <hr>

    <!-- Step 2: Verify code -->
    <form method="post">
        <label for="verification_code">Verification Code:</label><br>
        <input id="verification_code" type="text" name="verification_code" maxlength="6" required><br>
        <input type="hidden" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        <button id="submit-verification" type="submit" name="verify_code">Verify</button>
    </form>
</body>
</html>
