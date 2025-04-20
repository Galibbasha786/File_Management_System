<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Redirect if not admin
redirectIfNotAdmin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'File upload failed.';
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $error = 'File size exceeds maximum limit.';
        } else {
            if (saveFile($user_id, $file)) {
                $success = 'File uploaded successfully.';
            } else {
                $error = 'Failed to save file.';
            }
        }
    }
    
    // Handle file sharing
    if (isset($_POST['share'])) {
        $fileId = $_POST['file_id'];
        $emails = $_POST['emails'];

        if (shareFile($fileId, $user_id, $emails)) {
            $success = 'File shared successfully.';
        } else {
            $error = 'Failed to share file.';
        }
    }

    // Handle file renaming
    if (isset($_POST['rename'])) {
        $fileId = $_POST['file_id'];
        $newName = $_POST['new_name'];

        if (renameFile($fileId, $user_id, $newName, true)) {
            $success = 'File renamed successfully.';
        } else {
            $error = 'Failed to rename file.';
        }
    }
}

// Handle file deletion
if (isset($_GET['delete'])) {
    $fileId = $_GET['delete'];
    if (deleteFile($fileId, $user_id, true)) {
        $success = 'File deleted successfully.';
    } else {
        $error = 'Failed to delete file.';
    }
}

$files = getFiles($user_id, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Files - <?php echo APP_NAME; ?></title>
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        header {
            background: var(--lighter);
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 2rem;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        h1 {
            color: var(--primary);
            font-size: 1.75rem;
        }
        
        nav ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
        }
        
        nav a {
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 0;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        nav a:hover {
            color: var(--primary);
        }
        
        nav a.active {
            color: var(--primary);
        }
        
        nav a.active:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary);
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 6px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        section {
            margin-bottom: 2.5rem;
            background: var(--lighter);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        h2 {
            color: var(--darker);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        input[type="file"],
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            font-size: 1rem;
        }
        
        input[type="file"] {
            padding: 0.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        th {
            background: var(--light);
            color: var(--gray);
            font-weight: 600;
        }
        
        tr:hover {
            background: rgba(0, 0, 0, 0.02);
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
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #21867a;
            transform: translateY(-2px);
        }
        
        .btn-info {
            background: var(--primary-light);
            color: white;
        }
        
        .btn-info:hover {
            background: #3a7bd5;
            transform: translateY(-2px);
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: var(--lighter);
            margin: 10% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .close {
            color: var(--gray);
            float: right;
            font-size: 1.75rem;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--dark);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            nav ul {
                gap: 1rem;
            }
            
            .actions {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1><i class="fas fa-file-alt"></i> Manage Files</h1>
                <nav>
                    <ul>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="files.php" class="active"><i class="fas fa-file"></i> Files</a></li>
                        <li><a href="users.php"><i class="fas fa-users-cog"></i> Users</a></li>
                        <li><a href="?logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
        </header>
        
        <main>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <section class="file-upload">
                <h2><i class="fas fa-cloud-upload-alt"></i> Upload File</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="file" name="file" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
                </form>
            </section>
            
            <section class="file-list">
                <h2><i class="fas fa-folder-open"></i> All Files</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Owner</th>
                            <th>Size</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($file['filename']); ?></td>
                            <td><?php 
                                $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                                $stmt->execute([$file['user_id']]);
                                echo htmlspecialchars($stmt->fetchColumn());
                            ?></td>
                            <td><?php echo formatFileSize($file['filesize']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($file['created_at'])); ?></td>
                            <td class="actions">
                                <a href="<?php echo str_replace(UPLOAD_DIR, '../uploads/', $file['filepath']); ?>" download class="btn btn-primary"><i class="fas fa-download"></i> Download</a>
                                <a href="?delete=<?php echo $file['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this file?')"><i class="fas fa-trash"></i> Delete</a>
                                <button class="btn btn-success" onclick="openShareModal(<?php echo $file['id']; ?>)"><i class="fas fa-share"></i> Share</button>
                                <button class="btn btn-info" onclick="openRenameModal(<?php echo $file['id']; ?>, '<?php echo htmlspecialchars($file['filename'], ENT_QUOTES); ?>')"><i class="fas fa-edit"></i> Rename</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
            
            <!-- Share Modal -->
            <div id="shareModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('shareModal')">&times;</span>
                    <h2><i class="fas fa-share"></i> Share File</h2>
                    <form method="POST">
                        <input type="hidden" name="file_id" id="shareFileId">
                        <div class="form-group">
                            <label for="emails">Email addresses (comma separated)</label>
                            <input type="text" name="emails" id="emails" required placeholder="user1@example.com, user2@example.com">
                        </div>
                        <button type="submit" name="share" class="btn btn-success"><i class="fas fa-paper-plane"></i> Share</button>
                    </form>
                </div>
            </div>
            
            <!-- Rename Modal -->
            <div id="renameModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('renameModal')">&times;</span>
                    <h2><i class="fas fa-edit"></i> Rename File</h2>
                    <form method="POST">
                        <input type="hidden" name="file_id" id="renameFileId">
                        <div class="form-group">
                            <input type="text" name="new_name" id="newName" required>
                        </div>
                        <button type="submit" name="rename" class="btn btn-primary"><i class="fas fa-save"></i> Rename</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openShareModal(fileId) {
            document.getElementById('shareFileId').value = fileId;
            document.getElementById('shareModal').style.display = 'block';
        }

        function openRenameModal(fileId, fileName) {
            document.getElementById('renameFileId').value = fileId;
            document.getElementById('newName').value = fileName;
            document.getElementById('renameModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>