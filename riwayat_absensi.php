<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Get user and siswa data dengan JOIN yang benar
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

if (!isset($user_data['siswa_id'])) {
    header("Location: user_dashboard.php");
    exit();
}

$siswa_id = $user_data['siswa_id'];

// Hitung statistik dalam satu query
$stats_query = "SELECT 
    SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
    SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
    SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
    SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as alpha,
    SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as terlambat
FROM absensi WHERE siswa_id = ?";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $siswa_id);
$stmt->execute();
$stats_result = $stmt->get_result()->fetch_assoc();

$stats = [
    'Hadir' => $stats_result['hadir'] ?? 0,
    'Sakit' => $stats_result['sakit'] ?? 0,
    'Izin' => $stats_result['izin'] ?? 0,
    'Alpha' => $stats_result['alpha'] ?? 0,
    'Terlambat' => $stats_result['terlambat'] ?? 0
];

// Ambil riwayat absensi dalam query terpisah
$query = "SELECT * FROM absensi WHERE siswa_id = ? ORDER BY tanggal DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $siswa_id);
$stmt->execute();
$riwayat_absensi = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Include sidebar dan navbar -->
    <?php include 'sidebar_user.php'; ?>
    <?php include 'navbar_user.php'; ?>
    
    <!-- Main Content dengan penyesuaian margin top untuk navbar -->
    <div class="ml-64 mt-8 p-10">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#074799] to-blue-600 text-white p-6 rounded-xl shadow-lg mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        <i class="fas fa-history mr-3"></i>Riwayat Absensi
                    </h1>
                    <p class="text-blue-100">
                        <i class="fas fa-user mr-2"></i><?= htmlspecialchars($user_data['siswa_nama'] ?? 'Siswa') ?> - 
                        Kelas <?= htmlspecialchars($user_data['kelas'] ?? '-') ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">
                    <i class="fas fa-table mr-2 text-[#074799]"></i>Detail Riwayat Absensi
                </h2>
                <!-- Export button if needed -->
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-3 text-left">No</th>
                            <th class="p-3 text-left">Tanggal</th>
                            <th class="p-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = $riwayat_absensi->fetch_assoc()): 
                        ?>
                        <tr class="border-t">
                            <td class="p-3"><?= $no++ ?></td>
                            <td class="p-3"><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td class="p-3">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    <?php
                                    switch($row['status']) {
                                        case 'Hadir':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'Sakit':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        case 'Izin':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'Terlambat':
                                            echo 'bg-orange-100 text-orange-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
