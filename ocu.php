// ocu.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Assurance - OCU</title>
    <link rel="stylesheet" href="assets/css/fstyle.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <img src="assets/img/kyb.png" alt="KYB Logo" class="logo">
            <div class="title">LABORATORIUM QUALITY ASSURANCE - PT KAYABA INDONESIA</div>
            <div id="clock" class="clock">11:11</div>
            <button class="logout" id="logout-button" onclick="confirmLogout()">LOGOUT</button>
        </header>
        <div class="content">
            <?php
            // Database connection
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "kayaba_project";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Get sub_ir from URL parameter
           // Get sub_ir from URL parameter
           $sub_ir = isset($_GET['sub_ir']) ? $_GET['sub_ir'] : '';

           // Query to get data from sub_subir table based on sub_ir parameter
           if ($sub_ir != '') {
            $stmt = $conn->prepare("SELECT sub_ir.*, sub_subir.* FROM sub_subir LEFT JOIN sub_ir ON sub_subir.id_subir = sub_ir.id_subir WHERE sub_ir.sub_ir = ?");
            $stmt->bind_param("s", $sub_ir);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo '<div class="box-header">IR REGULARY ' . htmlspecialchars($sub_ir) . '<br></div>';
                echo '<div class="items">';
                // Output data for each row
                while ($row = $result->fetch_assoc()) {
                    echo '<button class="box-item" onclick="location.href=\'adjuster.php?id=' . $row["id_subsubIR"] . '\'">' . htmlspecialchars($row["sub_subIR"]) . '</button>';
                }
                echo '</div>';
            } else {
                echo '<div class="box-header">No results found for ' . htmlspecialchars($sub_ir) . '</div>';
            }

            $stmt->close();
        } else {
            echo '<div class="box-header">Please select a sub IR</div>';
        }

        // Close connection
        $conn->close();
        ?>
        </div>
        <footer class="footer">
            <button class="nav-button back-button" onclick="location.href='index.php'">⬅</button>
            <button class="nav-button home-button">🏠</button>
        </footer>
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
    </script>
</body>
</html>