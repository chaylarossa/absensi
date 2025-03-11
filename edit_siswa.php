<?php
session_start();
include 'config.php';

// Cek session admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil ID siswa dari URL
$id = $_GET['id'] ?? 0;

// Ambil data siswa berdasarkan ID
$stmt = $conn->prepare("SELECT * FROM siswa WHERE id = ?"); // Fixed query
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Jika data tidak ditemukan
if (!$data) {
    header("Location: siswa.php");
    exit();
}

// Jika form disubmit (update data)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];

    try {
        // Cek apakah ada foto baru yang diupload
        if (!empty($_FILES['foto']['name'])) {
            $foto = basename($_FILES['foto']['name']);
            $target_dir = "uploads/";
            $target_file = $target_dir . $foto;
            
            // Hapus foto lama jika ada
            if (!empty($data['foto']) && file_exists($target_dir . $data['foto'])) {
                unlink($target_dir . $data['foto']);
            }
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                // Update dengan foto baru
                $stmt = $conn->prepare("UPDATE siswa SET nama=?, tanggal_lahir=?, kelas=?, jurusan=?, foto=? WHERE id=?");
                $stmt->bind_param("sssssi", $nama, $tanggal_lahir, $kelas, $jurusan, $foto, $id);
            } else {
                throw new Exception("Gagal mengunggah foto.");
            }
        } else {
            // Update tanpa mengubah foto
            $stmt = $conn->prepare("UPDATE siswa SET nama=?, tanggal_lahir=?, kelas=?, jurusan=? WHERE id=?");
            $stmt->bind_param("ssssi", $nama, $tanggal_lahir, $kelas, $jurusan, $id);
        }

        if ($stmt->execute()) {
            header("Location: siswa.php?status=success&message=Data berhasil diperbarui");
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
    <title>Edit Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-2xl w-full bg-white rounded-xl shadow-lg">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#074799] to-blue-600 text-white p-6 rounded-t-xl">
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <i class="fas fa-user-edit"></i>
                Edit Data Siswa
            </h2>
        </div>

        <!-- Form Content -->
        <div class="p-8">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Nama Siswa</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" 
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?= $data['tanggal_lahir'] ?>" 
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Kelas</label>
                        <input type="text" name="kelas" value="<?= htmlspecialchars($data['kelas']) ?>" 
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Jurusan</label>
                        <input type="text" name="jurusan" value="<?= htmlspecialchars($data['jurusan']) ?>" 
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Foto Baru (Opsional)</label>
                    <input type="file" name="foto" accept="image/*" 
                           class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <?php if (!empty($data['foto'])): ?>
                    <div class="flex items-center gap-4">
                        <label class="block text-gray-700">Foto Saat Ini:</label>
                        <img src="uploads/<?= htmlspecialchars($data['foto']) ?>" 
                             alt="Foto <?= htmlspecialchars($data['nama']) ?>"
                             class="w-24 h-24 object-cover rounded-lg border-2 border-gray-200">
                    </div>
                <?php endif; ?>

                <div class="flex justify-end gap-4 pt-4">
                    <a href="siswa.php" 
                       class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
