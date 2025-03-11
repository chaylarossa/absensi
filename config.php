<?php

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'absensi_siswa';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set karakter encoding
$conn->set_charset("utf8");
?>