<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Belum Terhubung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full mx-4">
        <div class="text-center mb-6">
            <i class="fas fa-exclamation-triangle text-5xl text-yellow-500 mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-800">Akun Belum Terkonfigurasi</h1>
        </div>
        
        <div class="text-gray-600 mb-6">
            <p class="mb-4">Akun Anda belum terhubung dengan data siswa. Silakan hubungi administrator untuk mengaktifkan akun Anda.</p>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <p class="text-sm">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informasi yang diperlukan:
                </p>
                <ul class="list-disc list-inside ml-4 text-sm">
                    <li>Username: <?= htmlspecialchars($_SESSION['username'] ?? '') ?></li>
                    <li>User ID: <?= htmlspecialchars($_SESSION['user_id'] ?? '') ?></li>
                </ul>
            </div>
        </div>
        
        <div class="flex justify-center gap-4">
            <a href="logout.php" class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </div>
    </div>
</body>
</html>
