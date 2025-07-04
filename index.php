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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎬 Animated Hub - Home</title>
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
        
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/23.png');
            background-size: cover;
            background-position: center;
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 20px;
        }
        
        .hero-content {
            max-width: 800px;
        }
        
        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .hero-content h3 {
            font-size: 1.5rem;
            margin-bottom: 30px;
            font-weight: 400;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 30px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn:hover {
            background: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn i {
            margin-right: 10px;
        }
        
        footer {
            background: var(--footer-gradient);
            color: var(--dark);
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
            
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-content h3 {
                font-size: 1.2rem;
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
    
    <section class="hero-section">
        <div class="hero-content">
            <h1>Welcome, <?= htmlspecialchars($user['username']) ?>!</h1>
            <h3>Discover Amazing Animated Content</h3>
            <a href="video1.php" class="btn"><i class="fas fa-play"></i> Watch Now</a>
        </div>
    </section>
    
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Welcome to Animated Hub! Your premier destination for high-quality animated content.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-envelope"></i> Contact</a></li>
                    <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="#"><i class="fas fa-file-alt"></i> Terms of Service</a></li>
                    <li><a href="#"><i class="fas fa-lock"></i> Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul>
                    <li><a href="mailto:animatedHub@gmail.com"><i class="fas fa-envelope"></i> animatedHub@gmail.com</a></li>
                    <li><a href="tel:+94771234567"><i class="fas fa-phone"></i> +94 77 123 4567</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> 12/B, Main Road, Beruwala</a></li>
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
</body>
</html>