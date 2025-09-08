<?php
session_start();
require 'config/db.php';
require 'auth.php';

// === Function to resize & compress uploaded images ===
function compressImage($source, $destination, $quality = 70, $maxWidth = 800, $maxHeight = 800)
{
  $imgInfo = getimagesize($source);
  if ($imgInfo === false) return false;

  $mime = $imgInfo['mime'];

  switch ($mime) {
    case 'image/jpeg':
      $image = imagecreatefromjpeg($source);
      break;
    case 'image/png':
      $image = imagecreatefrompng($source);
      break;
    case 'image/gif':
      $image = imagecreatefromgif($source);
      break;
    default:
      return false; // unsupported format
  }

  $width = imagesx($image);
  $height = imagesy($image);

  // Keep aspect ratio
  $ratio = min($maxWidth / $width, $maxHeight / $height, 1); // don’t upscale
  $newWidth = (int)($width * $ratio);
  $newHeight = (int)($height * $ratio);

  // Resize
  $newImg = imagecreatetruecolor($newWidth, $newHeight);
  imagecopyresampled($newImg, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

  // Save as compressed JPEG
  imagejpeg($newImg, $destination, $quality);

  imagedestroy($image);
  imagedestroy($newImg);

  return true;
}

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

  $upload_dir = "uploads/";
  if (!is_dir($upload_dir)) mkdir($upload_dir);

  // Rear Camera Photo
  $photo_name = "";
  if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === 0) {
    $photo_name = uniqid("img_rear_") . ".jpg"; // always save as jpg
    $destination = $upload_dir . $photo_name;

    if (!compressImage($_FILES['photo']['tmp_name'], $destination, 70, 800, 800)) {
      $photo_name = "";
      $message = "❌ Rear camera photo upload failed (invalid format).";
    }
  }

  // Selfie / Gallery Photo
  $selfie_name = "";
  if (!empty($_FILES['photo_selfie']['name']) && $_FILES['photo_selfie']['error'] === 0) {
    $selfie_name = uniqid("img_selfie_") . ".jpg"; // always save as jpg
    $destination2 = $upload_dir . $selfie_name;

    if (!compressImage($_FILES['photo_selfie']['tmp_name'], $destination2, 70, 800, 800)) {
      $selfie_name = "";
      $message = "❌ Gallery photo upload failed (invalid format).";
    }
  }

  // Require at least one image
  if ($photo_name === "" && $selfie_name === "") {
    $message = "❌ Please upload at least one photo (rear camera or gallery).";
  } else {
    $school = $_SESSION['school'];

    $sql = "INSERT INTO id_cards 
        (student_name, father_name, class, section, roll_no, blood_group, dob, contact_no, address, photo, school) 
        VALUES 
        ('$student_name', '$father_name', '$class', '$section', '$roll_no', '$blood_group', '$dob', '$contact_no', '$address', 
         '" . ($photo_name ?: $selfie_name) . "', '$school')";


    // $sql = "INSERT INTO id_cards 
    //         (student_name, father_name, class, section, roll_no, blood_group, dob, contact_no, address, photo, selfie, school) 
    //         VALUES 
    //         ('$student_name', '$father_name', '$class', '$section', '$roll_no', '$blood_group', '$dob', '$contact_no', '$address', '$photo_name', '$selfie_name', '$school')";

    if ($conn->query($sql) === TRUE) {
      $message = "✅ Student ID saved successfully!";
    } else {
      $message = "❌ Error: " . $conn->error;
    }
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Student ID Entry</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="bg-gray-100 min-h-screen font-sans">
  <header class="bg-blue-700 h-20 flex items-center justify-between px-6 shadow">
    <h2 class="text-white text-2xl font-bold">Welcome, <?= htmlspecialchars($_SESSION['school']) ?>!</h2>
    <div>
      <a href="dashboard.php" class="bg-cyan-400 px-4 py-2 rounded">Show Listed Data</a>
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

      <form action="" method="POST" enctype="multipart/form-data" class="space-y-3" id="studentForm">
        <input type="text" name="student_name" placeholder="Student Name" required class="w-full p-2 border rounded">
        <input type="text" name="father_name" placeholder="Father's Name" required class="w-full p-2 border rounded">
        <input type="text" name="class" placeholder="Class" required class="w-full p-2 border rounded">
        <input type="text" name="section" placeholder="Section" class="w-full p-2 border rounded">
        <input type="text" name="roll_no" placeholder="Roll No." required class="w-full p-2 border rounded">
        <input type="text" name="blood_group" placeholder="Blood Group" class="w-full p-2 border rounded">

        <!-- Date of Birth -->
        <label class="block font-medium md:hidden">Date of Birth</label>
        <input type="text" id="dob" name="dob" placeholder="Select Date of Birth" class="w-full p-2 border rounded">
        <small class="text-gray-500">Click to choose from calendar</small>

        <input type="tel" name="contact_no" placeholder="Contact No." class="w-full p-2 border rounded">
        <input type="text" name="address" placeholder="Address" class="w-full p-2 border rounded">

        <!-- Rear Camera -->
        <label class="block font-medium">Upload from Rear Camera</label>
        <input type="file" name="photo" accept="image/*" capture="environment" class="w-full p-2 border rounded">

        <!-- Upload from Gallery -->
        <label class="block font-medium">Upload from Gallery</label>
        <input type="file" name="photo_selfie" accept="image/*" class="w-full p-2 border rounded">

        <button type="submit" class="bg-blue-600 text-white w-full py-2 font-bold rounded">Submit</button>
      </form>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr("#dob", {
      dateFormat: "Y-m-d",
      maxDate: "today"
    });

    // Client-side validation for at least one file
    document.getElementById("studentForm").addEventListener("submit", function(e) {
      const rear = document.querySelector('input[name="photo"]').files.length;
      const selfie = document.querySelector('input[name="photo_selfie"]').files.length;
      if (rear === 0 && selfie === 0) {
        alert("Please upload at least one photo (rear camera or gallery).");
        e.preventDefault();
      }
    });
  </script>
</body>

</html>