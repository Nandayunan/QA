<?php
// Include your database configuration
include 'konfig.php';

// Check if form is submitted for edit operation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['no_ppb'])) {
    // Validate and sanitize input if necessary
    $no_ppb = $_POST['no_ppb'];

    // Retrieve other fields from POST data
    $part_pengerjaan = $_POST['partPengerjaan'];
    $jenis_pengecekan = $_POST['jenisPengecekan'];
    $req_from = $_POST['reqFrom'];
    $pic = $_POST['pic'];
    $receive = $_POST['receive'];
    $status = $_POST['status'];
    $mulai_pengerjaan = $_POST['mulai'];
    $estimasi_selesai = $_POST['estSelesai'];

    // Query untuk update data berdasarkan no_ppb
    $sql = "UPDATE ppb SET 
            part_pengerjaan = '$part_pengerjaan', 
            jenis_pengecekan = '$jenis_pengecekan', 
            req_from = '$req_from', 
            pic = '$pic', 
            receive = '$receive', 
            status = '$status', 
            mulai_pengerjaan = '$mulai_pengerjaan', 
            estimasi_selesai = '$estimasi_selesai' 
            WHERE no_ppb = '$no_ppb'";

    if ($conn->query($sql) === TRUE) {
        $response['success'] = true;
        $response['message'] = 'Data berhasil diupdate.';
        echo json_encode($response);
    } else {
        $response['success'] = false;
        $response['message'] = 'Gagal melakukan update: ' . $conn->error;
        echo json_encode($response);
    }
}

// Close database connection
$conn->close();
?>
