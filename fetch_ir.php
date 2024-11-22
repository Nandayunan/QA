<?php
include 'konfig.php';

$sql = "SELECT no_ir FROM no_ir";
$result = $conn->query($sql);

$no_ir_array = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $no_ir_array[] = $row['no_ir'];
    }
}

echo json_encode($no_ir_array);

$conn->close();
?>
