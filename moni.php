<?php
session_start();
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
    <span>MONITORING PPB - LABORATORIUM QUALITY ASSURANCE </span>
    
    
    <div id="clock" class="clock"></div>
    
    <!-- <div class="header-right">
        <button class="home" id="home-button" onclick="goHome()">HOME</button>
        <button class="monitoring" id="monitoring-button" onclick="goApprove()">Approval</button>
        <button class="logout-button" onclick="confirmLogout()">Logout</button>
    </div> -->
    
</div>
<?php
require 'konfig.php';

// Fetch data from the database
$sql = "
    SELECT id_ppb, no_ppb, part_pengerjaan, jenis_pengecekan, request, klasifikasi_ppb, date_format(receive, '%d-%m-%Y') as receive, date_format(est_selesai, '%d-%m-%Y') as est_selesai, status, file 
    FROM monitoring 
    ORDER BY 
        FIELD(status, '2', '4', '1', '3'), 
        CASE WHEN status = 1 THEN FIELD(klasifikasi_ppb, 1, 2, 3, 4) END
";
$result = $conn->query($sql);
?>

<div class="container">
    <table id="monitoringData" >
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
                

            </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            $no = 1; // Row numbering
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . $row["no_ppb"] . "</td>";
                echo "<td>" . $row["part_pengerjaan"] . "</td>";
                echo "<td>" . $row["jenis_pengecekan"] . "</td>";
                switch(true) {
                    case (strpos($row["request"], "PDE") !== false):
                        echo "<td> <span class='req-badge pde'>" . $row["request"] . "</span></td>";
                        break;
                    case (strpos($row["request"], "VDD") !== false):
                            echo "<td> <span class='req-badge vdd'>" . $row["request"] . "</span></td>";
                    break;
                    case (strpos($row["request"], "PROC") !== false):
                        echo "<td> <span class='req-badge vdd'>" . $row["request"] . "</span></td>";
                break;
                case (strpos($row["request"], "PROD") !== false):
                    echo "<td> <span class='req-badge prod'>" . $row["request"] . "</span></td>";
            break;
                    default:
                        echo "<td>" . $row["request"] . "</td>";
                }
                
                

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
                // if ($row["status"] == 3) { // Status is "Finish"
                    $today = new DateTime(); // Tanggal hari ini
                    $est_selesai = new DateTime($row["est_selesai"]); // Tanggal "Est. Selesai"
                    $deviasi_waktu = $today->diff($est_selesai); // Selisih waktu

                    if ($today > $est_selesai) {
                        if($deviasi_waktu->days == 0){
                            echo "<td>" . $deviasi_waktu->days . " hari</td>";
                        }else{
                        // Jika hari ini lebih dari est_selesai, tampilkan negatif (-) menandakan terlambat
                        echo "<td style='background-color: red; color: white;'>-" . $deviasi_waktu->days . " hari</td>";
                        }
                    } else {
                        // Jika hari ini kurang dari est_selesai, tampilkan positif (+) menandakan lebih cepat
                        echo "<td>+" . $deviasi_waktu->days . " hari</td>";
                    }
                // } else {
                    // Jika status belum Finish, kolom deviasi waktu kosong
                    // echo "<td></td>";
                // }

                $status_text = '';
                $status_class = '';
                switch ($row["status"]) {
                    case 1:
                        $status_text = 'Waiting';
                        $status_class = 'status-waiting';
                        break;
                    case 2:
                        $status_text = 'On-Progress';
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

                // Show Upload File button only for status Finish (3), otherwise show message
                if ($row['status'] == 3 && empty($row['file'])) {
                    echo "<td><button class='btn btn-primary' id='DataPPB' data-id_ppb='" . $row['id_ppb'] . "' onclick='showFileUploadModal(this)'>Upload File</button></td>";
                } elseif ($row['status'] == 3 && !empty($row['file'])) {
                    echo "<td><button class='btn btn-info' onclick='viewFile(\"" . $row['file'] . "\")'><i class='bi bi-eye'></i></button></td>";
                } else {
                    echo "<td>File belum tersedia</td>";
                }

                // Action buttons for Edit/Delete (only if golongan == 2 and acting == 2)
                // if ($golongan == 2 && $acting == 2) {
                //     echo "<td>
                //             <i class='bi bi-pencil-square' style='cursor: pointer;' onclick='showEditModal(" . json_encode($row) . ")'></i> 
                //           </td>";
                // }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='11'>No data available</td></tr>";
        }
        $conn->close();
        ?>
    </tbody>
    </table>
    <div>@CopyRight by QA-LAB-SFN</div>
  


    <!-- Loader -->
