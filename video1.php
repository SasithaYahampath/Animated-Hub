<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Function to generate random color for fallback thumbnails
function getRandomColor() {
    $colors = ['#4a6fa5', '#166088', '#4fc3f7', '#90f788', '#f7c548', 
               '#f78868', '#a56fa5', '#68a56f', '#6888a5', '#a58868'];
    return $colors[array_rand($colors)];
}

// Fetch videos with proper ORDER BY syntax
$videos = [];
$result = $conn->query("SELECT * FROM videos ORDER BY upload_date DESC");  // Fixed column name
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎬 Animated Hub - Video Gallery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4a6fa5;
            --secondary: #166088;
            --accent: #4fc3f7;
            --light: #f8f9fa;
            --dark: #343a40;
            --gradient: linear-gradient(135deg, #AEEFFF, #ece28a, #90f788);
            --footer-gradient: linear-gradient(135deg, #8FBEC9, #C9C9C9, #F2E6AC);
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
        
        .admin-badge {
            background-color: var(--primary);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            margin-left: 5px;
        }
        
        .main-content {
            flex: 1;
            padding: 30px 20px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--dark);
            font-size: 2.2rem;
            position: relative;
            padding-bottom: 15px;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 3px;
            background: var(--primary);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .filter-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .category-filter {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .category-filter a {
            padding: 6px 12px;
            border-radius: 20px;
            background: white;
            color: var(--dark);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            border: 1px solid #ddd;
        }
        
        .category-filter a:hover, .category-filter a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .upload-btn {
            padding: 8px 16px;
            background: var(--primary);
            color: white;
            border-radius: 4px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .upload-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .video-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 25px; 
            padding: 0 20px;
        }
        
        .video-card { 
            background: white;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .video-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .video-thumbnail {
            position: relative;
            width: 100%;
            height: 160px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .video-card:hover .video-thumbnail img {
            transform: scale(1.05);
        }
        
        .thumbnail-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
        }
        
        .play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            opacity: 0.8;
            transition: all 0.3s ease;
        }
        
        .video-card:hover .play-icon {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.1);
        }
        
        .video-info {
            padding: 15px;
        }
        
        .video-title {
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 600;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3.2em;
        }
        
        .video-category {
            display: inline-block;
            padding: 2px 8px;
            background: #f0f0f0;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-bottom: 8px;
            color: #666;
        }
        
        .video-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 0.9rem;
        }
        
        .admin-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            z-index: 2;
        }
        
        .admin-actions a {
            width: 30px;
            height: 30px;
            background: rgba(0,0,0,0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .admin-actions a:hover {
            background: var(--primary);
            transform: scale(1.1);
        }
        
        .no-videos {
            text-align: center;
            grid-column: 1 / -1;
            padding: 50px;
            color: #666;
        }
        
        footer {
            background: var(--footer-gradient);
            color: var(--dark);
            padding: 50px 0 0;
            margin-top: 50px;
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
            background: var(--primary);
            bottom: 0;
            left: 0;
        }
        
        .footer-section p, 
        .footer-section ul {
            color: #555;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 10px;
        }
        
        .footer-section ul li a {
            color: #555;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .footer-section ul li a:hover {
            color: var(--primary);
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
            background: rgba(255,255,255,0.5);
            border-radius: 50%;
            color: var(--dark);
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            text-align: center;
            padding: 20px;
            background: rgba(0,0,0,0.1);
            margin-top: 50px;
            color: #555;
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
            }
            
            #navbar {
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }
            
            .filter-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .video-grid {
                grid-template-columns: 1fr;
                padding: 0 10px;
            }
            
            .footer-container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .footer-section h3::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .social-links {
                justify-content: center;
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
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li class="user-info">
                    <img src="images/user.png" width="20" alt="User">
                    <span><?= htmlspecialchars($user['username']) ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <span class="admin-badge">ADMIN</span>
                    <?php endif; ?>
                    </span>
                </li>
                <li>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </header>
    
    <main class="main-content">
        <h1 class="page-title">Video Gallery</h1>
        
        <div class="filter-controls">
            <?php if ($user['role'] === 'admin'): ?>
                <a href="upload.php" class="upload-btn">
                    <i class="fas fa-upload"></i> Upload Video
                </a>
            <?php endif; ?>
        </div>
        
        <div class="video-grid">
            <?php if (empty($videos)): ?>
                <div class="no-videos">
                    <i class="fas fa-video-slash" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <h3>No videos found</h3>
                    <p>Check back later for new content</p>
                </div>
            <?php else: ?>
                <?php foreach ($videos as $video): ?>
                <div class="video-card">
                    <?php if ($user['role'] === 'admin'): ?>
                        <div class="admin-actions">
                            <a href="edit_video.php?id=<?= $video['id'] ?>" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete_video.php?id=<?= $video['id'] ?>" title="Delete" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <a href="view.php?id=<?= $video['id'] ?>">
                        <div class="video-thumbnail">
                            <?php 
                            $thumbnailPath = 'uploads/thumbnails/' . ($video['thumbnail'] ?? '');
                            $fallbackInitial = strtoupper(substr($video['title'], 0, 1));
                            
                            if (!empty($video['thumbnail']) && file_exists($thumbnailPath)): ?>
                                <img src="<?= $thumbnailPath ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                            <?php else: ?>
                                <div class="thumbnail-fallback" style="background-color: <?= getRandomColor() ?>;">
                                    <?= $fallbackInitial ?>
                                </div>
                            <?php endif; ?>
                            <div class="play-icon"><i class="fas fa-play"></i></div>
                        </div>
                    </a>
                    
                    <div class="video-info">
                        <h3 class="video-title"><?= htmlspecialchars($video['title']) ?></h3>
                        <div class="video-meta">
                            <span><i class="fas fa-eye"></i> <?= number_format($video['views']) ?></span>
                            <span><i class="fas fa-calendar-alt"></i> <?= date('M j, Y', strtotime($video['upload_date'])) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
                    <li><a href="#"><i class="fas fa-envelope"></i> Contact</a></li>
                    <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="#"><i class="fas fa-file-alt"></i> Terms</a></li>
                    <li><a href="#"><i class="fas fa-lock"></i> Privacy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul>
                    <li><a href="mailto:animatedHub@gmail.com"><i class="fas fa-envelope"></i> animatedHub@gmail.com</a></li>
                    <li><a href="tel:+94771234567"><i class="fas fa-phone"></i> +94 77 123 4567</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Beruwala, Sri Lanka</a></li>
                </ul>
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

    <script>
        // Simple animation for video cards when they come into view
        document.addEventListener('DOMContentLoaded', function() {
            const videoCards = document.querySelectorAll('.video-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            videoCards.forEach(card => {
                card.style.opacity = 0;
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>