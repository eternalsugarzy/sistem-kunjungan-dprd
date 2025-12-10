<?php
include '../koneksi.php';
$id = $_GET['id'];

// Ambil Detail Kunjungan + Nama Ruangan
$q = mysqli_query($koneksi, "SELECT k.*, r.nama_ruangan 
                             FROM kunjungan k 
                             LEFT JOIN ruangan r ON k.id_ruangan=r.id_ruangan 
                             WHERE id_kunjungan='$id'");
$d = mysqli_fetch_array($q);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Surat Balasan - <?= $d['nama_instansi_tamu']; ?></title>
    <style>
        /* SETTING HALAMAN */
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; margin: 40px 60px; line-height: 1.5; }
        
        /* STYLE KOP SURAT KHUSUS */
        .kop-table { width: 100%; border-bottom: 4px double black; margin-bottom: 30px; padding-bottom: 10px; }
        .kop-table td { text-align: center; vertical-align: middle; }
        .logo-img { width: 90px; height: auto; }
        
        .text-pemkot { margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; }
        .text-dprd { margin: 0; font-size: 18pt; font-weight: bold; text-transform: uppercase; }
        .text-alamat { margin: 0; font-size: 10pt; font-weight: normal; }

        .no-print { display: none; }
        @media print { .no-print { display: none; } }
        
        /* STYLE ISI SURAT */
        .info-surat { width: 100%; margin-bottom: 30px; }
        .info-surat td { vertical-align: top; }
        .isi { text-align: justify; margin-bottom: 20px; }
        .detail-acara { margin-left: 40px; margin-bottom: 20px; }
        .ttd { float: right; width: 250px; text-align: center; margin-top: 50px; }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print"><button onclick="window.close()">Tutup</button></div>

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

    <p style="text-align: right;">Banjarmasin, <?= date('d F Y'); ?></p>
    
    <table class="info-surat">
        <tr>
            <td width="15%">Nomor</td>
            <td width="55%">: 005 / <?= $d['id_kunjungan']; ?> / DPRD-BJM</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>: -</td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>: <b>Persetujuan Kunjungan Kerja</b></td>
        </tr>
    </table>

    <div style="margin-bottom: 30px;">
        Kepada Yth,<br>
        <b>Pimpinan <?= $d['nama_instansi_tamu']; ?></b><br>
        di - <br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Tempat
    </div>

    <div class="isi">
                <p>Dengan hormat,</p>
                <p>Menindaklanjuti surat Saudara perihal permohonan Kunjungan Kerja ke DPRD Kota Banjarmasin dengan materi <b>"<?= $d['materi_kunjungan']; ?>"</b>, maka bersama ini kami sampaikan bahwa kami <b>BERSEDIA MENERIMA</b> kunjungan tersebut yang akan dilaksanakan pada:</p>
        
        <table class="detail-acara">
            <tr>
                <td width="120px">Hari / Tanggal</td>
                <td>: <b><?= date('l, d F Y', strtotime($d['tgl_kunjungan'])); ?></b></td>
            </tr>
             <tr>
                <td>Pukul</td>
                <td>: <?= $d['waktu_kunjungan']; ?> WITA - Selesai</td>
            </tr>
            <tr>
                <td>Tempat</td>
                <td>: <?= $d['nama_ruangan'] ?? 'Gedung DPRD Kota Banjarmasin'; ?></td>
            </tr>
            <tr>
                <td>Jumlah Peserta</td>
                <td>: <?= $d['jumlah_peserta_rencana']; ?> Orang</td>
            </tr>
        </table>

        <p>Demikian surat balasan ini kami sampaikan agar dapat dipergunakan sebagaimana mestinya. Atas perhatian dan kerjasamanya diucapkan terima kasih.</p>
    </div>

    <div class="ttd">
        <p>Sekretaris DPRD,</p>
        <br><br><br>
        <p><b>_______________________</b><br>NIP. ..............................</p>
    </div>
</body>
</html> 