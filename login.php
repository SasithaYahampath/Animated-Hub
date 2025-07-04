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
        $stmt = $conn->prepare("SELECT id, username, role, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            if($user['role'] == 'admin'){
                header('Location: dashboard.php');
                exit();
            }
            elseif($user['role'] == 'superadmin'){
                header('Location: super_dashboard.php');
                exit();
            }else{
                header('location: index.php');
            }
        } else {
            $errors['login'] = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Animated Hub</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #6c63ff;
      --secondary-color: #4a42d3;
      --accent-color: #ff6584;
      --light-bg: #f8f9fa;
    }

    body {
      background: linear-gradient(135deg, #e0f7fa, #b2ebf2, #80deea);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .navbar {
      background-color: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .login-container {
      max-width: 500px;
      margin: 2rem auto;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    .login-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 2rem;
      text-align: center;
    }

    .login-header img {
      width: 80px;
      margin-bottom: 1rem;
    }

    .login-header h2 {
      font-weight: 600;
      margin: 0;
    }

    .login-body {
      padding: 2rem;
    }

    .form-control {
      border-radius: 8px;
      padding: 12px 15px;
      border: 1px solid #e0e0e0;
      transition: all 0.3s;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(108, 99, 255, 0.25);
    }

    .input-group-text {
      background-color: transparent;
      border-right: none;
    }

    .form-floating>label {
      padding: 0.8rem 0.75rem;
    }

    .btn-login {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border: none;
      padding: 12px;
      font-weight: 600;
      width: 100%;
      border-radius: 8px;
      transition: all 0.3s;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
    }

    .alert-danger {
      background-color: #ffebee;
      color: #c62828;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
    }

    .remember-forgot {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 15px 0;
    }

    .login-footer {
      text-align: center;
      padding-top: 1.5rem;
      border-top: 1px solid #eee;
      margin-top: 1.5rem;
    }

    .login-footer a {
      color: var(--primary-color);
      font-weight: 500;
      text-decoration: none;
    }

    @media (max-width: 576px) {
      .login-container {
        margin: 1rem;
      }
      
      .login-header {
        padding: 1.5rem;
      }
      
      .login-body {
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>

<!-- Header/Navigation -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="images/2.png" alt="Animated Hub" height="40">
    </a>
    <div class="d-flex align-items-center">
      <a href="register.php" class="btn btn-outline-primary me-2">Sign Up</a>
      <a href="index.php" class="btn btn-link">Home</a>
    </div>
  </div>
</nav>

<!-- Main Login Content -->
<div class="container py-5">
  <div class="login-container">
    <div class="login-header">
      <img src="images/2.png" alt="Logo">
      <h2>Welcome Back</h2>
    </div>
    
    <div class="login-body">
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $error): ?>
            <p class="mb-1"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="email" name="email" 
                 placeholder="name@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
          <label for="email"><i class="fas fa-envelope me-2"></i>Email address</label>
        </div>
        
        <div class="form-floating mb-3">
          <input type="password" class="form-control" id="password" name="password" 
                 placeholder="Password" required>
          <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
        </div>
        
        <div class="remember-forgot mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
          
        </div>
        
        <button type="submit" name="login" class="btn btn-primary btn-login mb-3">
          <i class="fas fa-sign-in-alt me-2"></i> Sign In
        </button>
        
        <div class="login-footer">
          <p>Don't have an account? <a href="register.php">Create one</a></p>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/jquery-3.4.1.min.js"></script>
</body>
</html>