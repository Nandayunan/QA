<?php
require 'konfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ppb = $_POST['id_ppb'];
    $no_ppb = $_POST['no_ppb'];
    $part_pengerjaan = $_POST['part_pengerjaan'];
    $jenis_pengecekan = $_POST['jenis_pengecekan'];
    $request = $_POST['request'];
    $klasifikasi_ppb = $_POST['klasifikasi_ppb'];
    $status = $_POST['status'];

    $sql = "UPDATE monitoring SET 
                part_pengerjaan = ?, 
                jenis_pengecekan = ?, 
                request = ?, 
                klasifikasi_ppb = ?, 
                status = ? 
            WHERE id_ppb = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssiii', $part_pengerjaan, $jenis_pengecekan, $request, $klasifikasi_ppb, $status, $id_ppb);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update record.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
