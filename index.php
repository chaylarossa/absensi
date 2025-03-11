<?php
session_start();
include 'config.php';

// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set zona waktu dan tanggal hari ini
date_default_timezone_set('Asia/Jakarta');
$tanggal_hari_ini = date('Y-m-d');

// --- Menghitung Statistik Hari Ini ---

// Total siswa
$total_siswa = $conn->query("SELECT COUNT(*) AS total FROM siswa")->fetch_assoc()['total'];

// Statistik absensi berdasarkan status untuk hari ini
$query_status = "SELECT 
    SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
    SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
    SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
    SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as alpha,
    SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as terlambat
FROM absensi 
WHERE DATE(tanggal) = ?";

$stmt = $conn->prepare($query_status);
$stmt->bind_param("s", $tanggal_hari_ini);
$stmt->execute();
$result_status = $stmt->get_result()->fetch_assoc();

$hadir = $result_status['hadir'] ?? 0;
$sakit = $result_status['sakit'] ?? 0;
$izin = $result_status['izin'] ?? 0;
$alpha = $result_status['alpha'] ?? 0;
$terlambat = $result_status['terlambat'] ?? 0;

// --- Mengambil Data Siswa Tidak Hadir Hari Ini ---
$query_tidakhadir = "
    SELECT s.nama, s.kelas,
    COALESCE(a.status, 'Belum Diabsen') as status
    FROM siswa s
    LEFT JOIN (
        SELECT * FROM absensi 
        WHERE DATE(tanggal) = ?
    ) a ON s.id = a.siswa_id
    WHERE a.status IS NULL 
    OR a.status IN ('Sakit', 'Izin', 'Alpha', 'Terlambat')
    ORDER BY s.kelas, s.nama";

$stmt = $conn->prepare($query_tidakhadir);
$stmt->bind_param("s", $tanggal_hari_ini);
$stmt->execute();
$tidak_hadir_hari_ini = $stmt->get_result();

// --- Data Grafik Absensi 7 Hari Terakhir ---
$labels = [];
$data_hadir = [];
$data_sakit = [];
$data_izin = [];
$data_alpha = [];
$data_terlambat = [];

$query_grafik = "
    SELECT 
        DATE(tanggal) as tgl,
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as alpha,
        SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as terlambat
    FROM absensi 
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(tanggal)
    ORDER BY DATE(tanggal)";

$result_grafik = $conn->query($query_grafik);
$data_by_date = [];

while ($row = $result_grafik->fetch_assoc()) {
    $data_by_date[$row['tgl']] = $row;
}

