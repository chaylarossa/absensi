<?php
session_start();
include 'config.php';

// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Cek apakah ada ID yang dikirim
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Mulai transaction
        $conn->begin_transaction();

        // Hapus data absensi terkait terlebih dahulu
        $stmt = $conn->prepare("DELETE FROM absensi WHERE siswa_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Ambil info foto sebelum menghapus data siswa
        $result = $conn->prepare("SELECT foto FROM siswa WHERE id = ?");
        $result->bind_param("i", $id);
        $result->execute();
        $foto = $result->get_result()->fetch_assoc()['foto'];
        
        // Hapus data siswa
        $stmt = $conn->prepare("DELETE FROM siswa WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Hapus file foto jika ada
        if ($foto && file_exists("uploads/" . $foto)) {
            unlink("uploads/" . $foto);
        }
        
        header("Location: siswa.php?status=success&message=Data siswa berhasil dihapus!");
        exit();
        
    } catch (Exception $e) {
        // Rollback jika terjadi error
        $conn->rollback();
        header("Location: siswa.php?status=error&message=Gagal menghapus data: " . $e->getMessage());
        exit();
    }
} else {
    header("Location: siswa.php?status=error&message=ID tidak ditemukan");
    exit();
}
?>