<?php
include '../koneksi.php';

// Ambil semua data pejabat / penanggung jawab
$query = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab ORDER BY nama_pj ASC");

// Array Nama Bulan Indonesia untuk tanggal cetak
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Pejabat Penerima Tamu</title>
    <style>
        /* CSS RESET & PRINT */
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            margin: 20px 40px;
            color: #000;
        }
        
        /* ===================================================================
           TEMPLATE KOP SURAT:
           Salin blok CSS & HTML kop ini apa adanya ke surat lain agar
           kop selalu konsisten.
        =================================================================== */
        .kop-container {
            width: 100%;
            border-bottom: 4px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            position: relative;
        }
        .kop-container::after {
            content: "";
            position: absolute;
            left: 0; right: 0; bottom: -3px;
            border-bottom: 1px solid #000;
        }
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-table td { vertical-align: middle; text-align: center; }
        .logo-img { width: 85px; height: auto; }
        .text-pemkot { font-size: 14pt; margin: 0; font-weight: normal; }
        .text-dprd { font-size: 18pt; margin: 5px 0; font-weight: bold; letter-spacing: 1px; }
        .text-alamat { font-size: 10pt; margin: 0; }

        /* JUDUL LAPORAN */
        .judul { text-align: center; margin-bottom: 30px; }
        .judul h4 { font-size: 14pt; margin: 0; text-decoration: underline; text-transform: uppercase; }

        /* TABEL DATA LAPORAN */
        .table-laporan { width: 100%; border-collapse: collapse; font-size: 11pt; margin-bottom: 30px; }
        .table-laporan th, .table-laporan td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }
        .table-laporan th { background-color: #e0e0e0; text-align: center; font-weight: bold; }
        
        /* ===================================================================
           TEMPLATE FOOTER TTD DINAMIS (dipakai ulang di semua surat resmi):
           Semua data pejabat (jabatan, goresan TTD, nama, pangkat/golongan,
           NIP) HARUS berasal dari variabel hasil query ke `penanggung_jawab`.
        =================================================================== */
        .ttd-area {
            float: right;
            margin-top: 30px;
            text-align: center;
            width: 280px;
        }
        .ttd-area p { margin: 3px 0; }
        .graphic-ttd-layer { height: 80px; display: flex; align-items: center; justify-content: center; margin: 5px 0; }
        .graphic-ttd-layer img { max-height: 75px; max-width: 160px; object-fit: contain; }
        .nama-ttd { font-weight: bold; text-decoration: underline; margin-top: 70px !important; }

        @media print { 
            .no-print { display: none !important; } 
            body { margin: 0; }
        }
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
    <!-- ===== AKHIR TEMPLATE KOP SURAT ===== -->

    <div class="judul">
        <h4>LAPORAN MASTER DATA PEJABAT PENERIMA TAMU</h4>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Nama Pejabat / NIP</th>
                <th width="20%">Pangkat / Golongan</th>
                <th width="25%">Jabatan</th>
                <th width="20%">Kontak (No. HP)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($query) > 0) {
                while($d = mysqli_fetch_array($query)){
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td>
                    <strong><?= htmlspecialchars($d['nama_pj']); ?></strong><br>
                    <small>NIP: <?= htmlspecialchars($d['nip'] ?? '-'); ?></small>
                </td>
                <td style="text-align: center;"><?= htmlspecialchars($d['pangkat_golongan'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($d['jabatan'] ?? '-'); ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($d['no_hp'] ?? '-'); ?></td>
            </tr>
            <?php 
                }
            } else {
                echo '<tr><td colspan="5" style="text-align: center; padding: 15px;">Data Pejabat belum tersedia.</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <!-- ===== TEMPLATE FOOTER TTD: salin blok ini apa adanya ke surat lain ===== -->
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
    <!-- ===== AKHIR TEMPLATE FOOTER TTD ===== -->
    
    <div style="clear: both;"></div>

</body>
</html>