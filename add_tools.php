<?php
// Load the database configuration
require_once __DIR__ . '/konfig.php';

// Check if POST data is set
if (isset($_POST['id_tools']) && isset($_POST['nama_tools'])) {
    $id_tools = $_POST['id_tools'];
    $nama_tools = $_POST['nama_tools'];

    // Validate that input is not empty and id_tools is an integer
    if (empty($id_tools) || !is_numeric($id_tools) || empty($nama_tools)) {
        echo json_encode(['status' => 'error', 'message' => 'ID Tools must be a valid integer and Nama Tools is required']);
        exit;
    }

    // Prepare the SQL INSERT query
    $stmt = $conn->prepare("INSERT INTO tools (id_tools, nama_tools) VALUES (?, ?)");
    
    // Bind parameters (use 'i' for integer and 's' for string)
    $stmt->bind_param("is", $id_tools, $nama_tools);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}
