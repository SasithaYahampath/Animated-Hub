<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: admin_users.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Prevent deleting current user
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account!";
    header('Location: admin_users.php');
    exit();
}

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "User deleted successfully!";
} else {
    $_SESSION['error'] = "Error deleting user: " . $conn->error;
}
$stmt->close();

header('Location: admin_users.php');
exit();
?>