<?php
include '../koneksi.php';

if (!isset($_GET['bulan']) || !isset($_GET['tahun'])) {
    die("Error: Data Bulan/Tahun tidak ditemukan.");
}

$bulan = $_GET['bulan'];
$tahun = $_GET['tahun'];

// Array Nama Bulan Indonesia
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Query Data Kunjungan berdasarkan Bulan & Tahun
// Kita ambil yang statusnya 'selesai' atau 'dijadwalkan' (yang valid)
$query = "SELECT * FROM kunjungan 
          WHERE MONTH(tgl_kunjungan) = '$bulan' 
          AND YEAR(tgl_kunjungan) = '$tahun'
          ORDER BY tgl_kunjungan ASC";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kunjungan - <?= $nama_bulan[$bulan]; ?> <?= $tahun; ?></title>
    <style>
        /* CSS RESET & PRINT */
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            margin: 20px 40px;
        }
        
        /* KOP SURAT (Sama dengan Absensi) */
        .kop-container {
            width: 100%;
            border-bottom: 4px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-table td { vertical-align: middle; text-align: center; }
        .logo-img { width: 90px; height: auto; }
        .text-pemkot { font-size: 14pt; margin: 0; font-weight: normal; }
        .text-dprd { font-size: 18pt; margin: 5px 0; font-weight: bold; text-transform: uppercase; }
        .text-alamat { font-size: 10pt; margin: 0; }

        /* JUDUL LAPORAN */
        .judul {
            text-align: center;
            margin-bottom: 20px;
        }
        .judul h4 { margin: 0; text-decoration: underline; text-transform: uppercase; }
        .judul p { margin: 5px 0; }

        /* TABEL DATA LAPORAN */
        .table-laporan {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        .table-laporan th, .table-laporan td {
            border: 1px solid black;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        .table-laporan th {
            background-color: #e0e0e0;
            text-align: center;
        }
        
        /* FOOTER TTD */
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

    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 15px; cursor: pointer;">Cetak Laporan</button>
        <button onclick="window.close()" style="padding: 8px 15px; cursor: pointer;">Tutup</button>
    </div>

    <div class="kop-container">
        <table class="kop-table">
            <tr>
                <td width="15%">
                    <img src="../assets/images/logo.png" class="logo-img" alt="Logo">
                </td>
                <td>
                    <h3 class="text-pemkot">PEMERINTAH KOTA BANJARMASIN</h3>
                    <h1 class="text-dprd">SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH</h1>
                    <p class="text-alamat">Jl. Lambung Mangkurat No. 2 Telp. (0511) 3352467 – 3366379 Banjarmasin 70111</p>
                    <p class="text-alamat">Website : dprd.banjarmasinkota.go.id Email : dprdbjm@gmail.com</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="judul">
        <h4>LAPORAN REKAPITULASI KUNJUNGAN KERJA</h4>
        <p>Bulan: <?= $nama_bulan[$bulan]; ?> <?= $tahun; ?></p>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th>Instansi Tamu</th>
                <th>Perihal / Materi</th>
                <th width="10%">Jml Peserta</th>
                <th width="12%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($result) > 0) {
                while($d = mysqli_fetch_array($result)){
                    // Format Tanggal Indonesia
                    $tgl = date('d-m-Y', strtotime($d['tgl_kunjungan']));
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td style="text-align: center;"><?= $tgl; ?></td>
                <td>
                    <strong><?= $d['nama_instansi_tamu']; ?></strong><br>
                    <small>Asal: <?= $d['alamat_instansi']; ?></small>
                </td>
                <td><?= $d['materi_kunjungan']; ?></td>
                <td style="text-align: center;"><?= $d['jumlah_peserta_rencana']; ?> Orang</td>
                <td style="text-align: center;">
                    <?php 
                        if($d['status_kegiatan']=='selesai') echo "Terlaksana";
                        elseif($d['status_kegiatan']=='dijadwalkan') echo "Terjadwal";
                        elseif($d['status_kegiatan']=='batal') echo "Dibatalkan";
                        else echo "Pending";
                    ?>
                </td>
            </tr>
            <?php 
                }
            } else {
                echo '<tr><td colspan="6" style="text-align: center; padding: 20px;">Tidak ada data kunjungan pada bulan ini.</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <div class="ttd-area">
        <p>Banjarmasin, <?= date('d F Y'); ?></p>
        <p>Mengetahui,<br>Kepala Bagian Umum</p>
        <br><br><br>
        <p><strong>_________________________</strong></p>
        <p>NIP. ........................................</p>
    </div>

</body>
</html>