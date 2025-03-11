<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Cek apakah ada parameter id
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    // Ambil data siswa dan riwayat absensi
    $stmt = $conn->prepare("SELECT * FROM siswa WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $siswa = $stmt->get_result()->fetch_assoc();

    // Ambil riwayat absensi
    $stmt = $conn->prepare("
        SELECT tanggal, status
        FROM absensi
        WHERE siswa_id = ?
        ORDER BY tanggal DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $riwayat_absensi = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-8">
    <!-- Main Content Area -->
    <div class="max-w-4xl w-full bg-gray-50 rounded-xl shadow-lg p-6">
        <?php if ($siswa): ?>
            <!-- Header Card -->
            <div class="bg-gradient-to-r from-[#074799] to-blue-600 rounded-xl shadow-lg p-6 text-white mb-6 transform hover:scale-[1.01] transition-transform">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <?php if (!empty($siswa['foto']) && file_exists("uploads/" . $siswa['foto'])): ?>
                            <img src="uploads/<?= htmlspecialchars($siswa['foto']); ?>" 
                                 alt="Foto <?= htmlspecialchars($siswa['nama']); ?>"
                                 class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                        <?php else: ?>
                            <img src="uploads/default.png" 
                                 alt="Default"
                                 class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                        <?php endif; ?>
                        <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 rounded-full border-4 border-white"></div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($siswa['nama']); ?></h1>
                        <p class="text-blue-100 flex items-center">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            Kelas <?= htmlspecialchars($siswa['kelas']); ?> - 
                            <?= htmlspecialchars($siswa['jurusan']); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Detail Siswa -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <h2 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-user text-[#074799] mr-2"></i>
                        Informasi Siswa
                    </h2>
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <label class="text-sm text-gray-500">Nama Lengkap</label>
                            <p class="font-medium"><?= htmlspecialchars($siswa['nama']); ?></p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <label class="text-sm text-gray-500">Tanggal Lahir</label>
                            <p class="font-medium"><?= htmlspecialchars($siswa['tanggal_lahir']); ?></p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <label class="text-sm text-gray-500">Kelas</label>
                            <p class="font-medium"><?= htmlspecialchars($siswa['kelas']); ?></p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <label class="text-sm text-gray-500">Jurusan</label>
                            <p class="font-medium"><?= htmlspecialchars($siswa['jurusan']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Absensi -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">
                        <i class="fas fa-history text-[#074799] mr-2"></i>
                        Riwayat Absensi Terakhir
                    </h2>
                    <div class="overflow-hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left">Tanggal</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $riwayat_absensi->fetch_assoc()): ?>
                                <tr class="border-t">
                                    <td class="px-4 py-2">
                                        <?= date('d/m/Y', strtotime($row['tanggal'])); ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
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

            <!-- Tombol Kembali -->
            <div class="flex justify-center mt-8">
                <a href="siswa.php" class="bg-[#074799] hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Daftar Siswa
                </a>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-exclamation-circle text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg mb-4">Data siswa tidak ditemukan.</p>
                <a href="siswa.php" class="text-[#074799] hover:underline">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Siswa
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close();
?>
