<?php
if (!isset($_SESSION['school'])) {
    header('Location: login.php');
    exit;
}
