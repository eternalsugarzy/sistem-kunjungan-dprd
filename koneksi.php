<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_smart_guest"; 

// Secret Key untuk Digital Signature QR Code
$qr_secret_key = 'DPRD_SECRET_KEY_2026';

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>