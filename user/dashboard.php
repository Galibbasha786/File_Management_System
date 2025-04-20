<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$files = getFiles($user_id);

if (isset($_GET['logout'])) {
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - <?php echo APP_NAME; ?></title>

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
      <a href="?logout" class="block text-red-300 hover:text-red-500"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6 md:p-10">
    <h1 class="text-3xl font-bold mb-6 text-cyan-600">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h1>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl shadow-md">
        <div class="flex items-center">
          <div class="text-cyan-500 text-3xl mr-4"><i class="fas fa-file-alt"></i></div>
          <div>
            <p class="text-lg">Your Files</p>
            <p class="text-2xl font-semibold"><?php echo count($files); ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-md">
        <div class="flex items-center">
          <div class="text-cyan-400 text-3xl mr-4"><i class="fas fa-database"></i></div>
          <div>
            <p class="text-lg">Storage Used</p>
            <p class="text-2xl font-semibold">
              <?php 
                $total_size = array_sum(array_column($files, 'filesize'));
                echo formatFileSize($total_size);
              ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Files -->
    <div class="bg-white p-6 rounded-xl shadow-md">
      <h2 class="text-2xl font-semibold mb-4 text-cyan-600">Recent Files</h2>

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
              <?php foreach (array_slice($files, 0, 5) as $file): ?>
              <tr class="hover:bg-slate-100">
                <td class="py-2 px-4"><?php echo htmlspecialchars($file['filename']); ?></td>
                <td class="py-2 px-4"><?php echo formatFileSize($file['filesize']); ?></td>
                <td class="py-2 px-4"><?php echo date('M d, Y', strtotime($file['created_at'])); ?></td>
                <td class="py-2 px-4">
                  <a href="<?php echo str_replace(UPLOAD_DIR, '../uploads/', $file['filepath']); ?>" download class="bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-1 rounded text-sm">Download</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mt-4">
          <a href="files.php" class="inline-block bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-md text-sm font-medium transition">View All Files</a>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

</body>
</html>