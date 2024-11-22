<?php
session_start();
include 'konfig.php';
require_once __DIR__ . '/assets/PHPExcel/Classes/PHPExcel.php';
require_once __DIR__ . '/assets/fpdf/fpdf.php';

// Fetch session data
$npk = $_SESSION['npk'];
$golongan = $_SESSION['golongan'];
$acting = $_SESSION['acting'];

// Initialize conditions and button visibility
$status_condition = '';
$show_buttons = false;

// Supervisor conditions: golongan == 4 and acting == 2
if ($golongan == 4 && $acting == 2) {
    $status_condition = 'WHERE form.status = 1 OR form.status = 3';
    $show_buttons = true;
}

// Manager conditions: check if npk is in hrd_so and tipe == 1
$query_hrd = "SELECT tipe FROM hrd_so WHERE npk = '$npk' AND tipe = 1";
$result_hrd = mysqli_query($conn2, $query_hrd);
if (mysqli_num_rows($result_hrd) > 0) {
    $status_condition = 'WHERE form.status = 2'; // Waiting approval manager
    $show_buttons = true;
}

// Fetch the data with the determined status condition
$sql = "SELECT form.id_form, no_ir.no_ir AS no_ir, ir.ir AS ir, form.no_ppb, form.receive_qty, form.sampling_qty, form.status
        FROM form
        LEFT JOIN no_ir ON form.id_noir = no_ir.id_noir
        LEFT JOIN ir ON form.jenis_ir = ir.ir
        $status_condition";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Data Table</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/approve.css" rel="stylesheet">

</head>

