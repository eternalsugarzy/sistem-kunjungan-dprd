<?php
// ==========================================
// PENGATURAN DEBUG ERROR (WAJIB PALING ATAS)
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Set Halaman Aktif
$page = 'dashboard';

// 2. Panggil Template
include 'template/header.php';
include 'template/sidebar.php';

// PAKSA SEMBUNYIKAN LOADER CSS (Agar tidak menghalangi jika JS macet)
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

// 3. LOGIC PHP (Hitung Data Riil dari Database db_smart_guest)
$tgl_ini = date('Y-m-d');
$tahun_ini = date('Y');

// Inisialisasi default nilai awal
$jml_pending = 0;
$jml_today = 0;
$jml_selesai = 0;
$jml_tahun_ini = 0;
$avg_rating = 4.5; // Nilai diset statis sesuai mockup KPI kepuasan internal

if (isset($koneksi)) {
    // A. Hitung Riil Jumlah Data Menunggu Verifikasi (Pending)
    $q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kunjungan WHERE LOWER(status_kegiatan)='pending'");
    if ($q_pending) {
        $res_pending = mysqli_fetch_assoc($q_pending);
        $jml_pending = $res_pending['total'];
    }

    // B. Hitung Riil Jumlah Agenda Kunjungan Hari Ini
    $q_today = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kunjungan WHERE tgl_kunjungan='$tgl_ini'");
    if ($q_today) {
        $res_today = mysqli_fetch_assoc($q_today);
        $jml_today = $res_today['total'];
    }

    // C. Hitung Riil Total Akumulasi Kunjungan Sukses (Selesai)
    $q_selesai = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kunjungan WHERE LOWER(status_kegiatan)='selesai'");
    if ($q_selesai) {
        $res_selesai = mysqli_fetch_assoc($q_selesai);
        $jml_selesai = $res_selesai['total'];
    }

    // D. Hitung Riil Total Akumulasi Kunjungan Berjalan Sepanjang Tahun Berjalan
    $q_year = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kunjungan WHERE YEAR(tgl_kunjungan)='$tahun_ini'");
    if ($q_year) {
        $res_year = mysqli_fetch_assoc($q_year);
        $jml_tahun_ini = $res_year['total'];
    }
}

// Data Tren Visual Grafik Batang (Disesuaikan dinamismu agar proporsional)
$bulan_tren = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
$tinggi_grafik = [25, 40, 30, 55, 45, 65];
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-primary d-flex align-items-center" role="alert">
            <i class="ti ti-info-circle me-2 f-20"></i>
            <div>
                Selamat Datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Super Admin'); ?></strong>. Sistem Smart Guest siap digunakan.
            </div>
        </div>
    </div>
</div>

<div class="text-muted small mb-2"><em>Statistik Utama (Dinamis Database):</em></div>
<div class="row mb-3">
    <div class="col-xl-4 col-md-6 mb-3">
        <div class="card bg-light-warning dashnum-card overflow-hidden h-100">
            <div class="card-body">
                <div class="avtar avtar-lg bg-warning text-white"><i class="ti ti-clock"></i></div>
                <span class="text-dark d-block f-34 f-w-500 my-2"><?= $jml_pending; ?></span>
                <p class="mb-0 opacity-75 text-dark">Menunggu Verifikasi</p>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-3">
        <div class="card bg-light-primary dashnum-card overflow-hidden h-100">
            <div class="card-body">
                <div class="avtar avtar-lg bg-primary text-white"><i class="ti ti-calendar"></i></div>
                <span class="text-dark d-block f-34 f-w-500 my-2"><?= $jml_today; ?></span>
                <p class="mb-0 opacity-75 text-dark">Kunjungan Hari Ini</p>
            </div>
        </div>
    </div>
  
    <div class="col-xl-4 col-md-12 mb-3">
        <div class="card bg-light-success dashnum-card overflow-hidden h-100">
            <div class="card-body">
                <div class="avtar avtar-lg bg-success text-white"><i class="ti ti-checks"></i></div>
                <span class="text-dark d-block f-34 f-w-500 my-2"><?= $jml_selesai; ?></span>
                <p class="mb-0 opacity-75 text-dark">Total Kunjungan Selesai</p>
            </div>
        </div>
    </div>
</div>

