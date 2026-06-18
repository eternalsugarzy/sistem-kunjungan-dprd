<?php
include '../koneksi.php';

// 1. Ambil Statistik Kunjungan Global (Menjawab Revisi Panelis 1)
$q_kunjungan = mysqli_query($koneksi, "SELECT 
    COUNT(id_kunjungan) as total,
    SUM(CASE WHEN status_kegiatan = 'selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN status_kegiatan = 'batal' THEN 1 ELSE 0 END) as batal,
    SUM(CASE WHEN status_kegiatan IN ('pending', 'dijadwalkan') THEN 1 ELSE 0 END) as proses,
    SUM(CASE WHEN jenis_pendaftaran = 'online' THEN 1 ELSE 0 END) as online,
    SUM(CASE WHEN jenis_pendaftaran = 'walk-in' THEN 1 ELSE 0 END) as walkin
    FROM kunjungan");
$stat_k = mysqli_fetch_assoc($q_kunjungan);

// 2. Ambil Statistik Kepuasan / Rating (Menjawab Revisi Panelis 2)
$q_rating = mysqli_query($koneksi, "SELECT 
    COUNT(id_feedback) as total_responden,
    AVG(rating_pelayanan) as avg_pelayanan,
    AVG(rating_fasilitas) as avg_fasilitas,
    AVG(rating_ketepatan_waktu) as avg_waktu,
    AVG(rating_keseluruhan) as avg_total
    FROM feedback_kunjungan");
$stat_r = mysqli_fetch_assoc($q_rating);

// Format rating ke 1 angka di belakang koma (misal 4.5)
$avg_pelayanan = number_format((float)$stat_r['avg_pelayanan'], 1, '.', '');
$avg_fasilitas = number_format((float)$stat_r['avg_fasilitas'], 1, '.', '');
$avg_waktu = number_format((float)$stat_r['avg_waktu'], 1, '.', '');
$avg_total = number_format((float)$stat_r['avg_total'], 1, '.', '');

// 3. Ambil Daftar Komentar & Saran (Maksimal 15 terbaru)
$q_komentar = mysqli_query($koneksi, "SELECT f.*, k.nama_instansi_tamu 
    FROM feedback_kunjungan f 
    LEFT JOIN kunjungan k ON f.id_kunjungan = k.id_kunjungan 
    WHERE f.komentar_saran IS NOT NULL AND f.komentar_saran != ''
    ORDER BY f.created_at DESC LIMIT 15");

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
    <title>Laporan Statistik & Kepuasan</title>
    <style>
        /* CSS RESET & PRINT */
        body { font-family: "Times New Roman", Times, serif; font-size: 11pt; margin: 20px 40px; color: #000; }
        
        /* KOP SURAT */
        .kop-container { width: 100%; border-bottom: 4px solid #000; padding-bottom: 10px; margin-bottom: 20px; position: relative; }
        .kop-container::after { content: ""; position: absolute; left: 0; right: 0; bottom: -3px; border-bottom: 1px solid #000; }
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-table td { vertical-align: middle; text-align: center; }
        .logo-img { width: 85px; height: auto; }
        .text-pemkot { font-size: 14pt; margin: 0; font-weight: normal; }
        .text-dprd { font-size: 18pt; margin: 5px 0; font-weight: bold; letter-spacing: 1px; }
        .text-alamat { font-size: 10pt; margin: 0; }

        /* JUDUL LAPORAN */
        .judul { text-align: center; margin-bottom: 20px; }
        .judul h4 { font-size: 14pt; margin: 0; text-decoration: underline; text-transform: uppercase; }
        .judul p { margin: 5px 0 0 0; font-size: 10pt; }

        /* KOTAK STATISTIK */
        .box-container { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 15px; }
        .box-stat { flex: 1; border: 1px solid #000; padding: 15px; background: #f8f9fa; }
        .box-title { font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; font-size: 12pt; text-transform: uppercase;}
        
        .stat-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .stat-val { font-weight: bold; }

        /* INDIKATOR BINTANG */
        .rating-bar { width: 100%; background: #ddd; height: 12px; margin-top: 3px; position: relative; }
        .rating-fill { background: #212529; height: 100%; }
        .star-text { font-size: 14pt; color: #ffc107; text-shadow: 1px 1px 1px #000; }

        /* TABEL KOMENTAR */
        .table-komentar { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 30px; }
        .table-komentar th, .table-komentar td { border: 1px solid #000; padding: 8px; text-align: left; vertical-align: top; }
        .table-komentar th { background-color: #e0e0e0; text-align: center; font-weight: bold; }
        
        /* FOOTER TTD */
        .ttd-area { float: right; margin-top: 30px; text-align: center; width: 280px; }
        .ttd-area p { margin: 3px 0; }
        .nama-ttd { font-weight: bold; text-decoration: underline; margin-top: 70px !important; }

        @media print { .no-print { display: none !important; } body { margin: 0; } }
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
        <h4>LAPORAN STATISTIK PENGGUNAAN & KEPUASAN PELAYANAN</h4>
        <p>Tanggal Cetak: <?= date('d F Y'); ?></p>
    </div>

    <div class="box-container">
        
        <div class="box-stat">
            <div class="box-title">1. Statistik Penggunaan Sistem</div>
            <div class="stat-row">
                <span>Total Seluruh Pengajuan</span>
                <span class="stat-val"><?= $stat_k['total'] ?? 0; ?> Kunjungan</span>
            </div>
            <div class="stat-row" style="color: green;">
                <span>Total Kunjungan Terlaksana</span>
                <span class="stat-val"><?= $stat_k['selesai'] ?? 0; ?> Kunjungan</span>
            </div>
            <div class="stat-row" style="color: red;">
                <span>Total Kunjungan Batal</span>
                <span class="stat-val"><?= $stat_k['batal'] ?? 0; ?> Kunjungan</span>
            </div>
            <hr style="border-top: 1px dashed #ccc; margin: 10px 0;">
            <div class="stat-row">
                <span>Pendaftaran via Online (E-Ticket)</span>
                <span class="stat-val"><?= $stat_k['online'] ?? 0; ?> Kunjungan</span>
            </div>
            <div class="stat-row">
                <span>Pendaftaran via Walk-In (Manual)</span>
                <span class="stat-val"><?= $stat_k['walkin'] ?? 0; ?> Kunjungan</span>
            </div>
        </div>

        <div class="box-stat">
            <div class="box-title">2. Indeks Kepuasan Tamu</div>
            <p style="margin: 0 0 10px 0; font-size: 9pt;">Berdasarkan <?= $stat_r['total_responden'] ?? 0; ?> responden (Skala 1 - 5)</p>
            
            <div style="margin-bottom: 8px;">
                <div class="stat-row"><span>Kualitas Pelayanan</span> <span class="stat-val"><?= $avg_pelayanan; ?> / 5.0</span></div>
                <div class="rating-bar"><div class="rating-fill" style="width: <?= ($avg_pelayanan/5)*100; ?>%;"></div></div>
            </div>
            <div style="margin-bottom: 8px;">
                <div class="stat-row"><span>Kualitas Fasilitas</span> <span class="stat-val"><?= $avg_fasilitas; ?> / 5.0</span></div>
                <div class="rating-bar"><div class="rating-fill" style="width: <?= ($avg_fasilitas/5)*100; ?>%;"></div></div>
            </div>
            <div style="margin-bottom: 8px;">
                <div class="stat-row"><span>Ketepatan Waktu</span> <span class="stat-val"><?= $avg_waktu; ?> / 5.0</span></div>
                <div class="rating-bar"><div class="rating-fill" style="width: <?= ($avg_waktu/5)*100; ?>%;"></div></div>
            </div>
            <hr style="border-top: 1px dashed #ccc; margin: 10px 0;">
            <div class="stat-row" style="font-size: 13pt;">
                <span><strong>RATA-RATA KESELURUHAN</strong></span>
                <span class="stat-val star-text">&#9733; <?= $avg_total; ?></span>
            </div>
        </div>

    </div>

    <div style="font-weight: bold; margin-bottom: 10px; font-size: 12pt; text-transform: uppercase;">3. Rincian Komentar & Saran Tamu</div>
    <table class="table-komentar">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="25%">Nama / Instansi</th>
                <th width="10%">Rating</th>
                <th width="45%">Komentar / Saran</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($q_komentar) > 0) {
                while($f = mysqli_fetch_array($q_komentar)){
                    // Filter anonim
                    $nama = ($f['is_anonymous'] == 1) ? "<i>Hamba Allah (Anonim)</i>" : "<strong>" . htmlspecialchars($f['nama_pemberi']) . "</strong>";
                    $instansi = htmlspecialchars($f['nama_instansi_tamu'] ?? '-');
                    $tgl = date('d/m/Y', strtotime($f['created_at']));
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td style="text-align: center;"><?= $tgl; ?></td>
                <td><?= $nama; ?><br><small><?= $instansi; ?></small></td>
                <td style="text-align: center;" class="star-text">&#9733; <?= number_format($f['rating_keseluruhan'], 1); ?></td>
                <td><?= nl2br(htmlspecialchars($f['komentar_saran'])); ?></td>
            </tr>
            <?php 
                }
            } else {
                echo '<tr><td colspan="5" style="text-align: center; padding: 15px;">Belum ada data feedback/komentar dari tamu.</td></tr>';
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