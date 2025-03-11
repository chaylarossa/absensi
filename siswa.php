<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Jika form disubmit (menambahkan siswa baru)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama         = isset($_POST['nama']) ? $_POST['nama'] : '';
    $tanggal_lahir = isset($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : '';
    $kelas        = isset($_POST['kelas']) ? $_POST['kelas'] : '';
    $jurusan      = isset($_POST['jurusan']) ? $_POST['jurusan'] : '';

    // Upload foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto        = basename($_FILES['foto']['name']);
        $target_dir  = "uploads/";
        $target_file = $target_dir . $foto;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO siswa (nama, tanggal_lahir, kelas, jurusan, foto) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nama, $tanggal_lahir, $kelas, $jurusan, $foto);
            if ($stmt->execute()) {
                header("Location: siswa.php?status=success&message=Data siswa berhasil ditambahkan!");
                exit();
            } else {
                header("Location: siswa.php?status=error&message=Gagal menambahkan data siswa!");
                exit();
            }
            $stmt->close();
        } else {
            header("Location: siswa.php?status=error&message=Gagal mengunggah foto!");
            exit();
        }
    } else {
        header("Location: siswa.php?status=error&message=Foto belum dipilih atau terjadi kesalahan!");
        exit();
    }
}

// Ambil data siswa
$result = $conn->query("SELECT * FROM siswa ORDER BY kelas, nama");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <!-- Success Icon -->
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 00-1.414 1.414l2 2a1 1 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                <?php else: ?>
                    <!-- Error Icon -->
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 101.414 1.414L10 11.414l1.293 1.293a1 1 001.414-1.414L11.414 10l1.293-1.293a1 1 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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
            <h1 class="text-3xl font-bold text-black mb-6">Data Siswa</h1>
            
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-semibold text-black mb-4">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Siswa
                </h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <input type="text" name="nama" placeholder="Nama Siswa" 
                                class="w-full p-2 border rounded focus:ring-2 focus:ring-[#074799]" required>
                        </div>
                        <div>
                            <input type="date" name="tanggal_lahir" 
                                class="w-full p-2 border rounded focus:ring-2 focus:ring-[#074799]" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Kelas</label>
                            <select name="kelas" id="kelasSelect" class="w-full p-2 border rounded focus:ring-2 focus:ring-[#074799]" required>
                                <option value="">Pilih Kelas</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Jurusan</label>
                            <select name="jurusan" id="jurusanSelect" class="w-full p-2 border rounded focus:ring-2 focus:ring-[#074799]" required>
                                <option value="">Pilih Jurusan</option>
                                <option value="Akuntansi">Akuntansi</option>
                                <option value="Rekayasa Perangkat Lunak">Rekayasa Perangkat Lunak</option>
                                <option value="Desain Komunikasi Visual">Desain Komunikasi Visual</option>
                                <option value="Manajemen Perkantoran">Manajemen Perkantoran</option>
                                <option value="Bisnis Ritel">Bisnis Ritel</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Foto Siswa</label>
                        <input type="file" name="foto" accept="image/*" 
                            class="w-full p-2 border rounded focus:ring-2 focus:ring-[#074799]" required>
                    </div>

                    <div>
                        <button type="submit" class="bg-[#074799] text-white px-4 py-2 rounded hover:bg-[#052f63]">
                            <i class="fas fa-plus mr-2"></i>Tambah Siswa
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-black">
                        <i class="fas fa-list text-[#074799] mr-2"></i>Daftar Siswa
                    </h2>
                    <!-- Filter Dropdowns -->
                    <div class="flex items-center gap-4">
                        <select id="filterKelas" class="p-2 border rounded focus:ring-2 focus:ring-[#074799]">
                            <option value="">Semua Kelas</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>

                        <select id="filterJurusan" class="p-2 border rounded focus:ring-2 focus:ring-[#074799]">
                            <option value="">Semua Jurusan</option>
                            <option value="Akuntansi">Akuntansi</option>
                            <option value="Rekayasa Perangkat Lunak">Rekayasa Perangkat Lunak</option>
                            <option value="Desain Komunikasi Visual">Desain Komunikasi Visual</option>
                            <option value="Manajemen Perkantoran">Manajemen Perkantoran</option>
                            <option value="Bisnis Ritel">Bisnis Ritel</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <!-- Search input -->
                    <div class="relative mb-4">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class="fas fa-search text-gray-500"></i>
                        </span>
                        <input type="text" 
                               id="searchInput" 
                               class="pl-10 pr-4 py-2 w-full border rounded-lg focus:ring-2 focus:ring-[#074799]" 
                               placeholder="Cari nama siswa, kelas, atau jurusan...">
                    </div>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-[#074799] text-white">
                                <th class="border p-2">No</th>
                                <th class="border p-2">Nama</th>
                                <th class="border p-2">Tanggal Lahir</th>
                                <th class="border p-2">Kelas</th>
                                <th class="border p-2">Jurusan</th>
                                <th class="border p-2">Foto</th>
                                <th class="border p-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            <?php 
                            $no = 1; 
                            while ($row = $result->fetch_assoc()): 
                            ?>
                            <tr class="student-row <?= ($no % 2 == 0) ? 'bg-white-100' : 'bg-white' ?> text-center">
                                <td class="border p-2"><?= $no++; ?></td>
                                <td class="border p-2 searchable"><?= htmlspecialchars($row['nama']); ?></td>
                                <td class="border p-2"><?= htmlspecialchars($row['tanggal_lahir']) ?: '-'; ?></td>
                                <td class="border p-2 searchable"><?= htmlspecialchars($row['kelas']); ?></td>
                                <td class="border p-2 searchable"><?= htmlspecialchars($row['jurusan']); ?></td>
                                <td class="border p-2">
                                    <a href="profil.php?id=<?= $row['id']; ?>" class="block hover:opacity-75 transition-opacity">
                                        <?php if (!empty($row['foto']) && file_exists("uploads/" . $row['foto'])): ?>
                                            <img src="uploads/<?= htmlspecialchars($row['foto']); ?>" 
                                                 alt="Foto <?= htmlspecialchars($row['nama']); ?>"
                                                 class="w-16 h-16 rounded-full mx-auto object-cover">
                                        <?php else: ?>
                                            <img src="uploads/default.png" 
                                                 alt="Default" 
                                                 class="w-16 h-16 rounded-full mx-auto object-cover">
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td class="border p-2">
                                    <a href="edit_siswa.php?id=<?= $row['id']; ?>" 
                                       class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded inline-flex items-center">
                                        <span>Edit</span>
                                    </a>
                                    <a href="hapus_siswa.php?id=<?= $row['id']; ?>" 
                                       onclick="return confirm('Yakin ingin menghapus data siswa ini?')"
                                       class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded inline-flex items-center">
                                        <span>Hapus</span>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this script before closing body tag -->
    <script>
        // Function to filter table rows
        function filterTable() {
            const searchQuery = document.getElementById('searchInput').value.toLowerCase();
            const filterKelas = document.getElementById('filterKelas').value;
            const filterJurusan = document.getElementById('filterJurusan').value;
            const rows = document.querySelectorAll('.student-row');
            let noResults = true;

            rows.forEach(row => {
                const kelasCell = row.querySelector('td:nth-child(4)').textContent;
                const jurusanCell = row.querySelector('td:nth-child(5)').textContent;
                const searchableFields = row.querySelectorAll('.searchable');
                let matchesSearch = false;

                // Fixed kelas filter logic
                let matchesKelas = true; // Default to true if no filter selected
                if (filterKelas) {
                    // Exact match for kelas (X, XI, or XII)
                    matchesKelas = kelasCell.trim() === filterKelas;
                }

                let matchesJurusan = !filterJurusan || jurusanCell === filterJurusan;

                // Search in searchable fields
                searchableFields.forEach(field => {
                    if (field.textContent.toLowerCase().includes(searchQuery)) {
                        matchesSearch = true;
                    }
                });

                if (matchesSearch && matchesKelas && matchesJurusan) {
                    row.style.display = '';
                    noResults = false;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide "No results found" message
            let tbody = document.getElementById('studentTableBody');
            let noResultsRow = document.getElementById('noResultsRow');
            
            if (noResults) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'noResultsRow';
                    noResultsRow.innerHTML = `
                        <td colspan="9" class="border p-4 text-center text-gray-500">
                            <i class="fas fa-search mr-2"></i>
                            Tidak ada hasil yang ditemukan
                        </td>
                    `;
                    tbody.appendChild(noResultsRow);
                }
            } else if (noResultsRow) {
                noResultsRow.remove();
            }

            // Update row numbers
            let visibleRowNum = 1;
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    row.querySelector('td:first-child').textContent = visibleRowNum++;
                }
            });
        }

        // Add event listeners
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
        document.getElementById('filterKelas').addEventListener('change', filterTable);
        document.getElementById('filterJurusan').addEventListener('change', filterTable);
    </script>
</body>
</html>
