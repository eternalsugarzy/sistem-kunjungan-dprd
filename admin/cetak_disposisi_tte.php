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

// 1. QUERY UTAMA: Mengambil data kunjungan, kategori, ruangan, dan staf pendamping lapangan
$query = mysqli_query($koneksi, "
    SELECT k.*, 
           IFNULL(kat.nama_kategori, 'Audiensi') as nama_kategori,
           r.nama_ruangan, r.lantai,
           pj.nama_pj as nama_pj_lapangan, pj.jabatan as jabatan_pj_lapangan
    FROM kunjungan k
    LEFT JOIN kategori_kunjungan kat ON k.id_kategori = kat.id_kategori
    LEFT JOIN ruangan r ON k.id_ruangan = r.id_ruangan
    LEFT JOIN penanggung_jawab pj ON k.id_pj = pj.id_pj
    WHERE k.id_kunjungan = '$id_kunjungan'
");

if (!$query || mysqli_num_rows($query) == 0) {
    die("<h3 style='text-align:center;font-family:sans-serif;margin-top:50px;'>Error: Data kunjungan tidak valid.</h3>");
}

$d = mysqli_fetch_assoc($query);

// Parsing data tamu
$kode_booking  = $d['kode_booking'];
$instansi_tamu = $d['nama_instansi_tamu'];
$materi        = $d['materi_kunjungan'];
$tgl_kunjungan = isset($d['tgl_kunjungan']) ? date('d F Y', strtotime($d['tgl_kunjungan'])) : date('d F Y');
$waktu         = $d['waktu_kunjungan'];
$jumlah_orang  = $d['jumlah_peserta_rencana'];

// Parsing data rekomendasi hasil disposisi
$ruangan_ditunjuk = !empty($d['nama_ruangan']) ? $d['nama_ruangan'] . " (Lantai " . $d['lantai'] . ")" : 'Belum Ditentukan';
$pj_ditunjuk      = !empty($d['nama_pj_lapangan']) ? $d['nama_pj_lapangan'] . " (" . $d['jabatan_pj_lapangan'] . ")" : 'Belum Ditentukan';

// 2. QUERY DINAMIS SEKRETARIS DEWAN: Murni mencari penandatangan utama lembar disposisi
$query_sekwan = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab WHERE jabatan LIKE '%Sekretaris%' OR jabatan LIKE '%Sekwan%' LIMIT 1");
$data_sekwan = mysqli_fetch_assoc($query_sekwan);

if (!empty($data_sekwan['nama_pj'])) {
    $nama_sekwan    = $data_sekwan['nama_pj'];
    $nip_sekwan     = $data_sekwan['nip'];
    $jabatan_sekwan = $data_sekwan['jabatan'];
    $ttd_raw        = $data_sekwan['file_ttd'];
} else {
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
    <title>Lembar Disposisi - <?= htmlspecialchars($kode_booking); ?></title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.4;
        }
        .sheet {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 20mm 15mm 25mm;
            margin: auto;
            box-sizing: border-box;
            background: white;
        }
        /* Kop Dinas */
        .kop-disposisi {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .kop-disposisi h2 {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
            font-weight: normal;
        }
        .kop-disposisi h1 {
            font-size: 18px;
            margin: 2px 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .kop-disposisi p {
            font-size: 11px;
            margin: 0;
        }
        
        .title-lembar {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 15px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        /* Tabel Bergaris Tegas khas Lembar Disposisi */
        .table-disposisi {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 15px;
        }
        .table-disposisi th, .table-disposisi td {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: top;
        }
        .bg-gray-header {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        
        /* Layout Pembungkus Bawah: QR & TTD */
        .tanda-tangan-section {
            width: 100%;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .left-qr-block {
            width: 260px;
            text-align: left;
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
        .right-pejabat-block {
            width: 320px;
            text-align: left;
            font-size: 15px;
        }
        .graphic-ttd-layer {
            width: 100%;
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin: 5px 0;
        }
        .graphic-ttd-layer img {
            max-height: 70px;
            max-width: 150px;
            object-fit: contain;
        }

        /* Aturan Cetak */
        @media print {
            body { background: none; }
            .sheet { box-shadow: none; padding: 0; margin: 0; }
            .no-print { display: none; }
        }
        .no-print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #eab308;
            color: #000;
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
        [ Cetak Lembar Disposisi ]
    </button>

    <div class="sheet">
        <div class="kop-disposisi">
            <h2>PEMERINTAH PROVINSI KALIMANTAN SELATAN</h2>
            <h1>SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH</h1>
            <p>Jalan Jenderal A. Yani Km. 3,5 No. 21, Banjarmasin, Kode Pos 70234</p>
        </div>

        <div class="title-lembar">LEMBAR DISPOSISI PIMPINAN</div>

        <table class="table-disposisi">
            <tr>
                <td width="25%"><strong>Surat Dari</strong></td>
                <td width="3%">:</td>
                <td width="72%"><?= htmlspecialchars($instansi_tamu); ?></td>
            </tr>
            <tr>
                <td><strong>Nomor Registrasi / Booking</strong></td>
                <td>:</td>
                <td class="font-monospace" style="font-weight: bold; color: #000;"><?= htmlspecialchars($kode_booking); ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal Pengajuan</strong></td>
                <td>:</td>
                <td><?= date('d F Y', strtotime($d['created_at'] ?? 'now')); ?></td>
            </tr>
            <tr>
                <td><strong>Perihal / Agenda Utama</strong></td>
                <td>:</td>
                <td><strong><?= htmlspecialchars($materi); ?></strong></td>
            </tr>
        </table>

        <table class="table-disposisi">
            <thead>
                <tr>
                    <th colspan="3" class="bg-gray-header">REKOMENDASI &amp; INSTRUKSI PIMPINAN DPRD</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="35%"><strong>Status Otorisasi</strong></td>
                    <td width="3%">:</td>
                    <td>
                        <span style="text-transform: uppercase; font-weight: bold;">
                            <?= ($d['status_kegiatan'] == 'batal') ? 'DITOLAK / DIBATALKAN' : 'DISETUJUI &amp; DIJADWALKAN'; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Rekomendasi Ruangan</strong></td>
                    <td>:</td>
                    <td><strong><?= htmlspecialchars($ruangan_ditunjuk); ?></strong></td>
                </tr>
                <tr>
                    <td><strong>Penanggung Jawab Lapangan (Staf Pelaksana)</strong></td>
                    <td>:</td>
                    <td><?= htmlspecialchars($pj_ditunjuk); ?></td>
                </tr>
                <tr>
                    <td><strong>Catatan / Instruksi Tambahan</strong></td>
                    <td>:</td>
                    <td>
                        <p style="margin: 0; font-style: italic; color: #333;">
                            "Agar Penanggung Jawab lapangan segera mengoordinasikan kesiapan sarana prasarana ruangan pertemuan dan mendampingi rombongan tamu dari <?= htmlspecialchars($instansi_tamu); ?> pada tanggal <?= $tgl_kunjungan; ?> pukul <?= htmlspecialchars($waktu); ?> WITA sebaik-baiknya."
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="tanda-tangan-section">
            
            <div class="left-qr-block">
                <div class="qr-border-dashed">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://verif.dprd.go.id/disposisi/<?= urlencode($kode_booking); ?>" alt="QR Disposisi">
                </div>
                <div class="tte-info">
                    *Lembar Disposisi ini sah dikeluarkan secara elektronik (TTE) oleh Pimpinan DPRD Provinsi Kalimantan Selatan melalui otorisasi sistem Smart Guest.
                </div>
            </div>
            
            <div class="right-pejabat-block">
                Banjarmasin, <?= $tgl_surat; ?><br>
                <?= strtoupper($jabatan_sekwan); ?>,<br>
                <span style="font-size: 12px; font-weight: bold;">Sekretariat DPRD Prov. Kalsel</span>
                
                <div class="graphic-ttd-layer">
                    <?php if(!empty($ttd_raw)): ?>
                        <?php if(strpos($ttd_raw, 'data:image') !== false || substr($ttd_raw, 0, 4) === 'data'): ?>
                            <img src="<?= $ttd_raw; ?>" alt="TTD Goresan">
                        <?php else: ?>
                            <img src="uploads/ttd/<?= $ttd_raw; ?>" onerror="this.src='../uploads/ttd/<?= $ttd_raw; ?>';" alt="TTD File">
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="height: 55px;"></div>
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