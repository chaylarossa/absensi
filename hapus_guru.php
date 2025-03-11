<?php
include 'config.php';

// Cek apakah ada ID guru yang dikirim
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus data dari database
    $delete = $conn->query("DELETE FROM guru WHERE id = $id");

    if ($delete) {
        echo "<script>alert('Data guru berhasil dihapus!'); window.location='data_guru.php';</script>";
    } else {
        echo "Gagal menghapus data!";
    }
} else {
    echo "ID tidak ditemukan!";
}
?>
