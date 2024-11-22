<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kayaba_project";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$sql = "SELECT id_subir FROM subir";
$result = $conn->query($sql);

$options = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $options[] = $row;
    }
    echo json_encode(['success' => true, 'options' => $options]);
} else {
    echo json_encode(['success' => false, 'message' => 'No options found']);
}

$conn->close();
?>
