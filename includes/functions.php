<?php
require_once 'db.php';

function generateOTP() {
    return rand(100000, 999999);
}

function storeOTP($email, $otp) {
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM otp_verification WHERE email = ?");
    $stmt->execute([$email]);

    $stmt = $pdo->prepare("INSERT INTO otp_verification (email, otp) VALUES (?, ?)");
    return $stmt->execute([$email, $otp]);
}

function verifyOTP($email, $otp) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM otp_verification WHERE email = ? AND otp = ? AND expires_at > NOW()");
    $stmt->execute([$email, $otp]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function registerUser($username, $email, $password, $role = 'user') {
    global $pdo;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$username, $email, $hashedPassword, $role]);
}

function getUserByEmail($email) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function verifyUser($email) {
    global $pdo;

    $stmt = $pdo->prepare("UPDATE users SET is_verified = TRUE WHERE email = ?");
    return $stmt->execute([$email]);
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['is_verified'] = $user['is_verified'];
    $_SESSION['logged_in'] = true;
}

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['email'] === 'srivasthavavarma@gmail.com';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

function redirectIfNotAdmin() {
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        header("Location: ../user/dashboard.php");
        exit();
    }
}

function sanitizeFileName($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
    return time() . '_' . $filename;
}

function saveFile($userId, $file) {
    global $pdo;

    $filename = sanitizeFileName($file['name']);
    $filepath = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $stmt = $pdo->prepare("INSERT INTO files (user_id, filename, filepath, filesize, filetype) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([ 
            $userId,
            $file['name'],
            $filepath,
            $file['size'],
            $file['type']
        ]);
    }

    return false;
}

function getFiles($userId, $isAdmin = false) {
    global $pdo;

    if ($isAdmin) {
        $stmt = $pdo->prepare("SELECT * FROM files");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteFile($fileId, $userId, $isAdmin = false) {
    global $pdo;

    if ($isAdmin) {
        $stmt = $pdo->prepare("SELECT filepath FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
    } else {
        $stmt = $pdo->prepare("SELECT filepath FROM files WHERE id = ? AND user_id = ?");
        $stmt->execute([$fileId, $userId]);
    }

    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        if (file_exists($file['filepath'])) {
            unlink($file['filepath']);
        }

        $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
        return $stmt->execute([$fileId]);
    }

    return false;
}

function shareFile($fileId, $userId, $emails) {
    global $pdo;

    $stmt = $pdo->prepare("UPDATE files SET shared_with = ? WHERE id = ? AND user_id = ?");
    return $stmt->execute([$emails, $fileId, $userId]);
}

function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $units = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

function renameFile($fileId, $userId, $newName) {
    global $pdo;

    // Sanitize new file name
    $newName = sanitizeFileName($newName);
    
    // Get the file's current path
    $stmt = $pdo->prepare("SELECT filepath FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$fileId, $userId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Get the old filepath
        $oldFilePath = $file['filepath'];

        // Prepare the new file path
        $newFilePath = dirname($oldFilePath) . DIRECTORY_SEPARATOR . $newName;

        // Rename the actual file
        if (rename($oldFilePath, $newFilePath)) {
            // Update the database with the new name and path
            $stmt = $pdo->prepare("UPDATE files SET filename = ?, filepath = ? WHERE id = ? AND user_id = ?");
            return $stmt->execute([$newName, $newFilePath, $fileId, $userId]);
        }
    }

    return false;
}

function generateShareableLink($fileId, $userId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT filepath FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$fileId, $userId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        return 'http://localhost/your-project/uploads/' . basename($file['filepath']);
        // Replace 'your-project' with your actual project folder name or domain
    }

    return false;
}
/**
 * Deletes a user from the database
 * 
 * @param int $userId The ID of the user to delete
 * @return bool True if deletion was successful, false otherwise
 */
function deleteUser($userId) {
    global $pdo;
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // First, delete any related records in other tables (if needed)
        // Example: $pdo->prepare("DELETE FROM user_files WHERE user_id = ?")->execute([$userId]);
        
        // Then delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        // Commit transaction
        $pdo->commit();
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        // Rollback transaction if something failed
        $pdo->rollBack();
        error_log("Error deleting user: " . $e->getMessage());
        return false;
    }
}
?>