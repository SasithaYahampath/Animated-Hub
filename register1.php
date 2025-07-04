<?php
require 'config.php';
session_start();

$errors = [];
$username = $email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']) ? true : false;
    $role = trim($_POST['role']);

    // Validation
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (!$terms) {
        $errors['terms'] = 'You must agree to the terms';
    }

    // Check if email exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors['email'] = 'Email already registered';
        }
        $stmt->close();
    }

    // Register user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            header('Location: index.php');
            exit();
        } else {
            $errors['database'] = 'Registration failed: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | Animated Hub</title>
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

    .register-container {
      max-width: 600px;
      margin: 2rem auto;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    .register-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 2rem;
      text-align: center;
    }

    .register-header img {
      width: 80px;
      margin-bottom: 1rem;
    }

    .register-header h2 {
      font-weight: 600;
      margin: 0;
    }

    .register-body {
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

    .btn-register {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border: none;
      padding: 12px;
      font-weight: 600;
      width: 100%;
      border-radius: 8px;
      transition: all 0.3s;
    }

    .btn-register:hover {
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

    .terms-check {
      margin: 15px 0;
    }

    .register-footer {
      text-align: center;
      padding-top: 1.5rem;
      border-top: 1px solid #eee;
      margin-top: 1.5rem;
    }

    .register-footer a {
      color: var(--primary-color);
      font-weight: 500;
      text-decoration: none;
    }

    .password-strength {
      height: 5px;
      background: #eee;
      margin-top: 5px;
      border-radius: 3px;
      overflow: hidden;
    }

    .password-strength-bar {
      height: 100%;
      width: 0;
      background: #dc3545;
      transition: width 0.3s;
    }

    @media (max-width: 576px) {
      .register-container {
        margin: 1rem;
      }
      
      .register-header {
        padding: 1.5rem;
      }
      
      .register-body {
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
      <a href="login.php" class="btn btn-outline-primary me-2">Sign In</a>
      <a href="index.php" class="btn btn-link">Home</a>
    </div>
  </div>
</nav>

<!-- Main Registration Content -->
<div class="container py-5">
  <div class="register-container">
    <div class="register-header">
      <img src="images/2.png" alt="Logo">
      <h2>Create Your Account</h2>
    </div>
    
    <div class="register-body">
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $error): ?>
            <p class="mb-1"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form action="register.php" method="POST">
        <div class="row">
          <div class="col-md-12">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="username" name="username" 
                     placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required>
              <label for="username"><i class="fas fa-user me-2"></i>Username</label>
            </div>
          </div>
        </div>
        
        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="email" name="email" 
                 placeholder="name@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
          <label for="email"><i class="fas fa-envelope me-2"></i>Email address</label>
        </div>
        
        <div class="form-floating mb-3">
          <input type="password" class="form-control" id="password" name="password" 
                 placeholder="Password" required oninput="checkPasswordStrength(this.value)">
          <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
          <div class="password-strength">
            <div class="password-strength-bar" id="password-strength-bar"></div>
          </div>
          <small class="text-muted">Minimum 6 characters</small>
        </div>
        
        <div class="form-floating mb-3">
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                 placeholder="Confirm Password" required>
          <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirm Password</label>
        </div>
        
        <div class="terms-check mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
            <label class="form-check-label" for="terms">
              I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a>
            </label>
          </div>
          <input type="hidden" name="role" value="admin">
        </div>
        
        <button type="submit" name="register" class="btn btn-primary btn-register mb-3">
          <i class="fas fa-user-plus me-2"></i> Create Account
        </button>
        
        <div class="register-footer">
          <p>Already have an account? <a href="login.php">Sign In</a></p>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Terms & Conditions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Here you would include your actual terms and conditions content.</p>
        <p>This might include privacy policy, user agreements, and other legal information that users should agree to before registering.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function checkPasswordStrength(password) {
    const strengthBar = document.getElementById('password-strength-bar');
    let strength = 0;
    
    if (password.length >= 6) strength += 1;
    if (password.length >= 8) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    // Update the strength bar
    const width = (strength / 5) * 100;
    strengthBar.style.width = width + '%';
    
    // Change color based on strength
    if (strength <= 1) {
      strengthBar.style.backgroundColor = '#dc3545'; // Red
    } else if (strength <= 3) {
      strengthBar.style.backgroundColor = '#fd7e14'; // Orange
    } else {
      strengthBar.style.backgroundColor = '#28a745'; // Green
    }
  }
</script>
</body>
</html>