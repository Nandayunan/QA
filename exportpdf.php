<?php
session_start();

// Initialize session variables if set
if (isset($_SESSION['npk'], $_SESSION['golongan'], $_SESSION['acting'])) {
    $npk = $_SESSION['npk'];
    $golongan = $_SESSION['golongan'];
    $acting = $_SESSION['acting'];
}

// Include required libraries
require_once __DIR__ . '/assets/phpqrcode/qrlib.php'; // Path to QR code library
require_once __DIR__ . '/assets/fpdf/fpdf.php'; // Path to FPDF library

// Include database configuration
include_once 'konfig.php';

// Initialize main MySQLi connection (for kayaba_project)
$mysqli = new mysqli('localhost', 'root', '', 'kayaba_project');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Initialize secondary MySQLi connection (for lembur)
$mysqli_lembur = new mysqli('localhost', 'root', '', 'lembur');
if ($mysqli_lembur->connect_error) {
    die('Connection failed to lembur: ' . $mysqli_lembur->connect_error);
}

// Fetch form data based on id_form
$id_form = isset($_GET['id_form']) ? (int)$_GET['id_form'] : 0;
if ($id_form <= 0) {
    die('Invalid ID.');
}

// Your existing SQL query
$sql2 = "SELECT testing.id_inspeksi, inspeksi.item_inspeksi, inspeksi.standar, inspeksi.alat, inspeksi.terukur, inspeksi.min, inspeksi.max, 
                inspeksi.id_tools, tools.nama_tools,
                testing.s1, testing.s2, testing.s3, testing.s4, testing.s5, testing.s6, 
                testing.s7, testing.s8, testing.s9, testing.s10, testing.status
         FROM testing
         LEFT JOIN inspeksi ON testing.id_inspeksi = inspeksi.id_inspeksi
         LEFT JOIN tools ON inspeksi.id_tools = tools.id_tools
         WHERE testing.id_form = ?
         ORDER BY testing.id_inspeksi ASC"; 


$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $id_form); // Assume $id_form is the variable holding the form ID
$stmt2->execute();
$result2 = $stmt2->get_result();

// Use $conn2 to get the user data
$sql_user = "SELECT full_name FROM ct_users WHERE npk = ?";
$stmt_user = $conn2->prepare($sql_user);
if (!$stmt_user) {
    die('Prepare failed: ' . $conn2->error);
}
$stmt_user->bind_param('s', $row['npk']);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_row = $result_user->fetch_assoc();

// Query to select notes based on id_form
$sql_notes = "
SELECT npk, note
FROM note
WHERE id_form = ?";

// Prepare and execute the statement
$stmt_notes = $conn->prepare($sql_notes);
$stmt_notes->bind_param("i", $id_form);
$stmt_notes->execute();
$result_notes = $stmt_notes->get_result();

$note_supervisor = "";
$note_manager = "";

// Loop through each note
while ($row_note = $result_notes->fetch_assoc()) {
    $npk = $row_note['npk'];
    $note = $row_note['note'];

    // Query to check golongan and acting for this npk in ct_users table
    $sql_user = "
    SELECT golongan, acting 
    FROM ct_users 
    WHERE npk = ?";
    
    $stmt_user = $conn2->prepare($sql_user);
    $stmt_user->bind_param("i", $npk);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    // Check if user with this npk exists
    if ($row_user = $result_user->fetch_assoc()) {
        $golongan = $row_user['golongan'];
        $acting = $row_user['acting'];

        // Check for supervisor note (golongan == 4, acting == 2)
        if ($golongan == 4 && $acting == 2) {
            $note_supervisor = $note;
        }
        // Check for manager note (golongan == 4, acting == 1)
        if ($golongan == 4 && $acting == 1) {
            $note_manager = $note;
        }
    }
}

// SQL to fetch inspection record
$sql = "SELECT no_ir.no_reg, no_ir.revisi, no_ir.no_ir, sub_subir.sub_subIR, no_ir.image,
        form.no_ppb, form.supplier, form.receive_qty, form.sampling_qty, form.prepare, form.check, form.approve, 
        form.tgl_prepare, form.tgl_check, form.tgl_approve
    FROM no_ir
    INNER JOIN sub_subir ON no_ir.id_subsubIR = sub_subir.id_subsubIR
    INNER JOIN form ON no_ir.id_noir = form.id_noir
    WHERE form.id_form = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $id_form);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) {
    die('No data found for the given ID.');
}

