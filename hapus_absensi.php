<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM absensi WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: absensi.php?status=success&message=Data absensi berhasil dihapus");
        } else {
            header("Location: absensi.php?status=error&message=Gagal menghapus data absensi");
        }
        $stmt->close();
    } catch (Exception $e) {
        header("Location: absensi.php?status=error&message=Error: " . $e->getMessage());
    }
} else {
    header("Location: absensi.php?status=error&message=ID tidak ditemukan");
}
exit();