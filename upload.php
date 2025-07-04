<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $filename = $_FILES['filename']['name'] ?? '';
    $tmp_name = $_FILES['filename']['tmp_name'] ?? '';
    $file_size = $_FILES['filename']['size'] ?? 0;

    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    if (empty($filename)) {
        $errors[] = 'File is required';
    }

    // Process upload if no errors
    if (empty($errors)) {
        $upload_dir = 'uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                $errors[] = 'Failed to create upload directory';
            }
        }

        // Sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $filename);
        $target_file = $upload_dir . basename($filename);
        
        // Verify directory is writable
        if (!is_writable($upload_dir)) {
            $errors[] = 'Upload directory is not writable';
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            $errors[] = 'File already exists';
        }
        
        // Check file size (50MB max)
        if ($file_size > 50000000) {
            $errors[] = 'File is too large (max 50MB)';
        }
        
        // Allow certain file formats
        $allowed_types = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'Only video files are allowed (MP4, WebM, OGG, MOV, AVI)';
        }

        if (empty($errors)) {
            if (move_uploaded_file($tmp_name, $target_file)) {
                $stmt = $conn->prepare("INSERT INTO videos (title, filename) VALUES (?, ? )");
                $stmt->bind_param("ss", $title, $filename);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Video uploaded successfully!';
                    header('Location: modify.php');
                    exit();
                } else {
                    $errors[] = 'Database error: ' . $stmt->error;
                    // Delete the uploaded file if DB insert failed
                    if (file_exists($target_file)) {
                        unlink($target_file);
                    }
                }
                $stmt->close();
            } else {
                $errors[] = 'There was an error uploading your file';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🎬 Animated Hub - Upload Video</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    :root {
        --primary: #4a6fa5;
        --secondary: #166088;
        --accent: #4fc3f7;
        --light: #f8f9fa;
        --dark: #343a40;
        --success: #28a745;
        --danger: #dc3545;
    }
    
    body {
        background: linear-gradient(135deg, #e6f7ff, #f0f0f0);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }
    
    #header {
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 15px 0;
    }
    
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    #navbar {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        align-items: center;
    }
    
    #navbar li {
        margin-left: 20px;
        display: flex;
        align-items: center;
    }
    
    #navbar a {
        color: var(--dark);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s;
    }
    
    #navbar a:hover {
        color: var(--primary);
    }
    
    #navbar a.active {
        color: var(--primary);
    }
    
    .user-info {
        display: flex;
        align-items: center;
    }
    
    .user-info img {
        margin-right: 8px;
    }
    
    .container {
        max-width: 800px;
        margin: 30px auto;
        padding: 30px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    h1 {
        color: var(--primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    h1 i {
        font-size: 1.2em;
    }
    
    .nav-links {
        margin-bottom: 25px;
    }
    
    .nav-links a {
        display: inline-flex;
        align-items: center;
        padding: 8px 15px;
        margin-right: 10px;
        background: var(--primary);
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s;
    }
    
    .nav-links a:hover {
        background: var(--secondary);
        transform: translateY(-2px);
    }
    
    .nav-links a i {
        margin-right: 8px;
    }
    
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    
    .alert-success {
        background: #e8f5e9;
        color: var(--success);
        border-left: 4px solid var(--success);
    }
    
    .alert-error {
        background: #fdecea;
        color: var(--danger);
        border-left: 4px solid var(--danger);
    }
    
    .upload-form {
        margin-top: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--dark);
    }
    
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 195, 247, 0.2);
    }
    
    .file-upload {
        border: 2px dashed #ddd;
        padding: 20px;
        text-align: center;
        border-radius: 5px;
        background: #f9f9f9;
        transition: all 0.3s;
    }
    
    .file-upload:hover {
        border-color: var(--accent);
        background: #f1f9fe;
    }
    
    .file-requirements {
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn i {
        margin-right: 8px;
    }
    
    .btn:hover {
        background: var(--secondary);
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .container {
            padding: 20px;
            margin: 15px;
        }
        
        #navbar {
            flex-direction: column;
            align-items: flex-end;
        }
        
        #navbar li {
            margin: 5px 0;
        }
    }
</style>
</head>
<body>
  
  <section id="header">
    <div class="header-container">
        <div class="logo">
            <a href="dashboard.php"><img src="images/2.png" alt="main-logo" height="40"></a>
        </div>
        <ul id="navbar">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li class="user-info">
                <img src="images/user.png" width="20" alt="User">
                <span><?php echo htmlspecialchars($user['username']); ?></span>
            </li>
            <li>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
  </section>
  
  <div class="container">
    <h1><i class="fas fa-upload"></i> Upload Video</h1>
    
    <div class="nav-links">
        <a href="modify.php"><i class="fas fa-arrow-left"></i> Back to Videos</a>
        <a href="index.php"><i class="fas fa-eye"></i> View Site</a>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <h3><i class="fas fa-exclamation-triangle"></i> Errors occurred:</h3>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form class="upload-form" action="upload.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Video Title *</label>
            <input type="text" id="title" name="title" class="form-control" required 
                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="filename">Video File *</label>
            <div class="file-upload">
                <input type="file" id="filename" name="filename" accept="video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo" required>
            </div>
            <div class="file-requirements">
                Supported formats: MP4, WebM, OGG, MOV, AVI | Max size: 50MB
            </div>
        </div>
        
        <button type="submit" class="btn">
            <i class="fas fa-upload"></i> Upload Video
        </button>
    </form>
  </div>
</body>
</html>