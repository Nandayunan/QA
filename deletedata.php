<?php
include 'konfig.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_form = $_POST['id_form'];

    $sql_delete = "DELETE FROM form WHERE id_form='$id_form'";
    if ($conn->query($sql_delete) === TRUE) {
        echo "Record deleted successfully";
    } else {
        echo "Error: " . $sql_delete . "<br>" . $conn->error;
    }
}

$conn->close();
?>
