<?php
include 'konfig.php'; // Database connection

header('Content-Type: application/json');
// Get JSON input
$id_tools = $_POST['id_tools'];

// Query to check if id_tools exists with matching digit
$query = "SELECT nama_tools FROM tools WHERE id_tools = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $id_tools);
$stmt->execute();
$result = $stmt->get_result();

// Check if a matching row is found and get nama_tools
if ($row = $result->fetch_assoc()) {
    $nama_tools = $row['nama_tools'];
    $isValid = true;
} else {
    $nama_tools = null;
    $isValid = false;
}

// Return JSON response

echo json_encode(["isValid" => $isValid, "nama_tools" => $nama_tools]);

$stmt->close();
$conn->close();
