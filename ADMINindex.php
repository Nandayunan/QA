<?php

session_start();

if (!isset($_SESSION["npk"]) || $_SESSION["golongan"] != 4 || $_SESSION["acting"] != 2) {
    header("Location: forbidden.php");
    exit();
}
// Require the database configuration file
require 'konfig.php';

// Placeholder PHP code to handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $sub_ir = $_POST['sub_ir'];
    $id_ir = $_POST['id_ir'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO sub_ir (sub_ir, id_ir) VALUES (?, ?)");
    if ($stmt === false) {  
        die('Prepare() failed: ' . htmlspecialchars($conn->error));
    }

    $bind = $stmt->bind_param("si", $sub_ir, $id_ir); // Note: "si" indicates a string followed by an integer
    if ($bind === false) {
        die('Bind_param() failed: ' . htmlspecialchars($stmt->error));
    }

    // Execute the statement
    $exec = $stmt->execute();
    if ($exec) {
        echo "<script>Swal.fire('Success', 'New record created successfully', 'success').then(function() { window.location = 'index.php'; });</script>";
    } else {
        echo "<script>Swal.fire('Error', 'Execute() failed: " . htmlspecialchars($stmt->error) . "', 'error');</script>";
    }

    // Close the statement
    $stmt->close();
}

// Fetch sub_ir data from the database
$sub_ir_2w = array();
$sub_ir_4w = array();

$result = $conn->query("SELECT sub_ir, id_ir FROM sub_ir");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['id_ir'] == 1) {
            $sub_ir_2w[] = $row['sub_ir'];
        } elseif ($row['id_ir'] == 2) {
            $sub_ir_4w[] = $row['sub_ir'];
        }
    }
}

// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN Quality Assurance</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .ir, .approval-button {
            position: fixed;
            bottom: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        .ir {
            right: 20px;
            background-color: #2c3e50; /* Bootstrap primary blue */
            color: white;
        }

        .approval-button {
            left: 20px;
            background-color: #2c3e50; /* Dongker color */
            color: white;
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
                <button class="box-header">+ IR REGULARY 2W</button>
                <?php foreach ($sub_ir_2w as $sub_ir): ?>
                    <button class="box-item" onclick="location.href='ADMINsub.php?sub_ir=<?php echo urlencode($sub_ir); ?>'"><?php echo htmlspecialchars($sub_ir); ?></button>
                <?php endforeach; ?>
                <button class="plus" data-bs-toggle="modal" data-bs-target="#addMaterialModal">+</button>
            </div>
            <div class="box">
                <button class="box-header">+ IR REGULARY 4W</button>
                <?php foreach ($sub_ir_4w as $sub_ir): ?>
                    <button class="box-item" onclick="location.href='ADMINsub.php?sub_ir=<?php echo urlencode($sub_ir); ?>'"><?php echo htmlspecialchars($sub_ir); ?></button>
                <?php endforeach; ?>
                <button class="plus" data-bs-toggle="modal" data-bs-target="#addMaterialModal">+</button>
            </div>
        </div>
        <!-- <button class="ir" onclick="location.href='DataIR.php'">EDIT IR</button> -->
        <button class="approval-button" onclick="gotoApproval()">
            <i class="fas fa-eye"></i> Approval
        </button>
    </div>

    <!-- Add Material Modal -->
    <div class="modal fade" id="addMaterialModal" tabindex="-1" aria-labelledby="addMaterialModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMaterialModalLabel">Add Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="sub_ir" class="form-label">Sub IR</label>
                            <input type="text" class="form-control" id="sub_ir" name="sub_ir" required>
                        </div>
                        <div class="mb-3">
                            <label for="id_ir" class="form-label">ID IR</label>
                            <select class="form-select" id="id_ir" name="id_ir" required>
                                <option value="1">2W</option>
                                <option value="2">4W</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/nscript.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
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
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = "logout.php"; // Redirect to logout.php
                }
            });
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


