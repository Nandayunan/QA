<?php
include 'konfig.php';

header('Content-Type: application/json');
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil id_form dan id_inspeksi dari request
    $id_form = $_POST['id_form'];
    $no_ir = $_POST['no_ir'];
    $id_inspeksi = $_POST['id_inspeksi']; // Ambil ID inspeksi

    // Query untuk memilih data dari tabel 'form'
    $selectForm = "SELECT id_inspeksi FROM inspeksi WHERE no_ir = ?";
    $stmtSelectForm = $conn->prepare($selectForm);
    $stmtSelectForm->bind_param('s', $no_ir);
    $stmtSelectForm->execute();
    $result = $stmtSelectForm->get_result();

    // Loop untuk setiap row dari hasil query
    while ($row = $result->fetch_assoc()) {
        $checkQuery = "SELECT COUNT(id_buffer) FROM buffer WHERE id_form = ? AND id_inspeksi = ?";
        $stmtCheck = $conn->prepare($checkQuery);
        $stmtCheck->bind_param('ii', $id_form, $row['id_inspeksi']);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        $stmtCheck->close();

        // Jika tidak ada record dengan id_form dan id_inspeksi yang sama, maka insert
        if ($count == 0) {
            $insertQueryDefault = "INSERT INTO buffer (id_form, id_inspeksi, s1, s2, s3, s4, s5, s6, s7, s8, s9, s10, status)
                           VALUES (?, ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0)";
            $stmtInsertDefault = $conn->prepare($insertQueryDefault);
            $stmtInsertDefault->bind_param('ii', $id_form, $row['id_inspeksi']);

            if (!$stmtInsertDefault->execute()) {
                $response[] = "Gagal memasukkan data untuk id_inspeksi {$row['id_inspeksi']}: " . $stmtInsertDefault->error;
            } else {
                $response[] = "Data default untuk id_inspeksi {$row['id_inspeksi']} berhasil dimasukkan.";
            }
        } else {
            $response[] = "Data dengan id_form {$id_form} dan id_inspeksi {$row['id_inspeksi']} sudah ada.";
        }


        // Untuk data yang dikirim pengguna, masukkan nilai sesuai input
        if (isset($id_inspeksi)) {
            // Ambil data sampling yang dikirim pengguna untuk id_inspeksi ini
            // Mengambil data dari payload (contoh data yang dikirim)
            $id_form = $_POST['id_form']; // Misalnya, id_form = 243
            $id_inspeksi = $_POST['id_inspeksi']; // Misalnya, id_inspeksi = 83
            $sampling1 = $_POST['sampling1'] ?: 0.00; // Menggunakan nilai 0 jika tidak ada nilai
            $sampling2 = $_POST['sampling2'] ?: 0.00;
            $sampling3 = $_POST['sampling3'] ?: 0.00;
            $sampling4 = $_POST['sampling4'] ?: 0.00;
            $sampling5 = $_POST['sampling5'] ?: 0.00;
            $sampling6 = $_POST['sampling6'] ?: 0.00;
            $sampling7 = $_POST['sampling7'] ?: 0.00;
            $sampling8 = $_POST['sampling8'] ?: 0.00;
            $sampling9 = $_POST['sampling9'] ?: 0.00;
            $sampling10 = $_POST['sampling10'] ?: 0.00;
            $status = $_POST['status']; // Status = 1

            // Query Update untuk memperbarui data di buffer
            $updateQueryUser = "
                UPDATE buffer
                SET 
                    s1 = ?, 
                    s2 = ?, 
                    s3 = ?, 
                    s4 = ?, 
                    s5 = ?, 
                    s6 = ?, 
                    s7 = ?, 
                    s8 = ?, 
                    s9 = ?, 
                    s10 = ?, 
                    status = ?
                WHERE 
                    id_form = ? AND 
                    id_inspeksi = ?
            ";

            // Persiapkan statement SQL
            $stmtUpdateUser = $conn->prepare($updateQueryUser);

            // Bind parameter untuk update query
            $stmtUpdateUser->bind_param(
                'ddddddddddiii', // Menyesuaikan tipe data: d = double, i = integer
                $sampling1,
                $sampling2,
                $sampling3,
                $sampling4,
                $sampling5,
                $sampling6,
                $sampling7,
                $sampling8,
                $sampling9,
                $sampling10,
                $status,
                $id_form,
                $id_inspeksi[0]
            );

            // Eksekusi query dan periksa apakah berhasil
            if (!$stmtUpdateUser->execute()) {
                // Jika eksekusi gagal
                $response[] = "Gagal memperbarui data untuk id_inspeksi {$id_inspeksi}: " . $stmtUpdateUser->error;
            } else {
                // Jika berhasil
                $response[] = "Data untuk id_inspeksi {$id_inspeksi[0]} berhasil diperbarui.";
            }

            // Tutup statement dan koneksi
            $stmtUpdateUser->close();
        }
    }

    // Tutup statement dan koneksi
    $stmtSelectForm->close();
    $conn->close();

    // Kembalikan respons dalam format JSON
    // $response = array(
    //     "status" => "success",
    //     "message" => "Data berhasil diproses.",
    //     "id_inspeksi" => $id_inspeksi
    // );

    // Mengirim respons dalam format JSON
    // echo json_encode($response);

    echo json_encode($response);
}
