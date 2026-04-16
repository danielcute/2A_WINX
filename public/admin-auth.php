<?php
session_start();

$valid_username = 'admin';
$valid_password = 'sinta2024';  // Change this to a strong password

if ($_POST['username'] === $valid_username && $_POST['password'] === $valid_password) {
    $_SESSION['admin_logged_in'] = true;
    header('Location: admin-dashboard.php');
} else {
    header('Location: admin-login.php?error=1');
}
exit;