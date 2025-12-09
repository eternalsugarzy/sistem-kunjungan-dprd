<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_kunjungan_dprd"; 

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>