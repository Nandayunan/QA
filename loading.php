<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading...</title>
    <link rel="stylesheet" href="load.css"> <!-- Menghubungkan file CSS -->
</head>
<body>
    <div class="clock-loader"></div>
    <script>
        // Redirect setelah beberapa detik
        setTimeout(() => {
            window.location.href = 'login.php'; // Ubah ini sesuai halaman target setelah proses selesai
        }, 3000); // Loading berlangsung selama 3 detik
    </script>
</body>
</html>
