<?php
require 'config/db.php';
require 'auth.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die('Invalid student ID');
}

// Fetch existing data
$result = $conn->query("SELECT * FROM id_cards WHERE id = $id");
$student = $result->fetch_assoc();

if (!$student) {
    die('Student not found');
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $roll = $_POST['roll'];
    $class = $_POST['class'];
    $section = $_POST['section'];
    $dob = $_POST['dob'];
    $contact = $_POST['contact'];

    // Handle photo upload
    $photo = $student['photo'];
    if ($_FILES['photo']['name']) {
        $photo = time() . '_' . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo);
    }

    // Update database
    $stmt = $conn->prepare("UPDATE id_cards SET student_name=?, roll_no=?, class=?, section=?, dob=?, contact_no=?, photo=? WHERE id=?");
    $stmt->bind_param("sssssssi", $name, $roll, $class, $section, $dob, $contact, $photo, $id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Student</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Edit Student</h2>
        <form method="POST" enctype="multipart/form-data">
            <label class="block mb-2">Name:
                <input type="text" name="name" value="<?= $student['student_name'] ?>" class="border w-full p-2">
            </label>
            <label class="block mb-2">Roll:
                <input type="text" name="roll" value="<?= $student['roll_no'] ?>" class="border w-full p-2">
            </label>
            <label class="block mb-2">Class:
                <input type="text" name="class" value="<?= $student['class'] ?>" class="border w-full p-2">
            </label>
            <label class="block mb-2">Section:
                <input type="text" name="section" value="<?= $student['section'] ?>" class="border w-full p-2">
            </label>
            <label class="block mb-2">Date of Birth:
                <input type="date" name="dob" value="<?= $student['dob'] ?>" class="border w-full p-2">
            </label>
            <label class="block mb-2">Contact:
                <input type="text" name="contact" value="<?= $student['contact_no'] ?>" class="border w-full p-2">
            </label>
            <label class="block mb-2">Photo:
                <input type="file" name="photo" class="border w-full p-2">
                <?php if ($student['photo']): ?>
                    <img src="uploads/<?= $student['photo'] ?>" alt="Student Photo" class="w-24 mt-2 rounded">
                <?php endif; ?>
            </label>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
            <a href="dashboard.php" class="ml-4 text-gray-700">Cancel</a>
        </form>
    </div>
</body>

</html>