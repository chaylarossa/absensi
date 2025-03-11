<?php
include 'config.php';

// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data admin dari users dan admin table
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT a.* 
    FROM admin a 
    JOIN users u ON u.username = a.username 
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

?>

<nav class="fixed top-0 right-0 left-64 bg-white shadow-md z-50">
    <div class="px-6 py-3">
        <div class="flex items-center justify-end">
            <!-- Profil Admin -->
            <div class="flex items-center space-x-4">
                <a href="profil_admin.php" class="flex items-center gap-3 hover:bg-gray-100 px-4 py-2 rounded-lg transition-colors group">
                    <div class="relative">
                        <img src="uploads/background oca.jpg" 
                             alt="Foto Profil" 
                             class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 group-hover:border-blue-500 transition-colors">
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-700 font-medium group-hover:text-blue-600">
                            <?= htmlspecialchars($admin['nama'] ?? 'Admin'); ?>
                        </span>
                        <span class="text-xs text-gray-500">Administrator</span>
                    </div>
                </a>
                
                <!-- Tombol Logout -->
                <a href="logout.php" 
                   class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Spacer untuk menghindari konten tertutup navbar -->
<div class="h-16"></div>