<body>
    <div class="header">
        <img src="assets/img/kyb.png" alt="KYB Logo" class="logo">
        <div class="title">LABORATORIUM QUALITY ASSURANCE - PT KAYABA INDONESIA</div>
        <div id="clock" class="clock"></div>
        <button class="btn btn-primary home" id="home-button" onclick="goHome()">BERANDA</button>
        <button class="btn btn-primary home" id="monitoring-button" onclick="goMonitoring()">MONITORING</button>
        <button class="btn btn-danger logout" id="logout-button" onclick="confirmLogout()">KELUAR</button>
    </div>

    <main id="main" class="main">
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Datatables</h5>
                            <div class="table-responsive">
                                <table class="table table-striped datatable">
                                    <thead>
                                        <tr>
                                            <th>No IR</th>
                                            <th>Jenis IR</th>
                                            <th>No PPB</th>
                                            <th>Sample Diterima</th>
                                            <th>Jumlah Sample</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                            <th><button id="displayAllBtn" class="btn btn-secondary btn-sm">Tampilkan Semua</button></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <?php
                                        if ($result) {
                                            if ($result->num_rows > 0) {
                                                // Buat array untuk mengelompokkan data berdasarkan status
                                                $waiting_approval_supervisor = [];
                                                $finish = [];
                                                $others = [];

                                                // Pisahkan data berdasarkan status
                                                while ($row = $result->fetch_assoc()) {
                                                    if ($row['status'] == 1) {
                                                        $waiting_approval_supervisor[] = $row;
                                                    } elseif ($row['status'] == 3) {
                                                        $finish[] = $row;
                                                    } else {
                                                        $others[] = $row;
                                                    }
                                                }

                                                // Fungsi untuk menampilkan baris tabel
                                                function renderTableRows($data, $golongan, $acting, $result_hrd, $hide = false)
                                                {
                                                    foreach ($data as $row) {
                                                        $status_class = '';
                                                        $status_text = '';
                                                        $row_class = ($hide) ? 'hidden-row' : '';

                                                        switch ($row['status']) {
                                                            case 1:
                                                                $status_class = 'status-waiting';
                                                                $status_text = 'Menunggu Persetujan Supervisor';
                                                                break;
                                                            case 2:
                                                                $status_class = 'status-progress';
                                                                $status_text = 'Menunggu Persetujan Manager';
                                                                break;
                                                            case 3:
                                                                $status_class = 'status-finish';
                                                                $status_text = 'Selesai';
                                                                break;
                                                            case 0:
                                                                $status_class = 'status-decline'; // Class for Decline status
                                                                $status_text = 'Ditolak';
                                                                break;
                                                            default:
                                                                $status_class = 'status-pending';
                                                                $status_text = 'Tertunda';
                                                        }

                                                        $approve_button = (($golongan == 4 && $acting == 2 && $row['status'] == 1) || (mysqli_num_rows($result_hrd) > 0 && $row['status'] == 2)) ? "<button class='btn btn-success btn-sm' onclick='approveRecord({$row['id_form']})'><i class='bi bi-check-circle'></i> Setujui</button>" : '';
                                                        $decline_button = (($golongan == 4 && $acting == 2 && $row['status'] == 1) || (mysqli_num_rows($result_hrd) > 0 && $row['status'] == 2))
                                                            ? "<button class='btn btn-danger btn-sm' data-id='{$row['id_form']}' onclick='declineRecord({$row['id_form']})'><i class='bi bi-x-circle'></i> Tolak</button>"
                                                            : '';
                                                        $export_button = ($golongan == 4 && $acting == 2 && $row['status'] == 3) ? "<button class='btn btn-danger btn-sm' onclick='exportToExcel({$row['id_form']})'><i class='bi bi-file-earmark-pdf'></i> Unduh File PDF</button>" : '';

                                                        echo "<tr class='$row_class'>
                    <td>{$row['no_ir']}</td>
                    <td>{$row['ir']}</td>
                    <td>{$row['no_ppb']}</td>
                    <td>{$row['receive_qty']}</td>
                    <td>{$row['sampling_qty']}</td>
                    <td><span class='status $status_class'>$status_text</span></td>
                    <td class='action-buttons'>
                        $approve_button
                        $decline_button
                        $export_button
                    </td>
                    <td></td>
                </tr>";
                                                    }
                                                }

                                                // Tampilkan data dengan urutan yang diinginkan
                                                renderTableRows($waiting_approval_supervisor, $golongan, $acting, $result_hrd);
                                                renderTableRows($others, $golongan, $acting, $result_hrd);
                                                renderTableRows($finish, $golongan, $acting, $result_hrd, true);
                                            } else {
                                                echo "<tr><td colspan='7'>No data found</td></tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7'>Error: " . $conn->error . "</td></tr>";
                                        }

                                        $conn->close();
                                        ?>
                                    </tbody>
                                </table>

                                <!-- Approval Modal -->
                                <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="approveModalLabel">Approval Details</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div id="approveDetails" class="mb-4">
                                                    <!-- Displaying the note_supervisor fetched from the database -->
                                                    <label for="noteSupervisor">Catatan Supervisor:</label>
                                                    <div id="noteSupervisor" class="p-2 border" style="background-color: #f8f9fa;">
                                                        <?php echo isset($note_supervisor) ? htmlspecialchars($note_supervisor) : 'Catatan tidak tersedia'; ?>
                                                    </div>
                                                </div>
                                                <div class="form-group mt-3">
                                                    <label for="approvalNote">Catatan:</label>
                                                    <textarea class="form-control" id="approvalNote" rows="3" placeholder="Masukkan catatan"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="button" class="btn btn-success" id="confirmApprove">Approve</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>


                        </div>
                    </div>
                </div>
            </div>
            </div>
        </section>
    </main>

    <!-- Scripts -->
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {
            // Edit Record
            window.editRecord = function(id_form, no_ir, ir, no_ppb, receive_qty, sampling_qty) {
                Swal.fire({
                    title: 'Edit Record',
                    html: `
                <input id="no_ir" class="swal2-input" value="${no_ir}" placeholder="No IR" disabled>
                <input id="ir" class="swal2-input" value="${ir}" placeholder="IR" disabled>
                <input id="no_ppb" class="swal2-input" value="${no_ppb}" placeholder="No PPB">
                <input id="receive_qty" class="swal2-input" type="number" value="${receive_qty}" placeholder="Receive Quantity">
                <input id="sampling_qty" class="swal2-input" type="number" value="${sampling_qty}" placeholder="Sampling Quantity">
            `,
                    confirmButtonText: 'Save changes',
                    cancelButtonText: 'Cancel',
                    showCancelButton: true,
                    preConfirm: () => {
                        const no_ppb = Swal.getPopup().querySelector('#no_ppb').value;
                        const receive_qty = Swal.getPopup().querySelector('#receive_qty').value;
                        const sampling_qty = Swal.getPopup().querySelector('#sampling_qty').value;
                        return {
                            no_ppb,
                            receive_qty,
                            sampling_qty
                        };
                    }

                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'actions.php',
                            method: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({
                                action: 'edit',
                                id_form: id_form,
                                ...result.value
                            }),
                            success: function(response) {
                                Swal.fire('Updated!', response.message, 'success');
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr.responseText);
                                Swal.fire('Error!', 'Something went wrong: ' + error, 'error');
                            }
                        });
                    }
                });
            }

            // Approve Record
            window.approveRecord = function(id_form) {
                $.ajax({
                    url: 'proses/fetch_approve.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        id_form: id_form
                    }),
                    success: function(data) {
                        console.log(data);
                        let approveDetails = `
                <div class="left-align">
                    <p><strong>No IR:</strong> ${data.no_ir || ''}</p>
                    <p><strong>Jenis IR:</strong> ${data.ir || ''}</p>
                    <p><strong>No PPB:</strong> ${data.no_ppb || ''}</p>
                    <p><strong>Receive Qty:</strong> ${data.receive_qty || ''}</p>
                    <p><strong>Sampling Qty:</strong> ${data.sampling_qty || ''}</p>
                    <p><strong>Status:</strong> ${data.status || ''}</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Item Inspeksi</th>
                                <th style="text-align: left;">Standar</th>
                                <th style="text-align: left;">Alat</th>
                                <th>S1</th>
                                <th>S2</th>
                                <th>S3</th>
                                <th>S4</th>
                                <th>S5</th>
                                <th>S6</th>
                                <th>S7</th>
                                <th>S8</th>
                                <th>S9</th>
                                <th>S10</th>
                                <th>Status Approve</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${Array.isArray(data.testingResults) ? data.testingResults.map(result => `
                                <tr>
                                    <td style="text-align: left;">${result.item_inspeksi || ''}</td>
                                    <td style="text-align: left;">${result.standar || ''}</td>
                                    <td style="text-align: left;">${result.nama_tools || ''}</td>
                                    <td>${result.s1 || ''}</td>
                                    <td>${result.s2 || ''}</td>
                                    <td>${result.s3 || ''}</td>
                                    <td>${result.s4 || ''}</td>
                                    <td>${result.s5 || ''}</td>
                                    <td>${result.s6 || ''}</td>
                                    <td>${result.s7 || ''}</td>
                                    <td>${result.s8 || ''}</td>
                                    <td>${result.s9 || ''}</td>
                                    <td>${result.s10 || ''}</td>
                                    <td>
                                        <span class="status-icon ${result.status === 1 ? 'status-ok' : 'status-ng'}">
                                            ${result.status === 1 ? 'OK' : 'NG'}
                                        </span>
                                    </td>
                                </tr>
                            `).join('') : '<tr><td colspan="14">No testing results available</td></tr>'}
                        </tbody>
                    </table>
                </div>
            `;

                        if (data.approval_note) {
                            approveDetails += `
                    <p><strong>Supervisor's Note:</strong></p>
                    <p>${data.approval_note}</p>
                `;
                        }

                        Swal.fire({
                            title: 'Approval Details',
                            html: approveDetails + `
                    <div class="form-group">
                        <label for="approvalNote">Catatan:</label>
                        <textarea class="form-control" id="approvalNote" rows="3" placeholder="Tuliskan Note"></textarea>
                    </div>
                    <div class="form-check mt-3">
                        <input type="checkbox" class="form-check-input" id="confirmReadCheckbox">
                        <label class="form-check-label" for="confirmReadCheckbox">Saya telah membaca semua data ini dengan benar</label>
                    </div>
                `,
                            width: '90%',
                            showCancelButton: true,
                            confirmButtonText: 'Approve',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#28a745',
                            preConfirm: () => {
                                const note = Swal.getPopup().querySelector('#approvalNote').value;
                                if (!note) {
                                    Swal.showValidationMessage('Note cannot be empty');
                                }
                                return {
                                    note: note
                                };
                            },
                            didOpen: () => {
                                // Disable the confirm button initially
                                Swal.getConfirmButton().disabled = true;

                                // Event listener for checkbox
                                const checkbox = Swal.getPopup().querySelector('#confirmReadCheckbox');
                                checkbox.addEventListener('change', () => {
                                    if (checkbox.checked) {
                                        Swal.getConfirmButton().disabled = false; // Enable approve button if checkbox is checked
                                    } else {
                                        Swal.getConfirmButton().disabled = true; // Disable approve button if unchecked
                                    }
                                });
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: 'actions.php',
                                    method: 'POST',
                                    contentType: 'application/json',
                                    data: JSON.stringify({
                                        action: 'approve',
                                        id_form: id_form,
                                        note: result.value.note
                                    }),
                                    success: function(response) {
                                        console.log(response);
                                        Swal.fire('Approved!', response.message, 'success');
                                        location.reload();
                                    },
                                    error: function(xhr, status, error) {
                                        console.error(xhr.responseText);
                                        Swal.fire('Error!', 'Something went wrong: ' + error, 'error');
                                    }
                                });
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        Swal.fire('Error!', 'Something went wrong: ' + error, 'error');
                    }
                });
            }

            function declineRecord(id_form) {
                if (confirm("Are you sure you want to decline this record?")) {
                    $.ajax({
                        url: 'actions.php',
                        type: 'POST',
                        data: JSON.stringify({
                            action: 'decline',
                            id_form: id_form
                        }),
                        contentType: 'application/json',
                        success: function(response) {
                            let result = JSON.parse(response);
                            if (result.success) {
                                alert(result.message); // Show success message
                                location.reload(); // Reload the page
                            } else {
                                alert("Error: " + result.message); // Show error message
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", error);
                            alert("An error occurred while processing the decline.");
                        }
                    });
                }
            }




            // Export to Excel
            window.exportToExcel = function(idForm) {
                const url = `exportpdf.php?id_form=${idForm}`;
                window.location.href = url;
            }

            document.getElementById('displayAllBtn').addEventListener('click', function() {
                // Tampilkan baris yang sebelumnya tersembunyi
                var hiddenRows = document.querySelectorAll('.hidden-row');
                hiddenRows.forEach(function(row) {
                    row.style.display = 'table-row';
                });
                // Ubah teks tombol setelah diklik
                this.textContent = 'Hide Finished';
                this.classList.toggle('btn-secondary');
                this.classList.toggle('btn-danger');

                // Ubah fungsi tombol untuk menyembunyikan kembali jika diklik lagi
                this.addEventListener('click', function() {
                    hiddenRows.forEach(function(row) {
                        row.style.display = 'none';
                    });
                    this.textContent = 'Display All';
                    this.classList.toggle('btn-secondary');
                    this.classList.toggle('btn-danger');
                });
            });

            // Confirm Logout
            window.confirmLogout = function() {
                Swal.fire({
                    title: 'Apakah anda ingin Keluar?',
                    text: 'Kamu akan keluar.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Iya, Keluar',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'logout.php';
                    }
                });
            }

            // Initialize Clock
            function updateClock() {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
            }
            setInterval(updateClock, 1000);
            updateClock(); // Initialize clock immediately

            // Define the goHome function
            window.goHome = function() {
                var golongan = "<?php echo $golongan; ?>";
                var acting = "<?php echo $acting; ?>";

                if (golongan == 4 && acting == 2) {
                    window.location.href = 'adminindex.php';
                } else if (golongan == 4 && acting == 1) {
                    window.location.href = 'managerindex.php';
                } else if (golongan == 2 && acting == 2) {
                    window.location.href = 'index.php';
                } else {
                    window.location.href = 'index.php'; // Default redirect
                }
            }
        });

        function goMonitoring() {
            window.location.href = 'status.php'; // Change this URL to your desired monitoring page
        }
    </script>


</body>

</html>