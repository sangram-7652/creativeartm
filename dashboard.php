<?php
session_start();
require 'config/db.php';
require 'auth.php';
$schoolName = $_SESSION['school'];

// Delete logic
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM id_cards WHERE id=$id AND school='" . $conn->real_escape_string($schoolName) . "'");
    header("Location: dashboard.php");
    exit;
}

// Filter logic
$classFilter = $_GET['class'] ?? '';
$sql = "SELECT * FROM id_cards WHERE school='" . $conn->real_escape_string($schoolName) . "'";
if ($classFilter) {
    $sql .= " AND class='" . $conn->real_escape_string($classFilter) . "'";
}
$result = $conn->query($sql);

// Get distinct classes for filter dropdown (only for that school)
$classResult = $conn->query("SELECT DISTINCT class FROM id_cards WHERE school='" . $conn->real_escape_string($schoolName) . "'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">
    <div class="w-full mx-auto bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Students of <?= htmlspecialchars($schoolName) ?></h2>

        <!-- Filter -->
        <form method="GET" class="mb-4">
            <label for="class" class="mr-2">Filter by Class:</label>
            <select name="class" onchange="this.form.submit()" class="border px-2 py-1">
                <option value="">All Classes</option>
                <?php while ($row = $classResult->fetch_assoc()): ?>
                    <option value="<?= $row['class'] ?>" <?= $row['class'] == $classFilter ? 'selected' : '' ?>>
                        <?= $row['class'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <!-- Table -->
        <table class="table-auto w-full border">
            <thead>
                <tr class="bg-blue-200 text-left">
                    <th class="px-4 py-2">Photo</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Roll</th>
                    <th class="px-4 py-2">Class</th>
                    <th class="px-4 py-2">Section</th>
                    <th class="px-4 py-2">DOB</th>
                    <th class="px-4 py-2">Contact</th>
                    <th class="px-4 py-2">Address</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="text-center border-t">
                            <td>
                                <?php if (!empty($row['photo'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Photo" class="w-12 h-12 object-cover mx-auto rounded-full" />
                                <?php else: ?>
                                    <span class="text-gray-400">No Photo</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['roll_no']) ?></td>
                            <td><?= htmlspecialchars($row['class']) ?></td>
                            <td><?= htmlspecialchars($row['section']) ?></td>
                            <td><?= htmlspecialchars($row['dob']) ?></td>
                            <td><?= htmlspecialchars($row['contact_no']) ?></td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-500">Edit</a> |
                                <a href="dashboard.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this student?')" class="text-red-500">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500">No students found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>