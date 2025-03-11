<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] == 'admin') {
            // Cek data admin
            $admin_stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            $admin_stmt->bind_param("s", $username);
            $admin_stmt->execute();
            $admin_result = $admin_stmt->get_result();
            
            if ($admin_result->num_rows > 0) {
                $admin_data = $admin_result->fetch_assoc();
                
                // Cek status admin
                if ($admin_data['status'] === 'inactive') {
                    $error = "Akun Anda sedang dinonaktifkan. Silakan hubungi super admin.";
                } else {
                    $_SESSION['admin_id'] = $admin_data['id'];
                    $_SESSION['is_superadmin'] = ($admin_data['role'] === 'superadmin');
                    header("Location: index.php");
                    exit();
                }
            }
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="flex items-center justify-center h-screen bg-[#074799]">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center text-[#074799]">Login</h2>
        <?php if (!empty($error)) echo "<p class='text-red-500 text-center'>$error</p>"; ?>
        <form method="POST" class="mt-4">
            <div class="mb-4">
                <label class="block text-gray-600">
                    <i class="fas fa-user mr-2"></i>Username
                </label>
                <input type="text" name="username" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#074799]" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-600">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <input type="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#074799]" required>
            </div>
            <button type="submit" class="w-full bg-[#074799] hover:bg-blue-700 text-white py-2 rounded-lg transition duration-300">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>
    </div>
</body>
</html>
