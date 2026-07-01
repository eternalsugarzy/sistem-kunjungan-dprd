<?php
include '../koneksi.php';

// Tangkap ID Feedback
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data feedback riil join ke detail agenda kunjungan tamu
$q = mysqli_query($koneksi, "
    SELECT f.*, k.kode_booking, k.nama_instansi_tamu, k.tgl_kunjungan, k.materi_kunjungan
    FROM feedback_kunjungan f
    LEFT JOIN kunjungan k ON f.id_kunjungan = k.id_kunjungan
    WHERE f.id_feedback = '$id'
");
$d = mysqli_fetch_array($q);

if (!$d) {
    echo "<script>alert('Data tidak ditemukan!'); window.close();</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lembar Hasil Kuesioner - <?= htmlspecialchars($d['kode_booking']); ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            margin: 40px 60px;
            line-height: 1.5;
        }

        .kop-table {
            width: 100%;
            border-bottom: 4px double black;
            margin-bottom: 30px;
            padding-bottom: 10px;
        }

        .kop-table td {
            text-align: center;
            vertical-align: middle;
        }

        .logo-img {
            width: 90px;
            height: auto;
        }

        .text-pemkot {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-dprd {
            margin: 0;
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-alamat {
            margin: 0;
            font-size: 10pt;
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
            text-decoration: underline;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .subtitle-laporan {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 30px;
        }

        .table-detail {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 25px;
        }

        .table-detail th, .table-detail td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }

        .table-detail th {
            background-color: #f2f2f2;
        }

        .text-center { text-align: center !important; }
        .ttd {
            float: right;
            width: 250px;
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()">Cetak Ulang</button>
        <button onclick="window.close()">Tutup Halaman</button>
    </div>

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

    <div class="title-laporan">LEMBAR HASIL FEEDBACK KUESIONER</div>
    <div class="subtitle-laporan">Nomor Dokumen: FB-<?= htmlspecialchars($d['kode_booking']); ?>/DPRD-BJM</div>

    <p>Telah diterima data penilaian kuesioner dari pengguna layanan aplikasi <i>Smart Guest Book</i> DPRD Kota Banjarmasin dengan rincian identitas sebagai berikut:</p>

    <table class="table-detail">
        <tr>
            <th colspan="2" style="text-align: left;">I. IDENTITAS RESPONDEN / TAMU</th>
        </tr>
        <tr>
            <td width="35%">Kode Registrasi Booking</td>
            <td><strong><?= htmlspecialchars($d['kode_booking']); ?></strong></td>
        </tr>
        <tr>
            <td>Instansi / Lembaga Asal</td>
            <td><?= htmlspecialchars($d['nama_instansi_tamu']); ?></td>
        </tr>
        <tr>
            <td>Materi / Agenda Kunjungan</td>
            <td>"<?= htmlspecialchars($d['materi_kunjungan']); ?>"</td>
        </tr>
        <tr>
            <td>Nama Pemberi Feedback</td>
            <td><?= htmlspecialchars($d['nama_pemberi']); ?></td>
        </tr>
        <tr>
            <td>Jabatan Pemberi</td>
            <td><?= !empty($d['jabatan_pemberi']) ? htmlspecialchars($d['jabatan_pemberi']) : '-'; ?></td>
        </tr>
        
        <tr>
            <th colspan="2" style="text-align: left;">II. NILAI TINGKAT KEPUASAN (SKALA 1 - 5)</th>
        </tr>
        <tr>
            <td>Kualitas Pelayanan Petugas</td>
            <td><?= !empty($d['rating_pelayanan']) ? $d['rating_pelayanan'] . ' / 5' : '5 / 5'; ?></td>
        </tr>
        <tr>
            <td>Kualitas Fasilitas & Ruangan</td>
            <td><?= !empty($d['rating_fasilitas']) ? $d['rating_fasilitas'] . ' / 5' : '5 / 5'; ?></td>
        </tr>
        <tr>
            <td>Ketepatan Waktu Pelaksanaan</td>
            <td><?= !empty($d['rating_ketepatan_waktu']) ? $d['rating_ketepatan_waktu'] . ' / 5' : '5 / 5'; ?></td>
        </tr>
        <tr style="background: #f9f9f9; font-weight: bold;">
            <td>Skor Akumulasi Rata-rata</td>
            <td><?= !empty($d['rating_keseluruhan']) ? number_format($d['rating_keseluruhan'], 1) . ' / 5.0' : '5.0 / 5.0'; ?></td>
        </tr>

        <tr>
            <th colspan="2" style="text-align: left;">III. ASPEK EVALUASI (KRITIK / SARAN)</th>
        </tr>
        <tr>
            <td colspan="2" style="height: 80px; vertical-align: top; font-style: italic;">
                "<?= !empty($d['komentar_saran']) ? htmlspecialchars($d['komentar_saran']) : 'Tidak ada komentar/saran yang dilampirkan.'; ?>"
            </td>
        </tr>
    </table>

    <p>Data hasil kuesioner ini diekstrak secara otomatis melalui sistem dan bersifat sah sebagai bentuk arsip akuntabilitas indeks kepuasan masyarakat.</p>

    <div style="text-align: right; margin-top: 40px;">
        Banjarmasin, <?= date('d F Y', strtotime($d['created_at'])); ?>
    </div>

    <div class="ttd">
        <p>Sekretaris DPRD,</p>
        <br><br><br>
        <p><b>_______________________</b><br>NIP. ..............................</p>
    </div>

</body>
</html>