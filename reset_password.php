<?php
$token = $_GET['token'] ?? '';
if (!$token) {
  die("Invalid or missing token.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-indigo-700 to-pink-600 flex items-center justify-center px-4">
  <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full text-center">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Reset Your Password</h2>

    <form action="update_password.php" method="POST" class="space-y-4">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <input type="password" name="new_password" required placeholder="New Password"
        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      <button type="submit"
        class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition">Update Password</button>
    </form>

    <p class="mt-4 text-sm text-gray-500">
      <a href="login.php" class="text-indigo-500 hover:underline">Back to Login</a>
    </p>
  </div>
</body>

</html>
