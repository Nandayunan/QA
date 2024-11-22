<?php
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

$deleteId = $_POST['deleteId'];

// Prepare and bind
$stmt = $conn->prepare("DELETE FROM sub_subir WHERE id_subsubIR = ?");
$stmt->bind_param("i", $deleteId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}

$stmt->close();
$conn->close();
?>
