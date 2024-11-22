<?php
include 'konfig.php';

// Set the response header to JSON
header('Content-Type: application/json');

$response = array(); // Array untuk menyimpan respons

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data POST
    $id_form = $_POST['id_form'];
    $id_inspeksi = $_POST['id_inspeksi']; // This will be an array, loop through it later


    // Approve default value
    // $status = 1; // Hardcoded status, make sure this is appropriate

    // Loop through each row of inspection data
    foreach ($id_inspeksi as $index => $id_ins) {
        $row_index = $index + 1;

        $status = isset($_POST["status{$row_index}"]) ? (int) $_POST["status{$row_index}"] : 1;

        // Extract sampling data for this row
        $s1 = isset($_POST["sampling1"]) ? (float) $_POST["sampling1"] : 0;
        $s2 = isset($_POST["sampling2"]) ? (float) $_POST["sampling2"] : 0;
        $s3 = isset($_POST["sampling3"]) ? (float) $_POST["sampling3"] : 0;
        $s4 = isset($_POST["sampling4"]) ? (float) $_POST["sampling4"] : 0;
        $s5 = isset($_POST["sampling5"]) ? (float) $_POST["sampling5"] : 0;
        $s6 = isset($_POST["sampling6"]) ? (float) $_POST["sampling6"] : 0;
        $s7 = isset($_POST["sampling7"]) ? (float) $_POST["sampling7"] : 0;
        $s8 = isset($_POST["sampling8"]) ? (float) $_POST["sampling8"] : 0;
        $s9 = isset($_POST["sampling9"]) ? (float) $_POST["sampling9"] : 0;
        $s10 = isset($_POST["sampling10"]) ? (float) $_POST["sampling10"] : 0;

        // Query insert langsung ke tabel buffer
        $insertQuery = "INSERT INTO buffer 
            (id_form, id_inspeksi, s1, s2, s3, s4, s5, s6, s7, s8, s9, s10, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insertQuery);
        if ($stmt) {
            // Sesuaikan jumlah variabel dengan placeholder (?) dalam query
            $stmt->bind_param(
                'iiddddddddddi', // Data types: i = integer, d = double
                $id_form,
                $id_ins,
                $s1,
                $s2,
                $s3,
                $s4,
                $s5,
                $s6,
                $s7,
                $s8,
                $s9,
                $s10,
                $status
            );

            if (!$stmt->execute()) {
                // Jika gagal, masukkan pesan error ke dalam respons
                $response[] = "Gagal memasukkan data untuk id_inspeksi {$id_ins}: " . $stmt->error;
            } else {
                // Jika berhasil, tambahkan pesan sukses ke dalam respons
                $response[] = "Data untuk id_inspeksi {$id_ins} berhasil dimasukkan.";
            }
        } else {
            // Jika statement gagal disiapkan, masukkan pesan error ke dalam respons
            $response[] = "Gagal menyiapkan statement: " . $conn->error;
        }
    }

    // Tutup statement dan koneksi
    $stmt->close();
    $conn->close();

    // Kembalikan respons sebagai JSON
    echo json_encode($response);
}