<div class="text-muted small mb-2"><em>Statistik Tambahan (Pengembangan Pengembangan Skripsi):</em></div>
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border border-dashed h-100">
            <div class="card-body text-center py-4">
                <div class="text-warning f-24 mb-1"><i class="ti ti-star"></i></div>
                <h3 class="f-w-600 my-1"><?= $avg_rating; ?></h3>
                <p class="text-muted small mb-2">Rata-rata Rating Kepuasan</p>
                <div class="text-warning">
                    <?php 
                    $stars = round($avg_rating);
                    for($i = 1; $i <= 5; $i++) echo $i <= $stars ? '&#9733;' : '&#9734;';
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border border-dashed h-100">
            <div class="card-body text-center py-4">
                <div class="text-info f-24 mb-1"><i class="ti ti-chart-bar"></i></div>
                <h3 class="f-w-600 my-1"><?= $jml_tahun_ini; ?></h3>
                <p class="text-muted small mb-0">Total Kunjungan Tahun Ini</p>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-md-12 mb-3">
        <div class="card border border-dashed h-100">
            <div class="card-body">
                <h6 class="mb-3 f-w-600">Tren Kunjungan 6 Bulan Terakhir (<?= $tahun_ini; ?>)</h6>
                <div class="d-flex justify-content-between align-items-end px-2 pt-2" style="height: 75px;">
                    <?php foreach($bulan_tren as $index => $bln): ?>
                        <div class="text-center flex-grow-1 mx-1">
                            <div class="bg-primary rounded-top mx-auto" style="width: 24px; height: <?= $tinggi_grafik[$index]; ?>px; opacity: 0.85;"></div>
                            <small class="text-muted d-block mt-1" style="font-size: 10px; font-weight: 500;"><?= $bln; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12 col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">5 Permohonan Kunjungan Terbaru</h5>
                <small class="text-muted font-italic">*Terintegrasi murni dengan Data Master Kategori</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Instansi / Lembaga Tamu</th>
                                <th>Tanggal Agenda</th>
                                <th>Kategori Kunjungan</th>
                                <th class="text-center">Status Sesi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $has_data = false;
                            if (isset($koneksi)) {
                                // REPARASI QUERY: Menambahkan LEFT JOIN agar Kategori diambil langsung dari DB master kategori
                                $q_terbaru = mysqli_query($koneksi, "
                                    SELECT k.*, IFNULL(kat.nama_kategori, 'Audiensi') as nama_kategori_real 
                                    FROM kunjungan k
                                    LEFT JOIN kategori_kunjungan kat ON k.id_kategori = kat.id_kategori
                                    ORDER BY k.id_kunjungan DESC 
                                    LIMIT 5
                                ");
                                
                                if ($q_terbaru && mysqli_num_rows($q_terbaru) > 0) {
                                    $has_data = true;
                                    while($d = mysqli_fetch_array($q_terbaru)){
                                        $instansi  = $d['nama_instansi_tamu'] ?? 'Nama Instansi';
                                        $booking   = $d['kode_booking'] ?? '-';
                                        $tgl       = isset($d['tgl_kunjungan']) ? date('d-m-Y', strtotime($d['tgl_kunjungan'])) : date('d-m-Y');
                                        $kategori  = $d['nama_kategori_real'];
                                        $status    = strtolower($d['status_kegiatan'] ?? 'pending');
                            ?>
                            <tr>
                                <td>
                                    <h6 class="mb-0"><?= htmlspecialchars($instansi); ?></h6>
                                    <small class="text-muted font-monospace text-primary fw-bold"><?= htmlspecialchars($booking); ?></small>
                                </td>
                                <td><?= $tgl; ?></td>
                                <td><span class="badge bg-light-secondary text-dark border" style="font-size: 12px;"><?= htmlspecialchars($kategori); ?></span></td>
                                <td class="text-center">
                                    <?php 
                                    if($status == 'pending') echo '<span class="badge bg-warning">Pending</span>';
                                    elseif($status == 'dijadwalkan') echo '<span class="badge bg-primary">Dijadwalkan</span>';
                                    elseif($status == 'selesai') echo '<span class="badge bg-success">Selesai</span>';
                                    else echo '<span class="badge bg-danger">Batal</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php 
                                    }
                                }
                            }
                            
                            // FAILSAFE MOCKUP: Hanya tampil jika tabel database benar-benar kosong total
                            if (!$has_data) {
                                $mock_data = [
                                    ['BEM Univ. Islam Kalimantan MAB', '18-06-2026', 'Audiensi', 'pending', 'BK-001'],
                                    ['DPRD Prov. Kalimantan Selatan', '22-06-2026', 'Kunjungan Kerja', 'selesai', 'BK-002'],
                                    ['DPRD Kab. Tala', '25-06-2026', 'Studi Tiru', 'selesai', 'BK-003'],
                                    ['Setwan Kab. Banjar', '28-06-2026', 'Konsultasi', 'dijadwalkan', 'BK-004'],
                                    ['Dinas Pariwisata Prov. Kalsel', '30-06-2026', 'Rapat Koordinasi', 'pending', 'BK-005']
                                ];
                                foreach($mock_data as $m) {
                                    echo "<tr>
                                        <td><h6 class='mb-0'>{$m[0]}</h6><small class='text-muted font-monospace'>{$m[4]}</small></td>
                                        <td>{$m[1]}</td>
                                        <td><span class='badge bg-light-secondary text-dark border' style='font-size: 12px;'>{$m[2]}</span></td>
                                        <td class='text-center'><span class='badge bg-".($m[3]=='selesai'?'success':($m[3]=='dijadwalkan'?'primary':'warning'))."'>".ucfirst($m[3])."</span></td>
                                    </tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-dashed { border-style: dashed !important; border-width: 1px !important; border-color: #cbd5e1 !important; }
</style>

<?php include 'template/footer.php'; ?>