<?php
// Get user data if not already set
if (!isset($user_data) || empty($user_data)) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT u.*, s.nama as siswa_nama, s.kelas, s.jurusan 
        FROM users u 
        LEFT JOIN siswa s ON u.siswa_id = s.id 
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
}

// Set default name if not found
$siswa_nama = $user_data['siswa_nama'] ?? 'Siswa';
?>

<div class="fixed left-0 top-0 w-64 h-full bg-gradient-to-b from-[#074799] to-blue-600 text-white p-6">
    <div class="flex items-center gap-3 mb-8">
        <i class="fas fa-user-circle text-3xl"></i>
        <div>
            <h2 class="font-bold">Dashboard Siswa</h2>
            <p class="text-sm text-blue-200"><?= htmlspecialchars($siswa_nama) ?></p>
        </div>
    </div>
    
    <nav>
        <a href="user_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg <?= (basename($_SERVER['PHP_SELF']) == 'user_dashboard.php') ? 'bg-white/10' : 'hover:bg-white/10' ?> mb-2">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="riwayat_absensi.php" class="flex items-center gap-3 p-3 rounded-lg <?= (basename($_SERVER['PHP_SELF']) == 'riwayat_absensi.php') ? 'bg-white/10' : 'hover:bg-white/10' ?> mb-2">
            <i class="fas fa-history"></i>
            <span>Riwayat Absensi</span>
        </a>
    </nav>
</div>
