<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Assurance</title>
    <link rel="stylesheet" href="assets/css/ir.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-rP4X4p2mRt5eY+ptR7XcRnb8/y4WlB2jyZhLRzDzAD1nECGzFGOUjF1h9n5y7DWaPb3BwQl5U9OxG5EMuNBZ3w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="assets/img/kyb.png" alt="KYB Logo" class="logo">
            <div class="title">LABORATORIUM QUALITY ASSURANCE - PT KAYABA INDONESIA</div>
            <div id="clock" class="clock"></div>
            <button class="logout" id="logout-button">LOGOUT</button>
        </div>

        <!-- Tabel Data Inspection Report -->
        <table id="ir-table">
            <thead>
                <tr>
                    <th>No IR</th>
                    <th>Jenis IR</th>
                    <th>Jenis Produk</th>
                    <th>Part</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>IR001</td>
                    <td>Mekanikal</td>
                    <td>Piston</td>
                    <td>12345</td>
                    <td>
                        <button class="action-btn edit-btn">edit</button>
                        <button class="action-btn delete-btn">hapus</button>
                    </td>
                </tr>
                <tr>
                    <td>IR002</td>
                    <td>Elektrikal</td>
                    <td>PCB</td>
                    <td>67890</td>
                    <td>
                        <button class="action-btn edit-btn">edit</button>
                        <button class="action-btn delete-btn">hapus</button>
                    </td>
                </tr>
                <tr>
                    <td>IR003</td>
                    <td>Dimensi</td>
                    <td>Kotak</td>
                    <td>54321</td>
                    <td>
                        <button class="action-btn edit-btn">edit</button>
                        <button class="action-btn delete-btn">hapus</button>
                    </td>
                </tr>
                <tr>
                    <td><input type="text" id="new-ir-no" placeholder="No IR"></td>
                    <td><input type="text" id="new-jenis-ir" placeholder="Jenis IR"></td>
                    <td><input type="text" id="new-jenis-produk" placeholder="Jenis Produk"></td>
                    <td><input type="text" id="new-part" placeholder="Part"></td>
                    <td>
                        <button class="action-btn add-row-btn"><i class="fas fa-plus"></i> Tambah</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="nav-buttons">
        <button class="nav-btn" onclick="goBack()"><i class="fas fa-arrow-left"></i> Back</button>
            <button class="nav-btn"><i class="fas fa-home"></i> Home</button>
        </div>
    </div>

    <script src="assets/js/ir.js"></script>
    <script>
        function goBack() {
            window.location.href = 'adminindex.php';
        }
    </script>
</body>
</html>
