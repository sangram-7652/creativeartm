<?php
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if ($token && $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashed, $token);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "<script>alert('✅ Password updated successfully!'); window.location.href='login.html';</script>";
        } else {
            echo "<script>alert('❌ Invalid or expired token.'); window.location.href='forgot_password.php';</script>";
        }
    }
}
