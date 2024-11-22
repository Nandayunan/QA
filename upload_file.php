<?php
require 'konfig.php'; // Include your database connection file

// Set the upload directory for PDFs
$upload_dir = 'uploads/pdf/'; // Ensure this subdirectory exists and is writable

// Check if the uploads/pdf directory exists, if not, create it
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true); // Create the directory with read/write permissions
}

// Check if a file is uploaded
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Get file details
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_size = $_FILES['file']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Validate file extension (only allow PDF)
    $allowed_extensions = ['pdf'];
    if (!in_array($file_ext, $allowed_extensions)) {
        echo json_encode(['status' => 'error', 'message' => 'Only PDF files are allowed.']);
        exit;
    }

    // Validate file size (limit to 2MB)
    if ($file_size > 2 * 1024 * 1024) { // 2MB limit
        echo json_encode(['status' => 'error', 'message' => 'File size exceeds 2MB.']);
        exit;
    }

    // Generate a unique name for the file to avoid conflicts
    $new_file_name = uniqid() . '.' . $file_ext;

    // Move the uploaded file to the PDF upload directory
    if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
        // Prepare SQL to update the 'file' field in the 'monitoring' table using 'id_ppb'
        $id_ppb = $_POST['id_ppb']; // Assuming 'id_ppb' is sent via POST along with the file

        // Validate 'id_ppb'
        if (empty($id_ppb)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit;
        }

        $sql = "UPDATE monitoring SET file = ? WHERE id_ppb = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_file_name, $id_ppb);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'File uploaded and database updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update database.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload file.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or an error occurred during the upload.']);
}

$conn->close();
?>
