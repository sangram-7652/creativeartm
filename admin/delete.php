<?php
require 'auth.php';
require '../config/db.php';

$id = $_GET['id'];
$conn->query("DELETE FROM student_id WHERE id=$id");
header("Location: dashboard.php");
?>
