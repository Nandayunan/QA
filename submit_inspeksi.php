<?php
ini_set('display_errors', 1); // Show all errors
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'konfig.php';

// Set the response header to JSON
header('Content-Type: application/json');

$response = array(); // Array untuk menyimpan respons

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_form = $_POST['id_form'];
    $id_inspeksi_array = $_POST['id_inspeksi'];

    // Validasi jika id_form bukan integer
    if (!filter_var($id_form, FILTER_VALIDATE_INT)) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid id_form.';
        error_log(print_r($response, true));
        echo json_encode($response);
        exit;
    }

    // Loop untuk setiap id_inspeksi yang diterima
    foreach ($id_inspeksi_array as $index => $id_inspeksi) {
        $row_index = $index + 1; // Menghitung row_number

        $status = isset($_POST["status{$row_index}"]) ? (int) $_POST["status{$row_index}"] : 0;

        // Ambil nilai sampling, sesuai dengan row_number
        $s1 = isset($_POST["sampling1_{$row_index}"]) ? (float) $_POST["sampling1_{$row_index}"] : 0;
        $s2 = isset($_POST["sampling2_{$row_index}"]) ? (float) $_POST["sampling2_{$row_index}"] : 0;
        $s3 = isset($_POST["sampling3_{$row_index}"]) ? (float) $_POST["sampling3_{$row_index}"] : 0;
        $s4 = isset($_POST["sampling4_{$row_index}"]) ? (float) $_POST["sampling4_{$row_index}"] : 0;
        $s5 = isset($_POST["sampling5_{$row_index}"]) ? (float) $_POST["sampling5_{$row_index}"] : 0;
        $s6 = isset($_POST["sampling6_{$row_index}"]) ? (float) $_POST["sampling6_{$row_index}"] : 0;
        $s7 = isset($_POST["sampling7_{$row_index}"]) ? (float) $_POST["sampling7_{$row_index}"] : 0;
        $s8 = isset($_POST["sampling8_{$row_index}"]) ? (float) $_POST["sampling8_{$row_index}"] : 0;
        $s9 = isset($_POST["sampling9_{$row_index}"]) ? (float) $_POST["sampling9_{$row_index}"] : 0;
        $s10 = isset($_POST["sampling10_{$row_index}"]) ? (float) $_POST["sampling10_{$row_index}"] : 0;

        $approve = 0; // Status approve default 0

        // Siapkan query untuk memasukkan data ke tabel testing
        $query = $conn->prepare("INSERT INTO testing (id_form, id_inspeksi, s1, s2, s3, s4, s5, s6, s7, s8, s9, s10, status, approve) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$query) {
            $response['status'] = 'error';
            $response['message'] = "Prepare statement gagal: " . $conn->error;
            error_log(print_r($response, true));
            echo json_encode($response);
            exit;
        }

        // Binding parameter ke query
        $query->bind_param("iiddddddddddii", $id_form, $id_inspeksi, $s1, $s2, $s3, $s4, $s5, $s6, $s7, $s8, $s9, $s10, $status, $approve);

        // Eksekusi query dan cek apakah berhasil
        if (!$query->execute()) {
            $response['status'] = 'error';
            $response['message'] = "Eksekusi gagal: " . $query->error;
            error_log(print_r($response, true));
            echo json_encode($response);
            exit;
        }

        $query->close();
    }

    // Mengambil npk dari tabel ct_users dengan koneksi $conn2
    $query2 = $conn2->prepare("SELECT npk, golongan, acting FROM ct_users WHERE dept = 'QA' AND golongan = 4 AND acting = 2;");
    if (!$query2->execute()) {
        $response['status'] = 'error';
        $response['message'] = "Gagal mengambil data npk: " . $query2->error;
        error_log(print_r($response, true));
        echo json_encode($response);
        exit;
    }

    $result2 = $query2->get_result();
    while ($row2 = $result2->fetch_assoc()) {
        $npk = $row2['npk'];
        $golongan = $row2['golongan'];
        $acting = $row2['acting'];

        // Membuat pesan untuk Supervisor
        $message = "PEMBERITAHUAN UNTUK APPROVE!\n\nKami menginformasikan kepada anda bahwa ada part number yg telah melakukan pengukuran dan butuh approval dari anda sebagai supervisor.\n\nMohon untuk segera memproses dan memberikan persetujuan sesuai prosedur yang berlaku.\nTerima kasih atas perhatian dan kerjasamanya.";

        // Mencocokkan npk dengan tabel hp di $conn3 untuk mengambil no_hp
        $query3 = $conn3->prepare("SELECT no_hp FROM hp WHERE npk = ?");
        $query3->bind_param("s", $npk);

        if (!$query3->execute()) {
            $response['status'] = 'error';
            $response['message'] = "Gagal mengambil data no_hp: " . $query3->error;
            error_log(print_r($response, true));
            echo json_encode($response);
            exit;
        }

        $result3 = $query3->get_result();
        while ($row3 = $result3->fetch_assoc()) {
            $no_hp = $row3['no_hp'];

            // Memasukkan no_hp, message, dan flags (dengan default "queue") ke tabel notification_push
            $flags = "queue"; // Default flags

            $query4 = $conn->prepare("INSERT INTO notification_push (phone_number, `message`, flag) VALUES (?, ?, ?)");
            if (!$query4) {
                die("Prepare statement failed: " . $conn->error);
            }
            $query4->bind_param("sss", $no_hp, $message, $flags);

            if (!$query4->execute()) {
                $response['status'] = 'error';
                $response['message'] = "Gagal memasukkan data ke notification_push: " . $query4->error;
                error_log(print_r($response, true));
                echo json_encode($response);
                exit;
            }

            $query4->close();
        }

        $query3->close();
    }

    $query2->close();

    // Jika semua data berhasil disimpan
    $response['status'] = 'success';
    $response['message'] = 'Data berhasil dimasukkan ke database dan notifikasi terkirim.';
    error_log(print_r($response, true));
    echo json_encode($response);
}

$conn->close();
$conn2->close();
$conn3->close();
