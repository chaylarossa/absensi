<?php
include 'config.php';

$guru_id = $_GET['guru_id'] ?? 0;

$query = "SELECT COUNT(*) as sudah_absen 
          FROM absensi_guru 
          WHERE guru_id = ? 
          AND DATE(tanggal) = CURRENT_DATE()";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $guru_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

header('Content-Type: application/json');
echo json_encode(['sudah_absen' => $result['sudah_absen'] > 0]);
