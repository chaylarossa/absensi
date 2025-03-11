<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); 
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle delete request
if (isset($_GET['hapus_id'])) {
    $hapus_id = (int)$_GET['hapus_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM absensi_guru WHERE id = ?");
        $stmt->bind_param("i", $hapus_id);
        
        if ($stmt->execute()) {
            header("Location: absensi_guru.php?status=success&message=Data absensi berhasil dihapus");
        } else {
            header("Location: absensi_guru.php?status=error&message=Gagal menghapus data absensi");
        }
        $stmt->close();
        exit();
    } catch (Exception $e) {
        header("Location: absensi_guru.php?status=error&message=Error: " . $e->getMessage());
        exit();
    }
}

// Ambil data guru untuk dropdown
$guru_result = $conn->query("SELECT * FROM guru ORDER BY nama ASC");

// Cek apakah sudah ada absensi hari ini
$query_cek_absensi = "SELECT COUNT(*) as sudah_absen 
                      FROM absensi_guru 
                      WHERE guru_id = ? 
                      AND DATE(tanggal) = CURRENT_DATE()";

// Tambah data absensi guru
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $guru_id = $_POST['guru_id'];
    
    // Cek dulu apakah sudah absen
    $stmt_cek = $conn->prepare($query_cek_absensi);
    $stmt_cek->bind_param("i", $guru_id);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result()->fetch_assoc();
    
    if ($result_cek['sudah_absen'] > 0) {
        header("Location: absensi_guru.php?status=error&message=Guru ini sudah melakukan absensi hari ini!");
        exit();
    }

    $tanggal = date('Y-m-d H:i:s');
    $status = $_POST['status'];
    
    try {
        if (empty($guru_id)) {
            throw new Exception("Pilih guru terlebih dahulu!");
        }

        $stmt = $conn->prepare("INSERT INTO absensi_guru (guru_id, tanggal, status) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("iss", $guru_id, $tanggal, $status);
        
        if ($stmt->execute()) {
            header("Location: absensi_guru.php?status=success&message=Data absensi guru berhasil ditambahkan!");
            exit();
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        header("Location: absensi_guru.php?status=error&message=" . $e->getMessage());
        exit();
    }
}

// Ambil data absensi (dipindah ke sini agar selalu mengambil data terbaru)
$query = "
    SELECT ag.id AS absensi_id, ag.guru_id, ag.tanggal, ag.status, g.nama 
    FROM absensi_guru ag 
    JOIN guru g ON ag.guru_id = g.id 
    ORDER BY ag.tanggal DESC
";

$absensi_result = $conn->query($query);
if (!$absensi_result) {
    die("Error fetching attendance data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>
    <?php include 'navbar.php'; ?>
    
    <!-- Add Notification -->
    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        <div id="notification" class="fixed top-24 right-4 w-80 p-4 mb-4 rounded-lg text-sm font-medium shadow-lg border transition-all duration-300 transform translate-x-0
            <?php echo ($_GET['status'] == 'success') 
                ? 'bg-green-50 text-green-800 border-green-200' 
                : 'bg-red-50 text-red-800 border-red-200'; ?>">
            <div class="flex items-center gap-3">
                <?php if ($_GET['status'] == 'success'): ?>
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                <?php else: ?>
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                <?php endif; ?>
                <p><?= htmlspecialchars($_GET['message']); ?></p>
            </div>
        </div>

        <script>
            setTimeout(function() {
                var notif = document.getElementById('notification');
                if (notif) {
                    notif.style.opacity = '0';
                    notif.style.transform = 'translateX(100%)';
                    setTimeout(() => notif.style.display = 'none', 300);
                }
            }, 3000);
        </script>
    <?php endif; ?>

    <div class="ml-64">
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-black mb-6">
                <i class="fas fa-user-check mr-2"></i>Absensi Guru
            </h1>
            
            <!-- Form Tambah Absensi Guru -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-black mb-4">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Absensi
                </h2>
                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Pilih Guru</label>
                            <select name="guru_id" class="w-full p-2 border rounded mb-2 focus:ring-2 focus:ring-[#074799]" required
                                    onchange="checkAbsensiStatus(this.value)">
                                <option value="">-- Pilih Guru --</option>
                                <?php 
                                $guru_result->data_seek(0);
                                while ($guru = $guru_result->fetch_assoc()): 
                                    $stmt_cek = $conn->prepare($query_cek_absensi);
                                    $stmt_cek->bind_param("i", $guru['id']);
                                    $stmt_cek->execute();
                                    $sudah_absen = $stmt_cek->get_result()->fetch_assoc()['sudah_absen'] > 0;
                                ?>
                                    <option value="<?= $guru['id']; ?>" <?= $sudah_absen ? 'disabled' : ''; ?>>
                                        <?= htmlspecialchars($guru['nama'] . ' - ' . $guru['mapel']); ?>
                                        <?= $sudah_absen ? ' (Sudah Absen)' : ''; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2">Status Kehadiran</label>
                            <select name="status" class="w-full p-2 border rounded mb-2 focus:ring-2 focus:ring-[#074799]" required>
                                <option value="Hadir">Hadir</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Izin">Izin</option>
                                <option value="Alpha">Alpha</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-[#074799] text-white px-4 py-2 rounded hover:bg-blue-800">
                        <i class="fas fa-save mr-2"></i>Tambah Absensi
                    </button>
                </form>
            </div>
            
            <!-- Tabel Data Absensi Guru -->
            <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                <h2 class="text-xl font-semibold text-black mb-4">
                    <i class="fas fa-history mr-2"></i>Riwayat Absensi Guru
                </h2>
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-[#074799] text-white">
                            <th class="border p-2">No</th>
                            <th class="border p-2">Nama</th>
                            <th class="border p-2">Tanggal</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = $absensi_result->fetch_assoc()): ?>
                        <tr class="<?php echo ($no % 2 == 0) ? 'bg-white-100' : 'bg-white'; ?> text-center">
                            <td class="border p-2"><?php echo $no++; ?></td>
                            <td class="border p-2"><?php echo $row['nama']; ?></td>
                            <td class="border p-2"><?php echo date('d-m-Y H:i:s', strtotime($row['tanggal'])); ?></td>
                            <td class="border p-2">
                                <?php
                                $statusClasses = [
                                    'Hadir' => 'bg-green-100 text-green-800',
                                    'Sakit' => 'bg-red-100 text-red-800',
                                    'Izin' => 'bg-yellow-100 text-yellow-800',
                                    'Alpha' => 'bg-pink-100 text-pink-800'
                                ];
                                $statusClass = $statusClasses[$row['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-3 py-1 rounded-full text-sm font-medium <?= $statusClass ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td class="border p-2">
                                <div class="flex justify-center gap-2">
                                    <a href="edit_absensi_guru.php?id=<?= $row['absensi_id']; ?>" 
                                        class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded">Edit</a>
                                    <a href="absensi_guru.php?hapus_id=<?= $row['absensi_id']; ?>" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus data absensi ini?');"
                                        class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add this JavaScript -->
    <script>
    function checkAbsensiStatus(guruId) {
        if (!guruId) return;
        
        fetch(`check_absensi_guru.php?guru_id=${guruId}`)
            .then(response => response.json())
            .then(data => {
                if (data.sudah_absen) {
                    alert('Guru ini sudah melakukan absensi hari ini!');
                    document.querySelector('select[name="guru_id"]').value = '';
                }
            });
    }
    </script>
</body>
</html>
