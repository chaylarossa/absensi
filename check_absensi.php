<?php
include 'config.php';

$siswa_id = $_GET['siswa_id'] ?? 0;

$query = "SELECT COUNT(*) as sudah_absen 
          FROM absensi 
          WHERE siswa_id = ? 
          AND DATE(tanggal) = CURRENT_DATE()";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $siswa_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

header('Content-Type: application/json');
echo json_encode(['sudah_absen' => $result['sudah_absen'] > 0]);
