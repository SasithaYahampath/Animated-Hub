<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

function redirect($url) {
    header("Location: $url");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Get filename before deleting
        $stmt = $conn->prepare("SELECT filename FROM videos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $video = $result->fetch_assoc();
        $stmt->close();
        
        if ($video && isset($video['filename'])) {
            // Delete file
            $filepath = "uploads/" . $video['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            // Delete from database
            $delete_stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
            $delete_stmt->bind_param("i", $id);
            $delete_stmt->execute();
            $delete_stmt->close();
            
            $_SESSION['success'] = 'Video deleted successfully!';
        } else {
            $_SESSION['error'] = 'Video not found!';
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }
}

redirect('modify.php');
?>