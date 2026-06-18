<?php
// ==========================================
// PENGATURAN DEBUG ERROR & INTEGRASI DB
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi mandiri terpisah agar bebas dari gangguan CSS/JS Loader HTML template admin
$koneksi = mysqli_connect("localhost", "root", "", "db_smart_guest");

if (mysqli_connect_errno()) {
    die("<h3 style='text-align:center;font-family:sans-serif;margin-top:50px;'>Koneksi database gagal: " . mysqli_connect_error() . "</h3>");
}

// Ambil parameter ID kunjungan dari URL
$id_kunjungan = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if (empty($id_kunjungan)) {
    die("<h3 style='text-align:center;font-family:sans-serif;margin-top:50px;'>Error: ID Kunjungan tidak ditemukan!</h3>");
}

// 1. QUERY UTAMA: Mengambil data kunjungan & info staf pendamping lapangan (PJ Lapangan)
$query = mysqli_query($koneksi, "
    SELECT k.*, 
           IFNULL(kat.nama_kategori, 'Audiensi') as nama_kategori,
           pj.nama_pj as nama_pj_lapangan
    FROM kunjungan k
    LEFT JOIN kategori_kunjungan kat ON k.id_kategori = kat.id_kategori
    LEFT JOIN penanggung_jawab pj ON k.id_pj = pj.id_pj
    WHERE k.id_kunjungan = '$id_kunjungan'
");

if (!$query || mysqli_num_rows($query) == 0) {
    die("<h3 style='text-align:center;font-family:sans-serif;margin-top:50px;'>Error: Data kunjungan tidak valid.</h3>");
}

$d = mysqli_fetch_assoc($query);

$kode_booking  = $d['kode_booking'];
$instansi_tamu = $d['nama_instansi_tamu'];
$materi        = $d['materi_kunjungan'];
$tgl_kunjungan = isset($d['tgl_kunjungan']) ? date('d F Y', strtotime($d['tgl_kunjungan'])) : date('d F Y');
$waktu         = $d['waktu_kunjungan'];
$jumlah_orang  = $d['jumlah_peserta_rencana'];
$pj_lapangan   = $d['nama_pj_lapangan'] ?? 'Sekretariat DPRD';

// 2. QUERY DINAMIS SEKRETARIS DEWAN: Murni mencari data berdasarkan Jabatan di Database
$query_sekwan = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab WHERE jabatan LIKE '%Sekretaris%' OR jabatan LIKE '%Sekwan%' LIMIT 1");
$data_sekwan = mysqli_fetch_assoc($query_sekwan);

// Kondisi penentuan data Sekretaris murni dari database (Bebas dari Parse Error)
if (!empty($data_sekwan['nama_pj'])) {
    $nama_sekwan    = $data_sekwan['nama_pj'];
    $nip_sekwan     = $data_sekwan['nip'];
    $jabatan_sekwan = $data_sekwan['jabatan'];
    $ttd_raw        = $data_sekwan['file_ttd'];
} else {
    // Jika belum diinput di master_pj, maka tampilkan peringatan teks merah ini
    $nama_sekwan    = '<span style="color:red;">[Input Sekretaris di Master PJ]</span>';
    $nip_sekwan     = '-';
    $jabatan_sekwan = 'SECRETARIS DEWAN';
    $ttd_raw        = '';
}

$tgl_surat = date('d F Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Balasan Resmi - <?= htmlspecialchars($kode_booking); ?></title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.5;
        }
        .sheet {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm 20mm 20mm 25mm; /* Standar Margin Surat Dinas */
            margin: auto;
            box-sizing: border-box;
            background: white;
            position: relative;
        }
        /* Kop Surat Resmi Dinas */
        .kop-surat {
            border-bottom: 5px double #000;
            padding-bottom: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .logo-daerah {
            width: 70px;
            height: auto;
            margin-right: 20px;
        }
        .text-kop {
            text-align: center;
            flex-grow: 1;
            margin-right: 70px; /* Balance alignment */
        }
        .text-kop h2 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .text-kop h1 {
            font-size: 22px;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .text-kop p {
            font-size: 12px;
            margin: 2px 0 0 0;
            font-style: italic;
        }
        /* Atribut Surat Balasan */
        .Atribut-surat {
            width: 100%;
            margin-bottom: 25px;
            font-size: 16px;
        }
        .Atribut-surat td {
            vertical-align: top;
        }
        .tgl-kanan {
            text-align: right;
            padding-bottom: 15px;
        }
        /* Isi Dokumen Dinas */
        .isi-surat {
            font-size: 16px;
            text-align: justify;
            text-indent: 45px;
            margin-bottom: 15px;
        }
        .isi-surat p {
            margin: 0 0 15px 0;
            line-height: 1.6;
        }
        .data-point {
            margin: 15px 0 15px 45px;
            font-size: 16px;
        }
        .data-point td {
            padding: 3px 0;
            vertical-align: top;
        }

        /* LAYOUT TERPISAH: QR DI KIRI & TTD DI KANAN (GAMBAR 2) */
        .tanda-tangan-section {
            width: 100%;
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        /* BAGIAN KIRI: QR Code & Catatan TTE */
        .left-qr-block {
            width: 260px;
            text-align: left;
            font-size: 14px;
        }
        .qr-border-dashed {
            padding: 4px;
            border: 1px dashed #000;
            background-color: #fff;
            display: inline-block;
            margin-bottom: 6px;
        }
        .qr-border-dashed img {
            width: 85px;
            height: 85px;
            display: block;
        }
        .tte-info {
            font-size: 9px;
            color: #444;
            font-style: italic;
            line-height: 1.3;
            font-family: Arial, sans-serif;
        }

        /* BAGIAN KANAN: Pejabat Penandatangan & Goresan TTD */
        .right-pejabat-block {
            width: 320px;
            text-align: left;
            font-size: 16px;
        }
        .graphic-ttd-layer {
            width: 100%;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin: 5px 0;
        }
        .graphic-ttd-layer img {
            max-height: 75px;
            max-width: 160px;
            object-fit: contain;
        }

        /* Aturan Cetak Printer */
        @media print {
            body { background: none; }
            .sheet { box-shadow: none; padding: 0; margin: 0; }
            .no-print { display: none; }
        }
        .no-print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #1e293b;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-family: sans-serif;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

    <button class="no-print no-print-btn" onclick="window.print();">
        [ Cetak Dokumen Surat ]
    </button>

    <div class="sheet">
        <div class="kop-surat">
            <img src="../assets/images/logo.png" class="logo-daerah" alt="Logo">
            <div class="text-kop">
                <h2>PEMERINTAH PROVINSI KALIMANTAN SELATAN</h2>
                <h1>SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH</h1>
                <p>Jalan Jenderal A. Yani Km. 3,5 No. 21, Banjarmasin, Kode Pos 70234</p>
            </div>
        </div>

        <table class="Atribut-surat">
            <tr>
                <td colspan="2" class="tgl-kanan">Banjarmasin, <?= $tgl_surat; ?></td>
            </tr>
            <tr>
                <td width="15%">Nomor</td>
                <td>: 005 / <?= rand(100, 999); ?> / Setwan-DPRD / 2026</td>
            </tr>
            <tr>
                <td>Sifat</td>
                <td>: Penting / Segera</td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>: 1 (satu) Lembar Disposisi</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>: <strong>Persetujuan Konfirmasi Kunjungan Kerja / Studi Tiru</strong></td>
            </tr>
        </table>

        <div style="font-size: 16px; margin-bottom: 25px;">
            Kepada Yth.<br>
            <strong>Pimpinan / Ketua <?= htmlspecialchars($instansi_tamu); ?></strong><br>
            di -<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Tempat
        </div>

        <div class="isi-surat">
            <p>Menunjuk surat permohonan kunjungan dari instansi Saudara dengan nomor registrasi sistem booking <strong><?= htmlspecialchars($kode_booking); ?></strong> perihal permohonan agenda kunjungan kerja/studi tiru di lingkungan Sekretariat DPRD Provinsi Kalimantan Selatan.</p>
            
            <p>Dengan ini kami sampaikan bahwa Pimpinan Dewan Perwakilan Rakyat Daerah (DPRD) bersama jajaran Sekretariat Dewan **MENYETUJUI / MENERIMA** rencana pelaksanaan kunjungan kerja tersebut, yang dijadwalkan pada:</p>
        </div>

        <table class="data-point">
            <tr>
                <td width="140px">Hari / Tanggal</td>
                <td width="15px">:</td>
                <td><strong><?= $tgl_kunjungan; ?></strong></td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td style="vertical-align: middle;">:</td>
                <td>Pukul <?= htmlspecialchars($waktu); ?> WITA s.d Selesai</td>
            </tr>
            <tr>
                <td>Jumlah Rombongan</td>
                <td>:</td>
                <td>± <?= htmlspecialchars($jumlah_orang); ?> Orang Peserta</td>
            </tr>
            <tr>
                <td>Agenda Utama</td>
                <td>:</td>
                <td><?= htmlspecialchars($materi); ?></td>
            </tr>
            <tr>
                <td>Pendamping Lapangan</td>
                <td>:</td>
                <td><?= htmlspecialchars($pj_lapangan); ?></td>
            </tr>
        </table>

        <div class="isi-surat">
            <p>Demikian surat konfirmasi balasan ini kami sampaikan untuk menjadi perhatian. Atas kerja sama dan koordinasi yang baik dari instansi Saudara, kami ucapkan terima kasih.</p>
        </div>

        <div class="tanda-tangan-section">
            
            <div class="left-qr-block">
                <div class="qr-border-dashed">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://verif.dprd.go.id/check/<?= urlencode($kode_booking); ?>" alt="QR TTE">
                </div>
                <div class="tte-info">
                    *Dokumen ini sah dan ditandatangani secara elektronik (TTE) melalui sistem Smart Guest Sekretariat DPRD. Keabsahan dokumen dapat divalidasi dengan memindai kode QR.
                </div>
            </div>
            
            <div class="right-pejabat-block">
                <?= strtoupper($jabatan_sekwan); ?>,<br>
                <span style="font-size: 13px; font-weight: bold;">Sekretariat DPRD Prov. Kalsel</span>
                
                <div class="graphic-ttd-layer">
                    <?php if(!empty($ttd_raw)): ?>
                        <?php if(strpos($ttd_raw, 'data:image') !== false || substr($ttd_raw, 0, 4) === 'data'): ?>
                            <img src="<?= $ttd_raw; ?>" alt="TTD Goresan">
                        <?php else: ?>
                            <img src="uploads/ttd/<?= $ttd_raw; ?>" onerror="this.src='../uploads/ttd/<?= $ttd_raw; ?>';" alt="TTD File">
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="height: 60px;"></div>
                    <?php endif; ?>
                </div>
                
                <strong style="text-decoration: underline;"><?= htmlspecialchars($nama_sekwan); ?></strong><br>
                Pembina Utama Madya<br>
                NIP. <?= htmlspecialchars($nip_sekwan); ?>
            </div>

        </div>

    </div>

</body>
</html>