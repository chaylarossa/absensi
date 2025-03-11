<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Get user data with siswa info
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

// If no siswa_id is set, show error page
if (!isset($user_data['siswa_id']) || empty($user_data['siswa_id'])) {
    include 'default.php';
    exit();
}

$siswa_id = $user_data['siswa_id'];

// Get siswa data
$siswa_query = "SELECT * FROM siswa WHERE id = ?";
$stmt = $conn->prepare($siswa_query);
$stmt->bind_param("i", $siswa_id);
$stmt->execute();
$siswa_data = $stmt->get_result()->fetch_assoc();

date_default_timezone_set('Asia/Jakarta');
$tanggal_hari_ini = date('Y-m-d');

// Tambah absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $tanggal = date('Y-m-d H:i:s');

    try {
        $stmt = $conn->prepare("INSERT INTO absensi (siswa_id, status, tanggal) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $siswa_id, $status, $tanggal);
        
        if ($stmt->execute()) {
            echo "<script>alert('Absensi berhasil ditambahkan!');</script>";
        } else {
            throw new Exception("Gagal menambah absensi");
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

// Ambil riwayat absensi siswa
$query = "SELECT id, tanggal, status 
          FROM absensi 
          WHERE siswa_id = ? 
          ORDER BY tanggal DESC 
          LIMIT 10";
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
    <title>Dashboard User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <?php include 'sidebar_user.php'; ?>
    
    <!-- Navbar -->
    <?php include 'navbar_user.php'; ?>
    
    <!-- Main Content dengan padding top untuk navbar -->
    <div class="ml-64 pt-8"> <!-- Tambahkan pt-16 untuk memberi ruang pada navbar fixed -->
        <!-- Header -->
         <div class="ml-55 p-8">
        <div class="bg-gradient-to-r from-[#074799] to-blue-600 text-white p-6 rounded-xl shadow-lg mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        <i class="fas fa-user-check mr-3"></i>Absensi Hari Ini
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

        <!-- Form Absensi -->
        <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                <i class="fas fa-clipboard-check text-[#074799]"></i>
                Form Absensi
            </h2>

            <?php
            // Check if student already has attendance today
            $check_query = "SELECT COUNT(*) as sudah_absen FROM absensi 
                           WHERE siswa_id = ? AND DATE(tanggal) = CURRENT_DATE()";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("i", $siswa_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $sudah_absen = $result->fetch_assoc()['sudah_absen'] > 0;
            ?>

            <?php if ($sudah_absen): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-red-700">
                                Maaf anda tidak bisa menambahkan absen lagi pada hari ini
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Status Kehadiran</label>
                        <select name="status" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[#074799]" required>
                            <option value="Hadir">Hadir</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Izin">Izin</option>
                            <option value="Terlambat">Terlambat</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-[#074799] text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Simpan Absensi
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Riwayat Absensi -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                <i class="fas fa-history text-[#074799]"></i>
                Riwayat Absensi Terakhir
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-3 text-left">Tanggal</th>
                            <th class="p-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $riwayat_absensi->fetch_assoc()): ?>
                            <tr class="border-t">
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
    </script>
</body>
</html>
