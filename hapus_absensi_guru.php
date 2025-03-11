<?php
include 'config.php';

// Cek apakah ada ID absensi yang dikirim
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus data dari database
    $delete = $conn->query("DELETE FROM absensi_guru WHERE id = $id");

    if ($delete) {
        echo "<script>alert('Data absensi guru berhasil dihapus!'); window.location='absensi_guru.php';</script>";
    } else {
        echo "Gagal menghapus data!";
    }
} else {
    echo "ID tidak ditemukan!";
}
?>