$no_ir = $row['no_ir']; // 'no_ir' field value
$tgl_approve = $row['tgl_approve']; // 'tgl_approve' field value
$approve = $row['approve']; // 'approve' field value
$tgl_check = $row['tgl_check']; // 'tgl_check' field value
$check = $row['check']; // 'check' field value
$tgl_prepare = $row['tgl_prepare']; // 'tgl_prepare' field value
$prepare = $row['prepare']; // 'prepare' field value

// Paths to save the QR code images
$qr_image_path_approve = 'assets/pdfqrcodes/qrcode_approve_' . $no_ir . '.png';
$qr_image_path_check = 'assets/pdfqrcodes/qrcode_check_' . $no_ir . '.png';
$qr_image_path_prepare = 'assets/pdfqrcodes/qrcode_prepare_' . $no_ir . '.png';

// Generate QR codes with the specified data in the required format
QRcode::png(
    "No IR: $no_ir\nTelah melakukan approve pada tanggal: $tgl_approve\nOleh: $approve", 
    $qr_image_path_approve, 
    QR_ECLEVEL_L, 
    1.8
);

QRcode::png(
    "No IR: $no_ir\nTelah dilakukan check pada tanggal: $tgl_check\nOleh: $check", 
    $qr_image_path_check, 
    QR_ECLEVEL_L, 
    1.8
);

QRcode::png(
    "No IR: $no_ir\nTelah dipersiapkan pada tanggal: $tgl_prepare\nOleh: $prepare", 
    $qr_image_path_prepare, 
    QR_ECLEVEL_L, 
    1.8
);



$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddFont('Times', '', 'times.php'); // Add Times font
$pdf->SetFont('Times', '', 9); // Use Times font

// Page 1
$pdf->AddPage();
$pdf->SetAutoPageBreak(false, 5);

// Header Content
$pdf->SetFont('Times', 'B', 9);
$pdf->Cell(50, 5, 'PT.KAYABA INDONESIA', 1, 1, 'C');
$pdf->Cell(50, 20, 'QUALITY ASSURANCE DEPT.', 1, 1, 'C');
$pdf->Cell(65, 10, 'SUPPLIER DATA : ' . $row['supplier'], 1, 1, 'C');

// Inspection Record Title
$pdf->SetXY(60, 10);
$pdf->Cell(85, 15, 'INSPECTION RECORD', 1, 1, 'C');

// Inspection Details
$pdf->SetXY(60, 25);
$pdf->Cell(85, 5, 'NO   : ' . $row['no_reg'], 1, 1);
$pdf->SetXY(60, 30);
$pdf->Cell(85, 5, 'REV : ' . $row['revisi'], 1, 1);
$pdf->SetXY(75, 35);
$pdf->Cell(70, 10, 'PART NAME : ' . $row['sub_subIR'], 1, 1, 'C');

$pdf->SetXY(145, 10);
$pdf->Cell(60, 8, 'NO SPB                 : ' . $row['no_ppb'], 1, 1);
$pdf->SetXY(145, 18);
$pdf->Cell(60, 7, 'RECEIVED QTY : ' . $row['receive_qty'], 1, 1);
$pdf->SetXY(145, 25);
$pdf->Cell(60, 5, 'CHECK DATE     : ' . date('Y-m-d'), 1, 1);
$pdf->SetXY(145, 30);
$pdf->Cell(60, 5, 'SAMPLING QTY : ' . $row['sampling_qty'], 1, 1);
$pdf->SetXY(145, 35);
$pdf->Cell(60, 10, 'PART NUMBER : ' . $row['no_ir'], 1, 1, 'C');

// For Image on Page 1
$pdf->SetXY(10, 45);
$pdf->Cell(195, 220, '', 1, 1);


$imagePath = './assets/uploads/' . $row['image'];
if ($row['image'] && file_exists($imagePath)) {
    $pdf->Image($imagePath, 20, 50, 180, 200);
} else {
    $pdf->Cell(195, 220, 'No Image Available', 1, 1, 'C');
}

// Start creating the PDF document
$pdf->AddPage();

