<?php
require 'config.php';
session_start();

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }

    // Check credentials
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            header('Location: index.php');
            exit();
        } else {
            $errors['login'] = 'Invalid email or password';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <title>LogIn</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"/>
  <link href="css/bootstrap-4.4.1.css" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <style>
        body {
            background: linear-gradient(135deg, #AEEFFF, #ece28a, #90f788);
        }
    </style>
    <!-- start header section -->
    <section id="header">
        <div class="lavender">
            <a href="index.php"><img src="images/2.png" alt="main-logo"></a>
            
        </div>
        <!-------------navbar---------------->
        <div>
            <ul id="navbar">
                <li><a href="index.php">Home</a></li>
                <li><a href="#" class="active"><img src="images/user.png" width="20rem"></a></li>
            </ul> 
        </div>
    </section>
    <!-- end navbar section -->
    
    <h1>&nbsp;</h1><!--line break -->
    <center>
    
    <!-- start wrapper section -->
    <div class="wrapper">

        <div class="form-box login">
            <h2>Sign In</h2>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <label>Email</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox" name="remember">Remember me</label>
                    <a href="#">Forgot Password?</a>
                </div>
                <button type="submit" name="login" class="btn1">Sign In</button>
                <div class="login-register">
                    <p>Don't have an account? <a href="register.php" class="register-link">Sign Up</a></p>
                </div>
            </form>
        </div>
 
    </div>
<h1>&nbsp;</h1><!-- line break -->
    
    <!-- linked js files -->
    <script src="js/login.js"></script>
    <script src="js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap-4.4.1.js"></script>
    <!-- Ion Icons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    </html>