<div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 9999; text-align: center;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="spinner-border" role="status">
            <span class="sr-only"></span>
        </div>
        <p>Loading, please wait...</p>
    </div>
</div>


    <!-- Button "Tambah Data" (Only if golongan == 4 and acting == 2) -->

</div>




</div>

    <!-- <script src="assets/DataTable/datatables.js"></script>
    <script src="assets/DataTable/datatables.min.js"></script> -->
    <!-- <script src="assets/DataTable/table.min.js"></script> -->
    <script>
       $(document).ready(function() {
    initializeDataTables();
});

function initializeDataTables() {
    // Check if DataTable is already initialized and destroy it if it is
    if ($.fn.DataTable.isDataTable('#monitoringData')) {
        $('#monitoringData').DataTable().destroy();
    }

    // Initialize the DataTable with custom settings
    var table = $('#monitoringData').DataTable({
        "processing": true,
        "serverSide": false,
        "stateSave": true,
        "paging": true,
        "autoWidth": false,
        "pageLength": 10, // Adjust page length if needed
        "orderCellsTop": true,
        "language": {
            "emptyTable": "Belum ada data yang diterima."
        },
        "columnDefs": [
            { width: '15%', targets: 4 },
            { width: '10%', targets: 5 },
            { width: '18%', targets: -1 }
        ]
    });

    // Function to go to the next page every 3 minutes (180,000 ms)
    setInterval(function() {
        var info = table.page.info();
        var nextPage = (info.page + 1) % info.pages;
        table.page(nextPage).draw(false);
    }, 120000); // 3 minutes in milliseconds
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


        function showFileUploadModal(button) {
    const id_ppb = button.getAttribute('data-id_ppb');

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
            return { file: file };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const fileData = result.value.file;
            const formData = new FormData();
            formData.append('file', fileData);
            formData.append('id_ppb', id_ppb);

            // Kirim file dan id_ppb ke upload_file.php
            $.ajax({
                url: 'upload_file.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.fire('Success!', 'File has been uploaded.', 'success');

                    // Ganti tombol "Upload File" menjadi tombol dengan ikon mata setelah berhasil diunggah
                    const button = document.querySelector(`button[data-id_ppb='${id_ppb}']`);
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-info');
                    button.innerHTML = '<i class="bi bi-eye"></i>'; // Ikon mata
                    button.onclick = () => viewFile(fileData.name); // Ubah fungsinya menjadi melihat file
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error!', 'File upload failed.', 'error');
                }
            });
        }
    });
}


function viewFile(fileName) {
    Swal.fire({
        title: 'Review File',
        html: `<iframe src='uploads/pdf/${fileName}' style='width:100%; height:400px;' frameborder='0'></iframe>`,
        width: '80%',
        showCloseButton: true,
        showConfirmButton: false,
        scrollbarPadding: false,
    });
}



function getTodayDate() {
    return new DateTime(); // Mengembalikan tanggal hari ini dalam objek DateTime
}

