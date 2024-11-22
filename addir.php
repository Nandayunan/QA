<?php
include 'konfig.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_ir = $_POST['no_ir'];
    $no_reg = $_POST['no_reg'];
    $revisi = $_POST['revisi'];
    $id_subsubIR = $_POST['id_subsubIR'];
    $image = $_FILES['image'];

    if (trim($no_ir) === '' || trim($no_reg) === '' || trim($revisi) === '') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'All fields are required!']);
        exit();
    }
    

    // Check if no_ir already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM no_ir WHERE no_ir = ?");
    $stmt->bind_param("s", $no_ir);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No IR Sudah ada!']);
        exit();
    }

    // Check if file was uploaded
    if ($image['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 1 * 1024 * 1024; // 1 MB

        if (!in_array($image['type'], $allowedTypes)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, and PNG files are allowed!']);
            exit();
        }

        if ($image['size'] > $maxSize) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'File size must be less than 1MB!']);
            exit();
        }

        // Define the upload directory and file path
        $uploadDir = 'assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate a unique name for the file to prevent overwriting
        $filename = uniqid() . '_' . basename($image['name']);
        $filePath = $uploadDir . $filename;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($image['tmp_name'], $filePath)) {
            // Save the file path and other data to the database
            $stmt = $conn->prepare("INSERT INTO no_ir (no_ir, no_reg, revisi, id_subsubIR, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssis", $no_ir, $no_reg, $revisi, $id_subsubIR, $filename);

            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to save data to database!']);
            }
            $stmt->close();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to upload the file!']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'File upload error!']);
    }

    $conn->close();
}
?>
