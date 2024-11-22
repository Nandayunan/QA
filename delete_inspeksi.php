<?php
include 'konfig.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'delete') {
        $id_inspeksi = isset($_POST['id_inspeksi']) ? intval($_POST['id_inspeksi']) : null;

        if ($id_inspeksi !== null) {
            // Prepare SQL query for deleting the record
            $query = "DELETE FROM inspeksi WHERE id_inspeksi = ?";
            $stmt = $conn->prepare($query);

            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }

            // Bind parameters and execute
            $stmt->bind_param("i", $id_inspeksi);
            if ($stmt->execute()) {
                echo "Success";
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error: Missing required ID.";
        }
    } else {
        echo "Error: Invalid action.";
    }
} else {
    echo "Error: Invalid request method.";
}

$conn->close();
?>
