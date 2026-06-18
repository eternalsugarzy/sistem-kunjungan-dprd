<?php
include '../koneksi.php';

$id_kunjungan = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

// Ambil data dari tabel kunjungan digabung dengan spt_tugas
$query = mysqli_query($koneksi, "
    SELECT k.*, s.* FROM kunjungan k 
    LEFT JOIN spt_tugas s ON k.id_kunjungan = s.id_kunjungan 
    WHERE k.id_kunjungan = '$id_kunjungan'
");

$d = mysqli_fetch_assoc($query);

// Validasi jika data SPT belum dibuat
if (empty($d['id_spt'])) {
    echo "<script>alert('Data SPT belum dibuat! Silakan input data terlebih dahulu.'); window.location.href='input_spt.php?id=$id_kunjungan';</script>";
    exit;
}

// ===================================================================
// QUERY DINAMIS PENANDATANGAN (Sekretaris Dewan)
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

// Format Tanggal Indonesia
function tgl_indo($tanggal){
    $bulan = array (1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Cetak SPT - <?= $d['kode_booking']; ?></title>
    <style>
        /* Pengaturan Kertas A4 & Tipografi Formal */
        body { font-family: 'Times New Roman', Times, serif; color: #000; background: #e9ecef; font-size: 12pt; line-height: 1.5; }
        .kertas-a4 { width: 210mm; min-height: 297mm; padding: 20mm; margin: 20px auto; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); box-sizing: border-box; }
        
        /* ===================================================================
           TEMPLATE KOP SURAT (dipakai ulang di semua surat resmi):
           Salin blok CSS & HTML kop ini apa adanya ke surat lain agar
           kop selalu konsisten.
        =================================================================== */
        .kop-surat { display: flex; align-items: center; border-bottom: 4px solid #000; padding-bottom: 10px; margin-bottom: 30px; position: relative; }
        .kop-surat::after { content: ""; position: absolute; left: 0; right: 0; bottom: -3px; border-bottom: 1px solid #000; }
        .logo-kop { width: 80px; margin-right: 20px; }
        .teks-kop { text-align: center; flex: 1; }
        .teks-kop h1 { font-size: 16pt; font-weight: normal; margin:0; padding:0; }
        .teks-kop h2 { font-size: 18pt; font-weight: bold; letter-spacing: 1px; margin:0; padding:0; }
        .teks-kop p { font-size: 10pt; margin-top: 5px; }

        /* Judul & Isi Surat */
        .judul-surat { text-align: center; margin-bottom: 30px; }
        .judul-surat h3 { text-decoration: underline; margin: 0 0 5px 0; font-size: 14pt; }
        .isi-surat { text-align: justify; }
        .tabel-pegawai, .tabel-tujuan { width: 100%; margin: 15px 0; border-collapse: collapse; }
        .tabel-pegawai td, .tabel-tujuan td { vertical-align: top; padding: 4px 0; }
        
        /* ===================================================================
           TEMPLATE AREA TANDA TANGAN (dipakai ulang di semua surat resmi):
           Semua data pejabat (jabatan, goresan TTD, nama, pangkat/golongan,
           NIP) HARUS berasal dari variabel hasil query ke `penanggung_jawab`.
        =================================================================== */
        .ttd-area { width: 300px; float: right; text-align: center; margin-top: 50px; }
        .ttd-area p { margin: 0; }
        .graphic-ttd-layer { height: 80px; display: flex; align-items: center; justify-content: center; margin: 5px 0; }
        .graphic-ttd-layer img { max-height: 75px; max-width: 160px; object-fit: contain; }
        .nama-ttd { font-weight: bold; text-decoration: underline; }

        .btn-print { margin: 20px auto; display: block; padding: 10px 20px; font-size: 14pt; background: #212529; color: #fff; border: none; cursor: pointer; border-radius: 5px;}
        @media print { body { background: #fff; margin: 0; } .kertas-a4 { margin: 0; box-shadow: none; padding: 0; } .no-print { display: none !important; } }
    </style>
</head>
<body>

    <button class="btn-print no-print" onclick="window.print()">🖨️ Cetak Dokumen</button>

    <div class="kertas-a4">
        <!-- ===== TEMPLATE KOP SURAT: salin blok ini apa adanya ke surat lain ===== -->
        <div class="kop-surat">
            <img src="../assets/images/logo.png" class="logo-kop" alt="Logo">
            <div class="teks-kop">
                <h1>PEMERINTAH KOTA BANJARMASIN</h1>
                <h2>SEKRETARIAT DPRD</h2>
                <p>Jl. Lambung Mangkurat No. 1, Kota Banjarmasin, Kalimantan Selatan<br>
                Website: dprd.banjarmasinkota.go.id | Email: setwan@banjarmasinkota.go.id</p>
            </div>
            <div style="width: 80px;"></div> 
        </div>
        <!-- ===== AKHIR TEMPLATE KOP SURAT ===== -->

        <div class="judul-surat">
            <h3>SURAT PERINTAH TUGAS</h3>
            <span>Nomor: <?= $d['no_spt']; ?></span>
        </div>

        <div class="isi-surat">
            <p>Berdasarkan Peraturan Pemerintah terkait Administrasi Perjalanan Dinas dan penerimaan kunjungan pada institusi pemerintahan, dengan ini Sekretaris DPRD Kota Banjarmasin memerintahkan kepada:</p>

            <table class="tabel-pegawai">
                <tr><td width="30%">Nama Pegawai</td><td width="2%">:</td><td><strong><?= $d['nama_pegawai']; ?></strong></td></tr>
                <?php if (!empty($d['nip'])): ?>
                <tr><td>NIP</td><td>:</td><td><?= $d['nip']; ?></td></tr>
                <?php endif; ?>
                <tr><td>Jabatan</td><td>:</td><td><?= $d['jabatan']; ?></td></tr>
                <tr><td>Jumlah Penugasan</td><td>:</td><td><?= $d['jumlah_ditugaskan']; ?> Orang</td></tr>
            </table>

            <p>Untuk melaksanakan tugas mendampingi / menerima kunjungan rombongan tamu dengan rincian kegiatan sebagai berikut:</p>

            <table class="tabel-tujuan">
                <tr><td width="30%">Instansi Tamu</td><td width="2%">:</td><td><strong><?= $d['nama_instansi_tamu']; ?></strong></td></tr>
                <tr><td>Maksud / Tujuan</td><td>:</td><td><?= $d['materi_kunjungan']; ?></td></tr>
                <tr><td>Tanggal Pelaksanaan</td><td>:</td><td><?= tgl_indo($d['tgl_kunjungan']); ?> (<?= substr($d['waktu_kunjungan'], 0, 5); ?> WITA)</td></tr>
            </table>

            <p>Demikian Surat Perintah Tugas ini diberikan untuk dapat dilaksanakan dengan penuh tanggung jawab, dan setelah selesai melaksanakan tugas agar menyampaikan laporan pelaksanaannya.</p>
        </div>

        <!-- ===== TEMPLATE AREA TANDA TANGAN: salin blok ini apa adanya ke surat lain ===== -->
        <div class="ttd-area">
            <p>Banjarmasin, <?= tgl_indo($d['tgl_spt']); ?></p>
            <p><strong><?= strtoupper($jabatan_sekwan); ?></strong></p>
            
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

    </div>

</body>
</html>