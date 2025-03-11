<?php
session_start(); // Tambahkan ini di awal
include 'config.php';

// Cek autentikasi admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission untuk tambah admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    
    try {
        // Start transaction
        $conn->begin_transaction();

        // 1. Insert ke tabel users
        $insert_user = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        if (!$insert_user) {
            throw new Exception("Error preparing users statement: " . $conn->error);
        }
        $insert_user->bind_param("ss", $username, $password);
        
        if (!$insert_user->execute()) {
            // Cek jika error karena duplicate username
            if ($conn->errno == 1062) {
                throw new Exception("Username sudah digunakan!");
            }
            throw new Exception("Gagal menambahkan user: " . $insert_user->error);
        }

        // 2. Insert ke tabel admin
        $insert_admin = $conn->prepare("INSERT INTO admin (username, password, nama, email) VALUES (?, ?, ?, ?)");
        if (!$insert_admin) {
            throw new Exception("Error preparing admin statement: " . $conn->error);
        }
        $insert_admin->bind_param("ssss", $username, $password, $nama, $email);
        
        if (!$insert_admin->execute()) {
            throw new Exception("Gagal menambahkan admin: " . $insert_admin->error);
        }

        // If both successful, commit
        $conn->commit();
        
        header("Location: manage_admin.php?status=success&message=Admin baru berhasil ditambahkan!");
        exit();
        
    } catch (Exception $e) {
        // Rollback jika terjadi error
        $conn->rollback();
        header("Location: manage_admin.php?status=error&message=" . urlencode($e->getMessage()));
        exit();
    }
}

// Handle status update
if (isset($_GET['toggle_status'])) {
    $admin_id = $_GET['toggle_status'];
    $new_status = $_GET['new_status'];
    
    $stmt = $conn->prepare("UPDATE admin SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $admin_id);
    
    if ($stmt->execute()) {
        header("Location: manage_admin.php?status=success&message=Status admin berhasil diperbarui");
        exit();
    }
}

// Handle delete admin
if (isset($_GET['delete'])) {
    $admin_id = (int)$_GET['delete'];
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get username first
        $stmt = $conn->prepare("SELECT username FROM admin WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        if ($admin) {
            // Delete from users table
            $stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
            $stmt->bind_param("s", $admin['username']);
            $stmt->execute();
            
            // Delete from admin table
            $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            
            $conn->commit();
            header("Location: manage_admin.php?status=success&message=Admin berhasil dihapus!");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: manage_admin.php?status=error&message=Gagal menghapus admin: " . $e->getMessage());
    }
    exit();
}

// Modifikasi query untuk mengambil daftar admin
$admin_list = $conn->query("SELECT * FROM admin ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>
    <?php include 'navbar.php'; ?>
    
    <!-- Notifikasi -->
    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        <div id="notification" class="fixed top-24 right-4 w-80 p-4 rounded-lg text-sm font-medium shadow-lg border transition-all duration-300 transform translate-x-0
            <?= ($_GET['status'] == 'success') ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200' ?>">
            <div class="flex items-center gap-3">
                <i class="fas fa-<?= ($_GET['status'] == 'success') ? 'check-circle text-green-500' : 'exclamation-circle text-red-500' ?>"></i>
                <p><?= htmlspecialchars($_GET['message']); ?></p>
            </div>
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('notification').style.opacity = '0';
                document.getElementById('notification').style.transform = 'translateX(100%)';
            }, 3000);
        </script>
    <?php endif; ?>
    
    <div class="ml-64 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">
                <i class="fas fa-user-shield mr-2"></i>Manajemen Admin
            </h1>

            <!-- Form Tambah Admin -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">
                    <i class="fas fa-user-plus mr-2"></i>Tambah Admin Baru
                </h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="add_admin" value="1">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Username</label>
                            <input type="text" name="username" required 
                                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Password</label>
                            <input type="password" name="password" required 
                                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                            <input type="text" name="nama" required 
                                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" name="email" required 
                                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Admin
                    </button>
                </form>
            </div>

            <!-- Daftar Admin -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">
                    <i class="fas fa-users-cog mr-2"></i>Daftar Admin
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="p-3 text-left">No</th>
                                <th class="p-3 text-left">Nama</th>
                                <th class="p-3 text-left">Username</th>
                                <th class="p-3 text-left">Email</th>
                                <th class="p-3 text-left">Status</th>
                                <th class="p-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($admin = $admin_list->fetch_assoc()): 
                            ?>
                            <tr class="border-t">
                                <td class="p-3"><?= $no++ ?></td>
                                <td class="p-3"><?= htmlspecialchars($admin['nama']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($admin['username']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($admin['email']) ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        <?= ($admin['status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ucfirst($admin['status'] ?? 'active') ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <a href="?toggle_status=<?= $admin['id'] ?>&new_status=<?= ($admin['status'] ?? 'active') === 'active' ? 'inactive' : 'active' ?>"
                                       class="text-sm text-blue-600 hover:text-blue-800">
                                        <?= ($admin['status'] ?? 'active') === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </a>
                                    <a href="manage_admin.php?delete=<?= $admin['id'] ?>" 
                                       onclick="return confirm('Yakin ingin menghapus admin ini?')"
                                       class="text-red-600 hover:text-red-800 ml-2">
                                        <i class="fas fa-trash"></i> Hapus
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
</body>
</html>
