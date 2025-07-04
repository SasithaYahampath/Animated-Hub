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


// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;

// Get total videos for pagination
$total = $conn->query("SELECT COUNT(id) as total FROM videos")->fetch_assoc()['total'];
$pages = ceil($total / $per_page);

// Get videos with pagination
$videos = $conn->query("SELECT * FROM videos ORDER BY upload_date DESC LIMIT $start, $per_page");

// Search functionality
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $videos = $conn->query("SELECT * FROM videos WHERE title LIKE '%$search%' ORDER BY upload_date DESC");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎬 Animated Hub - Admin Panel</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="css/bootstrap-4.4.1.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #166088;
            --accent-color: #4fc3f7;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --sidebar-width: 250px;
        }
        
        body {
            background: 
                linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(245,245,245,0.98) 100%),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect fill="%23f0f0f0" width="50" height="50" x="0" y="0"></rect><rect fill="%23f0f0f0" width="50" height="50" x="50" y="50"></rect></svg>');
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
        }

        /* New Sidebar Navigation */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header img {
            max-width: 80%;
            height: auto;
            margin-bottom: 10px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu li a.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid var(--accent-color);
        }

        .sidebar-menu li a i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .user-profile {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .user-info {
            line-height: 1.3;
        }

        .user-name {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .user-role {
            font-size: 0.8rem;
            color: #bdc3c7;
        }

        .logout-btn {
            display: block;
            width: calc(100% - 40px);
            margin: 20px;
            padding: 10px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        .logout-btn i {
            margin-right: 8px;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 25px;
            background-color: rgba(255, 255, 255, 0.92);
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Rest of your existing styles for table, search, etc. */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }

        th {
            background-color: var(--primary-color);
            color: white;
            position: sticky;
            top: 0;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f7fd;
        }

        .action-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            margin: 0 3px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 13px;
            transition: all 0.2s;
            min-width: 30px;
        }

        .action-links a i {
            margin-right: 5px;
        }

        .view-link {
            background-color: #2196F3;
        }
        .view-link:hover {
            background-color: #0b7dda;
            transform: translateY(-1px);
        }

        .edit-link {
            background-color: #FF9800;
        }
        .edit-link:hover {
            background-color: #e68a00;
            transform: translateY(-1px);
        }

        .delete-link {
            background-color: #F44336;
        }
        .delete-link:hover {
            background-color: #d32f2f;
            transform: translateY(-1px);
        }

        .search-box {
            margin: 20px 0;
            display: flex;
        }

        .search-box input {
            padding: 10px 15px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 195, 247, 0.2);
        }

        .search-box button {
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-box button:hover {
            background-color: var(--secondary-color);
        }

        .pagination {
            margin-top: 25px;
            display: flex;
            justify-content: center;
        }

        .pagination a {
            color: var(--dark-color);
            padding: 8px 14px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 3px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .pagination a.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination a:hover:not(.active) {
            background-color: #f1f1f1;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .admin-actions {
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .admin-actions a {
            display: inline-flex;
            align-items: center;
            padding: 10px 18px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .admin-actions a i {
            margin-right: 8px;
        }

        .admin-actions a:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Mobile Responsiveness */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1100;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                margin-top: 15px;
                width: 100%;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .admin-actions {
                flex-direction: column;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="images/2.png" alt="Animated Hub Logo">
            <h3>Animated Hub</h3>
        </div>
        
        <nav class="sidebar-menu">
            <ul>
                <li>
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="upload.php">
                        <i class="fas fa-upload"></i> Upload Video
                    </a>
                </li>
                <li>
                    <a href="index.php">
                        <i class="fas fa-eye"></i> View Site
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="user-profile">
            <img src="images/user.png" alt="User Profile">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="admin-header">
                <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                <div class="search-box">
                    <form method="GET" action="">
                        <input type="text" name="search" placeholder="Search videos..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Filename</th>
                        <th>Views</th>
                        <th>Upload Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($videos->num_rows > 0): ?>
                        <?php while ($video = $videos->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($video['title']) ?></td>
                            <td><?= htmlspecialchars($video['filename']) ?></td>
                            <td><?= number_format($video['views']) ?></td>
                            <td><?= date('M d, Y H:i', strtotime($video['upload_date'])) ?></td>
                            <td class="action-links">
                                <a href="video1.php?id=<?= $video['id'] ?>" class="view-link" title="View"><i class="fas fa-eye"></i> View</a>
                                <a href="delete.php?id=<?= $video['id'] ?>" class="delete-link" title="Delete" onclick="return confirm('Are you sure you want to delete this video?');"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">No videos found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (!isset($_GET['search']) && $pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a href="?page=<?= $i ?>" <?= ($i == $page) ? 'class="active"' : '' ?>><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $pages): ?>
                    <a href="?page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
        
        // Close menu when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992 && !sidebar.contains(e.target) && e.target !== mobileMenuBtn) {
                sidebar.classList.remove('active');
            }
        });
        
        // Confirm before deleting
        document.querySelectorAll('.delete-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this video?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Add active class to current nav item
        const currentPage = window.location.pathname.split('/').pop();
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>