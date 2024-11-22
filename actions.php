<?php
include 'konfig.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session to access session variables
session_start();

// Set the timezone to Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Read the JSON input
$request = file_get_contents('php://input');
$data = json_decode($request, true);

$response = ['success' => false, 'message' => 'Invalid action'];

// Check if 'action' is set in the request data
if (isset($data['action'])) {
    switch ($data['action']) {
        case 'edit':
            $stmt = $conn->prepare("UPDATE form SET no_ppb=?, receive_qty=?, sampling_qty=? WHERE id_form=?");
            $stmt->bind_param('siii', $data['no_ppb'], $data['receive_qty'], $data['sampling_qty'], $data['id_form']);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Record updated successfully'];
            } else {
                $response['message'] = 'Error updating record: ' . htmlspecialchars($stmt->error);
            }
            $stmt->close();
            break;

        case 'delete':
            $stmt = $conn->prepare("DELETE FROM form WHERE id_form=?");
            if ($stmt === false) {
                $response['message'] = 'Prepare failed: ' . htmlspecialchars($conn->error);
            } else {
                $stmt->bind_param('i', $data['id_form']);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Record deleted successfully'];
                } else {
                    $response['message'] = 'Error deleting record: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            }
            break;

        case 'approve':
            // Capture the NPK from the session
            $npk = $_SESSION['npk'];

            // Check if the logged-in user is a supervisor or manager
            $stmt = $conn2->prepare("SELECT golongan, acting FROM ct_users WHERE npk = ?");
            $stmt->bind_param('s', $npk);
            $stmt->execute();
            $stmt->bind_result($golongan, $acting);
            $stmt->fetch();
            $stmt->close();

            $current_datetime = date('Y-m-d H:i:s');

            if ($golongan == 4 && $acting == 1) {
                // Manager approval
                $new_status = 3; // Finish
                $stmt = $conn->prepare("UPDATE form SET status=?, `approve`=?, tgl_approve=? WHERE id_form=?");
                $stmt->bind_param('iisi', $new_status, $npk, $current_datetime, $data['id_form']);
                if ($stmt->execute()) {
                    // Insert note into the note table
                    $stmt = $conn->prepare("INSERT INTO note (id_form, note, npk) VALUES (?, ?, ?)");
                    $stmt->bind_param('isi', $data['id_form'], $data['note'], $npk);
                    if ($stmt->execute()) {
                        // Send notification to Operator
                        $operator_message = "PEMBERITAHUAN!\n\nPart number yg anda ukur telah selesai dalam tahap approval dari supervisor dan manager\n\nTerima kasih telah mengukur part number dengan benar dan terima kasih atas kerja samanya.";
                        $stmt = $conn2->prepare("SELECT npk FROM ct_users WHERE golongan = 2 AND acting = 2"); // Get operator NPK
                        $stmt->execute();
                        $stmt->bind_result($operator_npk);
                        while ($stmt->fetch()) {
                            // Fetch phone number from the hp table
                            $stmt_phone = $conn3->prepare("SELECT no_hp FROM hp WHERE npk = ?");
                            $stmt_phone->bind_param('s', $operator_npk);
                            $stmt_phone->execute();
                            $stmt_phone->bind_result($phone_number);
                            $stmt_phone->fetch();
                            $stmt_phone->close();

                            // Insert notification into notification_push table
                            $stmt_push = $conn->prepare("INSERT INTO notification_push (phone_number, `message`, flag) VALUES (?, ?, ?)");
                            $flag = "queue"; // Example flag value
                            $stmt_push->bind_param('ssi', $phone_number, $operator_message, $flag);
                            $stmt_push->execute();
                            $stmt_push->close();
                        }
                        $response = ['success' => true, 'message' => 'Record approved and note saved successfully'];
                    } else {
                        $response['message'] = 'Record approved, but error saving note: ' . htmlspecialchars($stmt->error);
                    }
                } else {
                    $response['message'] = 'Error approving record: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } elseif ($golongan == 4 && $acting == 2) {
                // Supervisor approval
                $new_status = 2; // Waiting approval manager
                $stmt = $conn->prepare("UPDATE form SET status=?, `check`=?, tgl_check=? WHERE id_form=?");
                $stmt->bind_param('iisi', $new_status, $npk, $current_datetime, $data['id_form']);

                if ($stmt->execute()) {
                    // Retrieve no_ir (part number) based on id_noir
                    $stmt_part = $conn->prepare("SELECT no_ir.no_ir FROM no_ir JOIN form ON no_ir.id_noir = form.id_noir WHERE form.id_form = ?");
                    $stmt_part->bind_param('i', $data['id_form']); // Assuming id_noir is part of $data
                    $stmt_part->execute();
                    $stmt_part->bind_result($partnumber);
                    $stmt_part->fetch(); // Fetch the result and store it in $partnumber
                    $stmt_part->close(); // Close the statement
                    // Insert note into the note table

                    $stmt = $conn->prepare("INSERT INTO note (id_form, note, npk) VALUES (?, ?, ?)");
                    $stmt->bind_param('isi', $data['id_form'], $data['note'], $npk);
                    if ($stmt->execute()) {
                        // Jangan tetapkan ulang $partnumber dengan nilai lain di sini
                        // Send notification to Manager
                        $manager_message = "PEMBERITAHUAN APPROVE!\n\nBahwa part number $partnumber telah selesai melakukan pengukuran, kami membutuhkan approval dari Manager";
                        $stmt = $conn2->prepare("SELECT npk FROM ct_users WHERE dept = 'QA' AND golongan = 4 AND acting = 1"); // Get manager NPK
                        $stmt->execute();
                        $stmt->bind_result($manager_npk);
                        while ($stmt->fetch()) {
                            // Fetch phone number from the hp table
                            $stmt_phone = $conn3->prepare("SELECT no_hp FROM hp WHERE npk = ?");
                            $stmt_phone->bind_param('s', $manager_npk);
                            $stmt_phone->execute();
                            $stmt_phone->bind_result($phone_number);
                            $stmt_phone->fetch();
                            $stmt_phone->close();

                            // Insert notification into notification_push table
                            $stmt_push = $conn->prepare("INSERT INTO notification_push (phone_number, `message`, flag) VALUES (?, ?, ?)");
                            $flag = "queue"; // Example flag value
                            $stmt_push->bind_param('sss', $phone_number, $manager_message, $flag);
                            $stmt_push->execute();
                            $stmt_push->close();
                        }
                        $response = ['success' => true, 'message' => 'Record approved and note saved successfully'];
                    } else {
                        $response['message'] = 'Record approved, but error saving note: ' . htmlspecialchars($stmt->error);
                    }
                } else {
                    $response['message'] = 'Error approving record: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $response['message'] = 'Unauthorized action';
            }
            break;


        case 'decline':
            // Set status to 0 for decline
            $new_status = 0;
            $stmt = $conn->prepare("UPDATE form SET status=? WHERE id_form=?");
            $stmt->bind_param('ii', $new_status, $data['id_form']);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Record declined successfully'];
            } else {
                $response['message'] = 'Error declining record: ' . htmlspecialchars($stmt->error);
            }
            $stmt->close();
            break;
    }
}

// Return JSON response
echo json_encode($response);
