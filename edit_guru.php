<?php
include 'config.php';

// Cek apakah ada ID guru yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data guru berdasarkan ID
    $result = $conn->query("SELECT * FROM guru WHERE id = $id");

    if ($result->num_rows > 0) {
        $guru = $result->fetch_assoc();
    } else {
        echo "Data tidak ditemukan!";
        exit;
    }
} else {
    echo "ID tidak ditemukan!";
    exit;
}

// Jika tombol submit ditekan, update data
if (isset($_POST['update'])) {
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $mapel = $_POST['mapel'];

    $update = $conn->query("UPDATE guru SET nama='$nama', nip='$nip', mapel='$mapel' WHERE id=$id");

    if ($update) {
        echo "<script>alert('Data guru berhasil diperbarui!'); window.location='data_guru.php';</script>";
    } else {
        echo "Gagal memperbarui data!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-2xl w-full bg-white rounded-xl shadow-lg">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#074799] to-blue-600 text-white p-6 rounded-t-xl">
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <i class="fas fa-edit"></i>Edit Data Guru
            </h2>
        </div>

        <div class="p-8">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Guru</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($guru['nama']) ?>" 
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
                        <input type="text" name="nip" value="<?= htmlspecialchars($guru['nip']) ?>" 
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mata Pelajaran</label>
                        <input type="text" name="mapel" value="<?= htmlspecialchars($guru['mapel']) ?>" 
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4">
                    <a href="data_guru.php" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
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
