<?php
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email) {
        echo "<script>alert('❌ Email is required.'); window.location.href='forgot_password.php';</script>";
        exit;
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('❌ No user found with this email.'); window.location.href='forgot_password.php';</script>";
        exit;
    }

    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Save token and expiry to DB
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
    $stmt->bind_param("sss", $token, $expiry, $email);
    $stmt->execute();

    // Send email with reset link
    $resetLink = "https://creativeartm.com/reset_password.php?token=" . urlencode($token);

    $subject = "Reset Your Creative Art Password";
    $message = "
        Hi,<br><br>
        Click the link below to reset your password:<br>
        <a href='$resetLink'>$resetLink</a><br><br>
        This link will expire in 1 hour.<br><br>
        - Creative Art Team
    ";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Creative Art <no-reply@yourdomain.com>\r\n";

    if (mail($email, $subject, $message, $headers)) {
        echo "<script>alert('✅ Reset link sent to your email.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('⚠️ Failed to send email.'); window.location.href='forgot_password.php';</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-r from-purple-600 to-indigo-700 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl shadow-md w-full max-w-md p-8 text-center">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Forgot Password?</h2>
        <p class="text-sm text-gray-500 mb-6">Enter your email to get a password reset link.</p>

        <?php if ($message): ?>
            <div class="mb-4 text-sm text-blue-600 font-medium"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="email" name="email" required placeholder="Enter your email"
                class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-purple-400" />
            <button type="submit"
                class="w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700 transition">Send Reset Link</button>
        </form>

        <p class="mt-4 text-sm text-gray-500">
            <a href="login.php" class="text-purple-500 hover:underline">Back to Login</a>
        </p>
    </div>
</body>

</html>