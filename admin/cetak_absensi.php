<?php
include '../koneksi.php';

// Cek ID Kunjungan
if (!isset($_GET['id'])) {
    die("Error: ID Kunjungan tidak ditemukan.");
}

$id = $_GET['id'];

// 1. AMBIL DATA DARI DATABASE
$query_header = "SELECT kunjungan.*, ruangan.nama_ruangan, penanggung_jawab.nama_pj 
                 FROM kunjungan 
                 LEFT JOIN ruangan ON kunjungan.id_ruangan = ruangan.id_ruangan
                 LEFT JOIN penanggung_jawab ON kunjungan.id_pj = penanggung_jawab.id_pj
                 WHERE id_kunjungan = '$id'";
$run_header = mysqli_query($koneksi, $query_header);
$d = mysqli_fetch_assoc($run_header);

// 2. AMBIL DATA PESERTA HADIR
$query_tamu = mysqli_query($koneksi, "SELECT * FROM buku_tamu WHERE id_kunjungan = '$id'");

// 3. KONVERSI TANGGAL INDONESIA (Senin, 01 Januari 2025)
$hari_inggris = date('l', strtotime($d['tgl_kunjungan']));
$bulan_inggris = date('F', strtotime($d['tgl_kunjungan']));

$daftar_hari = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];
$daftar_bulan = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April',
    'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus',
    'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
];

$hari_indo = $daftar_hari[$hari_inggris];
$bulan_indo = $daftar_bulan[$bulan_inggris];
$tanggal_indo = $hari_indo . ", " . date('d', strtotime($d['tgl_kunjungan'])) . " " . $bulan_indo . " " . date('Y', strtotime($d['tgl_kunjungan']));

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir - <?= $d['nama_instansi_tamu']; ?></title>
    <style>
        /* CSS UNTUK CETAK */
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            margin: 10px 40px;
        }
        
        /* KOP SURAT */
        .kop-container {
            width: 100%;
            border-bottom: 4px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-table { width: 100%; border-collapse: collapse; }
        .logo-img { width: 90px; height: auto; }
        .kop-text { text-align: center; line-height: 1.2; }
        .text-pemkot { font-size: 14pt; font-weight: normal; margin: 0; }
        .text-dprd { font-size: 18pt; font-weight: bold; margin: 5px 0; text-transform: uppercase; }
        .text-alamat { font-size: 10pt; font-weight: normal; margin: 0; }
        
        /* JUDUL DOKUMEN */
        .judul-dokumen {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 14pt;
            text-transform: uppercase;
        }
        
        /* TABEL INFO (YANG DIUBAH) */
        .table-info {
            width: 100%;
            margin-bottom: 20px;
            font-size: 12pt;
            font-weight: bold; /* Sesuai gambar, semua teks tebal */
        }
        .table-info td {
            padding: 5px 0;
            vertical-align: top;
        }

        /* TABEL DATA PESERTA */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table-data th, .table-data td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            font-size: 11pt;
        }
        .table-data th {
            background-color: #e0e0e0;
            text-align: center;
        }
        
        .ttd-area {
            float: right;
            margin-top: 40px;
            text-align: center;
            width: 250px;
        }
        
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 15px; cursor: pointer;">Cetak</button>
        <button onclick="window.close()" style="padding: 8px 15px; cursor: pointer;">Tutup</button>
    </div>

    <div class="kop-container">
        <table class="kop-table">
            <tr>
                <td width="15%" align="center">
                    <img src="../assets/images/logo.png" class="logo-img" alt="Logo">
                </td>
                <td align="center" class="kop-text">
                    <h3 class="text-pemkot">PEMERINTAH KOTA BANJARMASIN</h3>
                    <h1 class="text-dprd">SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH</h1>
                    <p class="text-alamat">
                        Jl. Lambung Mangkurat No. 2 Telp. (0511) 3352467 – 3366379 Fax. (0511) 3366379 Banjarmasin 70111
                    </p>
                    <p class="text-alamat">
                        Website : dprd.banjarmasinkota.go.id Email : dprdbjm@gmail.com
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="judul-dokumen">DAFTAR HADIR TAMU</div>

    <table class="table-info">
        <tr>
            <td width="22%">Kunjungan Dari</td>
            <td width="2%">:</td>
            <td><?= $d['nama_instansi_tamu']; ?></td>
        </tr>
        <tr>
            <td>Hari/Tanggal</td>
            <td>:</td>
            <td><?= $tanggal_indo; ?></td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td><?= date('H.i', strtotime($d['waktu_kunjungan'])); ?> WITA</td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>DPRD KOTA BANJARMASIN</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Peserta</th>
                <th>Jabatan</th>
               
                <th width="20%">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(mysqli_num_rows($query_tamu) > 0) {
                while($t = mysqli_fetch_array($query_tamu)) { 
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td><?= $t['nama_peserta']; ?></td>
                <td><?= $t['jabatan_peserta']; ?></td>
             
                <td style="text-align: center;">
                    <?php if(!empty($t['tanda_tangan'])): ?>
                        <img src="../uploads/ttd/<?= $t['tanda_tangan']; ?>" style="height: 35px;">
                    <?php else: ?>
                        <br>
                    <?php endif; ?>
                </td>
            </tr>
            <?php 
                } 
            } else {
            ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">- Belum ada peserta -</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="ttd-area">
        <p>Banjarmasin, <?= $tanggal_indo; ?></p>
        <p>Mengetahui,<br>Penerima Tamu</p>
        <br><br><br>
        <p><strong><u><?= $d['nama_pj']; ?></u></strong></p>
    </div>

</body>
</html>