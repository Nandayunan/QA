<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Quality Assurance</title>
    <link rel="stylesheet" href="assets/css/req.css">
</head>
<body>
    <header class="header">
        <img src="assets/img/kyb.png" alt="KYB Logo" class="logo">
        <h1>LABORATORIUM QUALITY ASSURANCE - PT KAYABA INDONESIA</h1>
        <div class="time">11:11</div>
        <button class="logout" onclick="confirmLogout()">Logout</button> <!-- Tombol Logout -->
    </header>
    <div class="container">
        <h2 class="request-ppb">REQUEST PPB</h2>
        <div class="form-container">
            <h3>SILAHKAN ISI DATA DIBAWAH</h3>
            <form id="data-form">
                <label for="no-ir">NO IR</label>
                <input type="text" id="no-ir" value="13416-79014" required>

                <label for="jenis-ir">JENIS IR</label>
                <select id="jenis-ir" required>
                    <option value="">Select</option>
                </select>

                <label for="no-ppb">NO. PPB</label>
                <input type="text" id="no-ppb" required>

                <label for="receive-qty">RECEIVE QTY</label>
                <input type="number" id="receive-qty"  required>

                <label for="sampling-qty">SAMPLING QTY</label>
                <input type="number" id="sampling-qty" required>

                <div class="buttons">
                    <button type="button" class="cancel">CANCEL</button>
                    <button type="submit" class="submit" onclick="confirmSubmit(1)">SUBMIT</button>
                </div>
            </form>
        </div>
    </div>
    <button class="monitoring-ppb">MONITORING PPB</button>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script> <!-- Include SweetAlert 2 library -->
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff0000', // Merah untuk tombol logout
                cancelButtonColor: '#green',
                confirmButtonText: 'Ya, logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Lakukan tindakan logout di sini
                    window.location.href = 'logout.php'; // Ganti dengan URL yang sesuai
                }
            });
        }

        function confirmSubmit(row) {
            Swal.fire({
                title: 'Konfirmasi Submit',
                text: `Apakah Anda yakin dengan pengukuran baris ${row}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, submit!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Lakukan tindakan submit di sini
                    Swal.fire(
                        'Submitted!',
                        `Baris ${row} berhasil disubmit.`,
                        'success'
                    );
                }
            });
        }
    </script>
</body>
</html>
