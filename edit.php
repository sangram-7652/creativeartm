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
            return false;
    }

    $width = imagesx($image);
    $height = imagesy($image);

    $ratio = min($maxWidth / $width, $maxHeight / $height, 1); // no upscale
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);

    $newImg = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($newImg, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    imagejpeg($newImg, $destination, $quality);

    imagedestroy($image);
    imagedestroy($newImg);

    return true;
}

$message = "";
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$school = $_SESSION['school'];

// Fetch current student data
$sql = "SELECT * FROM id_cards WHERE id=$id AND school='" . $conn->real_escape_string($school) . "'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    die("❌ Student not found or access denied.");
}
$student = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // keep old photo by default
    $photo_name = $student['photo'];

    // Rear Camera
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === 0) {
        $photo_name = uniqid("img_rear_") . ".jpg";
        $destination = $upload_dir . $photo_name;
        compressImage($_FILES['photo']['tmp_name'], $destination, 70, 800, 800);
    }

    // Selfie/Gallery
    if (!empty($_FILES['photo_selfie']['name']) && $_FILES['photo_selfie']['error'] === 0) {
        $photo_name = uniqid("img_selfie_") . ".jpg";
        $destination = $upload_dir . $photo_name;
        compressImage($_FILES['photo_selfie']['tmp_name'], $destination, 70, 800, 800);
    }

    $sql = "UPDATE id_cards SET 
            student_name='$student_name',
            father_name='$father_name',
            class='$class',
            section='$section',
            roll_no='$roll_no',
            blood_group='$blood_group',
            dob='$dob',
            contact_no='$contact_no',
            address='$address',
            photo='$photo_name'
          WHERE id=$id AND school='" . $conn->real_escape_string($school) . "'";

    if ($conn->query($sql) === TRUE) {
        $message = "✅ Student ID updated successfully!";
        // refresh student data
        $result = $conn->query("SELECT * FROM id_cards WHERE id=$id AND school='" . $conn->real_escape_string($school) . "'");
        $student = $result->fetch_assoc();
    } else {
        $message = "❌ Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Student ID</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="bg-gray-100 min-h-screen font-sans">
    <header class="bg-blue-700 h-20 flex items-center justify-between px-6 shadow">
        <h2 class="text-white text-2xl font-bold">Editing: <?= htmlspecialchars($student['student_name']) ?></h2>
        <div>
            <a href="dashboard.php" class="bg-cyan-400 px-4 py-2 rounded">Back to Dashboard</a>
            <a href="logout.php" class="bg-red-400 text-white px-4 py-2 rounded">Logout</a>
        </div>
    </header>

    <main class="flex items-center flex-col lg:flex-row px-6 md:px-20 py-10 gap-10">
        <div class="lg:basis-1/2">
            <?php if (!empty($student['photo'])): ?>
                <img src="uploads/<?= htmlspecialchars($student['photo']) ?>" class="rounded shadow w-64 h-64 object-cover" alt="Student Photo">
            <?php else: ?>
                <img src="static/images/placeholder.jpg" class="rounded shadow w-64 h-64 object-cover" alt="No Photo">
            <?php endif; ?>
        </div>

        <div class="lg:basis-1/2 bg-white shadow rounded p-6 w-full">
            <h2 class="text-center text-2xl font-bold mb-4">Edit Student Details</h2>

            <?php if (!empty($message)): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded shadow"><?= $message ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-3" id="editForm">
                <input type="text" name="student_name" value="<?= htmlspecialchars($student['student_name']) ?>" required class="w-full p-2 border rounded">
                <input type="text" name="father_name" value="<?= htmlspecialchars($student['father_name']) ?>" required class="w-full p-2 border rounded">
                <input type="text" name="class" value="<?= htmlspecialchars($student['class']) ?>" required class="w-full p-2 border rounded">
                <input type="text" name="section" value="<?= htmlspecialchars($student['section']) ?>" class="w-full p-2 border rounded">
                <input type="text" name="roll_no" value="<?= htmlspecialchars($student['roll_no']) ?>" required class="w-full p-2 border rounded">
                <input type="text" name="blood_group" value="<?= htmlspecialchars($student['blood_group']) ?>" class="w-full p-2 border rounded">

                <label class="block font-medium md:hidden">Date of Birth</label>
                <input type="text" id="dob" name="dob" value="<?= htmlspecialchars($student['dob']) ?>" class="w-full p-2 border rounded">

                <input type="tel" name="contact_no" value="<?= htmlspecialchars($student['contact_no']) ?>" class="w-full p-2 border rounded">
                <input type="text" name="address" value="<?= htmlspecialchars($student['address']) ?>" class="w-full p-2 border rounded">

                <label class="block font-medium">Upload New Rear Camera Photo</label>
                <input type="file" name="photo" accept="image/*" capture="environment" class="w-full p-2 border rounded">

                <label class="block font-medium">Upload New Gallery Photo</label>
                <input type="file" name="photo_selfie" accept="image/*" class="w-full p-2 border rounded">

                <button type="submit" class="bg-blue-600 text-white w-full py-2 font-bold rounded">Update</button>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#dob", {
            dateFormat: "Y-m-d",
            maxDate: "today"
        });
    </script>
</body>

</html>