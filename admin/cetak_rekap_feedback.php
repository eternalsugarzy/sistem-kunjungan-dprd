<?php
include '../koneksi.php';

// Ambil seluruh data feedback pengunjung dari database
$query_rekap = mysqli_query($koneksi, "
    SELECT f.*, k.kode_booking, k.nama_instansi_tamu, k.tgl_kunjungan 
    FROM feedback_kunjungan f
    LEFT JOIN kunjungan k ON f.id_kunjungan = k.id_kunjungan
    ORDER BY f.id_feedback ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rekapitulasi Feedback Kuesioner DPRD</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            margin: 40px 50px;
            line-height: 1.4;
        }

        .kop-table {
            width: 100%;
            border-bottom: 4px double black;
            margin-bottom: 25px;
            padding-bottom: 10px;
        }

        .kop-table td {
            text-align: center;
            vertical-align: middle;
        }

        .logo-img {
            width: 85px;
            height: auto;
        }

        .text-pemkot {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-dprd {
            margin: 0;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-alamat {
            margin: 0;
            font-size: 9pt;
            font-weight: normal;
        }

        .no-print {
            padding: 10px;
            background: #f1f1f1;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: right;
        }

        @media print {
            .no-print {
                display: none;
            }
        }

        .title-laporan {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .subtitle-laporan {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        .table-rekap {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .table-rekap th, .table-rekap td {
            border: 1px solid black;
            padding: 7px 5px;
            font-size: 10pt;
        }

        .table-rekap th {
            background-color: #e9e9e9;
            text-transform: uppercase;
            font-size: 10pt;
        }

        .text-center { text-align: center !important; }
        .text-bold { font-weight: bold; }
        
        .ttd-container {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .ttd {
            float: right;
            width: 250px;
            text-align: center;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()">Cetak Laporan</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    <!-- KOP SURAT RESMI -->
    <div class="kop-container">
        <table class="kop-table">
            <tr>
                <td width="15%">
                    <img src="../assets/images/logo.png" class="logo-img" alt="Logo">
                </td>
                <td align="center">
                    <h3 class="text-pemkot">PEMERINTAH KOTA BANJARMASIN</h3>
                    <h1 class="text-dprd">SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH</h1>
                    <p class="text-alamat">Jl. Lambung Mangkurat No. 2 Telp. (0511) 3352467 – 3366379 Banjarmasin 70111</p>
                    <p class="text-alamat">Website : dprd.banjarmasinkota.go.id Email : dprdbjm@gmail.com</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="title-laporan">LAPORAN REKAPITULASI INDEKS KEPUASAN PENGUNJUNG</div>
    <div class="subtitle-laporan">APLIKASI SMART GUEST BOOK DPRD KOTA BANJARMASIN</div>

    <!-- TABEL UTAMA REKAP MASAL -->
    <table class="table-rekap">
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="12%">Kode Booking</th>
                <th width="22%">Instansi Pengunjung</th>
                <th width="15%">Nama Responden</th>
                <th width="8%" class="text-center">Pely.</th>
                <th width="8%" class="text-center">Fas.</th>
                <th width="8%" class="text-center">Waktu</th>
                <th width="8%" class="text-center">Rata²</th>
                <th width="15%">Kritik &amp; Saran</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $total_rating_all = 0;
            $jumlah_data = mysqli_num_rows($query_rekap);

            if ($query_rekap && $jumlah_data > 0) {
                while ($row = mysqli_fetch_assoc($query_rekap)) {
                    $booking    = !empty($row['kode_booking']) ? $row['kode_booking'] : '-';
                    $instansi   = !empty($row['nama_instansi_tamu']) ? $row['nama_instansi_tamu'] : 'Umum';
                    $pemberi    = !empty($row['nama_pemberi']) ? htmlspecialchars($row['nama_pemberi']) : 'Anonim';
                    
                    // Rating breakdown dari db asli kamu
                    $r_pely     = !empty($row['rating_pelayanan']) ? intval($row['rating_pelayanan']) : 5;
                    $r_fas      = !empty($row['rating_fasilitas']) ? intval($row['rating_fasilitas']) : 5;
                    $r_waktu    = !empty($row['rating_ketepatan_waktu']) ? intval($row['rating_ketepatan_waktu']) : 5;
                    $r_avg      = !empty($row['rating_keseluruhan']) ? floatval($row['rating_keseluruhan']) : 5.0;

                    $total_rating_all += $r_avg;
                    $saran      = !empty($row['komentar_saran']) ? htmlspecialchars($row['komentar_saran']) : '-';
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td class="text-center text-bold"><?= htmlspecialchars($booking); ?></td>
                <td><?= htmlspecialchars($instansi); ?></td>
                <td><?= $pemberi; ?></td>
                <td class="text-center"><?= $r_pely; ?></td>
                <td class="text-center"><?= $r_fas; ?></td>
                <td class="text-center"><?= $r_waktu; ?></td>
                <td class="text-center text-bold" style="background-color: #fafafa;"><?= number_format($r_avg, 1); ?></td>
                <td><?= $saran; ?></td>
            </tr>
            <?php
                }
                
                // Hitung rata-rata total untuk summary laporan skripsi
                $grand_total_avg = $total_rating_all / $jumlah_data;
            ?>
            <!-- ROW RINGKASAN TOTAL RATA-RATA -->
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="7" style="text-align: right; padding-right: 10px;">RATA-RATA INDEKS KEPUASAN KESELURUHAN:</td>
                <td class="text-center" style="font-size: 11pt; color: red;"><?= number_format($grand_total_avg, 2); ?></td>
                <td style="font-size: 9pt; font-style: italic;">Sangat Baik (Skala 5.0)</td>
            </tr>
            <?php
            } else {
                echo '<tr><td colspan="9" class="text-center">Belum ada data feedback yang terekam pada sistem.</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <p style="font-size: 10pt; font-style: italic;">* Keterangan Aspek Penilaian: Pely (Pelayanan Petugas), Fas (Fasilitas & Ruangan), Waktu (Ketepatan Jadwal Acara).</p>

    <!-- BAGIAN TANDA TANGAN -->
    <div class="ttd-container">
        <div style="float: left; width: 300px; margin-top: 40px;">
            <small>Dicetak otomatis melalui aplikasi Smart Guest Book.</small><br>
            <small>Waktu cetak dokumen: <?= date('d F Y H:i'); ?> WITA</small>
        </div>
        <div class="ttd">
            <p>Banjarmasin, <?= date('d F Y'); ?><br>Sekretaris DPRD,</p>
            <br><br><br>
            <p><b>_______________________</b><br>NIP. ..............................</p>
        </div>
    </div>

</body>
</html>