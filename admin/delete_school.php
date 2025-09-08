<?php
require 'auth.php';
require '../config/db.php';

$school = $_GET['school'] ?? '';

if ($school) {
    // Fetch all photos for that school
    $stmt = $conn->prepare("SELECT photo FROM id_cards WHERE school = ?");
    $stmt->bind_param("s", $school);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        if (!empty($row['photo'])) {
            $photoPath = "../uploads/" . $row['photo'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }
    }

    // Delete all records for that school
    $stmt = $conn->prepare("DELETE FROM id_cards WHERE school = ?");
    $stmt->bind_param("s", $school);
    $stmt->execute();

    header("Location: dashboard.php?msg=All+records+deleted+for+" . urlencode($school));
    exit;
} else {
    header("Location: dashboard.php?msg=Invalid+school+delete+request");
    exit;
}