// Set up header
$pdf->SetFont('Times', 'B', 9);
$pdf->SetXY(5, 10);
$pdf->Cell(50, 5, 'PT.KAYABA INDONESIA', 1, 1, 'C');
$pdf->SetXY(5, 15);
$pdf->Cell(50, 20, 'QUALITY ASSURANCE DEPT.', 1, 1, 'C');
$pdf->SetXY(5, 35);
$pdf->Cell(65, 10, 'SUPPLIER DATA : ' . $row['supplier'], 1, 1, 'C');

// Inspection Record Title
$pdf->SetXY(55, 10);
$pdf->Cell(85, 15, 'INSPECTION RECORD', 1, 1, 'C');

// Inspection Details (static values)
$pdf->SetXY(55, 25);
$pdf->Cell(85, 5, 'NO   : ' . $row['no_reg'], 1, 1);
$pdf->SetXY(55, 30);
$pdf->Cell(85, 5, 'REV : ' . $row['revisi'], 1, 1);
$pdf->SetXY(70, 35);
$pdf->Cell(70, 10, 'PART NAME : ' . $row['sub_subIR'], 1, 1, 'C');

// More inspection details (static values)
$pdf->SetXY(140, 10);
$pdf->Cell(65, 8, 'NO SPB                 : ' . $row['no_ppb'], 1, 1);
$pdf->SetXY(140, 18);
$pdf->Cell(65, 7, 'RECEIVED QTY : ' . $row['receive_qty'], 1, 1);
$pdf->SetXY(140, 25);
$pdf->Cell(65, 5, 'CHECK DATE     : ' . date('Y-m-d'), 1, 1);
$pdf->SetXY(140, 30);
$pdf->Cell(65, 5, 'SAMPLING QTY : ' . $row['sampling_qty'], 1, 1);
$pdf->SetXY(140, 35);
$pdf->Cell(65, 10, 'PART NUMBER : ' . $row['no_ir'], 1, 1, 'C');

// Table header
$pdf->SetXY(5, 45);
$pdf->Cell(10, 10, 'NO ', 1, 1, 'C');
$pdf->SetXY(15, 45);
$pdf->Cell(30, 10, 'INSPECTION ITEM ', 1, 1, 'C');
$pdf->SetXY(45, 45);
$pdf->Cell(25, 10, 'STANDARD ', 1, 1, 'C');
$pdf->SetXY(70, 45);
$pdf->Cell(20, 10, 'TOOLS ', 1, 1, 'C');
$pdf->SetXY(90, 45);
$pdf->Cell(100, 5, 'SAMPLING NUMBER ', 1, 1, 'C');
$pdf->SetXY(90, 50);
$pdf->Cell(10, 5, '1 ', 1, 1, 'C');

$pdf->SetXY(100, 50);
$pdf->Cell(10, 5, '2 ', 1, 1, 'C');

$pdf->SetXY(110, 50);
$pdf->Cell(10, 5, '3 ', 1, 1, 'C');

$pdf->SetXY(120, 50);
$pdf->Cell(10, 5, '4 ', 1, 1, 'C');

$pdf->SetXY(130, 50);
$pdf->Cell(10, 5, '5 ', 1, 1, 'C');

$pdf->SetXY(140, 50);
$pdf->Cell(10, 5, '6 ', 1, 1, 'C');

$pdf->SetXY(150, 50);
$pdf->Cell(10, 5, '7 ', 1, 1, 'C');

$pdf->SetXY(160, 50);
$pdf->Cell(10, 5, '8 ', 1, 1, 'C');

$pdf->SetXY(170, 50);
$pdf->Cell(10, 5, '9 ', 1, 1, 'C');

$pdf->SetXY(180, 50);
$pdf->Cell(10, 5, '10 ', 1, 1, 'C');

$pdf->SetXY(190, 45);
$pdf->Cell(15, 10, 'JUDGE ', 1, 1, 'C');


// Initialize variables to check statuses
$hasNgZero = false; // Flag to track if any status is "NG" (0)
$allStatusOk = true; // Flag to track if all statuses are "OK" (1)

$rowYPosition = 55;
$no = 1;
$rowCount = 0; // To count rows and handle pagination

