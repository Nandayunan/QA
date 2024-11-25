<?php
// Koneksi ke database
require_once '../konfig.php'; // Adjust the path as necessary

// Ambil id_inspeksi dari request GET
$id_inspeksi = isset($_GET['id_inspeksi']) ? $_GET['id_inspeksi'] : null;

if ($id_inspeksi) {
    // Query untuk mengambil data inspeksi berdasarkan id_inspeksi
    $query = "SELECT i.id_inspeksi, i.no_ir, i.item_inspeksi, i.standar, i.alat, i.min, i.max, i.terukur, t.nama_tools 
              FROM inspeksi i
              LEFT JOIN tools t ON i.alat = t.id_tools
              WHERE i.id_inspeksi = ?";

    // Persiapkan dan eksekusi query
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('i', $id_inspeksi); // Bind parameter id_inspeksi
        $stmt->execute();
        $result = $stmt->get_result();

        // Periksa apakah data ditemukan
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc(); // Ambil data sebagai array asosiatif
            // Kirimkan data dalam format JSON
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Data inspeksi tidak ditemukan.'
            ]);
        }

        // Tutup statement
        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan dalam query.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID inspeksi tidak ditemukan.'
    ]);
}

// Tutup koneksi
$conn->close();
