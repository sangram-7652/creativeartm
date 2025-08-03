<?php
require 'config/db.php';
require 'auth.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die('Invalid ID');
}

// Get photo filename before deleting
$result = $conn->query("SELECT photo FROM id_cards WHERE id = $id");
$student = $result->fetch_assoc();

if (!$student) {
    die('Student not found');
}

// Delete photo file if exists
if (!empty($student['photo']) && file_exists('uploads/' . $student['photo'])) {
    unlink('uploads/' . $student['photo']);
}

// Delete student record
$conn->query("DELETE FROM id_cards WHERE id = $id");

header("Location: dashboard.php");
exit;
