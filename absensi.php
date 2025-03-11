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
        $stmt = $conn->prepare("DELETE FROM absensi WHERE id = ?");
        $stmt->bind_param("i", $hapus_id);
        
        if ($stmt->execute()) {
            header("Location: absensi.php?status=success&message=Data absensi berhasil dihapus");
        } else {
            header("Location: absensi.php?status=error&message=Gagal menghapus data absensi");
        }
        $stmt->close();
        exit();
    } catch (Exception $e) {
        header("Location: absensi.php?status=error&message=Error: " . $e->getMessage());
        exit();
    }
}

// Cek apakah sudah ada absensi hari ini
$query_cek_absensi = "SELECT COUNT(*) as sudah_absen 
                      FROM absensi 
                      WHERE siswa_id = ? 
                      AND DATE(tanggal) = CURRENT_DATE()";

// Tambah data absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_absensi'])) {
    $siswa_id = $_POST['siswa_id'];
    
    // Cek dulu apakah sudah absen
    $stmt_cek = $conn->prepare($query_cek_absensi);
    $stmt_cek->bind_param("i", $siswa_id);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result()->fetch_assoc();
    
    if ($result_cek['sudah_absen'] > 0) {
        echo "<script>
            alert('Siswa ini sudah melakukan absensi hari ini!');
            window.location.href='absensi.php';
        </script>";
        exit();
    }

    // Jika belum absen, lanjutkan proses absensi
    $tanggal = date('Y-m-d H:i:s');
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO absensi (siswa_id, tanggal, status) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $siswa_id, $tanggal, $status);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: absensi.php?status=success&message=Absensi berhasil ditambahkan");
        exit();
    } else {
        $stmt->close();
        header("Location: absensi.php?status=error&message=Gagal menambahkan absensi");
        exit();
    }
}

// Ambil data siswa dan absensi
$siswa_result = $conn->query("SELECT * FROM siswa");

// Get unique classes and majors for filters
$kelas_list = $conn->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas");
$jurusan_list = $conn->query("SELECT DISTINCT jurusan FROM siswa ORDER BY jurusan");

// Modify absensi query to include filter parameters
$filter_kelas = $_GET['kelas'] ?? '';
$filter_jurusan = $_GET['jurusan'] ?? '';

$query_absensi = "SELECT absensi.*, siswa.nama, siswa.kelas, siswa.jurusan 
                  FROM absensi 
                  JOIN siswa ON absensi.siswa_id = siswa.id 
                  WHERE 1=1";

if ($filter_kelas) {
    $query_absensi .= " AND siswa.kelas = '$filter_kelas'";
}
if ($filter_jurusan) {
    $query_absensi .= " AND siswa.jurusan = '$filter_jurusan'";
}

