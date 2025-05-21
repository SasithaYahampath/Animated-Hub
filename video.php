<?php
// Start session if needed (for any PHP functionality you might add later)
session_start();

// Set page title
$pageTitle = "Canvas Video Player";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="css/style.css">
<link href="css/bootstrap-4.4.1.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="css/https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(135deg, #AEEFFF, #ece28a, #90f788);
            
        }
        
        #video-container {
            position: relative;
            margin-bottom: 20px;
margin-left:315px;
        }
        
        #videoCanvas {
            background: #000;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        
        #controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
        }
        
        button {
            padding: 8px 15px;
            background: #4285f4;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        button:hover {
            background: #3367d6;
        }
        
        #volume {
            width: 100px;
        }
    </style>
</head>
<body>
<section id="header">
    <div class="lavender">
      <a href="index.php"><img src="images/2.png" alt="main-logo"></a>
    </div>
    <ul id="navbar">
      <li><a href="index.php" class="active">Home</a></li>
      <li>
        <?php if(isset($_SESSION['user'])): ?>
          <a href="logout.php"><img src="images/logout.png" width="20rem"></a>
        <?php else: ?>
          <a href="logout.php"><img src="images/logout.png" width="20rem"></a>
        <?php endif; ?>
      </li>
    </ul>
  </section>
<div class="container">
&nbsp;


    <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
    
    <div id="video-container">
        <canvas id="videoCanvas" width="640" height="360"></canvas>
        <div id="controls">
            <button id="playPauseBtn">Play</button>
            <input type="range" id="volume" min="0" max="1" step="0.1" value="1">
            <span id="timeDisplay">00:00 / 00:00</span>
        </div>
    </div>
    
    <!-- Hidden video element that will feed the canvas -->
    <video id="sourceVideo" crossorigin="anonymous" style="display: none;">
        <?php
        // You can dynamically set the video source using PHP
        $videoSource = "1234567.mp4";
        ?>
        <source src="<?php echo htmlspecialchars($videoSource); ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    
    <script>
        // Get elements
        const canvas = document.getElementById('videoCanvas');
        const ctx = canvas.getContext('2d');
        const video = document.getElementById('sourceVideo');
        const playPauseBtn = document.getElementById('playPauseBtn');
        const volumeControl = document.getElementById('volume');
        const timeDisplay = document.getElementById('timeDisplay');
        
        // Set canvas size to match video aspect ratio
        function resizeCanvas() {
            const aspectRatio = video.videoWidth / video.videoHeight;
            const maxWidth = window.innerWidth * 0.9;
            const width = Math.min(maxWidth, 640);
            const height = width / aspectRatio;
            
            canvas.width = width;
            canvas.height = height;
        }
        
        // Update canvas with video frames
        function updateCanvas() {
            if (video.paused || video.ended) return;
            
            // Draw video frame to canvas
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // You can add additional canvas drawing here
            // Example: Draw a semi-transparent rectangle
            ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
            ctx.fillRect(20, 20, 200, 50);
            
            // Add text overlay (you could make this dynamic with PHP)
            ctx.fillStyle = 'white';
            ctx.font = '18px Arial';
            ctx.fillText('<?php echo addslashes($pageTitle); ?>', 30, 50);
            
            // Update time display
            updateTimeDisplay();
            
            // Continue animation
            requestAnimationFrame(updateCanvas);
        }
        
        // Format time as MM:SS
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        
        // Update the time display
        function updateTimeDisplay() {
            timeDisplay.textContent = `${formatTime(video.currentTime)} / ${formatTime(video.duration)}`;
        }
        
        // Event listeners
        playPauseBtn.addEventListener('click', function() {
            if (video.paused) {
                video.play();
                playPauseBtn.textContent = 'Pause';
                updateCanvas();
            } else {
                video.pause();
                playPauseBtn.textContent = 'Play';
            }
        });
        
        volumeControl.addEventListener('input', function() {
            video.volume = this.value;
        });
        
        video.addEventListener('loadedmetadata', function() {
            resizeCanvas();
            updateTimeDisplay();
        });
        
        video.addEventListener('timeupdate', updateTimeDisplay);
        
        video.addEventListener('ended', function() {
            playPauseBtn.textContent = 'Play';
        });
        
        // Handle window resize
        window.addEventListener('resize', resizeCanvas);
        
        // Initialize
        video.volume = volumeControl.value;
    </script>
</body>
</html>