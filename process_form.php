<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_testing = $_POST['id_testing'];
    $id_form = $_POST['id_form'];
    $id_inspeksi = $_POST['id_inspeksi'];
    $satuan_panjang = $_POST['satuan_panjang'];
    $field1 = $_POST['field1'];
    $field2 = $_POST['field2'];
    $field3 = $_POST['field3'];
    $field4 = $_POST['field4'];
    $field5 = $_POST['field5'];
    $field6 = $_POST['field6'];
    $field7 = $_POST['field7'];
    $field8 = $_POST['field8'];
    $field9 = $_POST['field9'];
    $field10 = $_POST['field10'];
    $status = $_POST['status'];

    // Example of saving to the database
    // $conn = new mysqli($servername, $username, $password, $dbname);

    // if ($conn->connect_error) {
    //     die("Connection failed: " . $conn->connect_error);
    // }

    // $sql = "INSERT INTO your_table (id_testing, id_form, id_inspeksi, satuan_panjang, field1, field2, field3, field4, field5, field6, field7, field8, field9, field10, status)
    // VALUES ('$id_testing', '$id_form', '$id_inspeksi', '$satuan_panjang', '$field1', '$field2', '$field3', '$field4', '$field5', '$field6', '$field7', '$field8', '$field9', '$field10', '$status')";

    // if ($conn->query($sql) === TRUE) {
    //     echo "New record created successfully";
    // } else {
    //     echo "Error: " . $sql . "<br>" . $conn->error;
    // }

    // $conn->close();

    echo json_encode(['status' => 'success', 'message' => 'Data submitted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
