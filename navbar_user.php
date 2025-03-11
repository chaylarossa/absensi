<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Get user data with siswa info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.*, s.nama as siswa_nama, s.foto, s.kelas
    FROM users u 
    LEFT JOIN siswa s ON u.siswa_id = s.id 
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Set default foto if not exists
$foto = !empty($user_data['foto']) && file_exists("uploads/" . $user_data['foto']) 
    ? "uploads/" . $user_data['foto'] 
    : "uploads/default.png";
?>

<nav class="fixed top-0 right-0 left-64 bg-white shadow-md z-50">
    <div class="px-6 py-3">
        <div class="flex items-center justify-end">
            <!-- Profil Siswa -->
            <div class="flex items-center space-x-4">
                <a href="profil_user.php" class="flex items-center gap-3 hover:bg-gray-100 px-4 py-2 rounded-lg transition-colors group">
                    <div class="relative">
                        <img src="<?= $foto ?>" 
                             alt="Foto Profil" 
                             class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 group-hover:border-blue-500 transition-colors">
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-700 font-medium group-hover:text-blue-600">
                            <?= htmlspecialchars($user_data['siswa_nama'] ?? 'Siswa'); ?>
                        </span>
                        <span class="text-xs text-gray-500">
                            Kelas <?= htmlspecialchars($user_data['kelas'] ?? '-'); ?>
                        </span>
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
