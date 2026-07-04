<?php
$page = 'laporan';
include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Cetak Laporan & Dokumen</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Laporan</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-4">
    <div class="card shadow h-100 border-dark">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0 text-white"><i class="ti ti-calendar-event me-2"></i>Laporan Kunjungan (Bulanan)</h5>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-4">Pilih periode bulan dan tahun untuk mencetak rekapitulasi data tamu dan jadwal kunjungan kerja.</p>
        
        <form action="cetak_laporan.php" method="GET" target="_blank">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Bulan</label>
                <select name="bulan" class="form-select border-dark" required>
                    <option value="">-- Pilih Bulan --</option>
                    <option value="01">Januari</option>
                    <option value="02">Februari</option>
                    <option value="03">Maret</option>
                    <option value="04">April</option>
                    <option value="05">Mei</option>
                    <option value="06">Juni</option>
                    <option value="07">Juli</option>
                    <option value="08">Agustus</option>
                    <option value="09">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Tahun</label>
                <select name="tahun" class="form-select border-dark" required>
                    <?php
                    $tahun_sekarang = date('Y');
                    for($i = 2024; $i <= $tahun_sekarang + 1; $i++){
                        // Auto-select tahun saat ini
                        $selected = ($i == $tahun_sekarang) ? 'selected' : '';
                        echo "<option value='$i' $selected>$i</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-dark btn-lg">
                    <i class="ti ti-printer me-2"></i> Cetak Rekap Kunjungan
                </button>
            </div>

        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <div class="card shadow h-100 border-dark">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0 text-white"><i class="ti ti-files me-2"></i>Laporan Master & Statistik</h5>
      </div>
      <div class="card-body">
         <p class="text-muted small mb-4">Pilih jenis dokumen master data atau statistik dashboard yang ingin Anda cetak secara keseluruhan:</p>
         
         <div class="d-grid gap-3">
             <a href="cetak_ruangan.php" target="_blank" class="btn btn-outline-dark d-flex justify-content-between align-items-center p-3 text-start">
                 <div>
                     <h6 class="mb-1 fw-bold">Cetak Data Ruangan</h6>
                     <small class="text-muted">Daftar ruangan & kapasitas di DPRD</small>
                 </div>
                 <i class="ti ti-door f-24"></i>
             </a>

             <a href="cetak_pejabat.php" target="_blank" class="btn btn-outline-dark d-flex justify-content-between align-items-center p-3 text-start">
                 <div>
                     <h6 class="mb-1 fw-bold">Cetak Daftar Pejabat Penerima Tamu</h6>
                     <small class="text-muted">Daftar penanggung jawab kunjungan (Tabel Pejabat)</small>
                 </div>
                 <i class="ti ti-users f-24"></i>
             </a>

             <a href="cetak_statistik.php" target="_blank" class="btn btn-outline-dark d-flex justify-content-between align-items-center p-3 text-start">
                 <div>
                     <h6 class="mb-1 fw-bold">Cetak Statistik Dashboard & Kepuasan</h6>
                     <small class="text-muted">Laporan statistik grafik dan hasil feedback tamu</small>
                 </div>
                 <i class="ti ti-chart-bar f-24"></i>
             </a>

             <a href="cetak_lap_batal.php" target="_blank" class="btn btn-outline-dark d-flex justify-content-between align-items-center p-3 text-start">
                 <div>
                     <h6 class="mb-1 fw-bold">Cetak Laporan Kunjungan Batal</h6>
                     <small class="text-muted">Rekap seluruh pengajuan kunjungan yang dibatalkan/ditolak</small>
                 </div>
                 <i class="ti ti-calendar-x f-24"></i>
             </a>
         </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12 mb-4">
    <div class="card shadow border-dark">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0 text-white"><i class="ti ti-chart-line me-2"></i>Laporan Rekapitulasi Kunjungan Per Periode</h5>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-4">Lihat jumlah total kunjungan per hari/minggu/bulan, tren kenaikan-penurunan tiap periode, dan perbandingan total kunjungan dengan periode sebelumnya.</p>

        <form action="cetak_rekap_periode.php" method="GET" target="_blank">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Jenis Periode</label>
                    <select name="tipe" class="form-select border-dark" required>
                        <option value="harian">Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan" selected>Bulanan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tanggal Mulai</label>
                    <input type="date" name="tgl_mulai" class="form-control border-dark" value="<?= date('Y-m-01'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tanggal Selesai</label>
                    <input type="date" name="tgl_selesai" class="form-control border-dark" value="<?= date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark btn-lg w-100">
                        <i class="ti ti-printer me-2"></i> Cetak Rekap Periode
                    </button>
                </div>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
      <div class="alert alert-secondary border-dark d-flex align-items-center shadow-sm">
          <i class="ti ti-info-circle f-24 me-3"></i>
          <div>
              <h6 class="mb-1 fw-bold">Informasi Pencetakan (Smart Guest)</h6>
              <p class="mb-0 small">Fitur ini digunakan untuk mencetak berbagai rekapitulasi data sebagai laporan pertanggungjawaban. Pastikan perangkat Anda sudah terhubung dengan printer, atau gunakan fitur <b>"Save as PDF"</b> (Simpan sebagai PDF) pada menu <i>Print Browser</i> Anda.</p>
          </div>
      </div>
  </div>
</div>

<?php include 'template/footer.php'; ?>