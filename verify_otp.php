<?php
session_start();
require_once(__DIR__ . '/konfig.php'); // Sesuaikan path koneksi Anda

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil input OTP dari form
    $otp1 = $_POST['otp1'];
    $otp2 = $_POST['otp2'];
    $otp3 = $_POST['otp3'];
    $otp4 = $_POST['otp4'];
    $otp5 = $_POST['otp5'];
    $otp6 = $_POST['otp6'];

    $entered_otp = $otp1 . $otp2 . $otp3 . $otp4 . $otp5 . $otp6; // Gabungkan OTP dari setiap field
    $npk = $_SESSION['npk']; // Ambil NPK dari session

    // Query ke database untuk mengecek OTP
    $sql = "SELECT otp FROM otp WHERE npk = '$npk'";
    $result = mysqli_query($conn, $sql); // Menggunakan koneksi $conn sesuai dengan variabel Anda

    if ($row = mysqli_fetch_assoc($result)) {
        $stored_otp = $row['otp'];
        if ($entered_otp == $stored_otp) {
            // Jika OTP benar, set flag session dan return sukses
            $_SESSION['otp_verified'] = true;
            echo json_encode(['status' => 'success', 'redirect_url' => $_SESSION["redirect_url"]]);
        } else {
            // Jika OTP salah, kirim pesan error
            echo json_encode(['status' => 'error', 'message' => 'OTP yang Anda masukkan salah.']);
        }
    } else {
        // Jika tidak ada OTP yang ditemukan di database
        echo json_encode(['status' => 'error', 'message' => 'OTP tidak valid. Coba lagi.']);
    }

    mysqli_close($conn); // Tutup koneksi database
    exit;
}

// // Cek apakah form OTP sudah disubmit
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp1'])) {
//     // Ambil input OTP dari form
//     $otp_input = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];

//     // Ambil OTP yang tersimpan di session
//     $npk = $_SESSION['npk'];

//     // Query ke database untuk mengambil OTP dari tabel
//     $query = "SELECT otp FROM otp WHERE npk = '$npk'";
//     $result = mysqli_query($conn, $query);

//     if ($result && mysqli_num_rows($result) > 0) {
//         $row = mysqli_fetch_assoc($result);
//         $stored_otp = $row['otp'];

//         // Cek apakah OTP yang dimasukkan sesuai dengan yang di database
//         if ($otp_input === $stored_otp) {
//             // Ambil golongan dan acting dari session
//             $golongan = $_SESSION['golongan'];
//             $acting = $_SESSION['acting'];

//             // Tentukan arah berdasarkan golongan dan acting
//             if ($golongan == 2 && $acting == 2) {
//                 header('Location: index.php');
//                 exit();
//             } elseif ($golongan == 4 && $acting == 2) {
//                 header('Location: approve.php');
//                 exit();
//             } elseif ($golongan == 4 && $acting == 1) {
//                 header('Location: approve.php');
//                 exit();
//             }
//         } else {
//             // OTP salah, simpan pesan error di session untuk tampilkan alert menggunakan SweetAlert
//             echo '<script>showAlert("OTP salah, silakan coba lagi.", "error");</script>';
//         }
//     } else {
//         // Terjadi kesalahan pada query
//         echo '<script>showAlert("Terjadi kesalahan, coba lagi.", "error", "login.php");</script>';
//     }
// }
