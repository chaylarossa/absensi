<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

// Ambil data absensi dan siswa
$stmt = $conn->prepare("
    SELECT a.*, s.nama, s.kelas, s.jurusan 
    FROM absensi a 
    JOIN siswa s ON a.siswa_id = s.id 
    WHERE a.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result->num_rows) {
    header("Location: absensi.php");
    exit();
}

$data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $tanggal = $_POST['tanggal'];

    try {
        $update = $conn->prepare("UPDATE absensi SET status = ?, tanggal = ? WHERE id = ?");
        $update->bind_param("ssi", $status, $tanggal, $id);
        
        if ($update->execute()) {
            header("Location: absensi.php?status=success&message=Data absensi berhasil diperbarui");
            exit();
        } else {
            throw new Exception("Gagal memperbarui data");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Absensi Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-2xl w-full bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#074799] to-blue-600 p-6">
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-edit"></i>Edit Absensi Siswa
            </h2>
            <!-- Info Siswa -->
            <div class="mt-4 text-white/90 space-y-1">
                <p class="flex items-center">
                    <i class="fas fa-user-graduate w-6"></i>
                    <?= htmlspecialchars($data['nama']) ?>
                </p>
                <p class="flex items-center">
                    <i class="fas fa-school w-6"></i>
                    Kelas <?= htmlspecialchars($data['kelas']) ?>
                </p>
                <p class="flex items-center">
                    <i class="fas fa-book w-6"></i>
                    <?= htmlspecialchars($data['jurusan']) ?>
                </p>
            </div>
        </div>

        <div class="p-6">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <!-- Status Kehadiran -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-clipboard-check mr-2"></i>Status Kehadiran
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <?php
                        $status_options = [
                            'Hadir' => ['icon' => 'check-circle', 'color' => 'emerald'],
                            'Sakit' => ['icon' => 'thermometer-half', 'color' => 'red'],
                            'Izin' => ['icon' => 'envelope', 'color' => 'yellow'],
                            'Alpha' => ['icon' => 'times-circle', 'color' => 'pink'],
                            'Terlambat' => ['icon' => 'clock', 'color' => 'orange']
                        ];

                        foreach ($status_options as $status => $info):
                        ?>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="status" value="<?= $status ?>" 
                                       class="peer sr-only" <?= ($data['status'] === $status) ? 'checked' : '' ?>>
                                <div class="p-4 rounded-lg border-2 peer-checked:border-<?= $info['color'] ?>-500 
                                           peer-checked:bg-<?= $info['color'] ?>-50 hover:bg-gray-50">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-<?= $info['icon'] ?> text-<?= $info['color'] ?>-500"></i>
                                        <span class="font-medium"><?= $status ?></span>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tanggal & Waktu -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-2"></i>Tanggal & Waktu
                    </label>
                    <input type="datetime-local" name="tanggal" 
                           value="<?= date('Y-m-d\TH:i', strtotime($data['tanggal'])) ?>" 
                           class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex justify-end gap-4">
                    <a href="absensi.php" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
