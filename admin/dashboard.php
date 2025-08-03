<?php
session_start();
require '../config/db.php';
require 'auth.php';

$schoolFilter = $_GET['school'] ?? '';

// === Export CSV ===
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $schoolFilter) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_' . $schoolFilter . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Roll No', 'Class', 'Section', 'DOB', 'Contact', 'School']);

    $stmt = $conn->prepare("SELECT * FROM id_cards WHERE school = ?");
    $stmt->bind_param("s", $schoolFilter);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        fputcsv($output, [
            $row['student_name'],
            $row['roll_no'],
            $row['class'],
            $row['section'],
            $row['dob'],
            $row['contact_no'],
            $row['school']
        ]);
    }
    fclose($output);
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'zip' && $schoolFilter) {
    $zip = new ZipArchive();
    $filename = "students_{$schoolFilter}.zip";

    if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
        exit("Could not create zip archive");
    }

    // CSV header
    $csvData = "Serial,Name,Roll No,Class,Section,DOB,Contact,School\n";

    $stmt = $conn->prepare("SELECT * FROM id_cards WHERE school = ?");
    $stmt->bind_param("s", $schoolFilter);
    $stmt->execute();
    $res = $stmt->get_result();

    $i = 1;
    while ($row = $res->fetch_assoc()) {
        // Add to CSV
        $csvData .= implode(',', [
            $i,
            '"' . $row['student_name'] . '"',
            $row['roll_no'],
            $row['class'],
            $row['section'],
            $row['dob'],
            $row['contact_no'],
            '"' . $row['school'] . '"'
        ]) . "\n";

        // Add photo as 1.jpg, 2.jpg, ...
        if (!empty($row['photo']) && file_exists("../uploads/{$row['photo']}")) {
            $ext = pathinfo($row['photo'], PATHINFO_EXTENSION);
            $zip->addFile("../uploads/{$row['photo']}", "photos/{$i}.$ext");
        }

        $i++;
    }

    $zip->addFromString("students.csv", $csvData);
    $zip->close();

    header('Content-Type: application/zip');
    header("Content-Disposition: attachment; filename=$filename");
    header('Content-Length: ' . filesize($filename));
    readfile($filename);
    unlink($filename);
    exit;
}

// === Export ZIP (CSV + photos) ===
// if (isset($_GET['export']) && $_GET['export'] === 'zip' && $schoolFilter) {
//     $zip = new ZipArchive();
//     $filename = "students_{$schoolFilter}.zip";

//     if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
//         exit("Could not create zip archive");
//     }

//     // Build CSV data
//     $csvData = "Name,Roll No,Class,Section,DOB,Contact,School\n";
//     $stmt = $conn->prepare("SELECT * FROM id_cards WHERE school = ?");
//     $stmt->bind_param("s", $schoolFilter);
//     $stmt->execute();
//     $res = $stmt->get_result();

//     while ($row = $res->fetch_assoc()) {
//         $csvData .= '"' . implode('","', [
//             $row['student_name'],
//             $row['roll_no'],
//             $row['class'],
//             $row['section'],
//             $row['dob'],
//             $row['contact_no'],
//             $row['school']
//         ]) . "\"\n";

//         if (!empty($row['photo']) && file_exists("../uploads/{$row['photo']}")) {
//             $zip->addFile("../uploads/{$row['photo']}", "photos/{$row['photo']}");
//         }
//     }

//     $zip->addFromString("students.csv", $csvData);
//     $zip->close();

//     header('Content-Type: application/zip');
//     header("Content-Disposition: attachment; filename=$filename");
//     header('Content-Length: ' . filesize($filename));
//     readfile($filename);
//     unlink($filename);
//     exit;
// }

// === Load school list ===
$schools = $conn->query("SELECT DISTINCT school FROM id_cards");

// === Load filtered students ===
$studentsQuery = "SELECT * FROM id_cards";
if ($schoolFilter) {
    $stmt = $conn->prepare("SELECT * FROM id_cards WHERE school = ?");
    $stmt->bind_param("s", $schoolFilter);
    $stmt->execute();
    $students = $stmt->get_result();
} else {
    $students = $conn->query($studentsQuery);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto bg-white shadow-md rounded-lg p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold mb-4">Admin Dashboard - Students</h1>
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
        </div>

        <!-- Filter -->
        <form method="GET" class="mb-4 flex items-center gap-4">
            <label for="school">Filter by School:</label>
            <select name="school" id="school" onchange="this.form.submit()" class="border px-3 py-1 rounded">
                <option value="">All Schools</option>
                <?php while ($row = $schools->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['school']) ?>" <?= $schoolFilter === $row['school'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['school']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <?php if ($schoolFilter): ?>
                <a href="?school=<?= urlencode($schoolFilter) ?>&export=csv" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Export CSV</a>
                <a href="?school=<?= urlencode($schoolFilter) ?>&export=zip" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Export ZIP</a>
            <?php endif; ?>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full border">
                <thead class="bg-blue-200 text-left">
                    <tr>
                        <th class="px-4 py-2">Photo</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Roll</th>
                        <th class="px-4 py-2">Class</th>
                        <th class="px-4 py-2">Section</th>
                        <th class="px-4 py-2">DOB</th>
                        <th class="px-4 py-2">Contact</th>
                        <th class="px-4 py-2">School</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <tr class="border-t">
                            <td>
                                <?php if (!empty($row['photo'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Photo" class="w-12 h-12 object-cover mx-auto rounded-full" />
                                <?php else: ?>
                                    <span class="text-gray-400">No Photo</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-2"><?= htmlspecialchars($row['student_name']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['roll_no']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['class']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['section']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['dob']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['contact_no']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['school']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>