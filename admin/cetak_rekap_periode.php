<?php
include '../koneksi.php';

// ===================================================================
// 1. AMBIL & VALIDASI PARAMETER FILTER DARI FORM
// ===================================================================
$tipe_valid = ['harian', 'mingguan', 'bulanan'];
$tipe = isset($_GET['tipe']) && in_array($_GET['tipe'], $tipe_valid) ? $_GET['tipe'] : 'bulanan';

// Validasi format tanggal YYYY-MM-DD, fallback ke default 30 hari terakhir jika tidak valid
$tgl_mulai   = (isset($_GET['tgl_mulai']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tgl_mulai'])) ? $_GET['tgl_mulai'] : date('Y-m-d', strtotime('-30 days'));
$tgl_selesai = (isset($_GET['tgl_selesai']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tgl_selesai'])) ? $_GET['tgl_selesai'] : date('Y-m-d');

// Jika tanggal mulai lebih besar dari tanggal selesai, tukar posisinya
if (strtotime($tgl_mulai) > strtotime($tgl_selesai)) {
    $tmp = $tgl_mulai; $tgl_mulai = $tgl_selesai; $tgl_selesai = $tmp;
}

$tgl_mulai_esc   = mysqli_real_escape_string($koneksi, $tgl_mulai);
$tgl_selesai_esc = mysqli_real_escape_string($koneksi, $tgl_selesai);

$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Helper: format tanggal jadi "04 Juli 2026"
function tgl_indo($tanggal, $nama_bulan) {
    if (empty($tanggal)) return '-';
    $t = strtotime($tanggal);
    return date('d', $t) . ' ' . $nama_bulan[date('m', $t)] . ' ' . date('Y', $t);
}
// Helper: format tanggal singkat "04 Jul"
function tgl_indo_singkat($tanggal, $nama_bulan) {
    if (empty($tanggal)) return '-';
    $t = strtotime($tanggal);
    return date('d', $t) . ' ' . substr($nama_bulan[date('m', $t)], 0, 3);
}

// ===================================================================
// 2. QUERY REKAP SESUAI JENIS PERIODE (Harian / Mingguan / Bulanan)
// ===================================================================
if ($tipe == 'harian') {
    $judul_tipe = 'HARIAN';
    $query_grp = "SELECT DATE(tgl_kunjungan) AS grp, 
                         DATE(tgl_kunjungan) AS tgl_awal, 
                         DATE(tgl_kunjungan) AS tgl_akhir,
                         COUNT(*) AS total,
                         SUM(CASE WHEN status_kegiatan = 'selesai' THEN 1 ELSE 0 END) AS selesai,
                         SUM(CASE WHEN status_kegiatan = 'batal' THEN 1 ELSE 0 END) AS batal
                  FROM kunjungan
                  WHERE tgl_kunjungan BETWEEN '$tgl_mulai_esc' AND '$tgl_selesai_esc'
                  GROUP BY DATE(tgl_kunjungan)
                  ORDER BY grp ASC";
} elseif ($tipe == 'mingguan') {
    $judul_tipe = 'MINGGUAN';
    $query_grp = "SELECT YEARWEEK(tgl_kunjungan, 1) AS grp,
                         MIN(tgl_kunjungan) AS tgl_awal,
                         MAX(tgl_kunjungan) AS tgl_akhir,
                         COUNT(*) AS total,
                         SUM(CASE WHEN status_kegiatan = 'selesai' THEN 1 ELSE 0 END) AS selesai,
                         SUM(CASE WHEN status_kegiatan = 'batal' THEN 1 ELSE 0 END) AS batal
                  FROM kunjungan
                  WHERE tgl_kunjungan BETWEEN '$tgl_mulai_esc' AND '$tgl_selesai_esc'
                  GROUP BY YEARWEEK(tgl_kunjungan, 1)
                  ORDER BY grp ASC";
} else { // bulanan
    $judul_tipe = 'BULANAN';
    $query_grp = "SELECT DATE_FORMAT(tgl_kunjungan, '%Y-%m') AS grp,
                         MIN(tgl_kunjungan) AS tgl_awal,
                         MAX(tgl_kunjungan) AS tgl_akhir,
                         COUNT(*) AS total,
                         SUM(CASE WHEN status_kegiatan = 'selesai' THEN 1 ELSE 0 END) AS selesai,
                         SUM(CASE WHEN status_kegiatan = 'batal' THEN 1 ELSE 0 END) AS batal
                  FROM kunjungan
                  WHERE tgl_kunjungan BETWEEN '$tgl_mulai_esc' AND '$tgl_selesai_esc'
                  GROUP BY DATE_FORMAT(tgl_kunjungan, '%Y-%m')
                  ORDER BY grp ASC";
}

$hasil_grp = mysqli_query($koneksi, $query_grp);
$data_rows = [];
if ($hasil_grp) {
    while ($r = mysqli_fetch_assoc($hasil_grp)) {
        $data_rows[] = $r;
    }
}

// Susun label per baris sesuai jenis periode
foreach ($data_rows as $i => $r) {
    if ($tipe == 'harian') {
        $data_rows[$i]['label'] = tgl_indo($r['tgl_awal'], $nama_bulan);
    } elseif ($tipe == 'mingguan') {
        $data_rows[$i]['label'] = tgl_indo_singkat($r['tgl_awal'], $nama_bulan) . ' - ' . tgl_indo($r['tgl_akhir'], $nama_bulan);
    } else {
        $data_rows[$i]['label'] = $nama_bulan[date('m', strtotime($r['tgl_awal']))] . ' ' . date('Y', strtotime($r['tgl_awal']));
    }
}

// ===================================================================
// 3. HITUNG TREN PER BARIS (naik / turun / tetap dibanding baris sebelumnya)
// ===================================================================
$max_total = 1;
foreach ($data_rows as $r) { if ($r['total'] > $max_total) $max_total = $r['total']; }

foreach ($data_rows as $i => $r) {
    if ($i == 0) {
        $data_rows[$i]['tren_simbol'] = '-';
        $data_rows[$i]['tren_warna']  = '#888';
        $data_rows[$i]['tren_teks']   = 'Data awal';
    } else {
        $prev = $data_rows[$i - 1]['total'];
        $diff = $r['total'] - $prev;
        if ($diff > 0) {
            $persen = $prev > 0 ? round(($diff / $prev) * 100, 1) : 100;
            $data_rows[$i]['tren_simbol'] = '&#9650; ' . $persen . '%';
            $data_rows[$i]['tren_warna']  = '#1e7e34';
            $data_rows[$i]['tren_teks']   = 'Naik';
        } elseif ($diff < 0) {
            $persen = $prev > 0 ? round((abs($diff) / $prev) * 100, 1) : 100;
            $data_rows[$i]['tren_simbol'] = '&#9660; ' . $persen . '%';
            $data_rows[$i]['tren_warna']  = '#c82333';
            $data_rows[$i]['tren_teks']   = 'Turun';
        } else {
            $data_rows[$i]['tren_simbol'] = '&#9644; 0%';
            $data_rows[$i]['tren_warna']  = '#888';
            $data_rows[$i]['tren_teks']   = 'Tetap';
        }
    }
    $data_rows[$i]['bar_width'] = round(($r['total'] / $max_total) * 100, 1);
}

// ===================================================================
// 4. PERBANDINGAN TOTAL PERIODE INI VS PERIODE SEBELUMNYA (durasi sama)
// ===================================================================
$total_periode_ini = 0;
foreach ($data_rows as $r) { $total_periode_ini += $r['total']; }

$jumlah_hari    = (int) ((strtotime($tgl_selesai) - strtotime($tgl_mulai)) / 86400) + 1;
$prev_akhir     = date('Y-m-d', strtotime($tgl_mulai . ' -1 day'));
$prev_mulai     = date('Y-m-d', strtotime($prev_akhir . ' -' . ($jumlah_hari - 1) . ' days'));
$prev_akhir_esc = mysqli_real_escape_string($koneksi, $prev_akhir);
$prev_mulai_esc = mysqli_real_escape_string($koneksi, $prev_mulai);

$q_prev = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kunjungan WHERE tgl_kunjungan BETWEEN '$prev_mulai_esc' AND '$prev_akhir_esc'");
$total_periode_lalu = $q_prev ? (int) mysqli_fetch_assoc($q_prev)['total'] : 0;

$selisih = $total_periode_ini - $total_periode_lalu;
if ($total_periode_lalu > 0) {
    $persen_banding = round((abs($selisih) / $total_periode_lalu) * 100, 1);
} else {
    $persen_banding = $total_periode_ini > 0 ? 100 : 0;
}

if ($selisih > 0) {
    $banding_simbol = '&#9650;'; $banding_warna = '#1e7e34'; $banding_teks = 'MENINGKAT';
} elseif ($selisih < 0) {
    $banding_simbol = '&#9660;'; $banding_warna = '#c82333'; $banding_teks = 'MENURUN';
} else {
    $banding_simbol = '&#9644;'; $banding_warna = '#888'; $banding_teks = 'TETAP / TIDAK BERUBAH';
}

// ===================================================================
// QUERY DINAMIS PENANDATANGAN (mengikuti pola surat resmi lainnya)
// ===================================================================
$query_sekwan = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab WHERE jabatan LIKE '%Sekretaris%' OR jabatan LIKE '%Sekwan%' LIMIT 1");
$data_sekwan = mysqli_fetch_assoc($query_sekwan);

if (!empty($data_sekwan['nama_pj'])) {
    $nama_sekwan    = $data_sekwan['nama_pj'];
    $nip_sekwan     = $data_sekwan['nip'];
    $jabatan_sekwan = $data_sekwan['jabatan'];
    $pangkat_sekwan = !empty($data_sekwan['pangkat_golongan']) ? $data_sekwan['pangkat_golongan'] : '-';
    $ttd_raw        = $data_sekwan['file_ttd'];
} else {
    $nama_sekwan    = '<span style="color:red;">[Input Sekretaris di Master PJ]</span>';
    $nip_sekwan     = '-';
    $jabatan_sekwan = 'SEKRETARIS DEWAN';
    $pangkat_sekwan = '-';
    $ttd_raw        = '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Kunjungan Per Periode</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 11pt; margin: 20px 40px; color: #000; }
        .kop-container { width: 100%; border-bottom: 4px solid #000; padding-bottom: 10px; margin-bottom: 20px; position: relative; }
        .kop-container::after { content: ""; position: absolute; left: 0; right: 0; bottom: -3px; border-bottom: 1px solid #000; }
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-table td { vertical-align: middle; text-align: center; }
        .logo-img { width: 85px; height: auto; }
        .text-pemkot { font-size: 14pt; margin: 0; font-weight: normal; }
        .text-dprd { font-size: 18pt; margin: 5px 0; font-weight: bold; letter-spacing: 1px; }
        .text-alamat { font-size: 10pt; margin: 0; }
        .judul { text-align: center; margin-bottom: 20px; }
        .judul h4 { font-size: 14pt; margin: 0; text-decoration: underline; text-transform: uppercase; }
        .judul p { margin: 5px 0; font-size: 10pt; }

        .banding-box { width: 100%; border: 1px solid #000; padding: 15px; margin-bottom: 20px; background: #f8f9fa; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .banding-col { text-align: center; padding: 0 10px; }
        .banding-label { font-size: 9pt; color: #555; text-transform: uppercase; }
        .banding-val { font-size: 16pt; font-weight: bold; }

        .table-rekap { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 25px; }
        .table-rekap th, .table-rekap td { border: 1px solid #000; padding: 6px; text-align: center; vertical-align: middle; }
        .table-rekap th { background-color: #e0e0e0; font-weight: bold; }
        .table-rekap td.label-col { text-align: left; font-weight: bold; }

        .bar-track { width: 100%; background: #e9e9e9; height: 10px; border: 1px solid #ccc; }
        .bar-fill { background: #212529; height: 100%; }

        .ttd-area { float: right; margin-top: 10px; text-align: center; width: 280px; }
        .ttd-area p { margin: 3px 0; }
        .graphic-ttd-layer { height: 80px; display: flex; align-items: center; justify-content: center; margin: 5px 0; }
        .graphic-ttd-layer img { max-height: 75px; max-width: 160px; object-fit: contain; }
        .nama-ttd { font-weight: bold; text-decoration: underline; }

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
                <td width="15%"><img src="../assets/images/logo.png" class="logo-img" alt="Logo"></td>
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
        <h4>LAPORAN REKAPITULASI KUNJUNGAN PER PERIODE (<?= $judul_tipe; ?>)</h4>
        <p>Rentang: <?= tgl_indo($tgl_mulai, $nama_bulan); ?> s/d <?= tgl_indo($tgl_selesai, $nama_bulan); ?></p>
    </div>

    <!-- KOTAK PERBANDINGAN ANTAR PERIODE -->
    <div class="banding-box">
        <div class="banding-col">
            <div class="banding-label">Periode Ini (<?= $jumlah_hari; ?> Hari)</div>
            <div class="banding-val"><?= $total_periode_ini; ?></div>
            <div class="banding-label">Kunjungan</div>
        </div>
        <div class="banding-col">
            <div class="banding-label">Periode Sebelumnya</div>
            <div class="banding-val"><?= $total_periode_lalu; ?></div>
            <div class="banding-label"><?= tgl_indo_singkat($prev_mulai, $nama_bulan); ?> - <?= tgl_indo_singkat($prev_akhir, $nama_bulan); ?></div>
        </div>
        <div class="banding-col">
            <div class="banding-label">Perbandingan</div>
            <div class="banding-val" style="color: <?= $banding_warna; ?>;"><?= $banding_simbol; ?> <?= $persen_banding; ?>%</div>
            <div class="banding-label" style="color: <?= $banding_warna; ?>; font-weight: bold;"><?= $banding_teks; ?></div>
        </div>
    </div>

    <!-- TABEL REKAP + TREN + GRAFIK BATANG -->
    <table class="table-rekap">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Periode</th>
                <th width="10%">Total Kunjungan</th>
                <th width="10%">Selesai</th>
                <th width="10%">Batal</th>
                <th width="16%">Tren</th>
                <th width="30%">Grafik</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($data_rows) > 0): $no = 1; foreach ($data_rows as $r): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td class="label-col"><?= $r['label']; ?></td>
                <td><b><?= $r['total']; ?></b></td>
                <td style="color:#1e7e34;"><?= $r['selesai']; ?></td>
                <td style="color:#c82333;"><?= $r['batal']; ?></td>
                <td style="color: <?= $r['tren_warna']; ?>; font-weight:bold;"><?= $r['tren_simbol']; ?></td>
                <td>
                    <div class="bar-track"><div class="bar-fill" style="width: <?= $r['bar_width']; ?>%;"></div></div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7" style="padding:15px;">Tidak ada data kunjungan pada rentang periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="font-size: 9pt; font-style: italic;">* Kolom Tren membandingkan jumlah kunjungan pada baris tersebut dengan periode sebelumnya di tabel yang sama. Kotak Perbandingan Antar Periode di atas membandingkan total keseluruhan rentang yang dipilih dengan rentang durasi yang sama tepat sebelumnya.</p>

    <div class="ttd-area">
        <p>Banjarmasin, <?= tgl_indo(date('Y-m-d'), $nama_bulan); ?></p>
        <p><?= strtoupper($jabatan_sekwan); ?></p>
        <div class="graphic-ttd-layer">
            <?php if (!empty($ttd_raw)): ?>
                <?php if (strpos($ttd_raw, 'data:image') !== false || substr($ttd_raw, 0, 4) === 'data'): ?>
                    <img src="<?= $ttd_raw; ?>" alt="TTD Goresan">
                <?php else: ?>
                    <img src="../uploads/ttd/<?= $ttd_raw; ?>" onerror="this.style.display='none'" alt="TTD File">
                <?php endif; ?>
            <?php else: ?>
                <div style="height: 60px;"></div>
            <?php endif; ?>
        </div>
        <p class="nama-ttd"><?= $nama_sekwan; ?></p>
        <p><?= htmlspecialchars($pangkat_sekwan); ?></p>
        <p>NIP. <?= $nip_sekwan; ?></p>
    </div>
    <div style="clear: both;"></div>

</body>
</html>