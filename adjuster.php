<?php
session_start(); // Start the session

// Assuming session variables are set
$npk = $_SESSION['npk'];
$golongan = $_SESSION['golongan'];
$acting = $_SESSION['acting'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Assurance</title>
    <link rel="stylesheet" href="assets/css/astyle.css">
    <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <header class="header">
            <img src="assets/img/kyb.png" alt="KYB Logo" class="logo">
            <div class="title">LABORATORIUM QUALITY ASSURANCE - PT KAYABA INDONESIA</div>
            <div id="clock" class="clock">11:11</div>
            <button class="logout" id="logout-button" onclick="confirmLogout()">LOGOUT</button>
        </header>
        <div class="main-content">
            <div class="title-box">
                IR REGULARLY 2W FRONT FORK <strong>(ADJUSTER)</strong>
                <input type="text" id="searchBar" class="search-bar" placeholder="Search No IR...">
            </div>
            <div class="table-container">
                <table class="no-ir-table">
                    <thead>
                        <tr>
                            <th>No IR</th>
                        </tr>
                    </thead>
                    <tbody id="irTableBody">
                        <?php
                        include 'konfig.php';
                        $id_subsubIR = $_GET['id'];
                        $sql = "SELECT id_noir, no_ir FROM no_ir WHERE id_subsubIR = $id_subsubIR";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='clickable-row' data-no-ir='" . $row['no_ir'] . "' data-id-noir='" . $row['id_noir'] . "'><td>" . $row['no_ir'] . "</td></tr>";
                            }
                        } else {
                            echo "<tr><td>No IR data found</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="button-container">
                <button onclick="goBack()">
                    <i class="fas fa-arrow-left"></i>Back
                </button>
                <button onclick="goHome()">
                    <i class="fas fa-home"></i>Home
                </button>
                <!-- Ubah data-toggle dan data-target menjadi data-bs-toggle dan data-bs-target -->
                <button type="button" data-bs-toggle="modal" data-bs-target="#addNoIrModal" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
        </div>
    </div>

    <!-- Modal for Lanjutkan or Buat Baru -->
    <div class="modal fade" id="noIrActionModal" tabindex="-1" role="dialog" aria-labelledby="noIrActionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noIrActionModalLabel">Pilih Aksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>No IR: <span id="selectedNoIr"></span></p>
                    <p>Apa yang ingin Anda lakukan?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="lanjutkanButton">Lanjutkan</button>
                    <button type="button" class="btn btn-primary" id="buatBaruButton">Buat Baru</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 9999; text-align: center;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p>Loading, please wait...</p>
        </div>
    </div>

    <!-- Modal for Lanjutkan Options -->
    <div class="modal fade" id="lanjutkanOptionsModal" tabindex="-1" role="dialog" aria-labelledby="lanjutkanOptionsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 80%;"> <!-- Inline style for width -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lanjutkanOptionsModalLabel">Lanjutkan Aksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Pilih opsi lanjutan untuk No IR: <span id="selectedNoIr"></span></p>
                    <div id="buttonContainer"></div> <!-- Button container to show dynamic buttons -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Add No IR Modal -->
    <div class="modal fade" id="addNoIrModal" tabindex="-1" role="dialog" aria-labelledby="addNoIrModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNoIrModalLabel">Add Part Number</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addNoIrForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="noIrInput">Part Number</label>
                            <input type="text" class="form-control" id="noIrInput" placeholder="Enter No IR" name="no_ir">
                        </div>
                        <div class="form-group">
                            <label for="noRegInput">No Registrasi</label>
                            <input type="text" class="form-control" id="noRegInput" placeholder="Enter No Registrasi" name="no_reg">
                        </div>
                        <div class="form-group">
                            <label for="revisiInput">Revisi</label>
                            <input type="text" class="form-control" id="revisiInput" placeholder="Enter Revisi" name="revisi">
                        </div>
                        <div class="form-group">
                            <label for="imageInput">Upload Image</label>
                            <input type="file" class="form-control" id="imageInput" name="image" accept=".jpg, .jpeg, .png">
                        </div>
                        <input type="hidden" id="idSubsubIrInput" name="id_subsubIR" value="<?php echo $id_subsubIR; ?>">
                        <button type="button" class="btn btn-primary" onclick="addNoIr()">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- jQuery harus di-load pertama -->
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="assets/js/nscript.js"></script>
    <script>
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
                    window.location.href = "logout.php";
                }
            });
        }

        $(document).ready(function() {
            // This function should be called when opening the modal
            $('#lanjutkanOptionsModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var noIr = button.data('noir'); // Extract info from data-* attributes
                var idForm = button.data('idform'); // Assuming you pass id_form through data-idform

                // Update the modal's content
                var modal = $(this);
                modal.find('#selectedNoIr').text(noIr);
                modal.find('#idFormDisplay').text(idForm);
                modal.find('#idFormOption1').text(idForm);
                modal.find('#idFormOption2').text(idForm);
                modal.find('#idFormOption3').text(idForm);
            });
        });

        function updateClock() {
            var now = new Date();
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            document.getElementById('clock').innerText = hours + ':' + minutes;
        }

        function goBack() {
            window.history.back();
        }

        function goHome() {
            window.location.href = 'index.php';
        }

        function addNoIr() {
            var noIr = document.getElementById('noIrInput').value;
            var noReg = document.getElementById('noRegInput').value;
            var revisi = document.getElementById('revisiInput').value;
            var idSubsubIr = document.getElementById('idSubsubIrInput').value;
            var imageInput = document.getElementById('imageInput').files[0];

            // Validate the required fields
            if (noIr === "" || noReg === "" || revisi === "") {
                Swal.fire("Error", "All fields must be filled out!", "error");
                return;
            }

            // Create a FormData object
            var formData = new FormData();
            formData.append('no_ir', noIr);
            formData.append('no_reg', noReg);
            formData.append('revisi', revisi);
            formData.append('id_subsubIR', idSubsubIr);
            formData.append('image', imageInput);

            // AJAX request to add the No IR
            $.ajax({
                url: 'addir.php',
                method: 'POST',
                data: formData,
                processData: false, // Prevent jQuery from automatically transforming the data into a query string
                contentType: false, // Prevent jQuery from setting contentType header
                success: function(response) {
                    if (response.success) {
                        Swal.fire("Success", "No IR added successfully!", "success").then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire("Error", response.message, "error");
                    }
                },
                error: function() {
                    Swal.fire("Error", "Failed to add No IR", "error");
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var rows = document.querySelectorAll('.clickable-row');
            var selectedNoIr = ''; // Store selected No IR
            var selectedIdIr = ''; // Store selected ID No IR

            rows.forEach(function(row) {
                row.addEventListener('click', function() {
                    selectedIdIr = this.getAttribute('data-id-noir');
                    selectedNoIr = this.getAttribute('data-no-ir'); // Corrected from 'data-no-noir' to 'data-no-ir'
                    var npk = '<?php echo $npk; ?>';
                    var golongan = '<?php echo $golongan; ?>';
                    var acting = '<?php echo $acting; ?>';

                    // Open the modal based on conditions
                    if (golongan == 2 && acting == 2) {
                        document.getElementById('selectedNoIr').innerText = selectedNoIr;
                        $('#noIrActionModal').modal('show');
                    } else if (golongan == 4 && acting == 2) {
                        window.location.href = 'admininput.php?no_ir=' + selectedNoIr;
                    }
                });
            });

            // Event listener for "Buat Baru" button
            document.getElementById('buatBaruButton').addEventListener('click', function() {
                var golongan = '<?php echo $golongan; ?>';
                var acting = '<?php echo $acting; ?>';

                // Redirect based on conditions
                if (golongan == 2 && acting == 2) {
                    window.location.href = 'dataFW.php?no_ir=' + selectedNoIr; // Pass no_ir when redirecting
                }
            });

            // Event listener for "Lanjutkan" button to fetch id_form
            document.getElementById('lanjutkanButton').addEventListener('click', function() {
                console.log(selectedNoIr);
                $('#noIrActionModal').modal('hide'); // Close the first modal

                // Show loader before the AJAX request
                document.getElementById('loader').style.display = 'block';

                // AJAX request to fetch id_form
                $.ajax({
                    url: 'proses/fetch_pengukuran.php',
                    type: 'GET',
                    data: {
                        id_noir: selectedIdIr // Send the correct id_noir
                    },
                    success: function(response) {
                        // Hide the loader after getting the response
                        document.getElementById('loader').style.display = 'none';

                        // Assuming the response is a comma-separated list of id_form
                        var idForms = response.split(", "); // Convert the response string to an array

                        // Clear the button container before adding new buttons
                        var buttonContainer = document.getElementById('buttonContainer');
                        buttonContainer.innerHTML = ""; // Clear previous buttons

                        // Loop through the idForms array and create buttons dynamically
                        idForms.forEach(function(idForm) {
                            var button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'btn btn-primary'; // Add button class
                            button.innerText = idForm; // Set button text
                            button.setAttribute('data-idform', idForm); // Store id_form in a data attribute
                            button.style.margin = '10px'; // Add inline style for margin

                            // Add click event listener for each button
                            button.addEventListener('click', function() {
                                // Show the loader
                                document.getElementById('loader').style.display = 'block';

                                // Redirect to datainput.php with the selected no_ir and id_form
                                window.location.href = 'datainput.php?no_ir=' + encodeURIComponent(selectedNoIr) + '&id_form=' + encodeURIComponent(idForm);
                            });

                            // Append the button to the container
                            buttonContainer.appendChild(button);
                        });

                        // Show the second modal after all buttons are created
                        $('#lanjutkanOptionsModal').modal('show');
                    },
                    error: function() {
                        // Hide the loader if there's an error
                        document.getElementById('loader').style.display = 'none';
                        alert('Error fetching data.');
                    }
                });
            });
        });
    </script>
</body>

</html>