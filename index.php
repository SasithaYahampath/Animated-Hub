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
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>&#x1F3E0;Animated Hub</title>
<link rel="stylesheet" href="css/style.css">
<link href="css/bootstrap-4.4.1.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="css/https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"/>
</head>
<body>
  <style>
    body {
      background: linear-gradient(135deg, #AEEFFF, #ece28a, #90f788);
    }
  </style>
  
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
        <center><h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2</center>
    </div>
  
  <div id="carouselExampleIndicators1" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
      <li data-target="#carouselExampleIndicators1" data-slide-to="0" class="active"></li>
      <li data-target="#carouselExampleIndicators1" data-slide-to="1"></li>
      <li data-target="#carouselExampleIndicators1" data-slide-to="2"></li>
    </ol>
    <div class="carousel-inner" role="listbox">
      <div class="carousel-item active"> 
        <img class="d-block mx-auto" src="images/24.png" height="550px" alt="First slide">
        <div class="carousel-caption">
          <p>
            <button type="button" class="btn btn-lg">
              <?php if(isset($_SESSION['user'])): ?>
                <a href="content.php"><b>Watch Now</b></a>
              <?php else: ?>
                <a href="video.php"><b>Watch Now</b></a>
              <?php endif; ?>
            </button>
          </p>
        </div>
      </div>
      <div class="carousel-item"> 
        <img class="d-block mx-auto" src="images/23.png" height="550px" alt="Second slide">
        <div class="carousel-caption">
          <p>
            <button type="button" class="btn btn-lg">
              <?php if(isset($_SESSION['user'])): ?>
                <a href="content.php"><b>Watch Now</b></a>
              <?php else: ?>
                <a href="video.php"><b>Watch Now</b></a>
              <?php endif; ?>
            </button>
          </p>
        </div>
      </div>
      <div class="carousel-item"> 
        <img class="d-block mx-auto" src="images/22.png" height="550px" alt="Third slide">
        <div class="carousel-caption">
          <p>
            <button type="button" class="btn btn-lg">
              <?php if(isset($_SESSION['user'])): ?>
                <a href="content.php"><b>Watch Now</b></a>
              <?php else: ?>
                <a href="video.php"><b>Watch Now</b></a>
              <?php endif; ?>
            </button>
          </p>
        </div>
      </div>
    </div>
    <a class="carousel-control-prev" href="#carouselExampleIndicators1" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleIndicators1" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>
  </div>
  
  &nbsp;
   
  <!-- linked js files -->
  <script src="js/jquery-3.4.1.min.js" type="text/javascript"></script>
  <script src="js/popper.min.js" type="text/javascript"></script>
  <script src="js/bootstrap-4.4.1.js" type="text/javascript"></script>
</body>
</html>