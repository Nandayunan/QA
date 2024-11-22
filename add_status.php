<?php
include 'konfig.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_ppb = $_POST['no_ppb'];
    $part_pengerjaan = $_POST['part_pengerjaan'];
    $jenis_pengecekan = $_POST['jenis_pengecekan'];
    $req_from = $_POST['req_from'];
    $pic = $_POST['pic'];
    $receive = $_POST['receive'];
    $status = $_POST['status'];
    $file = $_POST['file'];
    $mulai_pengerjaan = $_POST['mulai_pengerjaan'];
    $estimasi_selesai = $_POST['estimasi_selesai'];

    $sql = "INSERT INTO ppb (no_ppb, part_pengerjaan, jenis_pengecekan, req_from, pic, receive, status, file, mulai_pengerjaan, estimasi_selesai) 
            VALUES ('$no_ppb', '$part_pengerjaan', '$jenis_pengecekan', '$req_from', '$pic', '$receive', '$status', '$file', '$mulai_pengerjaan', '$estimasi_selesai')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
    header("Location: index.php"); // Redirect back to the main page
}
?>
