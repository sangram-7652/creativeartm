<?php
session_start();
require 'config/db.php'; // 🔧 Update path if needed
require 'auth.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitize inputs
  $student_name = $conn->real_escape_string($_POST['student_name']);
  $father_name  = $conn->real_escape_string($_POST['father_name']);
  $class        = $conn->real_escape_string($_POST['class']);
  $section      = $conn->real_escape_string($_POST['section']);
  $roll_no      = $conn->real_escape_string($_POST['roll_no']);
  $blood_group  = $conn->real_escape_string($_POST['blood_group']);
  $dob          = $conn->real_escape_string($_POST['dob']);
  $contact_no   = $conn->real_escape_string($_POST['contact_no']);
  $address      = $conn->real_escape_string($_POST['address']);

  // Handle photo upload
  $photo_name = "";
  if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir);
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $photo_name = uniqid("img_") . "." . $ext;
    move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_name);
  }

  // get school from session 
  $school = $_SESSION['school'];

  // Insert into database
  $sql = "INSERT INTO id_cards 
  (student_name, father_name, class, section, roll_no, blood_group, dob, contact_no, address, photo, school)
  VALUES 
  ('$student_name', '$father_name', '$class', '$section', '$roll_no', '$blood_group', '$dob', '$contact_no', '$address', '$photo_name', '$school')";

  if ($conn->query($sql) === TRUE) {
    $message = "✅ Student ID saved successfully!";
  } else {
    $message = "❌ Error: " . $conn->error;
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Student ID Entry</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen font-sans">
  <header class="bg-blue-700 h-20 flex items-center justify-between px-6 shadow">
    <h2 class="text-white text-2xl font-bold">Welcome, <?= htmlspecialchars($_SESSION['school']) ?>!</h2>
    <div>
      <a href="dashboard.php" class="bg-cyan-400  px-4 py-2 rounded">Show Listed Data</a>
      <a href="logout.php" class="bg-red-400 text-white px-4 py-2 rounded">Logout</a>
    </div>
  </header>

  <main class="flex items-center flex-col lg:flex-row px-6 md:px-20 py-10 gap-10">
    <div class="lg:basis-1/2">
      <img src="static/images/Project_70-09.jpg" class="rounded shadow" alt="Illustration">
    </div>

    <div class="lg:basis-1/2 bg-white shadow rounded p-6 w-full">
      <h2 class="text-center text-2xl font-bold mb-4">Enter Student ID Details</h2>

      <?php if (!empty($message)): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded shadow"><?= $message ?></div>
      <?php endif; ?>

      <form action="" method="POST" enctype="multipart/form-data" class="space-y-3">
        <input type="text" name="student_name" placeholder="Student Name" required class="w-full p-2 border rounded">
        <input type="text" name="father_name" placeholder="Father's Name" required class="w-full p-2 border rounded">
        <input type="text" name="class" placeholder="Class" required class="w-full p-2 border rounded">
        <input type="text" name="section" placeholder="Section" class="w-full p-2 border rounded">
        <input type="text" name="roll_no" placeholder="Roll No." required class="w-full p-2 border rounded">
        <input type="text" name="blood_group" placeholder="Blood Group" class="w-full p-2 border rounded">
        <input type="date" name="dob" placeholder="Date of Birth" class="w-full p-2 border rounded">
        <input type="tel" name="contact_no" placeholder="Contact No." class="w-full p-2 border rounded">
        <input type="text" name="address" placeholder="Address" class="w-full p-2 border rounded">
        <input type="file" name="photo" accept="image/*" class="w-full p-2 border rounded">
        <button type="submit" class="bg-blue-600 text-white w-full py-2 font-bold rounded">Submit</button>
      </form>
    </div>
  </main>
</body>

</html>