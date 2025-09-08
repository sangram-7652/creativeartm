<?php
session_start();
require '../config/db.php';
require 'auth.php';

$schoolFilter = $_GET['school'] ?? '';
$classFilter  = $_GET['class'] ?? '';
$msg = $_GET['msg'] ?? '';

// === Export CSV ===
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $schoolFilter) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_' . $schoolFilter . ($classFilter ? "_$classFilter" : "") . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', "Father's Name", 'Roll No', 'Class', 'Section', 'DOB', 'Contact', 'School', 'Address']);


    $sql = "SELECT * FROM id_cards WHERE school = ?";
    $params = [$schoolFilter];
    $types  = "s";

    if ($classFilter) {
        $sql .= " AND class = ?";
        $params[] = $classFilter;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        fputcsv($output, [
            $row['student_name'],
            $row['father_name'],
            $row['roll_no'],
            $row['class'],
            $row['section'],
            $row['dob'],
            $row['contact_no'],
            $row['school'],
            $row['address']
        ]);
    }
    fclose($output);
    exit;
}

// === Export ZIP ===
if (isset($_GET['export']) && $_GET['export'] === 'zip' && $schoolFilter) {
    $zip = new ZipArchive();
    $filename = "students_{$schoolFilter}" . ($classFilter ? "_$classFilter" : "") . ".zip";

    if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
        exit("Could not create zip archive");
    }

    $csvData = "Serial,Name,Father's Name,Roll No,Class,Section,DOB,Contact,School,Address\n";

    $sql = "SELECT * FROM id_cards WHERE school = ?";
    $params = [$schoolFilter];
    $types  = "s";

    if ($classFilter) {
        $sql .= " AND class = ?";
        $params[] = $classFilter;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $i = 1;
    while ($row = $res->fetch_assoc()) {
        $csvData .= implode(',', [
            $i,
            '"' . $row['student_name'] . '"',
            '"' . $row['father_name'] . '"',
            $row['roll_no'],
            $row['class'],
            $row['section'],
            $row['dob'],
            $row['contact_no'],
            '"' . $row['school'] . '"',
            '"' . $row['address'] . '"'
        ]) . "\n";


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

// === Load school list ===
$schools = $conn->query("SELECT DISTINCT school FROM id_cards");

// === Load class list (depends on selected school) ===
$classes = [];
if ($schoolFilter) {
    $stmt = $conn->prepare("SELECT DISTINCT class FROM id_cards WHERE school = ?");
    $stmt->bind_param("s", $schoolFilter);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $classes[] = $row['class'];
    }
}

// === Load filtered students ===
$sql = "SELECT * FROM id_cards WHERE 1=1";
$params = [];
$types  = "";

if ($schoolFilter) {
    $sql .= " AND school = ?";
    $params[] = $schoolFilter;
    $types .= "s";
}
if ($classFilter) {
    $sql .= " AND class = ?";
    $params[] = $classFilter;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();
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

        <!-- Alert -->
        <?php if ($msg): ?>
            <div class="mb-4 p-3 rounded 
                        <?= strpos($msg, 'deleted') !== false ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' ?>">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <!-- Filter -->
        <form method="GET" class="mb-4 flex items-center gap-4 flex-wrap">
            <!-- School Filter -->
            <label for="school">School:</label>
            <select name="school" id="school" onchange="this.form.submit()" class="border px-3 py-1 rounded">
                <option value="">All Schools</option>
                <?php while ($row = $schools->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['school']) ?>" <?= $schoolFilter === $row['school'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['school']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- Class Filter -->
            <?php if ($schoolFilter): ?>
                <label for="class">Class:</label>
                <select name="class" id="class" onchange="this.form.submit()" class="border px-3 py-1 rounded">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $cls): ?>
                        <option value="<?= htmlspecialchars($cls) ?>" <?= $classFilter === $cls ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cls) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <?php if ($schoolFilter): ?>
                <a href="?school=<?= urlencode($schoolFilter) ?>&class=<?= urlencode($classFilter) ?>&export=csv"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Export CSV</a>
                <a href="?school=<?= urlencode($schoolFilter) ?>&class=<?= urlencode($classFilter) ?>&export=zip"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Export ZIP</a>
                <a href="delete_school.php?school=<?= urlencode($schoolFilter) ?>"
                    onclick="return confirm('⚠️ Are you sure you want to delete ALL students from <?= htmlspecialchars($schoolFilter) ?>? This cannot be undone!')"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Delete All</a>
            <?php endif; ?>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full border">
                <thead class="bg-blue-200 text-left">
                    <tr>
                        <th class="px-4 py-2">Photo</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Father's Name</th>
                        <th class="px-4 py-2">Roll</th>
                        <th class="px-4 py-2">Class</th>
                        <th class="px-4 py-2">Section</th>
                        <th class="px-4 py-2">DOB</th>
                        <th class="px-4 py-2">Contact</th>
                        <th class="px-4 py-2">School</th>
                        <th class="px-4 py-2">Address</th>
                        <th class="px-4 py-2">Action</th>
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
                            <td class="p-2"><?= htmlspecialchars($row['father_name']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['roll_no']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['class']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['section']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['dob']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['contact_no']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['school']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($row['address']) ?></td>
                            <td>
                                <a href="delete.php?id=<?= $row['id'] ?>"
                                    onclick="return confirm('Delete this student?')"
                                    class="text-red-400 font-bold">delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>