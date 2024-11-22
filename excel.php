<?php
session_start();

if (isset($_SESSION['npk']) && isset($_SESSION['golongan']) && isset($_SESSION['acting'])) {
    $npk = $_SESSION['npk'];
    $golongan = $_SESSION['golongan'];
    $acting = $_SESSION['acting'];
}

require_once __DIR__ . '/assets/PHPExcel/Classes/PHPExcel.php'; // Path to PHPExcel
require_once __DIR__ . '/assets/phpqrcode/qrlib.php'; // Path to QR

include_once 'konfig.php'; // Ensure this file initializes $mysqli correctly

// If $mysqli is not set, manually initialize it
if (!isset($mysqli)) {
    $host = 'localhost'; // Replace with your database host
    $user = 'root'; // Replace with your database username
    $password = ''; // Replace with your database password
    $dbname = 'kayaba_project'; // Replace with your database name

    $mysqli = new mysqli($host, $user, $password, $dbname);

    if ($mysqli->connect_error) {
        die('Connection failed: ' . $mysqli->connect_error);
    }
}

// Join `no_ir`, `sub_subir`, and `form` tables based on relationships in the schema
$sql = "
    SELECT 
        no_ir.no_reg, no_ir.revisi, no_ir.no_ir, sub_subir.sub_subIR, no_ir.image,
        form.no_ppb, form.receive_qty, form.sampling_qty, form.prepare, form.check, form.approve, form.tgl_prepare, form.tgl_check, form.tgl_approve
    FROM no_ir
    INNER JOIN sub_subir ON no_ir.id_subsubIR = sub_subir.id_subsubIR
    INNER JOIN form ON no_ir.id_noir = form.id_noir
    WHERE form.id_form = ?";

// Prepare the statement
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die('Prepare failed: ' . $mysqli->error);
}

// Assuming `id_form` is passed as a parameter in the URL
$id_form = isset($_GET['id_form']) ? (int)$_GET['id_form'] : 0;
if ($id_form <= 0) {
    die('Invalid ID.');
}
$stmt->bind_param('i', $id_form);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();
if (!$result) {
    die('Execute failed: ' . $stmt->error);
}

// Fetch data
$row = $result->fetch_assoc();
if (!$row) {
    die('No data found for the given ID.');
}

$no_ir = $row['no_ir']; // Get the 'no_ir' field value

// Your existing SQL query
$sql2 = "SELECT testing.id_inspeksi, inspeksi.item_inspeksi, inspeksi.standar, inspeksi.alat, inspeksi.min, inspeksi.max, inspeksi.id_tools,
                testing.s1, testing.s2, testing.s3, testing.s4, testing.s5, testing.s6, 
                testing.s7, testing.s8, testing.s9, testing.s10, testing.status
         FROM testing
         LEFT JOIN inspeksi ON testing.id_inspeksi = inspeksi.id_inspeksi
         WHERE testing.id_form = ?
         ORDER BY testing.id_inspeksi ASC"; // Ensure data is sorted by id_inspeksi

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

// Fetch notes and assign based on conditions
while ($row_note = $result_notes->fetch_assoc()) {
    if ($npk == $row_note['npk']) {
        if ($golongan == 4 && $acting == 2) {
            $note_supervisor = $row_note['note'];
        } elseif ($golongan == 4 && $acting == 1) {
            $note_manager = $row_note['note'];
        }
    }
}

// Variables
$npk = isset($_SESSION['npk']) ? $_SESSION['npk'] : '';
$no_ir = isset($row['no_ir']) ? $row['no_ir'] : '';
$prepare = isset($row['prepare']) ? $row['prepare'] : '';
$tgl_prepare = isset($row['tgl_prepare']) ? $row['tgl_prepare'] : '';
$check = isset($row['check']) ? $row['check'] : '';
$tgl_check = isset($row['tgl_check']) ? $row['tgl_check'] : '';
$approve = isset($row['approve']) ? $row['approve'] : '';
$tgl_approve = isset($row['tgl_approve']) ? $row['tgl_approve'] : '';
$full_name = isset($user_row['full_name']) ? $user_row['full_name'] : '';

// Generate QR data strings
$qr_data_approve = "No IR: " . $no_ir . "\nNama: " . $approve . "\nTanggal Approve : " . $tgl_approve;
$qr_data_check = "No IR: " . $no_ir . "\nNama: " . $check . "\nTanggal Check : " . $tgl_check;
$qr_data_prepare = "No IR: " . $no_ir . "\nNama: " . $prepare . "\nTanggal Prepare : " . $tgl_prepare;

