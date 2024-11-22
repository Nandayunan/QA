<?php
include '../konfig.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id_form = $data['id_form'];

$response = array();

$sql = "SELECT form.id_form, no_ir.no_ir AS no_ir, ir.ir AS ir, form.no_ppb, form.receive_qty, form.sampling_qty, form.status
        FROM form
        LEFT JOIN no_ir ON form.id_noir = no_ir.id_noir
        LEFT JOIN ir ON form.jenis_ir = ir.ir
        WHERE form.id_form = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('i', $id_form);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $response = $result->fetch_assoc();

        $sql_testing = "SELECT testing.id_inspeksi, inspeksi.item_inspeksi, inspeksi.standar, inspeksi.terukur, tools.id_tools, tools.nama_tools,  
                        testing.s1, testing.s2, testing.s3, testing.s4, testing.s5, testing.s6, 
                        testing.s7, testing.s8, testing.s9, testing.s10, testing.status
                        FROM testing
                        LEFT JOIN inspeksi ON testing.id_inspeksi = inspeksi.id_inspeksi
                        LEFT JOIN tools ON inspeksi.id_tools = tools.id_tools
                        WHERE testing.id_form = ?";

        $stmt_testing = $conn->prepare($sql_testing);

        if ($stmt_testing) {
            $stmt_testing->bind_param('i', $id_form);
            $stmt_testing->execute();
            $result_testing = $stmt_testing->get_result();

            $response['testingResults'] = array();

            while ($row = $result_testing->fetch_assoc()) {
                // Cek jika terukur == 0
                if ($row['terukur'] == 0) {
                    // Modifikasi nilai s1 sampai s10
                    for ($i = 1; $i <= 10; $i++) {
                        $sKey = "s$i";
                        $row[$sKey] = $row[$sKey] == 1 ? "OK" : " ";
                    }
                }
                $response['testingResults'][] = $row;
            }

            // Fetch the approval note for the specific id_form
            $sql_note = "SELECT note FROM note WHERE id_form = ?";
            $stmt_note = $conn->prepare($sql_note);

            if ($stmt_note) {
                $stmt_note->bind_param('i', $id_form);
                $stmt_note->execute();
                $result_note = $stmt_note->get_result();

                if ($result_note && $result_note->num_rows > 0) {
                    $note_data = $result_note->fetch_assoc();
                    $response['approval_note'] = $note_data['note'];
                } else {
                    $response['approval_note'] = null;
                }
            } else {
                $response['error'] = 'Failed to prepare note query: ' . $conn->error;
            }
        } else {
            $response['error'] = 'Failed to prepare testing query: ' . $conn->error;
        }
    } else {
        $response['error'] = 'No data found';
    }
} else {
    $response['error'] = 'Failed to prepare form query: ' . $conn->error;
}

echo json_encode($response);

$conn->close();
