<?php
session_start();

if (!isset($_SESSION["npk"]) || $_SESSION["golongan"] != 2  || $_SESSION["acting"] != 2) {
    header("Location: index.php");
    exit();
}


include 'konfig.php';

function fetchIRData($id_ir)
{
    global $conn;
    $stmt = $conn->prepare("SELECT sub_ir FROM sub_ir WHERE id_ir = ?");
    $stmt->bind_param("i", $id_ir);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

$ir2w = fetchIRData(1);
$ir4w = fetchIRData(2);
?>
<!-- HTML content for HRD -->


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Assurance</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .approval-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #2c3e50;
            /* Dongker color */
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }

        .approval-button i {
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="assets/img/kyb.png" alt="KYB Logo" class="logo">
            <div class="title">LABORATORIUM QUALITY ASSURANCE - PT KAYABA INDONESIA</div>
            <div id="clock" class="clock"></div>
            <button class="logout" id="logout-button" onclick="confirmLogout()">LOGOUT</button>
        </div>
        <div class="content">
            <div class="box">
                <button class="box-header">+ IR REGULARLY 2W</button>
                <?php foreach ($ir2w as $item): ?>
                    <button class="box-item" onclick="location.href='ocu.php?sub_ir=<?= urlencode($item['sub_ir']) ?>'"><?= htmlspecialchars($item['sub_ir']) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="box">
                <button class="box-header">+ IR REGULARLY 4W</button>
                <?php foreach ($ir4w as $item): ?>
                    <button class="box-item" onclick="location.href='ocu.php?sub_ir=<?= urlencode($item['sub_ir']) ?>'"><?= htmlspecialchars($item['sub_ir']) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="approval-button" onclick="gotoApproval()">
            <i class="fas fa-eye"></i> Lihat Approval
        </button>
    </div>

    <!-- Loader -->
    <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 9999; text-align: center;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p>Loading, please wait...</p>
        </div>
    </div>

    <script src="assets/js/nscript.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: "Konfirmasi",
                text: "Apakah yakin anda akan logout?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, log out!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "logout.php"; // Redirect to logout.php
                }
            });
        }

        function navigateWithLoader(url) {
            // Show the loader
            document.getElementById('loader').style.display = 'block';

            // Delay the redirection to allow the loader to be visible
            setTimeout(function() {
                window.location.href = url;
            }, 500); // Adjust the delay time (500 ms) as needed
        }

        function gotoReq() {
            window.location.href = 'status.php';
        }

        function gotoApproval() {
            window.location.href = 'approve.php'; // Redirect to approval page
        }
    </script>
</body>

</html>