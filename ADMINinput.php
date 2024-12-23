<?php
include 'konfig.php';

// Initialize an empty array to store image filenames
$images = [];

// Check if 'no_ir' parameter is set and not empty
if (isset($_GET['no_ir']) && !empty($_GET['no_ir'])) {
    $no_ir = $_GET['no_ir']; // Keep as string

    // Prepare and execute the SQL query to fetch images
    $query = $conn->prepare("SELECT image FROM no_ir WHERE no_ir = ?");
    if ($query === false) {
        die("Prepare failed: " . $conn->error);
    }

    $query->bind_param("s", $no_ir); // Bind as a string
    $query->execute();
    $result = $query->get_result();

    // Check if the query executed successfully
    if ($result === false) {
        die("Query Failed: " . $conn->error);
    }

    // Fetch images from the result set
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['image'])) {
            $images[] = $row['image'];
        }
    }

    $query->close();
} else {
    echo "no_ir parameter is missing or empty.";
    exit;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Quality Assurance</title>
    <link rel="stylesheet" href="assets/css/inputstyle.css">
    <link rel="stylesheet" href="assets/css/modal.css">
    <link rel="stylesheet" href="assets/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <style>
        .btn-edit {
            color: white;
            background-color: #0d6efd;
            /* Bootstrap Primary Color */
            border: none;
        }

        .btn-delete {
            color: white;
            background-color: #dc3545;
            /* Bootstrap Danger Color */
            border: none;
        }
    </style>
</head>

<body>
    <header>
        <img src="assets/img/kyb.png" alt="KYB Logo" class="logo">
        <h1>LABORATORIUM QUALITY ASSURANCE - PT KAYABA INDONESIA</h1>
        <div class="clock" id="clock">11:11</div>
        <button class="logout-btn" onclick="confirmLogout()">LOGOUT</button>
    </header>
    <main>
        <div class="container">
            <div class="container-scroll">
                <h1>Inspeksi</h1>
                <div class="scroll-container">
                    <?php if (empty($images)): ?>
                        <p>No images found.</p>
                    <?php else: ?>
                        <?php foreach ($images as $image): ?>
                            <div class="image-container">
                                <img src="./assets/uploads/<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" alt="Image">
                                <button class="edit-image-btn" onclick="editImage('<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>')">Edit Image</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
            <table class="table table-bordered table-hover" id="inspectionTable">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th style="width: 30%;">Item Inspeksi</th>
                        <th style="width: 30%;">Standar</th>
                        <th style="width: 30%;">Nama Alat</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'konfig.php';

                    $query = $conn->prepare("
                SELECT inspeksi.id_inspeksi, inspeksi.item_inspeksi, inspeksi.standar, tools.nama_tools 
                FROM inspeksi 
                JOIN tools ON inspeksi.id_tools = tools.id_tools
                WHERE inspeksi.no_ir = ?
            ");

                    if ($query === false) {
                        die("Prepare failed: " . $conn->error);
                    }

                    $query->bind_param("s", $no_ir); // Bind as a string
                    $query->execute();
                    $result = $query->get_result();

                    if (!$result) {
                        die("Query Failed: " . $conn->error);
                    }

                    $rowNumber = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr data-row='{$rowNumber}' data-standar='{$row['standar']}'>
                    <td>{$rowNumber}</td>
                    <td>{$row['item_inspeksi']}</td>
                    <td>{$row['standar']}</td>
                    <td>{$row['nama_tools']}</td>
                    <td>
                        <button type='button' class='btn btn-sm btn-edit me-2' onclick='editInspection({$row['id_inspeksi']})'>Edit</button>
                        <button type='button' class='btn btn-sm btn-delete' onclick='deleteInspection({$row['id_inspeksi']})'>Delete</button>
                    </td>
                </tr>";
                        $rowNumber++;
                    }

                    $query->close();
                    $conn->close();
                    ?>
                </tbody>
            </table>



            <div class="button-container top-section">
                <button class="add-inspection-btn" onclick="openAddInspectionModal()">+ Inspection Item</button>
                <button class="add-inspection-btn" onclick="openAddToolsModal()"> + Tambah Tools</button> <!-- New button -->
                <button class="home-btn" onclick="window.location.href='adminindex.php'">
                    <img src="assets/img/home.png" alt="Home Icon">
                </button>
            </div>
        </div>
    </main>

    <script src="assets/jquery/jquery.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }

        // Function to open SweetAlert modal for adding tools and submit via AJAX
        function openAddToolsModal() {
            Swal.fire({
                title: 'Tambah Tools',
                html: '<input id="id_tools" class="swal2-input" placeholder="ID Tools" type="number">' + // Ensure it's a number input
                    '<input id="nama_tools" class="swal2-input" placeholder="Nama Tools">',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                preConfirm: () => {
                    const id_tools = document.getElementById('id_tools').value;
                    const nama_tools = document.getElementById('nama_tools').value;

                    // Ensure id_tools is a valid number and nama_tools is not empty
                    if (!id_tools || isNaN(id_tools) || !nama_tools) {
                        Swal.showValidationMessage('ID Tools must be a number and Nama Tools is required!');
                        return false;
                    }

                    return {
                        id_tools: parseInt(id_tools),
                        nama_tools: nama_tools
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const toolsData = result.value;

                    // AJAX request to send data to add_tools.php
                    $.ajax({
                        url: 'add_tools.php', // Target PHP page
                        type: 'POST',
                        data: {
                            id_tools: toolsData.id_tools,
                            nama_tools: toolsData.nama_tools
                        },
                        success: function(response) {
                            const jsonResponse = JSON.parse(response);

                            if (jsonResponse.status === 'success') {
                                Swal.fire('Success!', 'Tools added successfully!', 'success').then(() => {
                                    location.reload(); // Reload page or update content dynamically
                                });
                            } else {
                                Swal.fire('Error!', jsonResponse.message || 'Failed to add tools.', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error!', 'Failed to send request.', 'error');
                        }
                    });
                }
            });
        }


        function toggleMinMax(isMeasured) {
            const minMaxFields = document.getElementById('minMaxFields');
            minMaxFields.style.display = isMeasured ? 'block' : 'none';
        }

        function addInspectionItem(no_ir) {
            Swal.fire({
                title: 'Add Inspection Item',
                html: `<form id="inspectionForm" style="text-align: left;">
                <input type="hidden" id="no_ir" value="${no_ir}">  <!-- Hidden field for no_ir -->
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="item_inspeksi">Item Inspeksi:</label>
                    <input type="text" id="item_inspeksi" class="swal2-input" required style="width: 100%;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="standar">Standar:</label>
                    <input type="text" id="standar" class="swal2-input" required style="width: 100%;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="alat">Alat:</label>
                    <select id="alat" class="swal2-input" required style="width: 100%;">
                        <option value="">Loading...</option> <!-- Default placeholder while options are loading -->
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Measurement:</label>
                    <div class="radio-group" style="display: flex; gap: 10px;">
                        <label><input type="radio" name="measurement" value="1" onclick="toggleMinMax(true)"> Terukur</label>
                        <label><input type="radio" name="measurement" value="0" onclick="toggleMinMax(false)"> Tidak Terukur</label>
                        <label><input type="radio" name="measurement" value="2" onclick="toggleMinMax(true)"> Terukur Manual</label> <!-- New option -->
                    </div>
                </div>
                
                <div id="minMaxFields" style="display: none; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="min">Min:</label>
                        <input type="number" id="min" class="swal2-input" style="width: 100%;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="max">Max:</label>
                        <input type="number" id="max" class="swal2-input" style="width: 100%;">
                    </div>
                </div>
            </form>`,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Add',
                cancelButtonText: 'Cancel',
                didOpen: () => {
                    // Fetch alat (tools) options from server and populate the dropdown
                    $.ajax({
                        type: 'GET',
                        url: 'proses/fetch_tools.php', // PHP script that returns the tools data
                        success: function(response) {
                            const tools = JSON.parse(response);
                            const alatSelect = document.getElementById('alat');
                            alatSelect.innerHTML = ''; // Clear loading placeholder

                            if (tools.length > 0) {
                                tools.forEach(tool => {
                                    const option = document.createElement('option');
                                    option.value = tool.id_tools; // Set the value to id_tools
                                    option.textContent = tool.nama_tools; // Display the name
                                    alatSelect.appendChild(option);
                                });
                            } else {
                                alatSelect.innerHTML = '<option value="">No tools available</option>';
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading tools:", error);
                            const alatSelect = document.getElementById('alat');
                            alatSelect.innerHTML = '<option value="">Error loading options</option>';
                        }
                    });
                },
                preConfirm: () => {
                    const noIr = Swal.getPopup().querySelector('#no_ir').value;
                    const itemInspeksi = Swal.getPopup().querySelector('#item_inspeksi').value;
                    const standar = Swal.getPopup().querySelector('#standar').value;
                    const alat = Swal.getPopup().querySelector('#alat').value; // Get selected id_tools
                    const min = Swal.getPopup().querySelector('#min').value;
                    const max = Swal.getPopup().querySelector('#max').value;
                    const measurement = Swal.getPopup().querySelector('input[name="measurement"]:checked')?.value;

                    if (!itemInspeksi || !standar || !alat || measurement === undefined) {
                        Swal.showValidationMessage("Please fill all required fields");
                        return false;
                    }

                    return {
                        noIr,
                        itemInspeksi,
                        standar,
                        alat,
                        min,
                        max,
                        measurement
                    }; // alat is id_tools
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const {
                        noIr,
                        itemInspeksi,
                        standar,
                        alat,
                        min,
                        max,
                        measurement
                    } = result.value;

                    // Send data to the server using AJAX
                    $.ajax({
                        type: 'POST',
                        url: 'proses/crud_inspeksi.php',
                        data: {
                            no_ir: noIr, // Send the hidden no_ir value
                            item_inspeksi: itemInspeksi,
                            standar: standar,
                            alat: alat, // Send the selected id_tools
                            min: measurement === '1' || measurement === '2' ? min : null,
                            max: measurement === '1' || measurement === '2' ? max : null,
                            terukur: measurement
                        },
                        success: function(response) {
                            Swal.fire('Success!', response, 'success');
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error', 'Failed to add inspection item.', 'error');
                        }
                    });
                }
            });
        }



        function toggleMinMax(show) {
            const minMaxFields = document.getElementById('minMaxFields');
            minMaxFields.style.display = show ? 'block' : 'none';
        }


        // function toggleMinMax(show) {
        //     document.getElementById('minMaxFields').style.display = show ? 'block' : 'none';
        // }



        function addItemToTable(itemInspeksi, standar, alat, min, max, measurement) {
            const table = document.getElementById('inspectionTable').getElementsByTagName('tbody')[0];
            const rowNumber = table.rows.length + 1;
            const status = measurement === 'Measured' ? 'default' : 'Not Measured';

            const row = table.insertRow();
            row.innerHTML = `
            <td>${rowNumber}</td>
            <td>${itemInspeksi}</td>
            <td>${standar}</td>
            <td>${alat}</td>

                <button type='button' class='edit-button' onclick='editInspection(${rowNumber})'>Edit</button>
                <button type='button' class='delete-button' onclick='deleteInspection(${rowNumber})'>Delete</button>
            </td>
            <td><div class='status-icon' id='status${rowNumber}' name='status${rowNumber}'></div></td>
        `;
            document.getElementById('inspectionTable').getElementsByTagName('tbody')[0].appendChild(row);
        }

        function editInspection(id, no_ir) {
            // Ambil data inspeksi berdasarkan id_inspeksi
            $.ajax({
                url: 'proses/fetch_inspeksi.php', // PHP untuk fetch inspeksi data berdasarkan id_inspeksi
                type: 'GET',
                data: {
                    id_inspeksi: id
                },
                success: function(response) {
                    const res = JSON.parse(response); // Parse JSON response dari PHP
                    if (res.success) {
                        const data = res.data;

                        // Setelah mendapatkan data inspeksi, ambil data alat
                        $.ajax({
                            url: 'proses/fetch_tools.php', // PHP untuk fetch data tools
                            type: 'GET',
                            success: function(toolsResponse) {
                                const tools = JSON.parse(toolsResponse); // Parse JSON response dari PHP
                                let alatDropdown = '<select id="alat" class="swal2-input">';
                                alatDropdown += '<option value="" disabled>Pilih Alat</option>';

                                // Loop data tools dan tambahkan options
                                tools.forEach(tool => {
                                    // Tandai alat yang sesuai dengan id_tools yang tersimpan di data inspeksi
                                    const selected = tool.id_tools == data.id_tools ? 'selected' : '';
                                    alatDropdown += `<option value="${tool.id_tools}" ${selected}>${tool.nama_tools}</option>`;
                                });
                                alatDropdown += '</select>';

                                // Tampilkan modal dengan data inspeksi yang sudah terisi
                                Swal.fire({
                                    title: 'Edit Inspection',
                                    html: `
                                <input type="hidden" id="no_ir" value="${no_ir}">
                                <input type="text" id="item_inspeksi" class="swal2-input" placeholder="Item Inspeksi" value="${data.item_inspeksi}">
                                <input type="text" id="standar" class="swal2-input" placeholder="Standar" value="${data.standar}">
                                ${alatDropdown}
                                <input type="number" id="min" class="swal2-input" placeholder="Min" value="${data.min}">
                                <input type="number" id="max" class="swal2-input" placeholder="Max" value="${data.max}">
                                <select id="terukur" class="swal2-input">
                                    <option value="" disabled ${data.terukur === null ? 'selected' : ''}>Pilih terukur</option>
                                    <option value="1" ${data.terukur == 1 ? 'selected' : ''}>Terukur</option>
                                    <option value="0" ${data.terukur == 0 ? 'selected' : ''}>Tidak Terukur</option>
                                    <option value="2" ${data.terukur == 2 ? 'selected' : ''}>Terukur Manual</option>
                                </select>
                            `,
                                    showCancelButton: true,
                                    confirmButtonText: 'Save',
                                    cancelButtonText: 'Cancel',
                                    preConfirm: () => {
                                        const no_ir = document.getElementById('no_ir').value;
                                        const item_inspeksi = document.getElementById('item_inspeksi').value;
                                        const standar = document.getElementById('standar').value;
                                        const alat = document.getElementById('alat').value;
                                        const min = document.getElementById('min').value;
                                        const max = document.getElementById('max').value;
                                        const terukur = document.getElementById('terukur').value;

                                        // Validasi input yang wajib diisi
                                        if (!item_inspeksi || !standar || !alat || terukur === '') {
                                            Swal.showValidationMessage('Please fill out all required fields.');
                                            return false;
                                        }

                                        return {
                                            no_ir: no_ir,
                                            item_inspeksi: item_inspeksi,
                                            standar: standar,
                                            alat: alat, // id_tools
                                            min: min,
                                            max: max,
                                            terukur: terukur
                                        };
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Kirim data via Ajax
                                        $.ajax({
                                            url: 'proses/crud_inspeksi.php',
                                            type: 'POST',
                                            data: {
                                                id_inspeksi: id,
                                                no_ir: result.value.no_ir,
                                                item_inspeksi: result.value.item_inspeksi,
                                                standar: result.value.standar,
                                                alat: result.value.alat, // id_tools
                                                min: result.value.min,
                                                max: result.value.max,
                                                terukur: result.value.terukur
                                            },
                                            success: function(response) {
                                                Swal.fire('Success', response, 'success');
                                            },
                                            error: function(xhr, status, error) {
                                                Swal.fire('Error', 'Terjadi kesalahan: ' + error, 'error');
                                            }
                                        });
                                    }
                                });
                            },
                            error: function(xhr, status, error) {
                                Swal.fire('Error', 'Tidak bisa mengambil data tools: ' + error, 'error');
                            }
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'Tidak bisa mengambil data inspeksi: ' + error, 'error');
                }
            });
        }




        function deleteInspection(id) {
            // Display a SweetAlert modal to confirm deletion
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with deletion via $.ajax
                    $.ajax({
                        url: 'proses/delete_inspeksi.php',
                        type: 'POST',
                        data: {
                            id_inspeksi: id,
                            action: 'delete'
                        },
                        success: function(response) {
                            Swal.fire('Deleted!', response, 'success');
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error', 'Terjadi kesalahan: ' + error, 'error');
                        }
                    });
                }
            });
        }

        function checkStatus(rowNumber) {
            // Function to check status based on inputs
        }

        function openAddInspectionModal() {
            const no_ir = "<?php echo $no_ir; ?>";
            console.log(no_ir);
            addInspectionItem(no_ir);
        }

        function editImage(currentImage) {
            // Create a Swal modal with a file input
            Swal.fire({
                title: 'Select an Image',
                html: `
            <input type="file" id="fileInput" accept="image/*" class="swal2-file" style="display: block; margin: auto;">
        `,
                showCancelButton: true,
                confirmButtonText: 'Upload',
                focusConfirm: false,
                preConfirm: () => {
                    // Get the selected file
                    const fileInput = Swal.getPopup().querySelector('#fileInput');
                    const file = fileInput.files[0];
                    if (!file) {
                        Swal.showValidationMessage('Please select an image');
                    }
                    return file;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const file = result.value;

                    // Create FormData to send the file via AJAX
                    const formData = new FormData();
                    formData.append('new_image', file);
                    formData.append('current_image', currentImage); // Send current image name

                    // Send AJAX request to update the image
                    fetch('update_image.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            Swal.fire({
                                title: 'Success',
                                text: data,
                                icon: 'success',
                            }).then(() => {
                                location.reload(); // Reload the page to show updated image
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: 'An error occurred while uploading the image.',
                                icon: 'error',
                            });
                            console.error('Error:', error);
                        });
                }
            });
        }
    </script>
</body>

</html>