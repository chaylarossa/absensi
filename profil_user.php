<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Get user and siswa data with alias for siswa name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.*, 
           s.nama AS siswa_nama, 
           s.kelas,
           s.jurusan,
           s.tanggal_lahir,
           s.foto
    FROM users u 
    LEFT JOIN siswa s ON u.siswa_id = s.id 
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// If no siswa data found, redirect to default page
if (!$user_data['siswa_nama']) {
    header("Location: default.php");
    exit();
}

// Set default foto if not exists
$foto = !empty($user_data['foto']) && file_exists("uploads/" . $user_data['foto']) 
    ? "uploads/" . $user_data['foto'] 
    : "uploads/default.png";

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-screen">
    
    <!-- Main Content dengan Flexbox untuk centering -->
    <div class="flex items-center justify-center min-h-screen">
        <div class="max-w-2xl w-full mx-auto bg-white rounded-xl shadow-lg overflow-hidden my-8">
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#074799] to-blue-600 text-white p-8">
                <div class="flex items-center space-x-6">
                    <img src="<?= $foto ?>" alt="Foto Profil" 
                         class="w-28 h-28 rounded-full border-4 border-white shadow-lg object-cover">
                    <div>
                        <h1 class="text-3xl font-bold mb-1"><?= htmlspecialchars($user_data['siswa_nama']) ?></h1>
                        <p class="text-blue-100 flex items-center">
                            <i class="fas fa-user-graduate mr-2"></i>Siswa
                        </p>
                    </div>
                </div>
            </div>

            <!-- Info Detail dengan padding lebih besar -->
            <div class="p-8">
                <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label class="text-sm text-gray-500 block mb-1">
                                <i class="fas fa-layer-group mr-2"></i>Kelas
                            </label>
                            <p class="font-medium text-lg"><?= htmlspecialchars($user_data['kelas'] ?? '-') ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500 block mb-1">
                                <i class="fas fa-book mr-2"></i>Jurusan
                            </label>
                            <p class="font-medium text-lg"><?= htmlspecialchars($user_data['jurusan'] ?? '-') ?></p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div>
                            <label class="text-sm text-gray-500 block mb-1">
                                <i class="fas fa-calendar-alt mr-2"></i>Tanggal Lahir
                            </label>
                            <p class="font-medium text-lg">
                                <?= $user_data['tanggal_lahir'] ? date('d F Y', strtotime($user_data['tanggal_lahir'])) : '-' ?>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500 block mb-1">
                                <i class="fas fa-user mr-2"></i>Username
                            </label>
                            <p class="font-medium text-lg"><?= htmlspecialchars($user_data['username'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Tombol Kembali dengan margin yang lebih besar -->
                <div class="mt-10 flex justify-center">
                    <a href="user_dashboard.php" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition-colors text-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
