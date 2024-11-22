<?php
session_start(); // Mulai session sebelum mengakses $_SESSION

require_once __DIR__ . './assets/phpPasswordHashingLib/passwordLib.php';
require 'konfig.php';

// Function to mask the phone number and show only the last 4 digits
function maskPhoneNumber($phone)
{
    // Pastikan nomor telepon memiliki minimal 4 karakter
    if (strlen($phone) <= 4) {
        return $phone; // Jika kurang dari 4 karakter, tidak perlu masking
    }

    // Ambil 4 karakter terakhir
    $lastPart = substr($phone, -4);
    // Ganti karakter lainnya dengan tanda bintang (*)
    $maskedPart = str_repeat('*', strlen($phone) - 4);
    return $maskedPart . $lastPart;
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Dashboard QA</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons/bootstrap-icons.min.css">
</head>

<body>
    <div class="container">
        <div class="login-box">
            <img src="assets/img/kyb.png" alt="KYB Logo" style="width: 150px; margin-bottom: 20px;">
            <form id="loginForm" method="post">
                <div class="textbox">
                    <label for="npk">NPK:</label>
                    <input type="text" id="npk" name="npk" required>
                </div>
                <div class="textbox">
                    <label for="pwd">PASSWORD:</label>
                    <div class="input-group">
                        <input type="password" id="pwd" name="pwd" class="form-control" required>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="bi bi-eye-slash" id="togglePassword" style="cursor: pointer;"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="textbox" style="display: flex; align-items: center;">
                    <img src="assets/Captcha/Captcha.php" alt="Captcha Code" id="captchaImage">
                    <div style="margin-left: 10px;">
                        <input type="text" class="form-control" id="user_input" name="captcha" placeholder="Enter captcha code" maxlength="8" required>
                        <div class="captcha-refresh-text" id="refreshCaptchaText" style="margin-top: 5px;">
                            Captcha not read? Refresh<strong> <a href="javascript:void(0);" onclick="refreshCaptcha()">here</a></strong>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" id="login" name="login" value="login" class="btn btn-danger btn-block">Login</button>
                </div>
            </form>
            <div id="notification" class="notification"></div>
        </div>
    </div>

    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            var passwordField = document.getElementById('pwd');
            var type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });



        function refreshCaptcha() {
            const captchaImage = document.getElementById('captchaImage');
            const timestamp = new Date().getTime();
            captchaImage.src = 'assets/Captcha/Captcha.php?ts=' + timestamp;
        }
    </script>

    <?php
    $show_otp_modal;

    if (isset($_POST['login'])) {
        $npk = trim($_POST['npk']);
        $pwd = $_POST['pwd'];
        $captcha_input = $_POST['captcha'];

        $_SESSION['captcha_code'] = isset($_SESSION['captcha_code']) ? $_SESSION['captcha_code'] : '';
        $captcha = $_SESSION['captcha_code'];

        if ($captcha_input == $captcha) {
            $npk = mysqli_real_escape_string($conn2, $npk);
            $pwd = mysqli_real_escape_string($conn2, $pwd);

            $query = "SELECT * FROM ct_users WHERE npk = '$npk'";
            $result = mysqli_query($conn2, $query);
            $hitung = mysqli_num_rows($result);

            if ($hitung > 0) {
                $row = mysqli_fetch_assoc($result);
                if (password_verify($pwd, $row['pwd'])) {
                    $_SESSION['log'] = 'True';
                    $_SESSION['npk'] = $npk;
                    $_SESSION['golongan'] = $row['golongan'];
                    $_SESSION['acting'] = $row['acting'];

                    $golongan = $_SESSION['golongan'];
                    $acting = $_SESSION['acting'];

                    // Tentukan arah berdasarkan golongan dan acting
                    if ($golongan == 2 && $acting == 2) {
                        $_SESSION['redirect_url'] =  'index.php';
                    } elseif ($golongan == 4 && $acting == 2) {
                        $_SESSION['redirect_url'] =  'approve.php';
                    } elseif ($golongan == 4 && $acting == 1) {
                        $_SESSION['redirect_url'] =  'approve.php';
                    }

                    // Generate OTP code
                    $otp_code = sprintf('%06d', mt_rand(0, 999999));
                    $_SESSION["otp_code"] = $otp_code;

                    // Get phone number from 'isd' table
                    $sql_no_hp = "SELECT no_hp FROM hp WHERE npk = '$npk'";
                    $result_no_hp = mysqli_query($conn3, $sql_no_hp);

                    if ($no_hp_row = mysqli_fetch_assoc($result_no_hp)) {
                        $no_hp = $no_hp_row['no_hp'];
                    } else {
                        $no_hp = ''; // Handle if no number found
                    }

                    // Insert/update OTP in the database
                    $sql_check = "SELECT COUNT(*) as count FROM otp WHERE npk = '$npk'";
                    $result_check = mysqli_query($conn, $sql_check);
                    $check_row = mysqli_fetch_assoc($result_check);

                    if ($check_row['count'] > 0) {
                        $sql_update = "UPDATE otp SET otp = '$otp_code', no_hp = '$no_hp', send = '2', `use` = '2' WHERE npk = '$npk'";
                        mysqli_query($conn, $sql_update);
                    } else {
                        $sql_insert = "INSERT INTO otp (npk, otp, no_hp, send, `use`) VALUES ('$npk', '$otp_code', '$no_hp', '2', '2')";
                        mysqli_query($conn, $sql_insert);
                    }

                    $_SESSION['otp_sent_time'] = time(); // Record OTP sent time
                    $show_otp_modal = true; // Show OTP modal
                } else {
                    // Password salah
                    echo '<script>Swal.fire("Error", "Password salah", "error").then(() => { window.location.href = "login.php"; });</script>';
                    exit();
                }
            } else {
                // NPK tidak ditemukan
                echo '<script>Swal.fire("Error", "NPK tidak ditemukan", "error").then(() => { window.location.href = "login.php"; });</script>';
                exit();
            }
        } else {
            // Captcha salah
            echo '<script>Swal.fire("Error", "Captcha salah", "error").then(() => { window.location.href = "login.php"; });</script>';
            exit();
        }
    }

    if (isset($show_otp_modal) && $show_otp_modal): ?>
        <!-- OTP Modal -->
        <div class="modal fade" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="otpModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="otpModalLabel">Masukan OTP</h5>
                    </div>
                    <div class="modal-body">
                        <p>Silakan masukkan kode OTP</p>
                        <div class="alert alert-info">OTP telah dikirim ke:
                            <strong><?php echo htmlspecialchars(maskPhoneNumber($no_hp)); ?></strong>
                        </div>

                        <!-- Form OTP -->
                        <form id="otpForm" method="POST">
                            <div class="otp-container">
                                <input type="text" name="otp1" id="otp1" maxlength="1" class="otp-field" required>
                                <input type="text" name="otp2" id="otp2" maxlength="1" class="otp-field" required>
                                <input type="text" name="otp3" id="otp3" maxlength="1" class="otp-field" required>
                                <input type="text" name="otp4" id="otp4" maxlength="1" class="otp-field" required>
                                <input type="text" name="otp5" id="otp5" maxlength="1" class="otp-field" required>
                                <input type="text" name="otp6" id="otp6" maxlength="1" class="otp-field" required>
                            </div>

                            <div id="countdown" class="text-primary">300 detik tersisa</div>
                            <div id="resendOtp" class="text-danger d-none" style="cursor: pointer;">Kirim Ulang OTP</div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="verifyBtn" class="btn btn-primary btn-disabled" disabled>Verifikasi OTP</button>
                    </div>
                    </form>

                    <!-- Loader -->
                    <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 9999; text-align: center;">
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p>Loading, please wait...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <?php endif; ?>


    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#otpModal').modal('show');

            $('#otpModal').on('shown.bs.modal', function() {
                startTimer(); // Assuming there's a timer function for OTP
            });

            $("#otpForm").on('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                // Collect OTP input values
                let otp1 = $('#otp1').val();
                let otp2 = $('#otp2').val();
                let otp3 = $('#otp3').val();
                let otp4 = $('#otp4').val();
                let otp5 = $('#otp5').val();
                let otp6 = $('#otp6').val();

                // Show loader and disable form inputs
                $('#loader').show(); // Show loader
                $('#otpForm :input').prop('disabled', true); // Disable form inputs

                // Send OTP to server via AJAX
                $.ajax({
                    url: 'verify_otp.php', // Path to your PHP script
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        otp1: otp1,
                        otp2: otp2,
                        otp3: otp3,
                        otp4: otp4,
                        otp5: otp5,
                        otp6: otp6
                    },
                    success: function(response) {
                        // Hide loader and enable the form
                        $('#loader').hide(); // Hide loader
                        $('#otpForm :input').prop('disabled', false); // Enable form inputs

                        if (response.status === 'success') {
                            // OTP is correct, redirect to the URL from the response
                            Swal.fire({
                                icon: 'success',
                                title: 'OTP Verified!',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = response.redirect_url; // Redirect to the specified URL
                            });
                        } else {
                            // OTP is incorrect, show an error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Hide loader and enable the form
                        $('#loader').hide(); // Hide loader
                        $('#otpForm :input').prop('disabled', false); // Enable form inputs

                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'There was a problem connecting to the server. Please try again.',
                        });
                        console.log(xhr);
                    }
                });
            });
        });

        function showAlert(message, type, redirectURL = null) {
            Swal.fire({
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 1500
            }).then(function() {
                if (redirectURL) {
                    window.location.href = redirectURL;
                } else {
                    $('#otpModal').modal('show');
                }
            });
        }

        let otpInputFields = document.querySelectorAll('.otp-field');
        let verifyBtn = document.getElementById('verifyBtn');

        otpInputFields.forEach((input, index) => {
            input.addEventListener('keyup', (e) => {
                if (e.key >= '0' && e.key <= '9') {
                    if (index < otpInputFields.length - 1) {
                        otpInputFields[index + 1].focus();
                    }
                    enableVerifyBtn();
                } else if (e.key === 'Backspace' && index > 0) {
                    otpInputFields[index - 1].focus();
                }
            });
        });

        function startTimer() {
            let timer = 300; // Set the timer duration (in seconds)
            let countdownInterval = setInterval(function() {
                let minutes = Math.floor(timer / 60);
                let seconds = timer % 60;

                seconds = seconds < 10 ? '0' + seconds : seconds;
                document.getElementById('countdown').textContent = `${minutes}:${seconds} detik tersisa`;

                // Stop the timer at 0
                if (timer <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('countdown').textContent = "Waktu habis!";
                    document.getElementById('resendOtp').classList.remove("d-none"); // Show "Resend OTP" option
                }

                timer--;
            }, 1000);
        }

        function enableVerifyBtn() {
            let allFilled = true;
            otpInputFields.forEach(input => {
                if (input.value === '') {
                    allFilled = false;
                }
            });

            verifyBtn.disabled = !allFilled;
        }

        // Call this function to start the timer
        startTimer();
    </script>
</body>

</html>