<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle delete request
if (isset($_GET['hapus_id'])) {
    $hapus_id = (int)$_GET['hapus_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM guru WHERE id = ?");
        $stmt->bind_param("i", $hapus_id);
        
        if ($stmt->execute()) {
            header("Location: data_guru.php?status=success&message=Data guru berhasil dihapus");
        } else {
            header("Location: data_guru.php?status=error&message=Gagal menghapus data guru");
        }
        exit();
    } catch (Exception $e) {
        header("Location: data_guru.php?status=error&message=Error: " . $e->getMessage());
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $mapel = $_POST['mapel'];
    
    try {
        // Insert data guru
        $stmt = $conn->prepare("INSERT INTO guru (nama, nip, mapel) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $nip, $mapel);
        
        if ($stmt->execute()) {
            header("Location: data_guru.php?status=success&message=Data guru berhasil ditambahkan!");
            exit();
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        header("Location: data_guru.php?status=error&message=Gagal menambahkan data guru: " . $e->getMessage());
        exit();
    }
}

// Fetch guru data
$result = $conn->query("SELECT * FROM guru ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>
    <?php include 'navbar.php'; ?>
    
    <!-- Notifikasi -->
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
                <i class="fas fa-chalkboard-teacher mr-2"></i>Data Guru
            </h1>
            
            <!-- Form Tambah Guru -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-semibold text-black mb-4">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Guru
                </h2>
                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <input type="text" name="nama" placeholder="Nama Guru" required
                            class="w-full p-2 border rounded focus:ring-2 focus:ring-[#074799]">
                        
                        <input type="text" name="nip" placeholder="NIP" required
                            class="w-full p-2 border rounded focus:ring-2 focus:ring-[#074799]">
                            
                        <input type="text" name="mapel" placeholder="Mata Pelajaran" required
                            class="w-full p-2 border rounded focus:ring-2 focus:ring-[#074799]">
                    </div>
                    
                    <div>
                        <button type="submit" class="bg-[#074799] text-white px-6 py-2 rounded hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Tabel Data Guru -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-black mb-4">
                    <i class="fas fa-list mr-2"></i>Daftar Guru
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-[#074799] text-white">
                                <th class="border p-2">No</th>
                                <th class="border p-2">NIP</th>
                                <th class="border p-2">Nama</th>
                                <th class="border p-2">Mata Pelajaran</th>
                                <th class="border p-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr class="<?php echo ($no % 2 == 0) ? 'bg-gray-50' : 'bg-white'; ?> text-center">
                                <td class="border p-2"><?php echo $no++; ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($row['nip']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($row['mapel']); ?></td>
                                <td class="border p-2">
                                    <div class="flex justify-center gap-2">
                                        <a href="edit_guru.php?id=<?php echo $row['id']; ?>" 
                                           class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded">Edit
                                        </a>
                                        <a href="data_guru.php?hapus_id=<?php echo $row['id']; ?>" 
                                           class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data guru ini?');">Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
