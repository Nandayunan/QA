<?php
include '../konfig.php'; // Database connection

// Fetch tools from the database
$sql = "SELECT id_tools, nama_tools FROM tools";
$result = $conn->query($sql);

$tools = array();
if ($result->num_rows > 0) {
    // Collect all rows in an array
    while ($row = $result->fetch_assoc()) {
        $tools[] = array(
            'id_tools' => $row['id_tools'],
            'nama_tools' => $row['nama_tools']
        );
    }
}

// Return the list as JSON
echo json_encode($tools);

// Close the connection
$conn->close();
