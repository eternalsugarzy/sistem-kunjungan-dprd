<?php
include '../koneksi.php';

if (!isset($_GET['bulan']) || !isset($_GET['tahun'])) {
    die("Error: Data Bulan/Tahun tidak ditemukan.");
}

$bulan = $_GET['bulan'];
$tahun = $_GET['tahun'];

$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

$query = "SELECT * FROM kunjungan WHERE MONTH(tgl_kunjungan) = '$bulan' AND YEAR(tgl_kunjungan) = '$tahun' ORDER BY tgl_kunjungan ASC";
$result = mysqli_query($koneksi, $query);

$total_kunjungan = $total_online = $total_walkin = $total_selesai = $total_batal = $total_peserta = 0;
$data_laporan = [];
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result)){
        $data_laporan[] = $row;
        $total_kunjungan++;
        $total_peserta += $row['jumlah_peserta_rencana'];
        if($row['jenis_pendaftaran'] == 'online') $total_online++;
        if($row['jenis_pendaftaran'] == 'walk-in') $total_walkin++;
        if($row['status_kegiatan'] == 'selesai') $total_selesai++;
        if($row['status_kegiatan'] == 'batal') $total_batal++;
    }
}

// ===================================================================
// QUERY DINAMIS PENANDATANGAN
// -------------------------------------------------------------------
// TEMPLATE BLOK TTD/TTE PEJABAT (dipakai ulang di semua surat resmi):
// Semua atribut pejabat penandatangan (nama, NIP, jabatan, pangkat/
// golongan, file/goresan TTD) WAJIB diambil dari tabel
// `penanggung_jawab`, TIDAK boleh ditulis statis di file surat.
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
    <title>Laporan Kunjungan - <?= $nama_bulan[$bulan]; ?> <?= $tahun; ?></title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 11pt; margin: 20px 40px; color: #000; }
        /* ===================================================================
           TEMPLATE KOP SURAT:
           Salin blok CSS & HTML kop ini apa adanya ke surat lain agar
           kop selalu konsisten.
        =================================================================== */
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
        .summary-table { width: 100%; font-size: 10pt; }
        .summary-table td { padding: 3px 5px; }
        .table-laporan { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 30px; }
        .table-laporan th, .table-laporan td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; }
        .table-laporan th { background-color: #e0e0e0; text-align: center; font-weight: bold; }
        
        /* ===================================================================
           TEMPLATE AREA TANDA TANGAN DINAMIS (dipakai ulang di semua surat
           resmi): semua data pejabat (jabatan, goresan TTD, nama,
           pangkat/golongan, NIP) HARUS berasal dari variabel hasil query
           ke `penanggung_jawab`.
        =================================================================== */
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

    <!-- ===== TEMPLATE KOP SURAT: salin blok ini apa adanya ke surat lain ===== -->
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
    <!-- ===== AKHIR TEMPLATE KOP SURAT ===== -->

    <div class="judul">
        <h4>REKAPITULASI DATA KUNJUNGAN KERJA (SMART GUEST)</h4>
        <p>Periode: <?= $nama_bulan[$bulan]; ?> <?= $tahun; ?></p>
    </div>

    <div class="summary-box">
        <strong>Statistik Kunjungan Bulan Ini:</strong>
        <table class="summary-table">
            <tr><td width="20%">Total Pengajuan</td><td width="2%">:</td><td width="28%"><b><?= $total_kunjungan; ?></b> Instansi</td><td width="20%">Kunjungan Selesai</td><td width="2%">:</td><td width="28%"><b><?= $total_selesai; ?></b> Instansi</td></tr>
            <tr><td>Pendaftaran E-Ticket (Online)</td><td>:</td><td><b><?= $total_online; ?></b> Instansi</td><td>Kunjungan Batal</td><td>:</td><td><b><?= $total_batal; ?></b> Instansi</td></tr>
            <tr><td>Pendaftaran Walk-In (Manual)</td><td>:</td><td><b><?= $total_walkin; ?></b> Instansi</td><td>Total Peserta/Tamu</td><td>:</td><td><b><?= $total_peserta; ?></b> Orang</td></tr>
        </table>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="4%">No</th><th width="12%">Tgl & Waktu</th><th width="14%">Kode Tiket</th><th width="22%">Instansi Pemohon</th>
                <th width="8%">Jenis</th><th width="20%">Tujuan / Materi</th><th width="8%">Peserta</th><th width="12%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (count($data_laporan) > 0) {
                foreach($data_laporan as $d){
                    $tgl = date('d/m/Y', strtotime($d['tgl_kunjungan']));
                    $jam = substr($d['waktu_kunjungan'], 0, 5);
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td style="text-align: center;"><?= $tgl; ?><br><small><?= $jam; ?> WITA</small></td>
                <td style="text-align: center; font-family: monospace;"><b><?= $d['kode_booking']; ?></b></td>
                <td><strong><?= $d['nama_instansi_tamu']; ?></strong></td>
                <td style="text-align: center;"><?= ucfirst($d['jenis_pendaftaran'] ?? 'Online'); ?></td>
                <td><?= $d['materi_kunjungan']; ?></td>
                <td style="text-align: center;"><?= $d['jumlah_peserta_rencana']; ?> org</td>
                <td style="text-align: center;"><?php if($d['status_kegiatan']=='selesai') echo "Terlaksana"; elseif($d['status_kegiatan']=='dijadwalkan') echo "Terjadwal"; elseif($d['status_kegiatan']=='batal') echo "Batal"; else echo "Pending"; ?></td>
            </tr>
            <?php } } else { echo '<tr><td colspan="8" style="text-align: center; padding: 15px;">Tidak ada data kunjungan pada periode ini.</td></tr>'; } ?>
        </tbody>
    </table>

    <!-- ===== TEMPLATE AREA TANDA TANGAN: salin blok ini apa adanya ke surat lain ===== -->
    <div class="ttd-area">
        <p>Banjarmasin, <?= date('d') . ' ' . $nama_bulan[date('m')] . ' ' . date('Y'); ?></p>
        <p><?= strtoupper($jabatan_sekwan); ?></p>
        
        <div class="graphic-ttd-layer">
            <?php if(!empty($ttd_raw)): ?>
                <?php if(strpos($ttd_raw, 'data:image') !== false || substr($ttd_raw, 0, 4) === 'data'): ?>
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
    <!-- ===== AKHIR TEMPLATE AREA TANDA TANGAN ===== -->
    <div style="clear: both;"></div>

</body>
</html>