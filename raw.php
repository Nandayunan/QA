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
            <div class="box-header">IR REGULARY 2W<br>RAW MATERIAL</div>
            <div class="items">
            
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

                // Query to get data from sub_subir table where id_subir = 2
                $sql = "SELECT id_subsubIR, sub_subIR FROM sub_subir WHERE id_subir = 4";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Output data for each row
                    while($row = $result->fetch_assoc()) {
                        echo '<button class="box-item" onclick="location.href=\'adjuster.php?id=' . $row["id_subsubIR"] . '\'">' . $row["sub_subIR"] . '</button>';
                    }
                } else {
                    echo "No results";
                }

                // Close connection
                $conn->close();
                ?>
            </div>
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
