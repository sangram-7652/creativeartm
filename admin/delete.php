<?php
require 'auth.php';
require '../config/db.php';

$id = $_GET['id'];
$conn->query("DELETE FROM id_cards WHERE id=$id");
header("Location: dashboard.php");