$query_absensi .= " ORDER BY absensi.tanggal DESC";
$absensi_result = $conn->query($query_absensi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Absensi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>
    <?php include 'navbar.php'; ?>

    <div class="ml-64"> 
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-black mb-6">Data Absensi</h1>

            <!-- Notifikasi -->
            <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
                <div id="notification" class="fixed top-24 right-4 w-80 p-4 mb-4 rounded-lg text-sm font-medium shadow-lg border transition-all duration-300 transform translate-x-0
                    <?php echo ($_GET['status'] == 'success') 
                        ? 'bg-green-50 text-green-800 border-green-200' 
                        : 'bg-red-50 text-red-800 border-red-200'; ?>">
                    <div class="flex items-center gap-3">
                        <?php if ($_GET['status'] == 'success'): ?>
                            <!-- Success Icon -->
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 00-1.414 1.414l2 2a1 1 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        <?php else: ?>
                            <!-- Error Icon -->
                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 101.414 1.414L10 11.414l1.293 1.293a1 1 001.414-1.414L11.414 10l1.293-1.293a1 1 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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
                    }, 3000); // Notifikasi akan hilang setelah 3 detik
                </script>
            <?php endif; ?>

            <!-- Form Tambah Absensi -->
            <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                <h2 class="text-xl font-semibold text-black mb-4">Tambah Absensi</h2>
                <form method="POST">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Pilih Siswa</label>
                            <select name="siswa_id" class="w-full p-2 border rounded mb-2 focus:ring-2 focus:ring-[#074799]" required
                                    onchange="checkAbsensiStatus(this.value)">
                                <option value="">-- Pilih Siswa --</option>
                                <?php 
                                // Reset pointer siswa_result karena mungkin sudah digunakan sebelumnya
                                $siswa_result->data_seek(0);
                                while ($siswa = $siswa_result->fetch_assoc()): 
                                    // Cek status absensi untuk setiap siswa
                                    $stmt_cek = $conn->prepare($query_cek_absensi);
                                    $stmt_cek->bind_param("i", $siswa['id']);
                                    $stmt_cek->execute();
                                    $sudah_absen = $stmt_cek->get_result()->fetch_assoc()['sudah_absen'] > 0;
                                ?>
                                    <option value="<?= $siswa['id']; ?>" <?= $sudah_absen ? 'disabled' : ''; ?>>
                                        <?= htmlspecialchars($siswa['nama']); ?>
                                        <?= $sudah_absen ? ' (Sudah Absen)' : ''; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div id="warningMessage" class="hidden mt-2 text-red-500 text-sm">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Siswa ini sudah melakukan absensi hari ini
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-2">Status Kehadiran</label>
                            <select name="status" class="w-full p-2 border rounded focus:ring-2 focus:ring-[#074799]" required>
                                <option value="Hadir">Hadir</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Izin">Izin</option>
                                <option value="Alpha">Alpha</option>
                                <option value="Terlambat">Terlambat</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="tambah_absensi" class="bg-[#074799] text-white px-4 py-2 rounded hover:bg-[#063677]">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Absensi
                    </button>
                </form>
            </div>

            <!-- Tabel Data Absensi -->
            <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-black">
                        <i class="fas fa-history text-[#074799] mr-2"></i>Riwayat Absensi
                    </h2>
                    
                    <!-- Filter Controls -->
                    <div class="flex gap-4">
                        <select id="filterKelas" onchange="applyFilters()" class="p-2 border rounded focus:ring-2 focus:ring-[#074799]">
                            <option value="">Semua Kelas</option>
                            <option value="X" <?= ($filter_kelas == 'X') ? 'selected' : '' ?>>Kelas X</option>
                            <option value="XI" <?= ($filter_kelas == 'XI') ? 'selected' : '' ?>>Kelas XI</option>
                            <option value="XII" <?= ($filter_kelas == 'XII') ? 'selected' : '' ?>>Kelas XII</option>
                        </select>

                        <select id="filterJurusan" onchange="applyFilters()" class="p-2 border rounded focus:ring-2 focus:ring-[#074799]">
                            <option value="">Semua Jurusan</option>
                            <option value="Akuntansi" <?= ($filter_jurusan == 'Akuntansi') ? 'selected' : '' ?>>Akuntansi</option>
                            <option value="Manajemen Perkantoran" <?= ($filter_jurusan == 'Manajemen Perkantoran') ? 'selected' : '' ?>>Manajemen Perkantoran</option>
                            <option value="Bisnis Ritel" <?= ($filter_jurusan == 'Bisnis Ritel') ? 'selected' : '' ?>>Bisnis Ritel</option>
                            <option value="Desain Komunikasi Visual" <?= ($filter_jurusan == 'Desain Komunikasi Visual') ? 'selected' : '' ?>>Desain Komunikasi Visual</option>
                            <option value="Rekayasa Perangkat Lunak" <?= ($filter_jurusan == 'Rekayasa Perangkat Lunak') ? 'selected' : '' ?>>Rekayasa Perangkat Lunak</option>
                        </select>
                    </div>
                </div>

                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-[#074799] text-white">
                            <th class="border p-2">No</th>
                            <th class="border p-2">Nama</th>
                            <th class="border p-2">Kelas</th>
                            <th class="border p-2">Jurusan</th>
                            <th class="border p-2">Tanggal</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($absensi_result->num_rows > 0):
                            $no = 1; 
                            while ($row = $absensi_result->fetch_assoc()): 
                        ?>
                            <tr class="<?= ($no % 2 == 0) ? 'bg-gray-50' : 'bg-white' ?> text-center">
                                <td class="border p-2"><?= $no++; ?></td>
                                <td class="border p-2"><?= htmlspecialchars($row['nama']); ?></td>
                                <td class="border p-2"><?= htmlspecialchars($row['kelas']); ?></td>
                                <td class="border p-2"><?= htmlspecialchars($row['jurusan']); ?></td>
                                <td class="border p-2"><?= date('d-m-Y H:i:s', strtotime($row['tanggal'])); ?></td>
                                <td class="border p-2">
                                    <?php
                                    $statusClasses = [
                                        'Hadir' => 'bg-green-100 text-green-800',
                                        'Sakit' => 'bg-red-100 text-red-800',
                                        'Izin' => 'bg-yellow-100 text-yellow-800',
                                        'Alpha' => 'bg-pink-100 text-pink-800',
                                        'Terlambat' => 'bg-orange-100 text-orange-800'
                                    ];
                                    $statusClass = $statusClasses[$row['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $statusClass ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td class="border p-2">
                                    <div class="flex justify-center gap-2">
                                        <a href="edit_absensi_siswa.php?id=<?= $row['id']; ?>" 
                                            class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded">Edit</a>
                                        <a href="absensi.php?hapus_id=<?= $row['id']; ?>" 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus data absensi ini?');"
                                            class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" class="border p-4 text-center text-gray-500">
                                    <i class="fas fa-info-circle mr-2"></i>Tidak ada data absensi
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function checkAbsensiStatus(siswaId) {
        if (!siswaId) return;
        const warningMessage = document.getElementById('warningMessage');
        
        fetch(`check_absensi.php?siswa_id=${siswaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.sudah_absen) {
                    warningMessage.classList.remove('hidden');
                    document.querySelector('select[name="siswa_id"]').value = '';
                } else {
                    warningMessage.classList.add('hidden');
                }
            });
    }

    function applyFilters() {
        const kelas = document.getElementById('filterKelas').value;
        const jurusan = document.getElementById('filterJurusan').value;
        
        let url = new URL(window.location.href);
        url.searchParams.set('kelas', kelas);
        url.searchParams.set('jurusan', jurusan);
        
        window.location.href = url.toString();
    }
    </script>
</body>
</html>
