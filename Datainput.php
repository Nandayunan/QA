    <?php
    include 'konfig.php';

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Initialize variables
    $id_noir = null;
    $id_form = null;

    // Get id_noir from query string
    if (isset($_GET['no_ir'])) {
        $id_noir = $_GET['no_ir'];
    } else {
        die("Invalid id_noir.");
    }

    // Get id_form from query string or session
    if (isset($_GET['id_form']) && filter_var($_GET['id_form'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        $id_form = $_GET['id_form'];
    } elseif (isset($_SESSION['id_form']) && filter_var($_SESSION['id_form'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        $id_form = $_SESSION['id_form'];
    } else {
        die("Invalid id_form.");
    }

    // Only run the query if id_noir is set
    if ($id_noir !== null) {
        // Prepare the SQL statement
        $query = $conn->prepare("SELECT image FROM no_ir WHERE no_ir = ?");
        $query->bind_param("i", $id_noir);
        $query->execute();
        $result = $query->get_result();

        if ($result === false) {
            die("Query Failed: " . $conn->error);
        }

        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row['image'];
        }
        $query->close();
        $conn->close();
    }
    ?>



    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Laboratorium Quality Assurance</title>
        <link rel="stylesheet" href="assets/css/tstyle.css">
        <style>
            .scroll-container {
                max-height: 400px;
                /* Set height to limit the scroll area */
                overflow-y: auto;
                /* Enable vertical scrolling */
                margin: 20px auto;
                /* Center container with margin */
                padding: 10px;
                border: 1px solid #ccc;
                /* Border for better visibility */
                border-radius: 5px;
                /* Rounded corners */
            }

            .scroll-container img {
                display: block;
                margin: 10px auto;
                /* Center images horizontally */
                max-width: 100%;
                /* Ensure images do not overflow */
            }

            .locked-button {
                background-color: navy;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 16px;
                cursor: not-allowed;
                border-radius: 4px;
            }

            .input-button {
                background-color: navy;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 16px;
                cursor: pointer;
                border-radius: 4px;
            }

            .submit-button {
                background-color: blue;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 16px;
                cursor: pointer;
                border-radius: 4px;
            }

            .logout {
                background-color: red;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 16px;
                cursor: pointer;
                border-radius: 4px;
            }

            .final-submit-container {
                text-align: center;
                margin-top: 20px;
            }

            .final-submit-button {
                background-color: navy;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 16px;
                cursor: pointer;
                border-radius: 4px;
            }

            .center-image {
                display: block;
                margin: 0 auto 20px;
                /* Center the image horizontally and add some space below */
            }

            .status-icon {
                display: inline-block;
                width: 30px;
                /* Adjust width */
                height: 30px;
                /* Adjust height */
                background-size: contain;
                background-repeat: no-repeat;
                text-align: center;
                line-height: 30px;
                /* Center the text vertically */
                font-weight: bold;
                font-size: 14px;
            }

            .status-ok {
                background-color: green;
                color: white;
                border-radius: 5px;
                /* Slightly rounded corners for a square look */
            }

            .status-ng {
                background-color: red;
                color: white;
                border-radius: 5px;
                /* Slightly rounded corners for a square look */
            }


            .hidden-column {
                display: none;
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>

    <body>
        <div class="header">
            <img src="assets/img/kyb.png" alt="KYB Logo" class="logo">
            <div class="title">
                LABORATORIUM QUALITY ASSURANCE - PT KAYABA INDONESIA
            </div>
            <div class="clock-container">
                <div id="clock" class="clock"></div>
                <button class="logout" onclick="confirmLogout()">LOGOUT</button>
            </div>
        </div>

        <div class="container">
            <h1>INSPEKSI BATCH <?php echo htmlspecialchars($id_form); ?></h1>
            <div class="scroll-container">
                <!-- Display images -->
                <?php if (isset($images) && empty($images)): ?>
                    <p>No images found.</p>
                <?php elseif (isset($images)): ?>
                    <?php foreach ($images as $image): ?>
                        <img src="<?php echo "./assets/uploads/" . htmlspecialchars($image); ?>" alt="Image">
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- <img src="assets/img/tes.png" alt="Placeholder Image 2">
                <img src="assets/img/tes.png" alt="Placeholder Image 3"> -->
        <!-- Add more images as needed -->
        </div>

        <form id="inspectionForm" method="post">
            <input type="hidden" id="nama_tools_ajax" value="">
            <input type="hidden" name="id_form" value="<?php echo $id_form; ?>">
            <input type="hidden" name="no_ir" value="<?php echo $id_noir; ?>">
            <input type="hidden" name="status" value="2">
            <table class="table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Item Inspeksi</th>
                        <th>Standar</th>
                        <th class="hidden-column">Min</th>
                        <th class="hidden-column">Max</th>
                        <th>Alat</th>
                        <th>Input</th>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>6</th>
                        <th>7</th>
                        <th>8</th>
                        <th>9</th>
                        <th>10</th>
                        <th>Action</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'konfig.php';

                    // Query untuk mendapatkan data inspeksi dan join dengan tabel tools
                    $query = "SELECT i.id_inspeksi, i.item_inspeksi, i.standar, i.min, i.max, i.terukur, t.id_tools, t.nama_tools, t.detail, i.terukur 
                        FROM inspeksi i
                        JOIN tools t ON i.id_tools = t.id_tools
                        WHERE i.no_ir = ?"; // Filter berdasarkan no_ir

                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $id_noir);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result === false) {
                        die("Query Failed: " . $conn->error);
                    }

                    $rowNumber = 1;
                    while ($row = $result->fetch_assoc()) {
                        $terukur = $row['terukur'];


                        echo "<tr data-row='{$rowNumber}' data-standar='{$row['standar']}' data-min='{$row['min']}' data-max='{$row['max']}' data-terukur='{$terukur}'>
                        <td>{$rowNumber}</td>
                        <td>{$row['item_inspeksi']}</td>
                        <td>{$row['standar']}</td>
                        <td class='hidden-column'>{$row['min']}</td>
                        <td class='hidden-column'>{$row['max']}</td>
                        <td>{$row['nama_tools']}</td> <!-- Menampilkan nama alat dari tabel tools -->
                        <td><button type='button' class='input-button' onclick='enableInput({$rowNumber})'>Input</button></td>";

                        echo "<input type='hidden' name='id_tools' value='{$row['id_tools']}'>";
                        echo "<input type='hidden' name='detail[]' value='{$row['detail']}'>";
                        echo "<input type='hidden' name='nama_tools[]' value='{$row['nama_tools']}'>";


                        // Menampilkan dropdown atau input field berdasarkan nilai 'terukur'
                        for ($i = 1; $i <= 10; $i++) {
                            if ($terukur == 0) {
                                echo "<td>
                                <select name='sampling{$i}_{$rowNumber}' class='input-select' onchange='checkStatus({$rowNumber})' disabled>
                                    <option value=''>Select</option>
                                    <option value='1'>OK</option>
                                    <option value='0'>NG</option>
                                </select>
                            </td>";
                            } else {
                                echo "<td><input type='text' class='input-text' name='sampling{$i}_{$rowNumber}' oninput='checkStatus({$rowNumber})' disabled></td>";
                            }
                        }

                        echo "<td><button type='button' class='submit-button' onclick='confirmSubmit({$rowNumber})'>Submit</button></td>
                        <td>
                            <div class='status-icon' id='status{$rowNumber}'></div>
                            <input type='hidden' id='statusInput{$rowNumber}' name='status{$rowNumber}' value=''>
                        </td>
                        <input type='hidden' name='id_inspeksi[]' value='{$row['id_inspeksi']}'>
                    </tr>";

                        $rowNumber++;
                    }

                    $stmt->close();
                    $conn->close();
                    ?>
                </tbody>
            </table>

            <div class="final-submit-container">
                <button type="button" class="final-submit-button" id="finalSubmitButton" onclick="submitFinalForm()">Final Submit</button>
            </div>
        </form>


        </div>

        <script src="assets/jquery/jquery.min.js"></script>
        <script src="assets/sweetalert2/dist/sweetalert2.all.min.js "></script>


        <script>
            // Function to enable the input fields in the selected row
            // Function to enable the input fields in the selected row
            // Function to enable the input fields in the selected row
            async function enableInput(row) {
                const rowElement = document.querySelector(`tr[data-row="${row}"]`);
                const inputs = rowElement.querySelectorAll('.input-text');
                const selects = rowElement.querySelectorAll('.input-select');
                const terukur = parseInt(rowElement.getAttribute('data-terukur')); // Assuming terukur is set as a data attribute

                // Enable all inputs and selects in the row
                inputs.forEach(input => input.disabled = false);
                selects.forEach(select => select.disabled = false);

                // Focus on the first enabled input or select
                if (inputs.length > 0) {
                    inputs[0].focus();

                    // Add event listeners for navigation with Enter key
                    inputs.forEach((input, index) => {
                        input.addEventListener('keydown', async function(event) {
                            if (event.key === 'Enter') {
                                event.preventDefault(); // Prevent form submission on Enter key

                                if (terukur === 2) {
                                    // If terukur == 2, allow manual input and skip tool check
                                    checkStatus(row); // Directly check status after input
                                    const nextInput = inputs[index + 1];
                                    if (nextInput) {
                                        nextInput.focus();
                                    }
                                } else {
                                    const rawInput = input.value;
                                    const parseInput = parseCaliperValue(rawInput);

                                    // Call checkTool function and wait for the result
                                    const isValid = await checkTool(rowElement, parseInput?.idTools);

                                    if (isValid) {
                                        // If tool matches, update value and focus the next input
                                        input.value = parseInput.valueTools;
                                        checkStatus(row);

                                        const nextInput = inputs[index + 1];
                                        if (nextInput) {
                                            nextInput.focus();
                                        }
                                    } else {
                                        // If the tool is invalid, clear input, show alert, and disable further inputs
                                        input.value = '';
                                        alert('Alat tidak sesuai!'); // Alert if the tool is invalid

                                        // Disable all inputs after the current one
                                        disableRemainingInputs(inputs, index + 1);
                                    }
                                }
                            }
                        });
                    });
                }
            }

            // Helper function to disable inputs from a given start index
            function disableRemainingInputs(inputs, startIndex) {
                for (let i = startIndex; i < inputs.length; i++) {
                    inputs[i].disabled = true;
                }
            }

            // Function to verify tool data with AJAX
            function verifyToolData(rawData) {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: 'proses/get_tools.php',
                        type: 'POST',
                        data: {
                            id_tools: rawData,
                        },
                        success: function(response) {
                            // Resolve with tool name if found
                            resolve(response.nama_tools);
                        },
                        error: function(xhr, status, error) {
                            console.error("Error verifying tool:", error);
                            reject(error);
                        }
                    });
                });
            }

            // Function to check tool id with the hidden id_tools field in the row
            async function checkTool(rowElement, idToolsInput) {
                const hiddenIdToolsInput = rowElement.querySelector('input[name="id_tools"]');
                const namaToolsInput = rowElement.querySelector('input[name="nama_tools[]"]'); // Get hidden input for nama_tools

                try {
                    const namaToolsAjax = await verifyToolData(idToolsInput); // Await AJAX result
                    const inputValue = String(namaToolsInput.value).toLowerCase();
                    const targetValue = String(namaToolsAjax).toLowerCase();

                    // Check if targetValue is a substring of inputValue or vice versa
                    const isValid = inputValue.includes(targetValue) || targetValue.includes(inputValue);

                    return isValid;
                } catch (error) {
                    console.error("Tool verification failed:", error);
                    return false;
                }
            }



            // Function to parse digital caliper value
            function parseCaliperValue(caliperValue) {
                // Assuming the format is: DT{id_tools}+{value_tools}M
                const idToolsMatch = caliperValue.match(/DT(\d+)\+/); // Extract id_tools
                const valueMatch = caliperValue.match(/\+([\d.]+)M/); // Extract value_tools

                if (idToolsMatch && valueMatch) {
                    const idTools = idToolsMatch[1]; // Get the id_tools part
                    const valueTools = parseFloat(valueMatch[1]).toFixed(2); // Get value_tools and format it to 2 decimals

                    return {
                        idTools: idTools,
                        valueTools: valueTools
                    };
                }

                return null; // Return null if format is invalid
            }

            // Function to display the value in the correct row if id_tools matches
            function displayCaliperValue(caliperValue) {
                const parsedValue = parseCaliperValue(caliperValue);

                if (parsedValue) {
                    const {
                        idTools,
                        valueTools
                    } = parsedValue;

                    // Find the row that has matching id_tools hidden field
                    const rows = document.querySelectorAll('tr[data-row]');
                    let matchFound = false;

                    rows.forEach(row => {
                        const hiddenIdToolsInput = row.querySelector('input[name="id_tools"]'); // Assuming hidden input for id_tools

                        if (hiddenIdToolsInput && hiddenIdToolsInput.value == idTools) {
                            // Insert the value into the input fields for that row
                            const firstInput = row.querySelector('.input-text');
                            if (firstInput) {
                                firstInput.value = valueTools;
                                firstInput.disabled = false; // Optionally enable the input if needed
                                matchFound = true;
                            }
                        }
                    });

                    if (!matchFound) {
                        alert('No matching tool found for the provided caliper value.');
                    }
                } else {
                    alert('Invalid caliper value format.');
                }
            }


            // Example usage: When the value from the digital caliper is received, call the function
            // Assuming the caliper value format is: "DT10012+45.78M"
            // document.getElementById('caliperInputButton').addEventListener('click', function() {
            //     const caliperValue = document.getElementById('caliperValueInput').value; // Get the value from the caliper input field
            //     displayCaliperValue(caliperValue);  // Process and display the value
            // });


            function confirmLogout() {
                Swal.fire({
                    title: "Konfirmasi",
                    text: "Apakah yakin anda akan logout?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, log out!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "logout.php"; // Redirect to logout.php
                    }
                });
            }


            function checkBaris() {
                let allRowsFilled = true; // Flag to track if all rows are filled
                const totalRows = $('tbody tr').length; // Get the total number of rows in the table

                // Loop through each row
                for (let rowNumber = 1; rowNumber <= totalRows; rowNumber++) {
                    let rowFilled = false; // Flag to check if the current row is filled

                    // Check the inputs of the current row
                    for (let i = 1; i <= 10; i++) {
                        const samplingInput = $(`select[name='sampling${i}_${rowNumber}'], input[name='sampling${i}_${rowNumber}']`);
                        if (samplingInput.val() !== '') {
                            rowFilled = true; // Mark as filled if any input is not empty
                            break; // No need to check further inputs in this row
                        }
                    }

                    // If the row is not filled, set the allRowsFilled flag to false
                    if (!rowFilled) {
                        allRowsFilled = false;
                        break; // No need to check further rows
                    }
                }

                return allRowsFilled; // Return the final result
            }

            function submitFinalForm() {
                // Check if all rows are filled
                if (!checkBaris()) {
                    // Show alert if any row is not filled
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Setiap item inspeksi harus diisi.',
                        confirmButtonText: 'OK'
                    });
                    return; // Prevent form submission
                }

                // Enable all disabled input fields before form submission
                $('select[disabled], input[disabled]').each(function() {
                    $(this).prop('disabled', false);
                });

                // Serialize the form data
                var formData = $('#inspectionForm').serialize();

                // Show confirmation modal
                Swal.fire({
                    title: 'Apakah Anda ingin melakukan pengajuan approve sekarang?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Iya, Ajukan Sekarang',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // If "Iya, Ajukan Sekarang" is clicked, submit the form via AJAX
                        $.ajax({
                            url: 'submit_inspeksi.php', // URL tujuan untuk pengiriman data
                            type: 'POST', // Menggunakan POST untuk mengirim data
                            data: formData, // Data yang dikirim adalah isi dari form yang diserialisasi
                            dataType: 'json', // Tipe data yang diterima dari server adalah JSON
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Data Berhasil Disimpan!',
                                        text: 'Data inspeksi berhasil disimpan ke database.',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = 'approve.php';
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal Menyimpan Data!',
                                        text: response.message || 'Terjadi kesalahan saat menyimpan data.',
                                        confirmButtonText: 'Coba Lagi'
                                    });
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.log(jqXHR);
                                console.log(textStatus);
                                console.log(errorThrown);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan!',
                                    text: 'Tidak dapat mengirim data ke server: ',
                                    confirmButtonText: 'Coba Lagi'
                                });
                            }
                        });
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // If "Batal" is clicked, close the modal and do nothing
                        Swal.fire({
                            icon: 'info',
                            title: 'Pengajuan Dibatalkan',
                            text: 'Anda membatalkan pengajuan approve.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }



            // // Function untuk menyimpan data form ke localStorage setiap kali pengguna menginput data
            // function saveFormData() {
            //     const formElements = document.querySelectorAll('input, select'); // Ambil semua elemen input dan select
            //     formElements.forEach(element => {
            //         if (element.type !== 'hidden') { // Abaikan elemen yang tersembunyi
            //             localStorage.setItem(element.name, element.value); // Simpan data di localStorage dengan nama elemen sebagai kunci
            //         }
            //     });
            // }

            // // Function untuk mengisi form dengan data yang ada di localStorage ketika halaman dimuat
            // function populateFormData() {
            //     const formElements = document.querySelectorAll('input, select');
            //     formElements.forEach(element => {
            //         if (localStorage.getItem(element.name)) {
            //             element.value = localStorage.getItem(element.name); // Ambil data dari localStorage dan isi form
            //         }
            //     });
            // }

            // // Panggil function untuk mengisi data ketika halaman dimuat
            // window.onload = function() {
            //     populateFormData();
            // };

            // // Simpan data ke localStorage setiap kali ada perubahan di form
            // document.getElementById('inspectionForm').addEventListener('input', saveFormData);



            // Function to check the status of a specific row
            function checkStatus(row) {
                const rowElement = document.querySelector(`tr[data-row="${row}"]`);
                const inputs = rowElement.querySelectorAll('.input-text');
                const selects = rowElement.querySelectorAll('.input-select');
                const standar = parseFloat(rowElement.getAttribute('data-standar'));
                const min = parseFloat(rowElement.getAttribute('data-min'));
                const max = parseFloat(rowElement.getAttribute('data-max'));
                const statusIcon = document.getElementById(`status${row}`);
                const statusInput = document.getElementById(`statusInput${row}`);
                let status = 1; // Default to 1 (OK)

                // Check numeric inputs (terukur == 1 or 2)
                inputs.forEach(input => {
                    let value = parseFloat(input.value);
                    if (!isNaN(value)) {
                        if (value === 0) {
                            input.value = ''; // Hide value if it is 0
                        } else if (value >= min && value <= max) {
                            input.style.backgroundColor = 'green';
                            input.style.color = 'white';
                        } else {
                            input.style.backgroundColor = 'red';
                            input.style.color = 'white';
                            status = 0; // Set to 0 (NG) if any value doesn't match
                        }
                    } else {
                        input.style.backgroundColor = '';
                        input.style.color = '';
                    }
                });

                // Check dropdown selects (terukur == 0)
                selects.forEach(select => {
                    const selectedValue = select.value;
                    if (selectedValue === '0') { // NG
                        select.style.backgroundColor = 'red';
                        select.style.color = 'white';
                        status = 0; // Set to 0 (NG) if any dropdown is NG
                    } else if (selectedValue === '1') { // OK
                        select.style.backgroundColor = 'green';
                        select.style.color = 'white';
                    } else {
                        select.style.backgroundColor = '';
                        select.style.color = '';
                    }
                });

                // Update the status icon based on the overall status
                if (status === 1) {
                    statusIcon.classList.add('status-ok');
                    statusIcon.classList.remove('status-ng');
                    statusIcon.textContent = 'OK';
                    statusIcon.style.backgroundColor = 'green';
                    statusIcon.style.color = 'white';
                } else {
                    statusIcon.classList.add('status-ng');
                    statusIcon.classList.remove('status-ok');
                    statusIcon.textContent = 'NG';
                    statusIcon.style.backgroundColor = 'red';
                    statusIcon.style.color = 'white';
                }

                statusInput.value = status; // Update hidden input value
            }

            function confirmSubmit(row) {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin mengirimkan data ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, kirim!',
                    cancelButtonText: 'Tidak, batalkan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const rowElement = document.querySelector(`tr[data-row="${row}"]`);
                        const button = rowElement.querySelector('.submit-button');

                        // Gather data to be sent
                        const formData = new FormData();
                        formData.append('id_inspeksi[]', rowElement.querySelector('input[name="id_inspeksi[]"]').value);
                        formData.append('id_form', document.querySelector('input[name="id_form"]').value);

                        // Append all the sampling inputs (s1 to s10)
                        for (let i = 1; i <= 10; i++) {
                            const samplingValue = rowElement.querySelector(`[name="sampling${i}_${row}"]`).value;
                            formData.append(`sampling${i}`, samplingValue);
                        }

                        // Tambahkan status = 1 jika tombol submit diklik
                        formData.append('status', 1);

                        // Send AJAX request to submit_buffer.php
                        $.ajax({
                            url: 'submit_buffer.php', // Adjusted to submit each row one by one
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                // Handle the response
                                if (Array.isArray(response)) {
                                    response.forEach(msg => {
                                        Swal.fire({
                                            title: 'Berhasil!',
                                            text: msg,
                                            icon: 'success'
                                        });
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: 'Terjadi kesalahan saat mengirim data.',
                                        icon: 'error'
                                    });
                                }
                                // Disable submit button after submission
                                button.disabled = true;
                                button.style.backgroundColor = 'navy';
                                button.style.color = 'white';

                                // Set all inputs in the row to readonly
                                rowElement.querySelectorAll('input').forEach(input => {
                                    input.readOnly = true;
                                });
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: 'Terjadi kesalahan saat mengirim data: ' + error,
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            }

            function refreshPage() {
                const id_form = document.querySelector('input[name="id_form"]').value; // Get id_form
                const rows = document.querySelectorAll('tr[data-row]'); // All rows with inspection data

                rows.forEach(row => {
                    const rowNumber = row.getAttribute('data-row');
                    const id_inspeksi = row.querySelector('input[name="id_inspeksi[]"]').value;

                    // AJAX request to fetch buffer data for each row
                    $.ajax({
                        url: 'proses/fetch_buffer.php',
                        type: 'POST',
                        data: {
                            id_form: id_form,
                            id_inspeksi: id_inspeksi
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.length > 0) {
                                const bufferData = response[0];

                                // Populate the sampling data (s1 to s10)
                                for (let i = 1; i <= 10; i++) {
                                    const samplingValue = bufferData['s' + i];
                                    const inputField = row.querySelector(`select[name="sampling${i}_${rowNumber}"], input[name="sampling${i}_${rowNumber}"]`);

                                    if (inputField && samplingValue !== undefined) {
                                        if (inputField.tagName.toLowerCase() === 'select') {
                                            // For select fields (dropdowns)
                                            inputField.value = samplingValue;
                                        } else if (inputField.tagName.toLowerCase() === 'input') {
                                            // For text inputs, show empty if value is 0
                                            inputField.value = samplingValue === '0' ? '' : samplingValue;
                                        }
                                    }
                                }

                                // Update status icon if present
                                const statusIcon = document.getElementById(`status${rowNumber}`);
                                if (statusIcon) {
                                    statusIcon.innerHTML = bufferData['status'] == 1 ? 'OK' : 'NG'; // Assuming 1 = OK, 0 = NG
                                }

                                // Check the status after populating data
                                checkStatus(rowNumber);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching buffer data:", error);
                        }
                    });
                });
            }

            // Trigger refresh when page is loaded or user presses Ctrl+R
            window.onload = refreshPage; // Call refreshPage when the page loads




            function confirmLogout() {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin logout?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, logout!',
                    cancelButtonText: 'Tidak, batalkan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'logout.php';
                    }
                });
            }



            // Function to update clock
            function updateClock() {
                const now = new Date();
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const seconds = now.getSeconds().toString().padStart(2, '0');
                const timeString = `${hours}:${minutes}:${seconds}`;
                document.getElementById('clock').textContent = timeString;
            }

            // Update clock every second
            setInterval(updateClock, 1000);
            updateClock(); // Initial call to set the clock immediately
        </script>

    </body>

    </html>