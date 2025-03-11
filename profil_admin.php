<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data admin
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

// Set foto profil default jika tidak ada
$foto_admin = "uploads/background oca.jpg";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-8">
    <div class="max-w-2xl w-full bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#074799] to-blue-600 text-white p-8">
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <img src="<?= htmlspecialchars($foto_admin); ?>" 
                         alt="Foto Profil" 
                         class="w-28 h-28 rounded-full border-4 border-white shadow-lg object-cover">
                    <div class="absolute bottom-0 right-0 bg-green-500 p-2 rounded-full border-4 border-white">
                        <i class="fas fa-check text-white text-xs"></i>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold mb-1"><?= htmlspecialchars($admin['nama']); ?></h1>
                    <p class="text-blue-100 flex items-center">
                        <i class="fas fa-user-shield mr-2"></i>Administrator
                    </p>
                </div>
            </div>
        </div>

        <!-- Info Detail -->
        <div class="p-8">
            <div class="grid grid-cols-1 gap-6">
                <div class="space-y-6">
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">
                            <i class="fas fa-envelope mr-2"></i>Email
                        </label>
                        <p class="font-medium text-lg"><?= htmlspecialchars($admin['email'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">
                            <i class="fas fa-user mr-2"></i>Username
                        </label>
                        <p class="font-medium text-lg"><?= htmlspecialchars($admin['username']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Tombol Kembali -->
            <div class="mt-8 flex justify-center">
                <a href="index.php" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition-colors text-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