// Paths to save the QR code images
$qr_image_path_approve = 'assets/pdfqrcodes/qrcode_approve_' . $no_ir . '.png';
$qr_image_path_check = 'assets/pdfqrcodes/qrcode_check_' . $no_ir . '.png';
$qr_image_path_prepare = 'assets/pdfqrcodes/qrcode_prepare_' . $no_ir . '.png';

// Generate QR codes with the specified data
QRcode::png($qr_data_approve, $qr_image_path_approve, QR_ECLEVEL_L, 1.8);
QRcode::png($qr_data_check, $qr_image_path_check, QR_ECLEVEL_L, 1.8);
QRcode::png($qr_data_prepare, $qr_image_path_prepare, QR_ECLEVEL_L, 1.8);

// Define the dimensions of the merged cells in pixels
$cell_width_px = 120;  // Adjust based on the actual width of your merged cells in pixels
$cell_height_px = 60;  // Adjust based on the actual height of your merged cells in pixels

// Get image dimensions in pixels
list($image_width, $image_height) = getimagesize($qr_image_path_approve);

// Calculate offsets to center the image within the merged cells
$offsetX = ($cell_width_px - $image_width) / 4; // Offset to center horizontally
$offsetY = ($cell_height_px - $image_height) / 3; // Offset to center vertically


// if ($result->num_rows > 0) {
//     $response = $result->fetch_assoc();

//     $sql_testing = "SELECT testing.id_inspeksi, inspeksi.item_inspeksi, inspeksi.standar, inspeksi.alat, testing.s1, testing.s2, testing.s3, testing.s4, testing.s5, testing.s6, testing.s7, testing.s8, testing.s9, testing.s10, testing.approve AS approve_status
//                     FROM testing
//                     LEFT JOIN inspeksi ON testing.id_inspeksi = inspeksi.id_inspeksi
//                     WHERE testing.id_form = ?";
//     $stmt_testing = $conn->prepare($sql_testing);
//     $stmt_testing->bind_param('i', $id_form);
//     $stmt_testing->execute();
//     $result_testing = $stmt_testing->get_result();

//     $response['testingResults'] = array();

//     while ($row = $result_testing->fetch_assoc()) {
//         $response['testingResults'][] = $row;
//     }

// } else {
//     $response['error'] = 'No data found';
// }


//generate QR

// Create PHPExcel object
$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet(0);

// Set paper size to A4
$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

// Set orientation to portrait
$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);

// Set margins (in inches)
$sheet->getPageMargins()->setTop(0.5);
$sheet->getPageMargins()->setRight(0.5);
$sheet->getPageMargins()->setLeft(0.5);
$sheet->getPageMargins()->setBottom(0.5);

// Scale sheet to fit on one page
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

// Protect the sheet to make it read-only
$sheet->getProtection()->setSheet(true); // Enable sheet protection

// Add password to protect the sheet
$sheet->getProtection()->setPassword('QA_LAB'); // Ganti 'your_password' dengan password Anda

// Disable various actions
$sheet->getProtection()->setInsertRows(false); // Disable inserting rows
$sheet->getProtection()->setInsertColumns(false); // Disable inserting columns
$sheet->getProtection()->setDeleteRows(false); // Disable deleting rows
$sheet->getProtection()->setDeleteColumns(false); // Disable deleting columns
$sheet->getProtection()->setFormatCells(false); // Disable formatting cells
$sheet->getProtection()->setSort(false); // Disable sorting
$sheet->getProtection()->setObjects(false); // Disable object modification
$sheet->getProtection()->setScenarios(false); // Disable scenario editing


// Function to apply header style
function applyHeaderStyle($sheet, $range, $headerStyle)
{
    $sheet->getStyle($range)->applyFromArray($headerStyle);
}

// Create the header style array
$headerStyle = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
        ),
    ),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'FFFFFF')
    ),
    'font' => array(
        'bold' => true,
        'size' =>  8,
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    ),
);

