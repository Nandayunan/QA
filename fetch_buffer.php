<?php
// fetch_buffer.php
include 'konfig.php'; // Database configuration

$id_form = $_POST['id_form']; // Assuming id_form is sent via POST
$id_inspeksi = $_POST['id_inspeksi']; // Assuming id_inspeksi is sent via POST

// Query to fetch data from buffer table
$query = "SELECT id_inspeksi, s1, s2, s3, s4, s5, s6, s7, s8, s9, s10, status 
          FROM buffer 
          WHERE id_form = ? AND id_inspeksi = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_form, $id_inspeksi);
$stmt->execute();
$result = $stmt->get_result();

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$conn->close();

// Return data as JSON
echo json_encode($data);
