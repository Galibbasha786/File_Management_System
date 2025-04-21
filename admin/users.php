<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

// Handle logout before any output
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Handle user actions (delete only)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $userId = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
        
        // Prevent deleting the admin account
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && $user['email'] !== 'srivasthavavarma@gmail.com') {
            deleteUser($userId);
            $_SESSION['message'] = "User deleted successfully";
        } else {
            $_SESSION['message'] = "Cannot delete admin account";
        }
        
        header("Location: users.php");
        exit();
    }
}

// Get all users from database
global $pdo;
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* [Previous CSS styles remain exactly the same] */
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
        
        .page-title {
            margin-bottom: 1.5rem;
            color: var(--darker);
            font-size: 1.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .table-container {
            background: var(--lighter);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid var(--light-gray);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: var(--primary);
            color: white;
        }
        
        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background: rgba(67, 97, 238, 0.03);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-active {
            background: rgba(42, 157, 143, 0.1);
            color: var(--success);
        }
        
        .status-inactive {
            background: rgba(230, 57, 70, 0.1);
            color: var(--danger);
        }
        
        .status-pending {
            background: rgba(244, 162, 97, 0.1);
            color: var(--warning);
        }
        
        .admin-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .btn {
            padding: 0.5rem 1rem;
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
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--secondary);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #d62839;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #248277;
        }
        
        .btn-warning {
            background: var(--warning);
            color: white;
        }
        
        .btn-warning:hover {
            background: #e08c3e;
        }
        
        .actions-cell {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--darker);
        }
        
        .user-email {
            font-size: 0.875rem;
            color: var(--gray);
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
            
            table {
                display: block;
                overflow-x: auto;
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
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="files.php"><i class="fas fa-file"></i> Files</a></li>
                    <li><a href="users.php" class="active"><i class="fas fa-users-cog"></i> Users</a></li>
                    <li><a href="?logout=1" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <h2 class="page-title">
            <i class="fas fa-users-cog"></i> User Management
        </h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?php echo strpos($_SESSION['message'], 'Cannot delete') !== false ? 'alert-danger' : 'alert-success'; ?>">
                <i class="fas fa-<?php echo strpos($_SESSION['message'], 'Cannot delete') !== false ? 'times-circle' : 'check-circle'; ?>"></i>
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Status</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="avatar">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($user['is_verified']): ?>
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check-circle"></i> Verified
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['email'] === 'srivasthavavarma@gmail.com'): ?>
                                    <span class="admin-badge">
                                        <i class="fas fa-crown"></i> Admin
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-active">
                                        <i class="fas fa-user"></i> User
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="actions-cell">
                                <?php if ($user['email'] !== 'srivasthavavarma@gmail.com'): ?>
                                    <form method="POST" action="users.php" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>