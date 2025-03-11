<?php
include 'config.php';

$username = "user5";
$password = password_hash("user123", PASSWORD_DEFAULT); // Hash password
$role = "user";

// Masukkan ke database
$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $password, $role);

if ($stmt->execute()) {
    echo "User berhasil ditambahkan!";
} else {
    echo "Gagal menambahkan user: " . $conn->error;
}
?>
