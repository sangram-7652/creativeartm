<?php
require 'config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school = trim($_POST['school'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($school && $email && $password) {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = '<div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">❌ This email is already registered. Please login or use another email.</div>';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (school, contact_number, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $school, $contact, $email, $hashedPassword);

            if ($stmt->execute()) {
                // Send welcome email
                $to = $email;
                $subject = "Welcome to Creative Art Management";
                $messageBody = "
Hi $school,

Your account has been successfully created.

✅ Login Email: $email
✅ Password: $password

You can login at: http://yourdomain.com/login.html

Regards,
Creative Art Management Team
                ";

                $headers = "From: noreply@yourdomain.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                mail($to, $subject, $messageBody, $headers);

                $message = '<div class="bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded mb-4">✅ Signup successful! Please check your email.</div>';
            } else {
                $message = '<div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">❌ Database error: ' . $stmt->error . '</div>';
            }
        }

        $checkStmt->close();
    } else {
        $message = '<div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-2 rounded mb-4">⚠️ All fields are required.</div>';
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Signup - Creative Art</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-pink-600 to-purple-700 min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Create a New Account</h2>

        <?php if (!empty($message)) echo $message; ?>

        <form action="signup.php" method="POST" class="space-y-4">
            <input type="text" name="school" placeholder="School/College Name" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-pink-400" />
            <input type="text" name="contact" placeholder="Mobile No." required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-pink-400" />
            <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-pink-400" />
            <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-pink-400" />

            <button type="submit" class="w-full py-2 bg-pink-600 text-white rounded hover:bg-pink-700 transition">Sign Up</button>
        </form>

        <p class="text-sm text-center text-gray-600 mt-4">
            Already have an account?
            <a href="login.php" class="text-pink-500 hover:underline">Login</a>
        </p>
    </div>

</body>

</html>