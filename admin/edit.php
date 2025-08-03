<?php
require '../config/db.php';
require 'auth.php';

$id = $_GET['id'];
$row = $conn->query("SELECT * FROM student_id WHERE id=$id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $roll = $_POST['roll'];
    $class = $_POST['class'];
    $section = $_POST['section'];
    $dob = $_POST['dob'];
    $contact = $_POST['contact'];

    $photo = $row['photo'];
    if ($_FILES['photo']['name']) {
        $photo = $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/" . $photo);
    }

    $conn->query("UPDATE student_id SET name='$name', roll='$roll', class='$class',
        section='$section', dob='$dob', contact='$contact', photo='$photo' WHERE id=$id");
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

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-lg bg-white p-8 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-700">✏️ Edit Student</h2>
        <form method="post" enctype="multipart/form-data" class="space-y-4">

            <div>
                <label class="block text-sm font-medium">Name</label>
                <input name="name" value="<?= $row['name'] ?>" required
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Roll</label>
                <input name="roll" value="<?= $row['roll'] ?>" required
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Class</label>
                <input name="class" value="<?= $row['class'] ?>" required
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Section</label>
                <input name="section" value="<?= $row['section'] ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Date of Birth</label>
                <input type="date" name="dob" value="<?= $row['dob'] ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Contact</label>
                <input name="contact" value="<?= $row['contact'] ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block text-sm font-medium">Change Photo</label>
                <input type="file" name="photo"
                    class="w-full px-4 py-2 border rounded bg-white file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700" />
                <div class="mt-2">
                    <img src="../uploads/<?= $row['photo'] ?>" width="80" class="rounded border" alt="Student Photo" />
                </div>
            </div>

            <div class="flex justify-between items-center pt-4">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Update</button>
                <a href="dashboard.php" class="text-sm text-gray-500 hover:underline">← Back to Dashboard</a>
            </div>
        </form>
    </div>
</body>

</html>