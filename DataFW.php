<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Quality Assurance</title>
    <link rel="stylesheet" href="assets/css/istyle.css">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>

<body>
    <header>
        <img src="assets/img/kyb.png" alt="KYB Logo" id="logo">
        <h1>LABORATORIUM QUALITY ASSURANCE - PT KAYABA INDONESIA</h1>
        <div id="clock" class="clock"></div>
        <button class="logout" onclick="confirmLogout()">LOGOUT</button>
    </header>
    <main>
        <div class="title">IR REGULARLY 2W FRONT FORK</div>
        <?php
        session_start(); // Start the session at the top of the script

        include 'konfig.php'; // Include your database configuration file

        $no_ir = isset($_GET['no_ir']) ? $_GET['no_ir'] : "";
        $jenis_ir = "";
        $id_noir = "";
        $npk = isset($_SESSION['npk']) ? $_SESSION['npk'] : ""; // Get the NPK from session

        if ($no_ir) {
            // Fetch id_noir and jenis_ir based on no_ir
            $stmt = $conn->prepare("
        SELECT no_ir.id_noir, ir.ir
        FROM kayaba_project.no_ir 
        JOIN kayaba_project.sub_subir ON no_ir.id_subsubIR = sub_subir.id_subsubIR
        JOIN kayaba_project.sub_ir ON sub_subir.id_subir = sub_ir.id_subir
        JOIN kayaba_project.ir ON sub_ir.id_ir = ir.id_ir
        WHERE no_ir.no_ir = ?");
            if (!$stmt) {
                die('Prepare statement failed: ' . $conn->error);
            }
            $stmt->bind_param("s", $no_ir);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id_noir = $row['id_noir'];
                $jenis_ir = $row['ir'];
            } else {
                echo "<p>No data found for the selected no_ir.</p>";
            }
            $stmt->close();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $no_ir = $_POST['no-ir'];
            $no_ppb = $_POST['no-ppb'];
            $receive_qty = $_POST['receive-qty'];
            $sampling_qty = $_POST['sampling-qty'];
            $jenis_ir = $_POST['jenis-ir'];
            $supplier = $_POST['supplier']; // New Supplier field
            $status = 1;
            $prepare = $npk;
            date_default_timezone_set('Asia/Jakarta');
            $tgl_prepare = date('Y-m-d H:i:s');
        
            // Prepare SQL with the new "supplier" field
            $stmt = $conn->prepare("INSERT INTO form (id_noir, no_ppb, receive_qty, sampling_qty, jenis_ir, supplier, status, prepare, tgl_prepare) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                die('Prepare statement failed: ' . $conn->error);
            }
        
            // Bind parameters, including the new supplier field
            $stmt->bind_param("isiisssss", $id_noir, $no_ppb, $receive_qty, $sampling_qty, $jenis_ir, $supplier, $status, $prepare, $tgl_prepare);
        
            if ($stmt->execute()) {
                $id_form = $conn->insert_id;
                echo "<script>window.location.href = 'datainput.php?no_ir=" . urlencode($no_ir) . "&id_form=" . urlencode($id_form) . "';</script>";
            } else {
                echo "<script>alert('Error updating data: " . $conn->error . "');</script>";
            }
            $stmt->close();
        }
        
        $conn->close();
        
        ?>


<form id="qa-form" method="POST" onsubmit="return validateForm(event)">
            <div class="mb-3">
                <label for="no-ir" class="form-label">Part Number</label>
                <input type="text" id="no-ir" name="no-ir" class="form-control" value="<?php echo htmlspecialchars($no_ir); ?>" readonly required>
            </div>

            <div class="mb-3">
                <label for="jenis-ir" class="form-label">JENIS IR</label>
                <input type="text" id="jenis-ir" name="jenis-ir" class="form-control" value="<?php echo htmlspecialchars($jenis_ir); ?>" required readonly>
            </div>

            <div class="mb-3">
                <label for="supplier" class="form-label">Supplier</label>
                <input type="text" id="supplier" name="supplier" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label for="no-ppb" class="form-label">NO. SPB</label>
                <input type="text" id="no-ppb" name="no-ppb" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="receive-qty" class="form-label">RECEIVE QTY</label>
                <input type="number" class="form-control" id="receive-qty" name="receive-qty" required>
            </div>
            <div class="mb-3">
                <label for="sampling-qty" class="form-label">SAMPLING QTY</label>
                <input type="number" class="form-control" id="sampling-qty" name="sampling-qty" required>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-danger" onclick="handleCancel()">CANCEL</button>
                <button type="submit" class="btn btn-success">SUBMIT</button>
            </div>
        </form>
    </main>

    <!-- Modal Alert -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Sampling qty tidak boleh lebih besar dari receive qty.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php'; // Ganti dengan URL yang sesuai
                }
            });
        }

        function validateForm(event) {
    event.preventDefault();

    let noIr = document.getElementById('no-ir').value.trim();
    let jenisIr = document.getElementById('jenis-ir').value.trim();
    let noPpb = document.getElementById('no-ppb').value.trim();
    let supplier = document.getElementById('supplier').value.trim();
    let receiveQty = parseInt(document.getElementById('receive-qty').value.trim());
    let samplingQty = parseInt(document.getElementById('sampling-qty').value.trim());

    // Check if any field is empty
    if (!noIr || !jenisIr || !noPpb || !supplier || !receiveQty || !samplingQty) {
        Swal.fire('Error', 'All fields are required!', 'error');
        return false;
    }

    // Additional validations for quantities
    if (samplingQty > receiveQty) {
        var myModal = new bootstrap.Modal(document.getElementById('alertModal'));
        myModal.show();
        return false;
    }

    if (samplingQty > 10) {
        Swal.fire('Error', 'Sampling qty cannot exceed 10!', 'error');
        return false;
    }

    confirmSubmit(); // Call the confirmation function if all validations pass
}



        function confirmSubmit() {
            Swal.fire({
                title: 'Konfirmasi Submit',
                text: 'Apakah Anda yakin ingin submit?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, submit!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Sukses', 'Data berhasil disubmit!', 'success').then(() => {
                        document.getElementById('qa-form').submit(); // Submit form setelah konfirmasi
                    });
                }
            });
        }



        function handleCancel() {
            Swal.fire({
                title: 'Konfirmasi Pembatalan',
                text: 'Apakah Anda yakin ingin membatalkan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, batal!',
                cancelButtonText: 'Kembali'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'index.php'; // Ganti dengan URL yang sesuai
                }
            });
        }
    </script>
</body>

</html>