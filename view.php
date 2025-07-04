<?php
session_start();
require 'config.php';

function redirect($url) {
    header("Location: $url");
    exit();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Check video ID parameter
if (!isset($_GET['id'])) {
    redirect('video1.php');
}

$id = (int)$_GET['id'];

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get video details
$stmt = $conn->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$video = $result->fetch_assoc();
$stmt->close();

if (!$video) {
    redirect('index.php');
}

// Increment view count
$update_stmt = $conn->prepare("UPDATE videos SET views = views + 1 WHERE id = ?");
$update_stmt->bind_param("i", $id);
$update_stmt->execute();
$update_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($video['title']) ?> | Animated Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4a6fa5;
            --secondary: #166088;
            --accent: #4fc3f7;
            --light: #f8f9fa;
            --dark: #343a40;
            --gradient: linear-gradient(135deg, #AEEFFF, #ece28a, #90f788);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--gradient);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        #header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo img {
            height: 40px;
        }
        
        #navbar {
            display: flex;
            list-style: none;
            align-items: center;
            gap: 20px;
        }
        
        #navbar a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
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
            gap: 8px;
        }
        
        .main-content {
            flex: 1;
            padding: 30px 20px;
        }
        
        .video-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            padding: 20px;
        }
        
        .video-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .back-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .video-title {
            color: var(--dark);
            margin-bottom: 15px;
        }
        
        video {
            width: 100%;
            border-radius: 8px;
            background: #000;
            aspect-ratio: 16/9;
        }
        
        .video-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }
        
        footer {
            background: var(--dark);
            color: white;
            padding: 50px 0 0;
            margin-top: auto;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-section h3 {
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-section h3::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 2px;
            background: var(--accent);
            bottom: 0;
            left: 0;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 10px;
        }
        
        .footer-section ul li a {
            color: #ddd;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .footer-section ul li a:hover {
            color: var(--accent);
            padding-left: 5px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: var(--accent);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            text-align: center;
            padding: 20px;
            background: rgba(0,0,0,0.2);
            margin-top: 50px;
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
            }
            
            #navbar {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .video-container {
                padding: 15px;
            }
            
            .video-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .footer-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header id="header">
        <div class="header-container">
            <div class="logo">
                <a href="index.php"><img src="images/2.png" alt="Animated Hub Logo"></a>
            </div>
            <ul id="navbar">
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
                <li class="user-info">
                    <img src="images/user.png" width="20" alt="User">
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </li>
                <li>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </header>
    
    <main class="main-content">
        <div class="video-container">
            <div class="video-header">
                <a href="video1.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Videos</a>
            </div>
            
            <h1 class="video-title"><?= htmlspecialchars($video['title']) ?></h1>
            
            <video controls controlsList="nodownload">
                <source src="uploads/<?= htmlspecialchars($video['filename']) ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            
            <div class="video-meta">
                <span><i class="fas fa-eye"></i> <?= number_format($video['views'] + 1) ?> views</span>
                <span><i class="fas fa-calendar-alt"></i> <?= date('F j, Y', strtotime($video['upload_date'])) ?></span>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Welcome to Animated Hub! Your premier destination for animated content.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Contact</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p><i class="fas fa-envelope"></i> animatedHub@gmail.com</p>
                <p><i class="fas fa-phone"></i> +94 77 123 4567</p>
                <p><i class="fas fa-map-marker-alt"></i> 12/B, Main Road, Beruwala</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Animated Hub. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>