<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Redirect ke halaman login setelah 3 detik
        setTimeout(function() {
            window.location.href = "user_dashboard.php";
        }, 3000);
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto"></div>
        <h2 class="mt-5 text-lg font-semibold text-gray-700">Sedang Logout...</h2>
        <p class="text-gray-500">Anda akan diarahkan ke halaman login dalam beberapa detik.</p>
    </div>

</body>
</html>
