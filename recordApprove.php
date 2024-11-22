<?php
include 'konfig.php';
session_start();

if (!isset($_SESSION['role'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$role = $_SESSION['role'];
$id_form = json_decode(file_get_contents('php://input'), true)['id_form'];

if ($role == 2) {
    // Role 2: Waiting Approval Manager
    $status = 2;
} elseif ($role == 3) {
    // Role 3: Finish
    $status = 3;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
    exit;
}

$sql = "UPDATE form SET status = ? WHERE id_form = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $status, $id_form);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Record approved']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?>
