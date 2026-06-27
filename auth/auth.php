<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// only admins can access admin pages
if ($_SESSION['role'] != 'admin') {
    header("Location: ../pages/home.php");
    exit;
}
