<?php
require 'config/db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $token = bin2hex(random_bytes(32));

    $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
    $stmt->bind_param("ss", $token, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $link = "http://yourdomain.com/reset_password.php?token=" . $token;

        $subject = "Password Reset - Creative Art Management";
        $msg = "Click the link to reset your password:\n\n$link";
        $headers = "From: noreply@yourdomain.com";

        mail($email, $subject, $msg, $headers);
        $message = "✅ Password reset link sent to your email.";
    } else {
        $message = "❌ No account found with that email.";
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