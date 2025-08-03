<?php
require '../config/db.php';
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $roll = $_POST['roll'];
    $class = $_POST['class'];
    $section = $_POST['section'];
    $dob = $_POST['dob'];
    $contact = $_POST['contact'];

    $photo = $_FILES['photo']['name'];
    move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/" . $photo);

    $conn->query("INSERT INTO student_id (name, roll, class, section, dob, contact, photo)
                 VALUES ('$name', '$roll', '$class', '$section', '$dob', '$contact', '$photo')");
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Add Student</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-lg bg-white p-8 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-700">➕ Add New Student</h2>
        <form method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-medium">Name</label>
                <input name="name" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Roll</label>
                <input name="roll" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Class</label>
                <input name="class" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Section</label>
                <input name="section" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Date of Birth</label>
                <input type="date" name="dob" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Contact</label>
                <input name="contact" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Photo</label>
                <input type="file" name="photo" required class="w-full px-4 py-2 border rounded bg-white file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700" />
            </div>

            <div class="flex justify-between items-center pt-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Save</button>
                <a href="dashboard.php" class="text-sm text-gray-500 hover:underline">← Back to Dashboard</a>
            </div>
        </form>
    </div>
</body>

</html>