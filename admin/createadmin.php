<?php
// Run this only once to create a default admin
require '../config/db.php';

$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);

// Check if admin already exists
$check = $conn->query("SELECT * FROM admin WHERE username = '$username'");
if ($check->num_rows > 0) {
    echo "Admin already exists.";
} else {
    if ($conn->query("INSERT INTO admin (username, password) VALUES ('$username', '$password')")) {
        echo "✅ Admin user created successfully.";
    } else {
        echo "❌ Error: " . $conn->error;
    }
}
?>