function adjustTextToFitCell($pdf, $x, $y, $w, $h, $text, $border = 1, $align = 'C') {
    $pdf->SetXY($x, $y);
    $fontSize = 10; // Ukuran font awal
    $pdf->SetFont('Times', '', $fontSize);

    // Cek apakah teks muat dalam satu baris
    if ($pdf->GetStringWidth($text) > $w) {
        // Jika tidak muat, kecilkan font hingga pas
        while ($pdf->GetStringWidth($text) > $w && $fontSize > 5) {
            $fontSize--;
            $pdf->SetFont('Times', '', $fontSize);
        }
    }

    // Jika teks tetap panjang, pecah menjadi multiline
    if ($pdf->GetStringWidth($text) > $w) {
        $pdf->MultiCell($w, $h, $text, $border, $align);
    } else {
        // Cetak teks dalam satu baris
        $pdf->Cell($w, $h, $text, $border, 0, $align);
    }
}


// Function to print header
function printHeader($pdf, $row) {
    // Set up header
    $pdf->SetFont('Times', 'B', 9);
    $pdf->SetXY(5, 10);
    $pdf->Cell(50, 5, 'PT.KAYABA INDONESIA', 1, 1, 'C');
    $pdf->SetXY(5, 15);
    $pdf->Cell(50, 20, 'QUALITY ASSURANCE DEPT.', 1, 1, 'C');
    $pdf->SetXY(5, 35);
    $pdf->Cell(65, 10, 'SUPPLIER DATA : ' . $row['supplier'], 1, 1, 'C');

    // Inspection Record Title
    $pdf->SetXY(55, 10);
    $pdf->Cell(85, 15, 'INSPECTION RECORD', 1, 1, 'C');

    // Inspection Details (static values)
    $pdf->SetXY(55, 25);
    $pdf->Cell(85, 5, 'NO   : ' . $row['no_reg'], 1, 1);
    $pdf->SetXY(55, 30);
    $pdf->Cell(85, 5, 'REV : ' . $row['revisi'], 1, 1);
    $pdf->SetXY(70, 35);
    $pdf->Cell(70, 10, 'PART NAME : ' . $row['sub_subIR'], 1, 1, 'C');

    // More inspection details (static values)
    $pdf->SetXY(140, 10);
    $pdf->Cell(65, 8, 'NO SPB                 : ' . $row['no_ppb'], 1, 1);
    $pdf->SetXY(140, 18);
    $pdf->Cell(65, 7, 'RECEIVED QTY : ' . $row['receive_qty'], 1, 1);
    $pdf->SetXY(140, 25);
    $pdf->Cell(65, 5, 'CHECK DATE     : ' . date('Y-m-d'), 1, 1);
    $pdf->SetXY(140, 30);
    $pdf->Cell(65, 5, 'SAMPLING QTY : ' . $row['sampling_qty'], 1, 1);
    $pdf->SetXY(140, 35);
    $pdf->Cell(65, 10, 'PART NUMBER : ' . $row['no_ir'], 1, 1, 'C');

    // Table header
    $pdf->SetXY(5, 45);
    $pdf->Cell(10, 10, 'NO ', 1, 1, 'C');
    $pdf->SetXY(15, 45);
    $pdf->Cell(30, 10, 'INSPECTION ITEM ', 1, 1, 'C');
    $pdf->SetXY(45, 45);
    $pdf->Cell(25, 10, 'STANDARD ', 1, 1, 'C');
    $pdf->SetXY(70, 45);
    $pdf->Cell(20, 10, 'TOOLS ', 1, 1, 'C');
    $pdf->SetXY(90, 45);
    $pdf->Cell(100, 5, 'SAMPLING NUMBER ', 1, 1, 'C');
    $pdf->SetXY(90, 50);
    $pdf->Cell(10, 5, '1 ', 1, 1, 'C');
    $pdf->SetXY(100, 50);
    $pdf->Cell(10, 5, '2 ', 1, 1, 'C');
    $pdf->SetXY(110, 50);
    $pdf->Cell(10, 5, '3 ', 1, 1, 'C');
    $pdf->SetXY(120, 50);
    $pdf->Cell(10, 5, '4 ', 1, 1, 'C');
    $pdf->SetXY(130, 50);
    $pdf->Cell(10, 5, '5 ', 1, 1, 'C');
    $pdf->SetXY(140, 50);
    $pdf->Cell(10, 5, '6 ', 1, 1, 'C');
    $pdf->SetXY(150, 50);
    $pdf->Cell(10, 5, '7 ', 1, 1, 'C');
    $pdf->SetXY(160, 50);
    $pdf->Cell(10, 5, '8 ', 1, 1, 'C');
    $pdf->SetXY(170, 50);
    $pdf->Cell(10, 5, '9 ', 1, 1, 'C');
    $pdf->SetXY(180, 50);
    $pdf->Cell(10, 5, '10 ', 1, 1, 'C');
    $pdf->SetXY(190, 45);
    $pdf->Cell(15, 10, 'JUDGE ', 1, 1, 'C');
}

