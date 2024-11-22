<?php
include '../konfig.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $id_inspeksi = isset($_POST['id_inspeksi']) ? intval($_POST['id_inspeksi']) : null;
    $no_ir = isset($_POST['no_ir']) ? trim($_POST['no_ir']) : ''; // Assuming no_ir is required for both insert and update
    $item_inspeksi = isset($_POST['item_inspeksi']) ? trim($_POST['item_inspeksi']) : '';
    $standar = isset($_POST['standar']) ? trim($_POST['standar']) : '';
    $id_tools = isset($_POST['alat']) ? intval($_POST['alat']) : null; // Now using id_tools (foreign key)
    $min = isset($_POST['min']) ? floatval($_POST['min']) : null;
    $max = isset($_POST['max']) ? floatval($_POST['max']) : null;
    $terukur = isset($_POST['terukur']) ? intval($_POST['terukur']) : null;

    // Validate required fields
    if ($no_ir !== '' && $item_inspeksi !== '' && $standar !== '' && $id_tools !== null && $terukur !== null) {
        if ($id_inspeksi !== null) {
            // Update existing record
            $query = "UPDATE inspeksi SET item_inspeksi = ?, standar = ?, id_tools = ?, min = ?, max = ?, terukur = ? WHERE id_inspeksi = ?";
            $stmt = $conn->prepare($query);

            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }

            // Bind parameters and execute
            $stmt->bind_param("sssddii", $item_inspeksi, $standar, $id_tools, $min, $max, $terukur, $id_inspeksi);
            if ($stmt->execute()) {
                echo "Data successfully updated in the inspeksi table.";
            } else {
                echo "Error updating record: " . $stmt->error;
            }

            $stmt->close();
        } else {
            // Insert new record
            $query = "INSERT INTO inspeksi (no_ir, item_inspeksi, standar, id_tools, min, max, terukur) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);

            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }

            // Bind parameters and execute
            $stmt->bind_param("ssssddi", $no_ir, $item_inspeksi, $standar, $id_tools, $min, $max, $terukur);
            if ($stmt->execute()) {
                echo "Data successfully inserted into the inspeksi table. $id_tools";
            } else {
                echo "Error inserting record: " . $stmt->error;
            }

            $stmt->close();
        }
    } else {
        echo "Error: Missing required fields.";
    }
} else {
    echo "Error: Invalid request method.";
}

$conn->close();
