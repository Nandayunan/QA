<?php
// Include database connection
include 'konfig.php';

// Set response as JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize POST data
    $no_ppb = isset($_POST['noPpb']) ? mysqli_real_escape_string($conn, $_POST['noPpb']) : null;
    $part_pengerjaan = isset($_POST['partPengerjaan']) ? mysqli_real_escape_string($conn, $_POST['partPengerjaan']) : null;
    $jenis_pengecekan = isset($_POST['jenisPengecekan']) ? mysqli_real_escape_string($conn, $_POST['jenisPengecekan']) : null;
    $request = isset($_POST['reqFrom']) ? mysqli_real_escape_string($conn, $_POST['reqFrom']) : null;
    $klasifikasi_ppb = isset($_POST['klasifikasiPpb']) ? (int)$_POST['klasifikasiPpb'] : null;
    $receive = isset($_POST['receiveDate']) ? mysqli_real_escape_string($conn, $_POST['receiveDate']) : null;
    $est_selesai = isset($_POST['estSelesaiDate']) ? mysqli_real_escape_string($conn, $_POST['estSelesaiDate']) : null;

    // Default status to "Waiting" (1)
    $status = 1;

    // Validate that all required fields are present
    if ($no_ppb && $part_pengerjaan && $jenis_pengecekan && $request && isset($klasifikasi_ppb) && $receive && $est_selesai) {
        // Prepare the SQL query with prepared statements to prevent SQL injection
        $query = "INSERT INTO monitoring (no_ppb, part_pengerjaan, jenis_pengecekan, request, klasifikasi_ppb, receive, est_selesai, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        // Initialize the prepared statement
        if ($stmt = mysqli_prepare($conn, $query)) {
            // Bind parameters to the statement
            mysqli_stmt_bind_param($stmt, 'ssssssss', $no_ppb, $part_pengerjaan, $jenis_pengecekan, $request, $klasifikasi_ppb, $receive, $est_selesai, $status);
            
            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Success response
                echo json_encode(['success' => true]);
            } else {
                // Query execution failed
                echo json_encode(['success' => false, 'error' => 'Failed to execute query: ' . mysqli_error($conn)]);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);
        } else {
            // Failed to prepare the statement
            echo json_encode(['success' => false, 'error' => 'Failed to prepare query.']);
        }
    } else {
        // Missing required data
        echo json_encode(['success' => false, 'error' => 'Missing required data.']);
    }

    // Close the database connection
    mysqli_close($conn);
} else {
    // Not a POST request
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
