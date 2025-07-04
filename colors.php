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
$result = $conn->query("SELECT COUNT(*) AS total_users FROM users WHERE role = 'user'");
$row = $result->fetch_assoc();
$stmt->close();
?>
<?php

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bgcolor = trim($_POST['bg_color']);
    $textcolor = trim($_POST['text_color']);
    $accentcolor = trim($_POST['accent_color']);

    // Validation
    if (empty($bgcolor)) {
        $errors['bg_color'] = 'Blackground Color is required';
    }
    if (empty($textcolor)) {
        $errors['text_color'] = 'Text Color is required';
    }
    if (empty($accentcolor)) {
        $errors['accent_color'] = 'Accent Color is required';
    }

    // adding color
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET bg_color = ?, text_color = ?, accent_color = ? WHERE id = ? ");
        $stmt->bind_param("sssi", $bgcolor, $textcolor, $accentcolor, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['bg_color'] = $bgcolor;
            $_SESSION['text_color'] = $textcolor;
            $_SESSION['accent_color'] = $accentcolor;
            header('Location: colors.php');
            exit();
        } else {
            $errors['database'] = 'failed: ' . $stmt->error;
        }
        $stmt->close();
    }
}?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>&#x1F3E0;Animated Hub-Admin Panel</title>
<link rel="stylesheet" href="css/style.css">
<link href="css/bootstrap-4.4.1.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="css/https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css" rel="stylesheet">
<style>
    body {
      background: linear-gradient(135deg, #AEEFFF, #ece28a, #90f788);
    }
    .color-preview {
            width: 100%;
            height: 150px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 15px;
        }

  </style>

</head>
<body>
  
  <section id="header">
    <div class="lavender">
      <a href="super_dashboard.php"><img src="images/2.png" alt="main-logo"></a>
    </div>
    <ul id="navbar">
      <li><a href="super_dashboard.php" class="active">Dashboard</a></li>
      <li><h6><img src="images/user.png" width="20rem"><?php echo htmlspecialchars($user['username']); ?><h6></li>
      <li>
        <?php if(isset($_SESSION['user'])): ?>
          <a href="logout.php"><img src="images/logout.png" width="20rem"></a>
        <?php else: ?>
          <a href="logout.php"><img src="images/logout.png" width="20rem"></a>
        <?php endif; ?>
      </li>
    </ul>
  </section>
  <div class="container py-5">
        <h2>User Color Settings for <?php echo htmlspecialchars($user['username']); ?></h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <form method="POST" action="colors.php">
            <input type="hidden" name="user_id" value="<?= $userId ?>">
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Background Color</label>
                    <input type="text" class="form-control color-picker" name="bg_color" value="<?php echo htmlspecialchars($bgcolor); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Text Color</label>
                    <input type="text" class="form-control color-picker" name="text_color" value="<?php echo htmlspecialchars($textcolor); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Accent Color</label>
                    <input type="text" class="form-control color-picker" name="accent_color" value="<?php echo htmlspecialchars($accentcolor); ?>" required>
                </div>
            </div>
            
            <div class="color-preview mb-4" style="background-color:<?php echo htmlspecialchars($bgcolor); ?>; color: <?php echo htmlspecialchars($textcolor); ?>">
                <h4 style="color: <?php echo htmlspecialchars($accentcolor); ?>">Preview</h4>
                <p>This is how text will appear with these colors.</p>
                <button class="btn" style="background-color: <?php echo htmlspecialchars($accentcolor); ?>; color: white">Sample Button</button>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Colors</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".color-picker").spectrum({
                preferredFormat: "hex",
                showInput: true,
                allowEmpty: false
            });
            
            // Update preview when colors change
            $(".color-picker").on('change', function() {
                $(".color-preview").css({
                    'background-color': $("input[name='bg_color']").val(),
                    'color': $("input[name='text_color']").val()
                });
                $(".color-preview h4").css('color', $("input[name='accent_color']").val());
                $(".color-preview .btn").css('background-color', $("input[name='accent_color']").val());
            });
        });
    </script>
    </body>
    </html>