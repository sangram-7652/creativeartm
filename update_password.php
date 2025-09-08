<?php
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');

    if (!$token || !$newPassword) {
        echo "<script>alert('❌ Missing token or password.'); window.location.href='forgot_password.php';</script>";
        exit;
    }

    // Optional: Add minimum password length check
    if (strlen($newPassword) < 6) {
        echo "<script>alert('⚠️ Password must be at least 6 characters.'); window.history.back();</script>";
        exit;
    }

    // Validate token and expiry
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('❌ Invalid or expired token.'); window.location.href='forgot_password.php';</script>";
        exit;
    }

    // Valid token – update password
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $hashed, $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('✅ Password updated successfully!'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('❌ Password update failed. Try again.'); window.location.href='forgot_password.php';</script>";
    }
}