// Fill data into Excel according to the layout
$sheet->setCellValue('A1', 'PT KAYABA INDONESIA');
$sheet->mergeCells('A1:C1');
$sheet->getStyle('A1:C1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

$sheet->setCellValue('A3', 'QUALITY ASSURANCE DEPARTMENT');
$sheet->mergeCells('A2:C4', 'QUALITY ASSURANCE DEPARTEMENT');
$sheet->getStyle('A2:C4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

// Set column width for C
$sheet->getColumnDimension('C')->setWidth(16); // 116 pixels is approximately 16 Excel units

$sheet->setCellValue('D1', 'INSPECTION RECORD');
$sheet->mergeCells('D1:L2');
$sheet->getStyle('D1:L2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

$sheet->setCellValue('D3', 'NO : ' . $row['no_reg']);
$sheet->mergeCells('D3:L3');
$sheet->setCellValue('D4', 'REV : ' . $row['revisi']);
$sheet->mergeCells('D4:L4');

// Borders
$sheet->getStyle('A4:Q4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle('C1:C4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

$sheet->setCellValue('M1', 'NO SPB : ' . $row['no_ppb'])
    ->mergeCells('M1:Q1')
    ->getStyle('M1:Q1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$sheet->setCellValue('M2', 'RECEIVED QTY : ' . $row['receive_qty'])
    ->mergeCells('M2:Q2')
    ->getStyle('M2:Q2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$sheet->setCellValue('M3', 'CHECK DATE : ' . date('Y-m-d'))
    ->mergeCells('M3:Q3')
    ->getStyle('M3:Q3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$sheet->setCellValue('M4', 'SAMPLING QTY : ' . $row['sampling_qty'])
    ->mergeCells('M4:Q4')
    ->getStyle('M4:Q4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

// Merge and center for supplier, part name, and part number
$sheet->mergeCells('A5:E6');
$sheet->getStyle('A5:E6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet->setCellValue('A5', 'SUPPLIER DATA');

$sheet->mergeCells('F5:L6');
$sheet->getStyle('F5:L6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet->setCellValue('F5', 'PART NAME: ' . $row['sub_subIR']);

$sheet->mergeCells('M5:Q6');
$sheet->getStyle('M5:Q6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet->setCellValue('M5', 'PART NUMBER: ' . $row['no_ir']);

// Borders for row 4
$sheet->getStyle('A4:Q4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

// Merge from A7:Q54 and insert image
$sheet->mergeCells('A7:Q54');
$sheet->getStyle('A7:Q54')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

// Calculate the total width and height of the merged cells
$totalWidth = 0;
for ($col = 'A'; $col <= 'Q'; $col++) {
    $totalWidth += $sheet->getColumnDimension($col)->getWidth();
}

$totalHeight = 0;
for ($rowIndex = 7; $rowIndex <= 54; $rowIndex++) {
    $totalHeight += $sheet->getRowDimension($rowIndex)->getRowHeight();
}

// If row height is not explicitly set, assume default height
$defaultRowHeight = 15; // Default row height in points
if ($totalHeight == 0) {
    $totalHeight = $defaultRowHeight * 48; // 48 rows (7 to 54 inclusive)
}

$imagePath = './assets/uploads/' . $row['image'];
if (file_exists($imagePath)) {
    $objDrawing = new PHPExcel_Worksheet_Drawing();
    $objDrawing->setPath($imagePath);

    // Set the size and position of the image
    $objDrawing->setHeight(700); // Adjust the height as needed
    $objDrawing->setWidth(750); // Adjust the width as needed
    $objDrawing->setCoordinates('A7'); // Set the start position for the image

    // Adjust the offset if necessary
    $objDrawing->setOffsetX(50); // Adjust to center the image horizontally
    $objDrawing->setOffsetY(10); // Adjust to center the image vertically

    $objDrawing->setWorksheet($sheet);
}

// Define the header style array
$headerStyle2 = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
        ),
    ),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'FFFFFF')
    ),
    'font' => array(
        'bold' => true,
        'size' =>  8,
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    ),
);

// Create PHPExcel object
$objPHPExcel->createSheet();
$sheet2 = $objPHPExcel->setActiveSheetIndex(1);

$highestRow = $sheet2->getHighestRow();
$highestColumn = $sheet2->getHighestColumn();
$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

// Define the range for applying borders
$range = 'A1:' . $highestColumn . $highestRow;
$sheet2->getStyle($range)->applyFromArray($headerStyle2);

// Header Titles and Merging Cells
$sheet2->setCellValue('A1', 'PT KAYABA INDONESIA')->mergeCells('A1:C1');
$sheet2->setCellValue('A2', 'QUALITY ASSURANCE DEPARTMENT')->mergeCells('A2:C4');
$sheet2->getStyle('A2:C4')->applyFromArray($headerStyle2);

// Make $sheet2 read-only (protect the sheet with a password)
$sheet2->getProtection()->setSheet(true); // Enable protection
$sheet2->getProtection()->setPassword('your_password'); // Set a password for protection

// Disable specific actions on the sheet (same as sheet1)
$sheet2->getProtection()->setInsertRows(false); // Disable inserting rows
$sheet2->getProtection()->setInsertColumns(false); // Disable inserting columns
$sheet2->getProtection()->setDeleteRows(false); // Disable deleting rows
$sheet2->getProtection()->setDeleteColumns(false); // Disable deleting columns
$sheet2->getProtection()->setFormatCells(false); // Disable formatting cells
$sheet2->getProtection()->setSort(false); // Disable sorting
$sheet2->getProtection()->setObjects(false); // Disable object modification
$sheet2->getProtection()->setScenarios(false); // Disable scenario editing


// Set Column Width for C
$sheet2->getColumnDimension('C')->setWidth(3);
$sheet2->getColumnDimension('A')->setWidth(3);
$sheet2->getColumnDimension('B')->setWidth(15);
$sheet2->getColumnDimension('D')->setWidth(6);
$sheet2->getColumnDimension('E')->setWidth(6);
$sheet2->getColumnDimension('F')->setWidth(10);
$sheet2->getColumnDimension('G')->setWidth(6);
$sheet2->getColumnDimension('H')->setWidth(6);
$sheet2->getColumnDimension('I')->setWidth(6);
$sheet2->getColumnDimension('J')->setWidth(6);
$sheet2->getColumnDimension('K')->setWidth(6);
$sheet2->getColumnDimension('L')->setWidth(6);
$sheet2->getColumnDimension('M')->setWidth(6);
$sheet2->getColumnDimension('N')->setWidth(6);
$sheet2->getColumnDimension('O')->setWidth(6);
$sheet2->getColumnDimension('P')->setWidth(6);
$sheet2->getColumnDimension('Q')->setWidth(6);




$sheet2->setCellValue('D1', 'INSPECTION RECORD')->mergeCells('D1:L2');
$sheet2->getStyle('D1:L2')->applyFromArray($headerStyle2); // Use $headerStyle2 instead of $headerStyle

// Menetapkan nilai dan mengatur merge cells untuk "NO :"
$sheet2->setCellValue('D3', 'NO : ' . $row['no_reg']);
$sheet2->mergeCells('D3:L3');

// Mengatur teks "NO :" menjadi rata kiri
$sheet2->getStyle('D3:L3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

// Menerapkan gaya border pada sel "NO :"
$sheet2->getStyle('D3:L3')->applyFromArray($headerStyle2);

// Menetapkan nilai dan mengatur merge cells untuk "REV :"
$sheet2->setCellValue('D4', 'REV : ' . $row['revisi']);
$sheet2->mergeCells('D4:L4');

// Mengatur teks "REV :" menjadi rata kiri
$sheet2->getStyle('D4:L4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

// Menerapkan gaya border pada sel "REV :"
$sheet2->getStyle('D4:L4')->applyFromArray($headerStyle2);

// Tetap menerapkan gaya headerStyle2 pada "D1:L2"
$sheet2->getStyle('D1:L2')->applyFromArray($headerStyle2);

// Borders
// $sheet2->getStyle('A4:Q4')->applyFromArray($headerStyle2);
// $sheet2->getStyle('C1:C4')->applyFromArray($headerStyle2);

$sheet2->setCellValue('M1', 'NO SPB               : ' . $row['no_ppb']);
$sheet2->mergeCells('M1:Q1');
$sheet2->getStyle('M1:Q1')->applyFromArray($headerStyle2);
$sheet2->getStyle('M1:Q1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$sheet2->setCellValue('M2', 'RECEIVED QTY : ' . $row['receive_qty']);
$sheet2->mergeCells('M2:Q2');
$sheet2->getStyle('M2:Q2')->applyFromArray($headerStyle2);
$sheet2->getStyle('M2:Q2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$sheet2->setCellValue('M3', 'CHECK DATE    : ' . date('Y-m-d'));
$sheet2->mergeCells('M3:Q3');
$sheet2->getStyle('M3:Q3')->applyFromArray($headerStyle2);
$sheet2->getStyle('M3:Q3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$sheet2->setCellValue('M4', 'SAMPLING QTY : ' . $row['sampling_qty']);
$sheet2->mergeCells('M4:Q4');
$sheet2->getStyle('M4:Q4')->applyFromArray($headerStyle2);
$sheet2->getStyle('M4:Q4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


// Merge and center for supplier, part name, and part number
$sheet2->mergeCells('A5:E6');
$sheet2->getStyle('A5:E6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->setCellValue('A5', 'SUPPLIER DATA');
$sheet2->getStyle('A5:E6')->applyFromArray($headerStyle2);

$sheet2->mergeCells('F5:L6');
$sheet2->getStyle('F5:L6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->setCellValue('F5', 'PART NAME: ' . $row['sub_subIR']);
$sheet2->getStyle('F5:L6')->applyFromArray($headerStyle2);

$sheet2->mergeCells('M5:Q6');
$sheet2->getStyle('M5:Q6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->setCellValue('M5', 'PART NUMBER: ' . $row['no_ir']);
$sheet2->getStyle('M5:Q6')->applyFromArray($headerStyle2);

// Set table header
$sheet2->mergeCells('A7:A8');
$sheet2->setCellValue('A7', 'NO');
$sheet2->getStyle('A7:A8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('A7:A8')->applyFromArray($headerStyle2);

$sheet2->mergeCells('B7:C8');
$sheet2->setCellValue('B7', 'INSPECTION ITEM');
$sheet2->getStyle('B7:C8')->getFont()->setBold(true);
$sheet2->getStyle('B7:C8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('B7:C8')->applyFromArray($headerStyle2);

$sheet2->mergeCells('D7:E8');
$sheet2->setCellValue('D7', 'STANDARD');
$sheet2->getStyle('D7:E8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('D7:E8')->applyFromArray($headerStyle2);

$sheet2->mergeCells('F7:F8');
$sheet2->setCellValue('F7', 'TOOLS');
$sheet2->getStyle('F7:F8')->getFont()->setBold(true);
$sheet2->getStyle('F7:F8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('F7:F8')->applyFromArray($headerStyle2);


$sheet2->mergeCells('G7:P7');
$sheet2->setCellValue('G7', 'SAMPLING NUMBER');
$sheet2->getStyle('G7:P7')->getFont()->setBold(true);
$sheet2->getStyle('G7:P7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('G7:P7')->applyFromArray($headerStyle2);

$sheet2->setCellValue('G8', '1');
$sheet2->getStyle('G8')->applyFromArray($headerStyle2);
$sheet2->setCellValue('H8', '2');
$sheet2->getStyle('H8')->applyFromArray($headerStyle2);
$sheet2->setCellValue('I8', '3');
$sheet2->getStyle('I8')->applyFromArray($headerStyle2);
$sheet2->setCellValue('J8', '4');
$sheet2->getStyle('J8')->applyFromArray($headerStyle2);
$sheet2->setCellValue('K8', '5');
$sheet2->getStyle('K8')->applyFromArray($headerStyle2);
$sheet2->setCellValue('L8', '6');
$sheet2->getStyle('L8')->applyFromArray($headerStyle2);
$sheet2->setCellValue('M8', '7');
$sheet2->getStyle('M8')->applyFromArray($headerStyle2);
$sheet2->setCellValue('N8', '8');
$sheet2->getStyle('N8')->applyFromArray($headerStyle2);
$sheet2->setCellValue('O8', '9');
$sheet2->getStyle('O8')->applyFromArray($headerStyle2);
$sheet2->setCellValue('P8', '10');
$sheet2->getStyle('P8')->applyFromArray($headerStyle2);

$sheet2->mergeCells('Q7:Q8');
$sheet2->setCellValue('Q7', 'STATUS');
$sheet2->getStyle('Q7:Q8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('Q7:Q8')->applyFromArray($headerStyle2);

// Set footer data
$footerLabels = ['APPROVE BY : ', 'CHECKED : ', 'PREPARED BY : '];
$footerCells = ['L45:M46', 'N45:O46', 'P45:Q46'];
foreach ($footerCells as $index => $cellRange) {
    $sheet2->mergeCells($cellRange);
    $sheet2->setCellValue(substr($cellRange, 0, strpos($cellRange, ':')), $footerLabels[$index]);
    $sheet2->getStyle($cellRange)->applyFromArray($headerStyle2);
}


// Pengaturan format untuk hasil status akhir
$sheet2->mergeCells('A45:B46');
$sheet2->setCellValue('A45', 'HASIL STATUS');
$sheet2->getStyle('A47:B46')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('A45:B46')->applyFromArray($headerStyle2);

// Pengaturan format untuk cell OK atau NG
$sheet2->getStyle('A47:B51')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('A47:B51')->applyFromArray($headerStyle2);

$sheet2->getStyle('B47:B51')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->mergeCells('C45:K48');
$sheet2->getStyle('B47:B51')->applyFromArray($headerStyle2);

// Note for Supervisor
$sheet2->mergeCells('C45:K48');
$sheet2->setCellValue('C45', "Note Supervisor: " . $note_supervisor);
$sheet2->getStyle('C45:K48')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet2->getStyle('C45:K48')->applyFromArray($headerStyle2);
$sheet2->getStyle('C45:K48')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

// Note for Manager
$sheet2->mergeCells('C49:K51');
$sheet2->setCellValue('C49', "Note Manager: " . $note_manager);
// $sheet2->getStyle('C49:K51')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet2->getStyle('C49:K51')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet2->getStyle('C49:K51')->applyFromArray($headerStyle2);
// $sheet2->getStyle('C49:K51')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);




$sheet2->mergeCells('L45:M46');
$sheet2->setCellValue('L45', 'APPROVE BY : ');
$sheet2->getStyle('L45:M46')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('L47:M51')->applyFromArray($headerStyle2);

$sheet2->mergeCells('N45:O46');
$sheet2->setCellValue('N45', 'CHECHKED : ');
$sheet2->getStyle('N45:O46')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('N47:O51')->applyFromArray($headerStyle2);

$sheet2->mergeCells('P45:Q46');
$sheet2->setCellValue('P45', 'PREPARED : ');
$sheet2->getStyle('P45:Q46')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->applyFromArray($headerStyle2);
$sheet2->getStyle('P47:Q51')->applyFromArray($headerStyle2);

// Place the first QR code for approval
$objDrawing1 = new PHPExcel_Worksheet_Drawing();
$objDrawing1->setName('QR Code Approve');
$objDrawing1->setDescription('QR Code for Approve');
$objDrawing1->setPath($qr_image_path_approve);
$objDrawing1->setHeight($image_height);
$objDrawing1->setCoordinates('L47'); // Set coordinates for the merged cell
$objDrawing1->setOffsetX($offsetX);
$objDrawing1->setOffsetY($offsetY);
$objDrawing1->setWorksheet($sheet2);

// Place the second QR code for check
$objDrawing2 = new PHPExcel_Worksheet_Drawing();
$objDrawing2->setName('QR Code Check');
$objDrawing2->setDescription('QR Code for Check');
$objDrawing2->setPath($qr_image_path_check);
$objDrawing2->setHeight($image_height);
$objDrawing2->setCoordinates('N47'); // Set coordinates for the merged cell
$objDrawing2->setOffsetX($offsetX);
$objDrawing2->setOffsetY($offsetY);
$objDrawing2->setWorksheet($sheet2);

// Place the third QR code for prepare
$objDrawing3 = new PHPExcel_Worksheet_Drawing();
$objDrawing3->setName('QR Code Prepare');
$objDrawing3->setDescription('QR Code for Prepare');
$objDrawing3->setPath($qr_image_path_prepare);
$objDrawing3->setHeight($image_height);
$objDrawing3->setCoordinates('P47'); // Set coordinates for the merged cell
$objDrawing3->setOffsetX($offsetX);
$objDrawing3->setOffsetY($offsetY);
$objDrawing3->setWorksheet($sheet2);
// Mengatur penggabungan dan gaya untuk sel L51:M51
$sheet2->mergeCells('L51:M51');
$sheet2->setCellValue('L51', 'MANAGER');
$sheet2->getStyle('L51:M51')
    ->applyFromArray($headerStyle2);

// Mengatur penggabungan dan gaya untuk sel N51:O51
$sheet2->mergeCells('N51:O51');
$sheet2->setCellValue('N51', 'Panji Gumilang P');
$sheet2->getStyle('N51:O51')
    ->applyFromArray($headerStyle2);

// Mengatur penggabungan dan gaya untuk sel P51:Q51
$sheet2->mergeCells('P51:Q51');
$sheet2->setCellValue('P51', 'Operator');
$sheet2->getStyle('P51:Q51')
    ->applyFromArray($headerStyle2);

// Mengatur penggabungan dan gaya untuk sel L47:M50
$sheet2->mergeCells('L47:M50');
$sheet2->getStyle('L47:M50')
    ->applyFromArray($headerStyle2);

// Mengatur penggabungan dan gaya untuk sel N47:O50
$sheet2->mergeCells('N47:O50');
$sheet2->getStyle('N47:O50')
    ->applyFromArray($headerStyle2);

$sheet2->mergeCells('P47:Q50');
$sheet2->getStyle('P47:Q50')
    ->applyFromArray($headerStyle2);

// Mengatur penggabungan dan gaya untuk sel P47:Q51
$sheet2->mergeCells('P47:Q51');
$sheet2->getStyle('P47:Q51')
    ->applyFromArray($headerStyle2);


// Labels for footer cells
$footerLabels = ['APPROVE BY : ', 'CHECKED BY : ', 'PREPARED BY : '];
$footerCells = ['L45:M46', 'N45:O46', 'P45:Q46'];

// Apply footer label settings
foreach ($footerCells as $index => $cellRange) {
    $sheet2->mergeCells($cellRange);
    $sheet2->setCellValue(substr($cellRange, 0, strpos($cellRange, ':')), $footerLabels[$index]);
    $sheet2->getStyle($cellRange)
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
        ->applyFromArray($headerStyle2);
}

// The following block is removed as it's no longer needed
/*
foreach ($footerData as $footerItem) {
    $sheet2->mergeCells($footerItem[0]);
    $sheet2->setCellValue(substr($footerItem[0], 0, strpos($footerItem[0], ':')), $footerItem[1]);
    $sheet2->getStyle($footerItem[0])
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
        ->applyFromArray($headerStyle2);
}
*/

// Define QR code data
$qr_data = 'No IR: ' . $row['no_ir'] . ' | Date: ' . date('Y-m-d');

// Define QR code positions (if needed for styling or future use)
$qrCells = ['L47:M51', 'N47:O51', 'P47:Q51'];

// QR code generation logic here (use $qr_data)




$qrImages = [
    $qr_image_path_approve, // Path for the first QR code
    $qr_image_path_check, // Path for the second QR code
    $qr_image_path_prepare  // Path for the third QR code
];

// Define image properties
$imageHeight = $image_height; // Define this based on your needs
$offsetX = $offsetX; // Define this based on your needs
$offsetY = $offsetY; // Define this based on your needs

// Place QR codes
foreach ($qrCells as $index => $cellRange) {
    $objDrawing = new PHPExcel_Worksheet_Drawing();
    $objDrawing->setName('QR Code ' . ($index + 1));
    $objDrawing->setDescription('QR Code ' . ($index + 1));
    $objDrawing->setPath($qrImages[$index]);
    $objDrawing->setHeight($imageHeight);
    $objDrawing->setCoordinates(substr($cellRange, 0, strpos($cellRange, ':')));
    $objDrawing->setOffsetX($offsetX);
    $objDrawing->setOffsetY($offsetY);
    $objDrawing->setWorksheet($sheet2);

    $sheet2->mergeCells($cellRange);
    $sheet2->getStyle($cellRange)
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
        ->applyFromArray($headerStyle2);
}


// Mengonversi 10 piksel ke points (1 piksel = 0.75 points)
$heightInPoints = 10 * 0.75;

for ($row = 21; $row <= 44; $row += 2) { // Increment by 2 to merge every 2 rows
    // Set row height to 10 pixels (converted to points)
    $sheet2->getRowDimension($row)->setRowHeight($heightInPoints);
    $sheet2->getRowDimension($row + 1)->setRowHeight($heightInPoints);

    // Merge cells for each specified column and apply styles
    $sheet2->mergeCells('A' . $row . ':A' . ($row + 1));
    $sheet2->getStyle('A' . $row . ':A' . ($row + 1))->applyFromArray($headerStyle2);

    $sheet2->mergeCells('B' . $row . ':C' . ($row + 1));
    $sheet2->getStyle('B' . $row . ':C' . ($row + 1))->applyFromArray($headerStyle2);

    $sheet2->mergeCells('D' . $row . ':E' . ($row + 1));
    $sheet2->getStyle('D' . $row . ':E' . ($row + 1))->applyFromArray($headerStyle2);

    $sheet2->mergeCells('F' . $row . ':F' . ($row + 1));
    $sheet2->getStyle('F' . $row . ':F' . ($row + 1))->applyFromArray($headerStyle2);

    // Columns G to P (single cells)
    for ($col = 'G'; $col <= 'P'; $col++) {
        $sheet2->mergeCells($col . $row . ':' . $col . ($row + 1));
        $sheet2->getStyle($col . $row . ':' . $col . ($row + 1))->applyFromArray($headerStyle2);
    }

    $sheet2->mergeCells('Q' . $row . ':Q' . ($row + 1));
    $sheet2->getStyle('Q' . $row . ':Q' . ($row + 1))->applyFromArray($headerStyle2);
}

// Set Column Width (Nomor - NO) to Auto-Size based on content
$sheet2->getColumnDimension('A')->setAutoSize(true);

// Apply the style to the specified range
$range = 'A21:Q44'; // Adjust this range based on your needs
$sheet2->getStyle($range)->applyFromArray($headerStyle2);

// Mulai baris setelah header
$startRow = 9;


// Inisialisasi variabel untuk memeriksa status
$hasNgZero = false;
$allStatusOk = true;

$no = 1; // Inisialisasi nomor urut

while ($row2 = $result2->fetch_assoc()) {
    // Atur tinggi baris menjadi 10 piksel (7.5 poin)
    $heightInPoints = 10 * 0.75;

    $sheet2->getRowDimension($startRow)->setRowHeight($heightInPoints);
    $sheet2->getRowDimension($startRow + 1)->setRowHeight($heightInPoints);

    // NO
    $sheet2->mergeCells('A' . $startRow . ':A' . ($startRow + 1));
    $sheet2->setCellValue('A' . $startRow, $no); // Menggunakan nomor urut
    $sheet2->getStyle('A' . $startRow . ':A' . ($startRow + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet2->getStyle('A' . $startRow . ':A' . ($startRow + 1))->applyFromArray($headerStyle2);

    // INSPECTION ITEM
    $sheet2->mergeCells('B' . $startRow . ':C' . ($startRow + 1));
    $sheet2->setCellValue('B' . $startRow, $row2['item_inspeksi']);
    $sheet2->getStyle('B' . $startRow . ':C' . ($startRow + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet2->getStyle('B' . $startRow . ':C' . ($startRow + 1))->applyFromArray($headerStyle2);

    // STANDARD
    $sheet2->mergeCells('D' . $startRow . ':E' . ($startRow + 1));
    $sheet2->setCellValue('D' . $startRow, $row2['standar']);
    $sheet2->getStyle('D' . $startRow . ':E' . ($startRow + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet2->getStyle('D' . $startRow . ':E' . ($startRow + 1))->applyFromArray($headerStyle2);

    // TOOLS
    $sheet2->mergeCells('F' . $startRow . ':F' . ($startRow + 1));
    $sheet2->setCellValue('F' . $startRow, $row2['alat']);
    $sheet2->getStyle('F' . $startRow . ':F' . ($startRow + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet2->getStyle('F' . $startRow . ':F' . ($startRow + 1))->applyFromArray($headerStyle2);

    // SAMPLING NUMBER (1-10)
    for ($i = 0; $i < 10; $i++) {
        $col = chr(ord('G') + $i); // Mendapatkan kolom dari G ke P
        $sheet2->mergeCells($col . $startRow . ':' . $col . ($startRow + 1));
        $sheet2->getStyle($col . $startRow . ':' . $col . ($startRow + 1))->applyFromArray($headerStyle2);

        // Periksa apakah nilai 0, jika iya biarkan sel kosong
        if ($row2['s' . ($i + 1)] != 0) {
            $sheet2->setCellValue($col . $startRow, $row2['s' . ($i + 1)]);
        }
        $sheet2->getStyle($col . $startRow . ':' . $col . ($startRow + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }

    // STATUS
    $statusText = ($row2['status'] == 1) ? 'OK' : 'NG';
    $sheet2->mergeCells('Q' . $startRow . ':Q' . ($startRow + 1));
    $sheet2->setCellValue('Q' . $startRow, $statusText);
    $sheet2->getStyle('Q' . $startRow . ':Q' . ($startRow + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    // Terapkan warna latar belakang berdasarkan status
    if ($row2['status'] == 1) {
        $sheet2->getStyle('Q' . $startRow . ':Q' . ($startRow + 1))->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '00FF00') // Warna hijau
                )
            )
        );
    } elseif ($row2['status'] == 0) {
        $sheet2->getStyle('Q' . $startRow . ':Q' . ($startRow + 1))->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FF0000') // Warna merah
                )
            )
        );
    }

    // Perbarui status flag
    if ($row2['status'] == 0) {
        $hasNgZero = true; // Tandai bahwa ada setidaknya satu NG dengan status 0
    } elseif ($row2['status'] != 1) {
        $allStatusOk = false; // Jika ada status yang tidak OK, set ini ke false
    }

    // Tambah baris untuk data berikutnya
    $startRow += 2;
    $no++; // Increment nomor urut
}


$styleArray = [
    'font' => [
        'bold' => true,
        'size' => 25, // Ukuran font yang diinginkan
    ],
];

// Mengatur hasil akhir berdasarkan status
$sheet2->setCellValue('A45', 'HASIL STATUS');
$sheet2->mergeCells('A47:B51');
if ($hasNgZero) {
    $sheet2->setCellValue('A47', 'NG');
    $sheet2->getStyle('A47')->applyFromArray($styleArray);
} elseif ($allStatusOk) {
    $sheet2->setCellValue('A47', 'OK');
    $sheet2->getStyle('A47')->applyFromArray($styleArray);
} else {
    $sheet2->setCellValue('A47', 'MIXED');
}


// Apply the styles to the entire range
applyHeaderStyle($sheet, 'A1:Q54', $headerStyle);

// Save or download the Excel file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Inspection_Record.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

exit;