for ($i = 6; $i >= 0; $i--) {
    $tanggal = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($tanggal));

    if (isset($data_by_date[$tanggal])) {
        $data_hadir[] = (int)$data_by_date[$tanggal]['hadir'];
        $data_sakit[] = (int)$data_by_date[$tanggal]['sakit'];
        $data_izin[] = (int)$data_by_date[$tanggal]['izin'];
        $data_alpha[] = (int)$data_by_date[$tanggal]['alpha'];
        $data_terlambat[] = (int)$data_by_date[$tanggal]['terlambat'];
    } else {
        $data_hadir[] = 0;
        $data_sakit[] = 0;
        $data_izin[] = 0;
        $data_alpha[] = 0;
        $data_terlambat[] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'sidebar.php'; ?>
    <?php include 'navbar.php'; ?>
    
    <div class="ml-64 p-8">
        <!-- Header Welcome -->
        <div class="bg-gradient-to-r from-[#074799] to-blue-600 text-white p-6 rounded-lg shadow-lg mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        <i class="fas fa-solar-panel mr-3"></i>Dashboard Admin
                    </h1>
                    <p class="flex items-center text-blue-100">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <?php echo date('l, d F Y'); ?>
                        <span class="mx-2">•</span>
                        <i class="far fa-clock mr-2"></i>
                        <span id="jam"><?php echo date('H:i'); ?></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <!-- Total Siswa -->
            <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 p-4 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex-1">
                        <p class="text-blue-100 text-sm font-medium">Total Siswa</p>
                        <h3 class="text-white text-2xl font-bold mt-1"><?= $total_siswa ?></h3>
                    </div>
                    <div class="bg-blue-400/30 p-3 rounded-lg">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>
                <div class="text-blue-100 text-xs">Keseluruhan Data</div>
            </div>

            <!-- Hadir -->
            <div class="stat-card bg-gradient-to-br from-emerald-500 to-emerald-600 p-4 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex-1">
                        <p class="text-emerald-100 text-sm font-medium">Hadir</p>
                        <h3 class="text-white text-2xl font-bold mt-1"><?= $hadir ?></h3>
                    </div>
                    <div class="bg-emerald-400/30 p-3 rounded-lg">
                        <i class="fas fa-check-circle text-white text-xl"></i>
                    </div>
                </div>
                <div class="text-emerald-100 text-xs">Hari Ini</div>
            </div>

            <!-- Sakit -->
            <div class="stat-card bg-gradient-to-br from-red-500 to-red-600 p-4 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex-1">
                        <p class="text-red-100 text-sm font-medium">Sakit</p>
                        <h3 class="text-white text-2xl font-bold mt-1"><?= $sakit ?></h3>
                    </div>
                    <div class="bg-red-400/30 p-3 rounded-lg">
                        <i class="fas fa-thermometer-half text-white text-xl"></i>
                    </div>
                </div>
                <div class="text-red-100 text-xs">Hari Ini</div>
            </div>

            <!-- Izin -->
            <div class="stat-card bg-gradient-to-br from-yellow-500 to-yellow-600 p-4 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex-1">
                        <p class="text-yellow-100 text-sm font-medium">Izin</p>
                        <h3 class="text-white text-2xl font-bold mt-1"><?= $izin ?></h3>
                    </div>
                    <div class="bg-yellow-400/30 p-3 rounded-lg">
                        <i class="fas fa-envelope text-white text-xl"></i>
                    </div>
                </div>
                <div class="text-yellow-100 text-xs">Hari Ini</div>
            </div>

            <!-- Terlambat -->
            <div class="stat-card bg-gradient-to-br from-orange-500 to-orange-600 p-4 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex-1">
                        <p class="text-orange-100 text-sm font-medium">Terlambat</p>
                        <h3 class="text-white text-2xl font-bold mt-1"><?= $terlambat ?></h3>
                    </div>
                    <div class="bg-orange-400/30 p-3 rounded-lg">
                        <i class="fas fa-clock text-white text-xl"></i>
                    </div>
                </div>
                <div class="text-orange-100 text-xs">Hari Ini</div>
            </div>

            <!-- Alpha -->
            <div class="stat-card bg-gradient-to-br from-pink-500 to-pink-600 p-4 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex-1">
                        <p class="text-pink-100 text-sm font-medium">Alpha</p>
                        <h3 class="text-white text-2xl font-bold mt-1"><?= $alpha ?></h3>
                    </div>
                    <div class="bg-pink-400/30 p-3 rounded-lg">
                        <i class="fas fa-times-circle text-white text-xl"></i>
                    </div>
                </div>
                <div class="text-pink-100 text-xs">Hari Ini</div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Grafik Absensi -->
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-chart-line text-[#074799] mr-3"></i>
                        Grafik Kehadiran Siswa 7 Hari Terakhir
                    </h2>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <canvas id="chartAbsensi"></canvas>
                    </div>
                </div>
            </div>

            <!-- Siswa Tidak Hadir -->
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-user-slash text-[#074799] mr-3"></i>
                        Ketidakhadiran Hari Ini
                    </h2>
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-[#074799] to-blue-600 text-white">
                                    <th class="py-3 px-4 text-left">#</th>
                                    <th class="py-3 px-4 text-left">Nama</th>
                                    <th class="py-3 px-4 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($tidak_hadir_hari_ini->num_rows > 0): ?>
                                    <?php $no = 1; while ($row = $tidak_hadir_hari_ini->fetch_assoc()): ?>
                                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                                            <td class="py-3 px-4"><?= $no++ ?></td>
                                            <td class="py-3 px-4"><?= htmlspecialchars($row['nama']) ?></td>
                                            <td class="py-3 px-4">
                                                <?php
                                                $status_colors = [
                                                    'Sakit' => 'bg-red-100 text-red-800',
                                                    'Izin' => 'bg-yellow-100 text-yellow-800',
                                                    'Alpha' => 'bg-red-100 text-red-800',
                                                    'Belum Diabsen' => 'bg-gray-100 text-gray-800'
                                                ];
                                                $color = $status_colors[$row['status']] ?? 'bg-gray-100 text-gray-800';
                                                ?>
                                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $color ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-gray-500">
                                            <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                                            <p>Semua siswa hadir hari ini</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time clock update
        function updateClock() {
            const now = new Date();
            document.getElementById('jam').textContent = 
                now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0') + ':' +
                now.getSeconds().toString().padStart(2, '0');
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Chart configuration
        const ctx = document.getElementById('chartAbsensi').getContext('2d');
        const chartAbsensi = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels); ?>,
                datasets: [
                    { 
                        label: 'Hadir',
                        data: <?= json_encode($data_hadir); ?>,
                        backgroundColor: '#10B981',
                        borderRadius: 6
                    },
                    { 
                        label: 'Sakit',
                        data: <?= json_encode($data_sakit); ?>,
                        backgroundColor: '#EF4444',
                        borderRadius: 6
                    },
                    { 
                        label: 'Izin',
                        data: <?= json_encode($data_izin); ?>,
                        backgroundColor: '#F59E0B',
                        borderRadius: 6
                    },
                    { 
                        label: 'Alpha',
                        data: <?= json_encode($data_alpha); ?>,
                        backgroundColor: '#DC2626',
                        borderRadius: 6
                    },
                    { 
                        label: 'Terlambat',
                        data: <?= json_encode($data_terlambat); ?>,
                        backgroundColor: '#F97316',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