while ($row2 = $result2->fetch_assoc()) {
    if ($rowCount >= 20) {
        $pdf->AddPage();
        $rowYPosition = 55;
        $rowCount = 0;
        printHeader($pdf, $row);
    }

    $xStart = 5;
    $height = 10; // Tinggi cell tetap

    // Kolom NO
    $pdf->SetXY($xStart, $rowYPosition);
    $pdf->Cell(10, $height, $no, 1, 1, 'C');
    $xStart += 10;

    // Kolom INSPECTION ITEM
    adjustTextToFitCell($pdf, $xStart, $rowYPosition, 30, $height, isset($row2['item_inspeksi']) ? $row2['item_inspeksi'] : '');
    $xStart += 30;

    // Kolom STANDARD
    adjustTextToFitCell($pdf, $xStart, $rowYPosition, 25, $height, isset($row2['standar']) ? iconv('UTF-8', 'windows-1252', $row2['standar']) : '');
    $xStart += 25;

    // Kolom TOOLS
    adjustTextToFitCell($pdf, $xStart, $rowYPosition, 20, $height, isset($row2['nama_tools']) ? $row2['nama_tools'] : '');
    $xStart += 20;

    // Kolom SAMPLING NUMBER (S1 hingga S10)
    for ($i = 1; $i <= 10; $i++) {
        $samplingNumber = isset($row2['s' . $i]) ? $row2['s' . $i] : ''; // Ambil nilai sampling number

        // Logika untuk kolom sampling number berdasarkan nilai 'terukur'
        if ($row2['terukur'] == 0) {
            $samplingNumber = ($samplingNumber == 1) ? 'OK' : ''; // Tampilkan 'OK' jika 1, kosong jika 0
        } else {
            $samplingNumber = ($samplingNumber == 0 || $samplingNumber === null) ? '' : $samplingNumber; // Kosong jika 0/null
        }

        $pdf->SetXY($xStart, $rowYPosition);
        $pdf->Cell(10, $height, $samplingNumber, 1, 1, 'C');
        $xStart += 10;
    }

    // Kolom JUDGE
    $status = ($row2['status'] == 1) ? 'OK' : 'NG'; // Judge berdasarkan status
    if ($status == 'NG') {
        $pdf->SetFillColor(255, 0, 0); // Background merah untuk NG
    } else {
        $pdf->SetFillColor(255, 255, 255); // Background putih untuk OK
    }
    $pdf->SetXY($xStart, $rowYPosition);
    $pdf->Cell(15, $height, $status, 1, 1, 'C', 1); // Isi cell dengan warna latar

    // Increment posisi dan counter
    $rowYPosition += $height;
    $no++;
    $rowCount++;
}

// Tambahkan baris kosong jika kurang dari 20 baris
while ($rowCount < 20) {
    $xStart = 5;

    // Kolom NO
    $pdf->SetXY($xStart, $rowYPosition);
    $pdf->Cell(10, $height, $no, 1, 1, 'C');
    $xStart += 10;

    // Kolom kosong untuk INSPECTION ITEM, STANDARD, TOOLS
    $pdf->SetXY($xStart, $rowYPosition);
    $pdf->Cell(30, $height, ' ', 1, 1, 'C');
    $xStart += 30;

    $pdf->SetXY($xStart, $rowYPosition);
    $pdf->Cell(25, $height, ' ', 1, 1, 'C');
    $xStart += 25;

    $pdf->SetXY($xStart, $rowYPosition);
    $pdf->Cell(20, $height, ' ', 1, 1, 'C');
    $xStart += 20;

    // Kolom kosong untuk SAMPLING NUMBER (S1 hingga S10)
    for ($i = 1; $i <= 10; $i++) {
        $pdf->SetXY($xStart, $rowYPosition);
        $pdf->Cell(10, $height, ' ', 1, 1, 'C');
        $xStart += 10;
    }

    // Kolom kosong untuk JUDGE
    $pdf->SetFillColor(255, 255, 255); // Background putih
    $pdf->SetXY($xStart, $rowYPosition);
    $pdf->Cell(15, $height, ' ', 1, 1, 'C', 1);

    // Increment posisi dan counter
    $rowYPosition += $height;
    $no++;
    $rowCount++;
}