function getTodayDate() {
    return date('Y-m-d'); // Mengembalikan tanggal dalam format Y-m-d (misalnya 2024-09-01)
}



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
                            <option value="1" ${data.klasifikasi_ppb == 1 ? 'selected' : ''}>Top urgent</option>
                            <option value="2" ${data.klasifikasi_ppb == 2 ? 'selected' : ''}>Urgent</option>
                            <option value="3" ${data.klasifikasi_ppb == 3 ? 'selected' : ''}>ASAP</option>
                            <option value="4" ${data.klasifikasi_ppb == 4 ? 'selected' : ''}>FIFO</option>
                        </select>
                    </div>
                    <div style="flex: 1; margin-left: 10px;">
                        <label for="edit-status" style="display: block; text-align: left; font-weight: bold;">Status Pengerjaan:</label>
                        <select id="edit-status" class="swal2-input" style="width: 100%;">
                            <option value="1" ${data.status == 1 ? 'selected' : ''}>Waiting</option>
                            <option value="2" ${data.status == 2 ? 'selected' : ''}>On-Progress</option>
                            <option value="3" ${data.status == 3 ? 'selected' : ''}>Finish</option>
                            <option value="4" ${data.status == 4 ? 'selected' : ''}>Pending</option>
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
                id_ppb: data.id_ppb, // Ensure id_ppb is passed
                no_ppb: document.getElementById('edit-no_ppb').value,
                part_pengerjaan: document.getElementById('edit-part_pengerjaan').value,
                jenis_pengecekan: document.getElementById('edit-jenis_pengecekan').value,
                request: document.getElementById('edit-request').value,
                klasifikasi_ppb: document.getElementById('edit-klasifikasi').value,
                status: document.getElementById('edit-status').value
            };

            // Validate input
            if (!updatedData.part_pengerjaan || !updatedData.jenis_pengecekan || !updatedData.request) {
                Swal.showValidationMessage('Harap isi semua field!');
                return false;
            }
            return updatedData;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'update_ppb.php',
                type: 'POST',
                data: result.value,
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.status === 'success') {
                        Swal.fire('Success!', 'Data has been updated.', 'success').then(() => {
                            location.reload(); // Refresh the page to see the updated data
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
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
        html: `
        <div class="modal-grid">
            <p><strong>Isi data berikut sebelum menambahkan PPB:</strong></p>
            
            <label for="noPpb">No. PPB:</label>
            <input type="text" id="noPpb" class="swal2-input full-width" placeholder="NO. PPB">
            
            <label for="partPengerjaan">Part Pengerjaan:</label>
            <input type="text" id="partPengerjaan" class="swal2-input full-width" placeholder="Part Pengerjaan">
            
            <label for="jenisPengecekan">Jenis Pengecekan:</label>
            <input type="text" id="jenisPengecekan" class="swal2-input full-width" placeholder="Jenis Pengecekan">
            
            <div class="row">
                <label for="reqFrom">Request From:</label>
                <input type="text" id="reqFrom" class="swal2-input input-half" placeholder="Request From">
                
                <label for="klasifikasiPpb">Klasifikasi PPB:</label>
                <select id="klasifikasiPpb" class="swal2-input input-half">
                    <option value="1">Top Urgent</option>
                    <option value="2">Urgent</option>
                    <option value="3">Asap</option>
                    <option value="4">Fifo</option>
                </select>
            </div>
            
            <div class="row">
                <label for="receiveDate">Receive Date:</label>
                <input type="datetime-local" id="receiveDate" class="swal2-input input-half">
                
                <label for="estSelesaiDate">Est Selesai Date:</label>
                <input type="datetime-local" id="estSelesaiDate" class="swal2-input input-half">
            </div>
        </div>
        `,
        focusConfirm: false,
        preConfirm: () => {
            const noPpb = document.getElementById('noPpb').value;
            const partPengerjaan = document.getElementById('partPengerjaan').value;
            const jenisPengecekan = document.getElementById('jenisPengecekan').value;
            const reqFrom = document.getElementById('reqFrom').value;
            const klasifikasiPpb = document.getElementById('klasifikasiPpb').value;
            const receiveDate = document.getElementById('receiveDate').value;
            const estSelesaiDate = document.getElementById('estSelesaiDate').value;

            // Validate input
            if (!noPpb || !partPengerjaan || !jenisPengecekan || !reqFrom || !receiveDate || !estSelesaiDate) {
                Swal.showValidationMessage('Harap isi semua field!');
                return false;
            }

            // Return the form data
            return { noPpb, partPengerjaan, jenisPengecekan, reqFrom, klasifikasiPpb, receiveDate, estSelesaiDate };
        }
    }).then((result) => {
        if (result.isConfirmed) {

            // Show loader
            document.getElementById('loader').style.display = 'flex';
            // Convert form data to URL-encoded format
            const formData = new URLSearchParams(result.value).toString();
            console.log(formData);
            // Send data to server via AJAX
            fetch('add_ppb.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            })
            .then(response => response.json())
            .then(serverResult => {
                // Hide loader
                document.getElementById('loader').style.display = 'none';
                if (serverResult.success) {
                    // Add data to the table locally
                    addDataToTable(result.value);
                    Swal.fire('Berhasil!', 'Data berhasil ditambahkan.', 'success').then(() => {
                    // Refresh halaman setelah menutup pesan
                    location.reload();
                });
                } else {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat mengirim data.', 'error');
                }
            })
            .catch(error => {
                document.getElementById('loader').style.display = 'none';
                console.error('Error:', error);
                Swal.fire('Gagal!', 'Terjadi kesalahan koneksi.', 'error');
            });
        }
    });
}

function autoReload() {
    setInterval(() => {
        location.reload();
    }, 18 * 60 * 1000); // 13 menit dalam milidetik
}

// Panggil fungsi autoReload saat halaman dimuat
window.onload = () => {
    autoReload();
};

function addDataToTable(data) {
    const table = document.getElementById("monitoringData").getElementsByTagName('tbody')[0];
    const newRow = table.insertRow();

    // Increment row number
    const newCellNo = newRow.insertCell(0);
    newCellNo.innerHTML = table.rows.length;

    const newCellNoPpb = newRow.insertCell(1);
    newCellNoPpb.innerHTML = data.noPpb;

    const newCellPartPengerjaan = newRow.insertCell(2);
    newCellPartPengerjaan.innerHTML = data.partPengerjaan;

    const newCellJenisPengecekan = newRow.insertCell(3);
    newCellJenisPengecekan.innerHTML = data.jenisPengecekan;

    const newCellReqFrom = newRow.insertCell(4);
    newCellReqFrom.innerHTML = data.reqFrom;

    const newCellKlasifikasi = newRow.insertCell(5);
    newCellKlasifikasi.innerHTML = getKlasifikasiText(data.klasifikasiPpb);

    const newCellReceive = newRow.insertCell(6);
    newCellReceive.innerHTML = formatDate(data.receiveDate);

    const newCellEstSelesai = newRow.insertCell(7);
    newCellEstSelesai.innerHTML = formatDate(data.estSelesaiDate);

    const newCellStatus = newRow.insertCell(8);
    newCellStatus.innerHTML = 'Waiting'; // Set status to "Waiting" by default
}

function getKlasifikasiText(klasifikasiPpb) {
    switch (klasifikasiPpb) {
        case '1': return '<span class="klasifikasi-badge top-urgent">Top Urgent</span>';
        case '2': return '<span class="klasifikasi-badge urgent">Urgent</span>';
        case '3': return '<span class="klasifikasi-badge asap">Asap</span>';
        case '4': return '<span class="klasifikasi-badge fifo">Fifo</span>';
        default: return 'Unknown';
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString(); // Format tanggal dan waktu
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
                data: { no_ppb: no_ppb },
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

function goHome() {
    window.location.href = 'index.php'; // Change this URL to your desired monitoring page
}

function goApprove() {
    window.location.href = 'approve.php'; // Change this URL to your desired monitoring page
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
