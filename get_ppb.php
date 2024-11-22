<?php
// Include the database configuration file
require 'konfig.php';

// SQL query
$query = "SELECT npk, otp FROM otp ORDER BY send_date desc ";

// Execute the query
$result = $conn->query($query);

// Check for query execution success
if ($result) {
    $data = [];
    
    // Fetch each row and store it in the $data array
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    // Set the content type to JSON and output the result
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    // In case of query failure, output an error message
    echo json_encode(['error' => 'Failed to execute query']);
}

// Close the database connection
$conn->close();
?>