// Tentukan hasil akhir HASIL JUDGE
$hasilJudge = ($hasNgZero) ? 'NG' : ($allStatusOk ? 'OK' : 'Mixed');




// FOOTER

// Calculate the Y position for the footer, leaving some space at the bottom (e.g., 15mm from the bottom)
$pageHeight = $pdf->GetPageHeight(); // Get the page height
$footerYPosition = $pageHeight - 45; // Adjust as necessary for spacing

// Display the HASIL JUDGE result
$pdf->SetXY(5, $footerYPosition); // Position HASIL JUDGE label
$pdf->Cell(35, 10, 'HASIL JUDGE', 1, 1, 'C');

// Increase font size for HASIL JUDGE result
$pdf->SetFont('Arial', 'B', 40); // Set to bold and size 40
$pdf->SetXY(5, $footerYPosition + 10);
$pdf->Cell(35, 25, $hasilJudge, 1, 1, 'C'); // Display enlarged HASIL JUDGE result

// Reset font size if needed for other parts of the document
$pdf->SetFont('Arial', '', 10); // Reset to default font size

// Notes for Supervisor and Manager
$pdf->SetXY(40, $footerYPosition);
$pdf->Cell(90, 18, 'Note Supervisor: ' . $note_supervisor , 1, 1, 'C');
$pdf->SetXY(40, $footerYPosition + 18);
$pdf->Cell(90, 17, 'Note Manager: ' . $note_manager , 1, 1, 'C');


$pdf->SetFont('Arial', '', 8); // Reset to default font size
// APPROVE BY section
$pdf->SetXY(130, $footerYPosition);
$pdf->Cell(25, 5, 'APPROVE BY: ', 1, 1, 'C');

// Empty cell where the QR code will be placed for APPROVE BY
$pdf->SetXY(130, $footerYPosition + 5);
$pdf->Cell(25, 25, ' ', 1, 1, 'C');

// Place the QR code inside the empty cell for APPROVE BY
$pdf->Image($qr_image_path_approve, 133, $footerYPosition + 8, 20, 20);  // QR code adjusted to cell size
$pdf->SetXY(130, $footerYPosition + 30);
$pdf->Cell(25, 5, 'Hasoloan M', 1, 1, 'C');

// CHECKED BY section
$pdf->SetXY(155, $footerYPosition);
$pdf->Cell(25, 5, 'CHECKED BY: ', 1, 1, 'C');

// Empty cell where the QR code will be placed for CHECKED BY
$pdf->SetXY(155, $footerYPosition + 5);
$pdf->Cell(25, 25, ' ', 1, 1, 'C');

// Place the QR code inside the empty cell for CHECKED BY
$pdf->Image($qr_image_path_check, 157, $footerYPosition + 8, 20, 20);  // QR code adjusted to cell size
$pdf->SetXY(155, $footerYPosition + 30);
$pdf->Cell(25, 5, 'Panji A', 1, 1, 'C');

// PREPARED BY section
$pdf->SetXY(180, $footerYPosition);
$pdf->Cell(25, 5, 'PREPARED BY: ', 1, 1, 'C');

// Empty cell where the QR code will be placed for PREPARED BY
$pdf->SetXY(180, $footerYPosition + 5);
$pdf->Cell(25, 25, ' ', 1, 1, 'C');

// Place the QR code inside the empty cell for PREPARED BY
$pdf->Image($qr_image_path_prepare, 183, $footerYPosition + 8, 20, 20);  // QR code adjusted to cell size
$pdf->SetXY(180, $footerYPosition + 30);
$pdf->Cell(25, 5, 'Operator', 1, 1, 'C');








// Further content for Page 2
// Add any additional content for page 2 here as needed

$pdf->Output();
?>
