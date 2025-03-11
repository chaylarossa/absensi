<?php
// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
// Add trailing slash if needed for comparison
$current_url = $_SERVER['REQUEST_URI'];
$current_url = rtrim($current_url, '/');
?>

<div class="fixed left-0 top-0 w-64 bg-[#074799] text-white min-h-screen p-6">
    <h2 class="text-2xl font-bold mb-6">Dashboard Admin</h2>
    <ul>
        <li class="mb-4">
            <a href="index.php" 
               class="block py-2 px-4 rounded-lg <?= (strpos($current_url, 'index.php') !== false || $current_url == '') ? 'bg-blue-500' : 'hover:bg-blue-400' ?>">
                <i class="fas fa-home mr-2"></i>Dashboard
            </a>
        </li>
        <li class="mb-4">
            <a href="siswa.php" 
               class="block py-2 px-4 rounded-lg <?= strpos($current_url, 'siswa.php') !== false ? 'bg-blue-500' : 'hover:bg-blue-400' ?>">
                <i class="fas fa-user-graduate mr-2"></i>Data Siswa
            </a>
        </li>
        <li class="mb-4">
            <a href="absensi.php" 
               class="block py-2 px-4 rounded-lg <?= strpos($current_url, 'absensi.php') !== false ? 'bg-blue-500' : 'hover:bg-blue-400' ?>">
                <i class="fas fa-clipboard-list mr-2"></i>Absensi Siswa
            </a>
        </li>
        <li class="mb-4">
            <a href="data_guru.php" 
               class="block py-2 px-4 rounded-lg <?= strpos($current_url, 'data_guru.php') !== false ? 'bg-blue-500' : 'hover:bg-blue-400' ?>">
                <i class="fas fa-chalkboard-teacher mr-2"></i>Data Guru
            </a>
        </li>
        <li class="mb-4">
            <a href="absensi_guru.php" 
               class="block py-2 px-4 rounded-lg <?= strpos($current_url, 'absensi_guru.php') !== false ? 'bg-blue-500' : 'hover:bg-blue-400' ?>">
                <i class="fas fa-clipboard-check mr-2"></i>Absensi Guru
            </a>
        </li>
        <li class="mb-4">
            <a href="manage_admin.php" 
               class="block py-2 px-4 rounded-lg <?= strpos($current_url, 'manage_admin.php') !== false ? 'bg-blue-500' : 'hover:bg-blue-400' ?>">
                <i class="fas fa-user-shield mr-2"></i>Manajemen Admin
            </a>
        </li>
    </ul>
</div>

