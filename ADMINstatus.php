<?php

session_start();

// Fetch session data
$npk = $_SESSION['npk'];
$golongan = $_SESSION['golongan'];
$acting = $_SESSION['acting'];

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring PPB - Laboratorium Quality Assurance</title>

    <!-- Java Script -->
    <script src="assets/jquery/jquery.min.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <script src="assets/DataTable/datatables.js"></script>
    <script src="assets/DataTable/datatables.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>

    <!-- css -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/DataTable/datatables.css">
    <link rel="stylesheet" href="assets/DataTable/datatables.min.css">
    <link rel="stylesheet" href="assets/css/nstyle.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="header">
        <img src="assets/img/kyb.png" alt="KYB Logo">
        <span>MONITORING PPB - LABORATORIUM QUALITY ASSURANCE</span>

        <div id="clock" class="clock"></div>

        <div class="header-right">
            <button class="home" id="home-button" onclick="goHome()">HOME</button>
            <button class="monitoring" id="monitoring-button" onclick="goMonitoring()">MONITORING</button>
            <button class="logout-button" onclick="confirmLogout()">Logout</button>
        </div>
    </div>

    <?php
    require 'konfig.php';



    // Fetch data from the database
    $sql = "SELECT no_ppb, part_pengerjaan, jenis_pengecekan, request, klasifikasi_ppb, receive, est_selesai, status, file FROM monitoring ORDER BY FIELD(status, '2', '1', '4', '3');";
    $result = $conn->query($sql);
    ?>

    <div class="container">
        <table id="monitoringData">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NO. PPB</th>
                    <th>PART PENGERJAAN</th>
                    <th>JENIS PENGECEKAN</th>
                    <th>REQ FROM</th>
                    <th>KLASIFIKASI PPB</th>
                    <th>RECEIVE</th>
                    <th>EST. SELESAI</th>
                    <th>DEV. WAKTU (HARI)</th> <!-- Kolom baru untuk Deviasi Waktu -->
                    <th>STATUS PENGERJAAN</th>
                    <th style="width: 200px;">FILE</th>

                    <?php if ($golongan == 2 && $acting == 2): ?> <!-- Only show Actions for golongan == 2 and acting == 2 -->
                        <th style="width: 10px;">ACTIONS</th> <!-- New column for Edit/Delete icons -->
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $no = 1; // Row numbering
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . $row["no_ppb"] . "</td>";
                        echo "<td>" . $row["part_pengerjaan"] . "</td>";
                        echo "<td>" . $row["jenis_pengecekan"] . "</td>";
                        echo "<td>" . $row["request"] . "</td>";

                        // Klasifikasi PPB
                        $klasifikasi_text = '';
                        $klasifikasi_class = '';
                        switch ($row["klasifikasi_ppb"]) {
                            case 1:
                                $klasifikasi_text = 'Top Urgent';
                                $klasifikasi_class = 'top-urgent';
                                break;
                            case 2:
                                $klasifikasi_text = 'Urgent';
                                $klasifikasi_class = 'urgent';
                                break;
                            case 3:
                                $klasifikasi_text = 'Asap';
                                $klasifikasi_class = 'asap';
                                break;
                            case 4:
                                $klasifikasi_text = 'Fifo';
                                $klasifikasi_class = 'fifo';
                                break;
                            default:
                                $klasifikasi_text = 'Unknown';
                                $klasifikasi_class = '';
                        }
                        echo "<td><span class='klasifikasi-badge " . $klasifikasi_class . "'>" . $klasifikasi_text . "</span></td>";

                        echo "<td>" . $row["receive"] . "</td>";
                        echo "<td>" . $row["est_selesai"] . "</td>";

                        // Hitung deviasi waktu jika status Finish
                        if ($row["status"] == 3) { // Status is "Finish"
                            $today = new DateTime(); // Tanggal hari ini
                            $est_selesai = new DateTime($row["est_selesai"]); // Tanggal "Est. Selesai"
                            $deviasi_waktu = $today->diff($est_selesai); // Selisih waktu

                            if ($today > $est_selesai) {
                                // Jika hari ini lebih dari est_selesai, tampilkan negatif (-) menandakan terlambat
                                echo "<td>-" . $deviasi_waktu->days . " hari (Telat)</td>";
                            } else {
                                // Jika hari ini kurang dari est_selesai, tampilkan positif (+) menandakan lebih cepat
                                echo "<td>+" . $deviasi_waktu->days . " hari (Lebih Cepat)</td>";
                            }
                        } else {
                            // Jika status belum Finish, kolom deviasi waktu kosong
                            echo "<td></td>";
                        }

                        $status_text = '';
                        $status_class = '';
                        switch ($row["status"]) {
                            case 1:
                                $status_text = 'Waiting';
                                $status_class = 'status-waiting';
                                break;
                            case 2:
                                $status_text = 'Progress';
                                $status_class = 'status-progress';
                                break;
                            case 3:
                                $status_text = 'Finish';
                                $status_class = 'status-finish';
                                break;
                            case 4:
                                $status_text = 'Pending';
                                $status_class = 'status-pending';
                                break;
                            default:
                                $status_text = 'Unknown';
                                $status_class = '';
                        }
                        echo "<td><span class='status-badge " . $status_class . "'>" . $status_text . "</span></td>";

                        // Tombol Upload File hanya jika status Finish
                        if ($row["status"] == 3) { // Status is "Finish"
                            echo "<td><button class='btn btn-primary' onclick='showFileUploadModal()'>Upload File</button></td>";
                        } else {
                            echo "<td></td>"; // Kosongkan kolom jika bukan Finish
                        }

                        // Action buttons for Edit/Delete (only if golongan == 2 and acting == 2)
                        if ($golongan == 2 && $acting == 2) {
                            echo "<td>
                            <i class='bi bi-pencil-square' style='cursor: pointer;' onclick='showEditModal(" . json_encode($row) . ")'></i> 
                          </td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='11'>No data available</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>

        <!-- Button "Tambah Data" (Only if golongan == 4 and acting == 2) -->
        <?php if ($golongan == 4 && $acting == 2): ?>
            <div style="text-align: center; margin-top: 20px;">
                <button class="btn btn-success" onclick="showAddDataModal()">Tambah Data</button>
            </div>
        <?php endif; ?>
    </div>



    </div>

    <!-- <script src="assets/DataTable/datatables.js"></script>
    <script src="assets/DataTable/datatables.min.js"></script> -->
    <!-- <script src="assets/DataTable/table.min.js"></script> -->
    <script>
        $(document).ready(function() {
            initializeDataTables();
        })

        function initializeDataTables() {
            $('#monitoringData').DataTable({
                "processing": true,
                "serverSide": false,
                "stateSave": true,
                orderCellsTop: true,
                autoWidth: false,
                "language": {
                    "emptyTable": "Belum ada data yang diterima."
                },
                columnDefs: [{
                        width: '15%',
                        targets: 4
                    },
                    {
                        width: '10%',
                        targets: 5
                    },
                    {
                        width: '18%',
                        targets: -1
                    },
                ],
            });
        }

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
                    // Lakukan tindakan logout di sini
                    window.location.href = 'logout.php'; // Ganti dengan URL yang sesuai
                }
            });
        }
        // Show SweetAlert modal for file upload with restrictions
        function showFileUploadModal() {
            Swal.fire({
                title: 'Upload File',
                html: `<input type="file" id="fileUpload" class="swal2-input" accept=".pdf">`,
                showCancelButton: true,
                confirmButtonText: 'Save',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const file = Swal.getPopup().querySelector('#fileUpload').files[0];
                    if (!file) {
                        Swal.showValidationMessage(`Please select a file`);
                        return false;
                    }
                    if (file.type !== "application/pdf") {
                        Swal.showValidationMessage(`Only PDF files are allowed`);
                        return false;
                    }
                    if (file.size > 2 * 1024 * 1024) { // 2MB limit
                        Swal.showValidationMessage(`File size exceeds 2MB`);
                        return false;
                    }
                    return {
                        file: file
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const fileData = result.value.file;
                    const formData = new FormData();
                    formData.append('file', fileData);

                    // Send file data using AJAX
                    $.ajax({
                        url: 'upload_file.php', // Replace with your PHP upload file handler
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            Swal.fire('Success!', 'File has been uploaded.', 'success');
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error!', 'File upload failed.', 'error');
                        }
                    });
                }
            });
        }


        function getTodayDate() {
            return new DateTime(); // Mengembalikan tanggal hari ini dalam objek DateTime
        }

        function getTodayDate() {
            return date('Y-m-d'); // Mengembalikan tanggal dalam format Y-m-d (misalnya 2024-09-01)
        }



        // Show modal to edit data
        function showEditModal(data) {
            Swal.fire({
                title: 'Edit Data PPB',
                html: `
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div>
                    <label for="edit-no_ppb" style="display: block; text-align: left; font-weight: bold;">No. PPB:</label>
                    <input type="text" id="edit-no_ppb" class="swal2-input" value="${data.no_ppb}" readonly style="background-color: #e9ecef;">
                </div>
                <div>
                    <label for="edit-part_pengerjaan" style="display: block; text-align: left; font-weight: bold;">Part Pengerjaan:</label>
                    <input type="text" id="edit-part_pengerjaan" class="swal2-input" value="${data.part_pengerjaan}" placeholder="Part Pengerjaan">
                </div>
                <div>
                    <label for="edit-jenis_pengecekan" style="display: block; text-align: left; font-weight: bold;">Jenis Pengecekan:</label>
                    <input type="text" id="edit-jenis_pengecekan" class="swal2-input" value="${data.jenis_pengecekan}" placeholder="Jenis Pengecekan">
                </div>
                <div>
                    <label for="edit-request" style="display: block; text-align: left; font-weight: bold;">Request From:</label>
                    <input type="text" id="edit-request" class="swal2-input" value="${data.request}" placeholder="Request From">
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <div style="flex: 1; margin-right: 10px;">
                        <label for="edit-klasifikasi" style="display: block; text-align: left; font-weight: bold;">Klasifikasi PPB:</label>
                        <select id="edit-klasifikasi" class="swal2-input" style="width: 100%;">
                            <option value="Top urgent" ${data.klasifikasi === 'Top urgent' ? 'selected' : ''}>Top urgent</option>
                            <option value="Urgent" ${data.klasifikasi === 'Urgent' ? 'selected' : ''}>Urgent</option>
                            <option value="ASAP" ${data.klasifikasi === 'ASAP' ? 'selected' : ''}>ASAP</option>
                            <option value="FIFO" ${data.klasifikasi === 'FIFO' ? 'selected' : ''}>FIFO</option>
                        </select>
                    </div>
                    <div style="flex: 1; margin-left: 10px;">
                        <label for="edit-status" style="display: block; text-align: left; font-weight: bold;">Status Pengerjaan:</label>
                        <select id="edit-status" class="swal2-input" style="width: 100%;">
                            <option value="progress" ${data.status === 'progress' ? 'selected' : ''}>Progress</option>
                            <option value="waiting" ${data.status === 'waiting' ? 'selected' : ''}>Waiting</option>
                            <option value="pending" ${data.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="finish" ${data.status === 'finish' ? 'selected' : ''}>Finish</option>
                        </select>
                    </div>
                </div>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: 'Save',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const updatedData = {
                        no_ppb: document.getElementById('edit-no_ppb').value,
                        part_pengerjaan: document.getElementById('edit-part_pengerjaan').value,
                        jenis_pengecekan: document.getElementById('edit-jenis_pengecekan').value,
                        request: document.getElementById('edit-request').value,
                        klasifikasi: document.getElementById('edit-klasifikasi').value,
                        status: document.getElementById('edit-status').value
                    };
                    return updatedData;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'update_ppb.php',
                        type: 'POST',
                        data: result.value,
                        success: function(response) {
                            Swal.fire('Success!', 'Data has been updated.', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to update data.', 'error');
                        }
                    });
                }
            });
        }

        function showAddDataModal() {
            Swal.fire({
                title: 'Tambah Data PPB',
                html: '<form id="tambahDataForm">' +
                    '<div style="margin-bottom: 10px;">' +
                    '<label for="noPpb">No. PPB</label>' +
                    '<input type="text" id="noPpb" name="noPpb" class="swal2-input" placeholder="Masukkan No. PPB" required>' +
                    '</div>' +
                    '<div style="margin-bottom: 10px;">' +
                    '<label for="partPengerjaan">Part Pengerjaan</label>' +
                    '<input type="text" id="partPengerjaan" name="partPengerjaan" class="swal2-input" placeholder="Masukkan Part Pengerjaan" required>' +
                    '</div>' +
                    '<div style="margin-bottom: 10px;">' +
                    '<label for="jenisPengecekan">Jenis Pengecekan</label>' +
                    '<input type="text" id="jenisPengecekan" name="jenisPengecekan" class="swal2-input" placeholder="Masukkan Jenis Pengecekan" required>' +
                    '</div>' +
                    '<div style="margin-bottom: 10px;">' +
                    '<label for="reqFrom">Req From</label>' +
                    '<input type="text" id="reqFrom" name="reqFrom" class="swal2-input" placeholder="Masukkan Req From" required>' +
                    '</div>' +
                    '<div style="margin-bottom: 10px;">' +
                    '<label for="klasifikasi">Klasifikasi PPB</label>' +
                    '<select id="klasifikasi" name="klasifikasi" class="swal2-select" required>' +
                    '<option value="top urgent">Top Urgent</option>' +
                    '<option value="urgent">Urgent</option>' +
                    '<option value="asap">ASAP</option>' +
                    '<option value="fifo">FIFO</option>' +
                    '</select>' +
                    '</div>' +
                    '<div style="margin-bottom: 10px;">' +
                    '<label for="statusPengerjaan">Status Pengerjaan</label>' +
                    '<select id="statusPengerjaan" name="statusPengerjaan" class="swal2-select" required>' +
                    '<option value="finish">Finish</option>' +
                    '<option value="pending">Pending</option>' +
                    '<option value="waiting">Waiting</option>' +
                    '<option value="progres">Progres</option>' +
                    '</select>' +
                    '</div>' +
                    '</form>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    // Collect form data
                    const noPpb = document.getElementById('noPpb').value;
                    const partPengerjaan = document.getElementById('partPengerjaan').value;
                    const jenisPengecekan = document.getElementById('jenisPengecekan').value;
                    const reqFrom = document.getElementById('reqFrom').value;
                    const klasifikasi = document.getElementById('klasifikasi').value;
                    const statusPengerjaan = document.getElementById('statusPengerjaan').value;

                    // Validation
                    if (!noPpb || !partPengerjaan || !jenisPengecekan || !reqFrom || !klasifikasi || !statusPengerjaan) {
                        Swal.showValidationMessage('Semua field harus diisi!');
                        return false;
                    }

                    // Return form data
                    return {
                        noPpb,
                        partPengerjaan,
                        jenisPengecekan,
                        reqFrom,
                        klasifikasi,
                        statusPengerjaan
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = result.value;

                    // AJAX request to send data to add_ppb.php
                    $.ajax({
                        url: 'add_ppb.php',
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            // Assuming 'response' is JSON and contains success status
                            if (response.success) {
                                Swal.fire('Success', 'Data berhasil ditambahkan!', 'success');
                            } else {
                                Swal.fire('Error', response.message || 'Terjadi kesalahan saat menambah data.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Terjadi kesalahan saat mengirim data ke server.', 'error');
                        }
                    });
                }
            });
        }



        // Confirm deletion
        function confirmDelete(no_ppb) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_ppb.php',
                        type: 'POST',
                        data: {
                            no_ppb: no_ppb
                        },
                        success: function(response) {
                            Swal.fire('Deleted!', 'Your data has been deleted.', 'success').then(() => {
                                location.reload(); // Reload page after success
                            });
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to delete data.', 'error');
                        }
                    });
                }
            });
        }

        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const timeString = `${hours}:${minutes}`;
            document.getElementById('clock').innerText = timeString;
        }

        setInterval(updateTime, 1000);
        updateTime(); // Initialize clock immediately
    </script>
</body>

</html>