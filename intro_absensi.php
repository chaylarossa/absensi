<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deskripsi Web Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-3xl bg-white p-8 rounded-lg shadow-lg text-center">
        <h1 class="text-3xl font-bold text-[#074799]">Selamat Datang di Sistem Absensi Siswa</h1>
        <p class="mt-4 text-gray-600 text-lg">
            Web absensi ini dirancang untuk mempermudah pencatatan kehadiran siswa secara digital. Dengan fitur unggulan seperti upload foto siswa, navigasi yang intuitif, serta laporan kehadiran yang terstruktur, sistem ini memastikan proses absensi lebih efisien dan akurat.
        </p>
        <div class="mt-6 flex justify-center space-x-4">
            <div class="bg-blue-100 p-4 rounded-lg w-1/3 shadow-md">
                <i class="fas fa-user-check text-3xl text-blue-600"></i>
                <p class="mt-2 text-gray-700 font-semibold">Pencatatan Kehadiran</p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg w-1/3 shadow-md">
                <i class="fas fa-file-alt text-3xl text-green-600"></i>
                <p class="mt-2 text-gray-700 font-semibold">Laporan Terstruktur</p>
            </div>
            <div class="bg-yellow-100 p-4 rounded-lg w-1/3 shadow-md">
                <i class="fas fa-camera text-3xl text-yellow-600"></i>
                <p class="mt-2 text-gray-700 font-semibold">Upload Foto</p>
            </div>
        </div>
        <p class="mt-4 text-gray-600">Silakan lanjutkan ke halaman login untuk mulai menggunakan sistem ini.</p>
        <a href="login.php" class="mt-6 inline-block bg-[#074799] text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-sign-in-alt mr-2"></i>Masuk
        </a>
    </div>
</body>
</html>
