<?php
session_start(); // ensure session started
$valid_username = 'admin';
$hashed_password = password_hash('sinta2024', PASSWORD_DEFAULT); // store this once

if ($_POST['username'] === $valid_username && password_verify($_POST['password'], $hashed_password)) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['user_name'] = 'Administrator';
    header('Location: admin-dashboard.php');
} else {
    header('Location: admin-login.php?error=1');
}
exit;