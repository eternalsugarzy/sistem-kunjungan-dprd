<?php
include '../koneksi.php';

// ===================================================================
// AMBIL DATA SELURUH KUNJUNGAN YANG BERSTATUS "BATAL"
// Kolom sesuai kebutuhan laporan:
// id_kunjungan, kode_booking, nama_instansi, tgl_rencana (tgl_kunjungan),
// alasan_pembatalan, tgl_pembatalan
// ===================================================================
$query = "SELECT id_kunjungan, kode_booking, nama_instansi_tamu, tgl_kunjungan, 
                 alasan_pembatalan, tgl_pembatalan 
          FROM kunjungan 
          WHERE status_kegiatan = 'batal' 
          ORDER BY tgl_pembatalan DESC, id_kunjungan DESC";
$result = mysqli_query($koneksi, $query);

$total_batal = ($result) ? mysqli_num_rows($result) : 0;

// ===================================================================
// QUERY DINAMIS PENANDATANGAN (mengikuti pola surat resmi lainnya)
// Semua atribut pejabat penandatangan WAJIB diambil dari tabel
// `penanggung_jawab`, TIDAK boleh ditulis statis di file laporan.
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
    <title>Laporan Kunjungan Batal</title>
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
        .judul p { margin: 5px 0; font-weight: bold; }
        .summary-box { width: 100%; border: 1px solid #000; padding: 10px; margin-bottom: 20px; background-color: #f9f9f9; }
        .table-laporan { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 30px; }
        .table-laporan th, .table-laporan td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; }
        .table-laporan th { background-color: #e0e0e0; text-align: center; font-weight: bold; }
        .ttd-area { float: right; margin-top: 20px; text-align: center; width: 280px; }
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
        <h4>LAPORAN REKAPITULASI KUNJUNGAN DIBATALKAN</h4>
        <p>APLIKASI SMART GUEST BOOK DPRD KOTA BANJARMASIN</p>
    </div>

    <div class="summary-box">
        <strong>Total Kunjungan Dibatalkan Tercatat:</strong> <?= $total_batal; ?> Pengajuan
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="8%">ID</th>
                <th width="14%">Kode Booking</th>
                <th width="22%">Nama Instansi</th>
                <th width="12%">Tgl Rencana Kunjungan</th>
                <th width="12%">Tgl Pembatalan</th>
                <th width="28%">Alasan Pembatalan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if ($result && $total_batal > 0) {
                while ($d = mysqli_fetch_assoc($result)) {
                    $tgl_rencana = !empty($d['tgl_kunjungan']) ? date('d/m/Y', strtotime($d['tgl_kunjungan'])) : '-';
                    $tgl_batal   = !empty($d['tgl_pembatalan']) ? date('d/m/Y H:i', strtotime($d['tgl_pembatalan'])) . ' WITA' : '-';
                    $alasan      = !empty($d['alasan_pembatalan']) ? htmlspecialchars($d['alasan_pembatalan']) : '<span style="color:#888; font-style: italic;">Tidak ada keterangan</span>';
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td style="text-align: center;"><?= $d['id_kunjungan']; ?></td>
                <td style="text-align: center; font-family: monospace;"><b><?= htmlspecialchars($d['kode_booking']); ?></b></td>
                <td><strong><?= htmlspecialchars($d['nama_instansi_tamu']); ?></strong></td>
                <td style="text-align: center;"><?= $tgl_rencana; ?></td>
                <td style="text-align: center;"><?= $tgl_batal; ?></td>
                <td><?= $alasan; ?></td>
            </tr>
            <?php
                }
            } else {
                echo '<tr><td colspan="7" style="text-align: center; padding: 15px;">Belum ada data kunjungan yang dibatalkan.</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <div class="ttd-area">
        <p>Banjarmasin, <?= date('d') . ' ' . $nama_bulan[date('m')] . ' ' . date('Y'); ?></p>
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