<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get regular users
$users = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🏠 Animated Hub - Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<style>
:root {
  --primary-color: #6c63ff;
  --sidebar-width: 250px;
}

body {
  background-color: #f5f7fa;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Sidebar Styles */
.sidebar {
  width: var(--sidebar-width);
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  background: linear-gradient(180deg, #3a3a3a, #2a2a2a);
  color: white;
  transition: all 0.3s;
  z-index: 1000;
  box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar-brand {
  padding: 1.5rem 1rem;
  text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-brand img {
  max-width: 80%;
}

.sidebar-menu {
  padding: 0;
  margin: 0;
  list-style: none;
}

.sidebar-menu li {
  padding: 0.5rem 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.sidebar-menu li a {
  color: #b3b3b3;
  text-decoration: none;
  display: flex;
  align-items: center;
  transition: all 0.3s;
}

.sidebar-menu li a:hover, .sidebar-menu li a.active {
  color: white;
  background: rgba(255,255,255,0.1);
}

.sidebar-menu li a i {
  margin-right: 10px;
  width: 20px;
  text-align: center;
}

.main-content {
  margin-left: var(--sidebar-width);
  padding: 20px;
  min-height: 100vh;
}

.navbar {
  background-color: white;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 0.5rem 1rem;
}

.user-profile {
  display: flex;
  align-items: center;
}

.user-profile img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 10px;
}

.card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  transition: transform 0.3s;
  margin-bottom: 20px;
}

.card:hover {
  transform: translateY(-5px);
}

.card-header {
  background-color: white;
  border-bottom: 1px solid rgba(0,0,0,0.05);
  font-weight: 600;
}

.stat-card {
  color: white;
  border-radius: 10px;
  padding: 20px;
  text-align: center;
}

.stat-card i {
  font-size: 2.5rem;
  margin-bottom: 15px;
}

.stat-card h3 {
  font-size: 1.2rem;
  margin-bottom: 10px;
}

.stat-card h4 {
  font-size: 1.8rem;
  font-weight: 700;
}

.bg-primary { background: linear-gradient(135deg, #6c63ff, #4a42d3); }
.bg-success { background: linear-gradient(135deg, #28a745, #1e7e34); }
.bg-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
.bg-danger { background: linear-gradient(135deg, #dc3545, #bd2130); }

.activity-list .list-group-item {
  border-left: none;
  border-right: none;
  padding: 15px 20px;
}

.activity-list .list-group-item:first-child {
  border-top: none;
}

@media (max-width: 768px) {
  .sidebar {
    width: 70px;
    overflow: hidden;
  }
  
  .sidebar-brand span, .sidebar-menu li a span {
    display: none;
  }
  
  .sidebar-menu li a i {
    margin-right: 0;
    font-size: 1.2rem;
  }
  
  .main-content {
    margin-left: 70px;
  }
}
</style>
</head>
<body>
 
<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-brand">
    <a href="dashboard.php"><img src="images/2.png" alt="Logo"></a>
  </div>
  <ul class="sidebar-menu">
    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
    <li><a href="users.php" class="active"><i class="fas fa-users"></i> <span>Users</span></a></li>
    <li><a href="modify.php"><i class="fas fa-film"></i> <span>Content</span></a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand">
    <div class="ms-auto d-flex align-items-center">
      <div class="user-profile me-3">
        <img src="images/user.png" alt="User">
        <span><?php echo htmlspecialchars($user['username']); ?></span>
      </div>
      <a href="logout.php" class="btn btn-outline-danger btn-sm">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </nav>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Manage Users</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="register.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-plus-circle"></i> Add User
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <a href="admin_edit_user.php?id=<?= $user['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary"
                                   data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="admin_delete_user.php?id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       data-bs-toggle="tooltip" title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <script>
        // Enable tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html>