<?php
include 'konfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['new_image']) && isset($_POST['current_image'])) {
        $currentImage = $_POST['current_image'];

        $targetDir = "./assets/uploads/";
        $newImageName = basename($_FILES['new_image']['name']);
        $targetFilePath = $targetDir . $newImageName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES['new_image']['tmp_name']);
        if ($check === false) {
            die("File is not an image.");
        }

        // Allow certain file formats
        $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            die("Sorry, only JPG, JPEG, PNG, and GIF files are allowed.");
        }

        // Upload the new image file
        if (move_uploaded_file($_FILES['new_image']['tmp_name'], $targetFilePath)) {
            // Update the database with the new image name
            $query = $conn->prepare("UPDATE no_ir SET image = ? WHERE image = ?");
            if ($query === false) {
                die("Prepare failed: " . $conn->error);
            }

            $query->bind_param("ss", $newImageName, $currentImage);
            if ($query->execute()) {
                echo "Image updated successfully.";
            } else {
                echo "Error updating image: " . $conn->error;
            }

            $query->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "No image selected or current image name missing.";
    }

    $conn->close();
} else {
    echo "Invalid request.";
}
?>
