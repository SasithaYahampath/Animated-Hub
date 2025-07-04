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
$result1 = $conn->query("SELECT COUNT(*) AS total_admins FROM users WHERE role = 'admin'");
$row1 = $result1->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🏠 Animated Hub - Super Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
  height: 100%;
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

.stat-card a {
  color: white;
  text-decoration: underline;
}

.bg-primary { background: linear-gradient(135deg, #6c63ff, #4a42d3); }
.bg-success { background: linear-gradient(135deg, #28a745, #1e7e34); }
.bg-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
.bg-danger { background: linear-gradient(135deg, #dc3545, #bd2130); }
.bg-info { background: linear-gradient(135deg, #17a2b8, #138496); }
.bg-purple { background: linear-gradient(135deg, #6f42c1, #5a3d8e); }

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
  
  .stat-card {
    padding: 15px 10px;
  }
  
  .stat-card h3 {
    font-size: 1rem;
  }
  
  .stat-card h4 {
    font-size: 1.4rem;
  }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-brand">
    <a href="super_dashboard.php"><img src="images/2.png" alt="Logo"></a>
  </div>
  <ul class="sidebar-menu">
    <li><a href="super_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
    <li><a href="users.php"><i class="fas fa-users-cog"></i> <span>Manage Users</span></a></li>
    <li><a href="admins.php"><i class="fas fa-user-shield"></i> <span>Manage Admins</span></a></li>
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

  <!-- Dashboard Content -->
  <div class="container-fluid">
    <h3 class="mb-4">Super Admin Dashboard</h3>
    
    <!-- Stats Cards -->
    <div class="row">
      <div class="col-md-3">
        <div class="stat-card bg-primary">
          <i class="fas fa-users"></i>
          <h3>Total Users</h3>
          <h4><?php echo $row['total_users']; ?></h4>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-purple">
          <i class="fas fa-user-shield"></i>
          <h3>Total Admins</h3>
          <h4><?php echo $row1['total_admins']; ?></h4>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-warning">
          <i class="fas fa-clock"></i>
          <h3>Pending Updates</h3>
          <h4>12</h4>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-info">
          <i class="fas fa-calendar-alt"></i>
          <h3>Upcoming Events</h3>
          <h4>3</h4>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h5>Recent Activity</h5>
          </div>
          <div class="card-body">
            <ul class="list-group activity-list">
              <li class="list-group-item">
                <i class="fas fa-user-plus text-success me-2"></i> New user registered
                <span class="float-end text-muted small">2 min ago</span>
              </li>
              <li class="list-group-item">
                <i class="fas fa-film text-primary me-2"></i> Uploaded new Cartoon
                <span class="float-end text-muted small">1 hour ago</span>
              </li>
              <li class="list-group-item">
                <i class="fas fa-users text-info me-2"></i> Staff meeting scheduled
                <span class="float-end text-muted small">3 hours ago</span>
              </li>
              <li class="list-group-item">
                <i class="fas fa-sync-alt text-warning me-2"></i> System update completed
                <span class="float-end text-muted small">Yesterday</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
      
      <!-- Quick Actions -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5>Quick Actions</h5>
          </div>
          <div class="card-body">
            <a href="register1.php" class="btn btn-success btn-block mb-2">
              <i class="fas fa-user-plus me-2"></i> Add New Admin
            </a>
            <a href="upload.php" class="btn btn-primary btn-block mb-2">
              <i class="fas fa-edit me-2"></i> Add New Content
            </a>
            <a href="modify.php" class="btn btn-info btn-block mb-2">
              <i class="fas fa-film me-2"></i> Manage Content
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/jquery-3.4.1.min.js"></script>
</body>
</html>