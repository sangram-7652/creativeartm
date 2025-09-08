<?php
require 'config/db.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    die("❌ Invalid or missing token.");
}

// Validate token and check expiry
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Invalid or expired token.");
}

$user = $result->fetch_assoc();
$user_id = $user['id'];
?>
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
  <meta charset="UTF-8" />
  <title>Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-indigo-700 to-pink-600 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-4">
  <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-8 max-w-md w-full text-center">
    <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">Reset Your Password</h2>

    <form action="update_password.php" method="POST" class="space-y-4 text-left">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      
      <div>
        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">New Password</label>
        <div class="relative">
          <input type="password" name="new_password" id="new_password" required
            placeholder="Enter new password"
            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
          <button type="button" onclick="togglePassword()"
            class="absolute right-3 top-2.5 text-gray-500 dark:text-gray-300">
            👁️
          </button>
        </div>
      </div>

      <button type="submit"
        class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition">Update Password</button>
    </form>

    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 text-center">
      <a href="login.php" class="text-indigo-300 hover:underline">Back to Login</a>
    </p>
  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById("new_password");
      input.type = input.type === "password" ? "text" : "password";
    }
  </script>
</body>

</html>
