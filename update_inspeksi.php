<?php
include 'konfig.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect POST data
    $nomor_ir = isset($_POST['no_ir']) ? $_POST['no_ir'] : '';
    $item_inspeksi = isset($_POST['item_inspeksi']) ? $_POST['item_inspeksi'] : '';
    $standar = isset($_POST['standar']) ? $_POST['standar'] : '';
    $alat = isset($_POST['alat']) ? $_POST['alat'] : '';
    $min = isset($_POST['min']) ? $_POST['min'] : '';
    $max = isset($_POST['max']) ? $_POST['max'] : '';

    // Validate and sanitize input
    $nomor_ir = $conn->real_escape_string($nomor_ir);
    $item_inspeksi = $conn->real_escape_string($item_inspeksi);
    $standar = $conn->real_escape_string($standar);
    $alat = $conn->real_escape_string($alat);
    $min = $conn->real_escape_string($min);
    $max = $conn->real_escape_string($max);

    // Prepare and execute the SQL query
    $query = $conn->prepare("INSERT INTO inspeksi (nomor_ir, item_inspeksi, standar, alat, min, max) VALUES (?, ?, ?, ?, ?, ?)");
    if ($query === false) {
        $response['message'] = 'Prepare failed: ' . $conn->error;
    } else {
        $query->bind_param("ssssss", $nomor_ir, $item_inspeksi, $standar, $alat, $min, $max); // Correct types: "ssssss" for all strings
        if ($query->execute()) {
            $response['success'] = true;
            $response['message'] = 'Data successfully submitted!';
        } else {
            $response['message'] = 'Execute failed: ' . $query->error;
        }
        $query->close();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
header('Content-Type: application/json'); // Ensure the header is sent before output
echo json_encode($response);
?>
