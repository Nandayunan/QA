<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['materialName']) && isset($data['sub_ir'])) {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "kayaba_project";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
    }

    // Get id_subir based on sub_ir
    $stmt = $conn->prepare("SELECT id_subir FROM sub_ir WHERE sub_ir = ?");
    $stmt->bind_param("s", $data['sub_ir']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_subir = $row['id_subir'];

        // Insert new material into sub_subir table
        $stmt = $conn->prepare("INSERT INTO sub_subir (sub_subIR, id_subir) VALUES (?, ?)");
        $stmt->bind_param("si", $data['materialName'], $id_subir);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add material: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid sub_ir']);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}
?>
