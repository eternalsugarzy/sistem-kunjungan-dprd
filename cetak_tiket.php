<?php
include 'koneksi.php';
$kode = $_GET['kode'];
$query = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE kode_booking='$kode'");
$d = mysqli_fetch_array($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tiket Kunjungan - <?= $kode; ?></title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .tiket { border: 2px dashed #333; width: 600px; margin: 50px auto; padding: 30px; text-align: left; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .info { line-height: 1.8; font-size: 14pt; }
        .kode { font-size: 24pt; font-weight: bold; text-align: center; margin: 20px 0; background: #eee; padding: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print"><button onclick="window.close()">Tutup</button></div>
    
    <div class="tiket">
        <div class="header">
            <h2>BUKTI REGISTRASI KUNJUNGAN</h2>
            <p>DPRD KOTA BANJARMASIN</p>
        </div>
        
        <div class="kode"><?= $d['kode_booking']; ?></div>
        
        <div class="info">
            <strong>Instansi:</strong> <?= $d['nama_instansi_tamu']; ?><br>
            <strong>Tanggal:</strong> <?= date('d F Y', strtotime($d['tgl_kunjungan'])); ?><br>
            <strong>Pukul:</strong> <?= $d['waktu_kunjungan']; ?> WITA<br>
            <strong>Jumlah:</strong> <?= $d['jumlah_peserta_rencana']; ?> Orang<br>
            <strong>Status:</strong> <?= strtoupper($d['status_kegiatan']); ?>
        </div>
        
        <div style="margin-top: 30px; font-size: 10pt; color: #666; text-align: center;">
            *Harap tunjukkan tiket ini kepada petugas penerima tamu.
        </div>
    </div>
</body>
</html>