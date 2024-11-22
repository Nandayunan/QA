<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "kayaba_project";
$database2 = "lembur";
$database3 = "isd";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $database);
$conn2 = new mysqli($servername, $username, $password, $database2);
$conn3 = new mysqli($servername, $username, $password, $database3);

// Memeriksa koneksi
