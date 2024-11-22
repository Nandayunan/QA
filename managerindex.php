<?php

session_start();

if (!isset($_SESSION["npk"]) || $_SESSION["golongan"] != 4 || $_SESSION["acting"] != 1) {
    header("Location: forbidden.php");
    exit();
}
// Require the database configuration file
require 'konfig.php';

function fetchIRData($id_ir) {
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Assurance</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    .btn-approval {
    background-color: #1a44d8;
    color: white;
    border: none;
    padding: 20px 30px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
}

.btn-approval:hover {
    background-color: #051c71;
}</style>
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
                <button class="box-header">IR REGULARY 2W</button>
                <?php foreach ($ir2w as $item): ?>
                    <button class="box-item" onclick="location.href='ocu.php?sub_ir=<?= urlencode($item['sub_ir']) ?>'"><?= htmlspecialchars($item['sub_ir']) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="box">
                <button class="box-header">IR REGULARY 4W</button>
                <?php foreach ($ir4w as $item): ?>
                    <button class="box-item" onclick="location.href='ocu.php?sub_ir=<?= urlencode($item['sub_ir']) ?>'"><?= htmlspecialchars($item['sub_ir']) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="button-container">
            <button class="btn-approval" onclick="location.href='approve.php'">Approval</button>
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

        function gotoReq(){
            window.location.href = 'status.php';
        }

        function approve(){
            window.location.href = 'approve.php';
        }

        
    </script>
</body>
</html>
