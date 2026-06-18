<?php
include '../koneksi.php';

// Ambil semua data ruangan
$query = mysqli_query($koneksi, "SELECT * FROM ruangan ORDER BY nama_ruangan ASC");

// Array Nama Bulan Indonesia untuk tanggal cetak
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Ruangan DPRD</title>
    <style>
        /* CSS RESET & PRINT */
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            margin: 20px 40px;
            color: #000;
        }
        
        /* KOP SURAT (Konsisten dengan cetak laporan lain) */
        .kop-container {
            width: 100%;
            border-bottom: 4px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            position: relative;
        }
        .kop-container::after {
            content: "";
            position: absolute;
            left: 0; right: 0; bottom: -3px;
            border-bottom: 1px solid #000;
        }
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-table td { vertical-align: middle; text-align: center; }
        .logo-img { width: 85px; height: auto; }
        .text-pemkot { font-size: 14pt; margin: 0; font-weight: normal; }
        .text-dprd { font-size: 18pt; margin: 5px 0; font-weight: bold; letter-spacing: 1px; }
        .text-alamat { font-size: 10pt; margin: 0; }

        /* JUDUL LAPORAN */
        .judul { text-align: center; margin-bottom: 30px; }
        .judul h4 { font-size: 14pt; margin: 0; text-decoration: underline; text-transform: uppercase; }

        /* TABEL DATA LAPORAN */
        .table-laporan { width: 100%; border-collapse: collapse; font-size: 11pt; margin-bottom: 30px; }
        .table-laporan th, .table-laporan td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }
        .table-laporan th { background-color: #e0e0e0; text-align: center; font-weight: bold; }
        
        /* FOOTER TTD */
        .ttd-area {
            float: right;
            margin-top: 30px;
            text-align: center;
            width: 280px;
        }
        .ttd-area p { margin: 3px 0; }
        .nama-ttd { font-weight: bold; text-decoration: underline; margin-top: 70px !important; }

        @media print { 
            .no-print { display: none !important; } 
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 15px; cursor: pointer; background: #212529; color: white; border: none; border-radius: 4px;">🖨️ Cetak Dokumen</button>
        <button onclick="window.close()" style="padding: 8px 15px; cursor: pointer; border: 1px solid #ccc; border-radius: 4px;">Tutup</button>
    </div>

    <div class="kop-container">
        <table class="kop-table">
            <tr>
                <td width="15%">
                    <img src="../assets/images/logo.png" class="logo-img" alt="Logo">
                </td>
                <td>
                    <h3 class="text-pemkot">PEMERINTAH KOTA BANJARMASIN</h3>
                    <h1 class="text-dprd">SEKRETARIAT DPRD</h1>
                    <p class="text-alamat">Jl. Lambung Mangkurat No. 1, Kota Banjarmasin, Kalimantan Selatan</p>
                    <p class="text-alamat">Website: dprd.banjarmasinkota.go.id | Email: setwan@banjarmasinkota.go.id</p>
                </td>
                <td width="15%"></td>
            </tr>
        </table>
    </div>

    <div class="judul">
        <h4>LAPORAN MASTER DATA RUANGAN</h4>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%">Nama Ruangan</th>
                <th width="25%">Lokasi / Lantai</th>
                <th width="25%">Kapasitas Maksimal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($query) > 0) {
                while($d = mysqli_fetch_array($query)){
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td><strong><?= htmlspecialchars($d['nama_ruangan']); ?></strong></td>
                <td style="text-align: center;"><?= htmlspecialchars($d['lantai'] ?? '-'); ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($d['kapasitas'] ?? '0'); ?> Orang</td>
            </tr>
            <?php 
                }
            } else {
                echo '<tr><td colspan="4" style="text-align: center; padding: 15px;">Data ruangan belum tersedia.</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <div class="ttd-area">
        <p>Banjarmasin, <?= date('d') . ' ' . $nama_bulan[date('m')] . ' ' . date('Y'); ?></p>
        <p>Sekretaris DPRD Kota Banjarmasin,</p>
        <p class="nama-ttd">Iwan Fitriady, SH., MH.</p>
        <p>Pembina Utama Muda</p>
        <p>NIP. 19700101 199503 1 002</p>
    </div>
    
    <div style="clear: both;"></div>

</body>
</html>