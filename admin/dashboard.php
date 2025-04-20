<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

// Handle logout before any output
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php");  // Changed to ../login.php to ensure proper path
    exit();
}

$user_id = $_SESSION['user_id'];
$files = getFiles($user_id, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --light: #f8f9fa;
            --lighter: #ffffff;
            --dark: #212529;
            --darker: #1a1a1a;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --danger: #e63946;
            --success: #2a9d8f;
            --warning: #f4a261;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: var(--light);
            color: var(--dark);
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            line-height: 1.6;
        }
        
        header {
            background: var(--lighter);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .logo {
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        h1 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        nav ul {
            list-style: none;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        nav a {
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        nav a:hover, nav a.active {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
        }
        
        nav a i {
            font-size: 0.9rem;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .dashboard-title {
            margin-bottom: 1.5rem;
            color: var(--darker);
            font-size: 1.75rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--lighter);
            padding: 1.75rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--light-gray);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .files-icon { background: rgba(67, 97, 238, 0.1); color: var(--primary); }
        .users-icon { background: rgba(42, 157, 143, 0.1); color: var(--success); }
        
        .stat-card h3 {
            color: var(--gray);
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .stat-value {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--darker);
        }
        
        .stat-change {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .positive { color: var(--success); }
        .negative { color: var(--danger); }
        
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .btn i {
            font-size: 0.8rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #d62839;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            nav ul {
                width: 100%;
                justify-content: space-between;
            }
            
            .main-container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="brand">
                <div class="logo">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1>Admin Dashboard</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="files.php"><i class="fas fa-file"></i> Files</a></li>
                    <li><a href="users.php"><i class="fas fa-users-cog"></i> Users</a></li>
                    <li><a href="?logout=1" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <h2 class="dashboard-title">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <h3>Total Files</h3>
                        <div class="stat-value"><?php echo count($files); ?></div>
                    </div>
                    <div class="stat-icon files-icon">
                        <i class="fas fa-file"></i>
                    </div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 12% from last week
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <h3>Total Users</h3>
                        <div class="stat-value">
                            <?php 
                            global $pdo;
                            echo $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                            ?>
                        </div>
                    </div>
                    <div class="stat-icon users-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 5% from last month
                </div>
            </div>
            
            <!-- Additional stat cards can be added here -->
        </div>
        
        <!-- You can add more sections here -->
    </main>
</body>
</html>