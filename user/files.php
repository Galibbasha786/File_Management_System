<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
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

// Handle file deletion
if (isset($_GET['delete'])) {
    $fileId = $_GET['delete'];
    if (deleteFile($fileId, $user_id)) {
        $success = 'File deleted successfully.';
    } else {
        $error = 'Failed to delete file.';
    }
}

$files = getFiles($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Files - <?php echo APP_NAME; ?></title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-800">

<div class="flex flex-col md:flex-row min-h-screen">

  <!-- Sidebar -->
  <aside class="w-full md:w-64 bg-slate-800 text-white p-6">
    <h2 class="text-2xl font-bold text-cyan-400 mb-8"><?php echo APP_NAME; ?></h2>
    <nav class="space-y-4">
      <a href="dashboard.php" class="block hover:text-cyan-400"><i class="fas fa-home mr-2"></i> Dashboard</a>
      <a href="files.php" class="block hover:text-cyan-400"><i class="fas fa-folder mr-2"></i> My Files</a>
      <a href="../?logout" class="block text-red-300 hover:text-red-500"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6 md:p-10">
    <h1 class="text-3xl font-bold mb-6 text-cyan-600">My Files</h1>

    <!-- Alerts -->
    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- File Upload -->
    <section class="bg-white p-6 rounded-xl shadow mb-8">
      <h2 class="text-xl font-semibold mb-4 text-cyan-600">Upload New File</h2>
      <form method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row items-start md:items-center gap-4">
        <input type="file" name="file" required class="block w-full border border-slate-300 p-2 rounded-md text-sm" />
        <button type="submit" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-md text-sm font-medium">Upload</button>
      </form>
    </section>

    <!-- File List -->
    <section class="bg-white p-6 rounded-xl shadow">
      <h2 class="text-xl font-semibold mb-4 text-cyan-600">Your Files</h2>
      <?php if (empty($files)): ?>
        <p class="text-slate-500">You haven't uploaded any files yet.</p>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full table-auto text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-200 text-cyan-700">
                <th class="py-2 px-4">Filename</th>
                <th class="py-2 px-4">Size</th>
                <th class="py-2 px-4">Uploaded</th>
                <th class="py-2 px-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($files as $file): ?>
              <tr class="hover:bg-slate-100">
                <td class="py-2 px-4"><?php echo htmlspecialchars($file['filename']); ?></td>
                <td class="py-2 px-4"><?php echo formatFileSize($file['filesize']); ?></td>
                <td class="py-2 px-4"><?php echo date('M d, Y', strtotime($file['created_at'])); ?></td>
                <td class="py-2 px-4 space-x-2">
                  <a href="<?php echo str_replace(UPLOAD_DIR, '../uploads/', $file['filepath']); ?>" download class="bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-1 rounded text-sm">Download</a>
                  <a href="?delete=<?php echo $file['id']; ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm" onclick="return confirm('Delete this file?')">Delete</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>
</div>

</body>
</html>