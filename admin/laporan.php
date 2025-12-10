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
          <h5 class="m-b-10">Cetak Laporan</h5>
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
  <div class="col-md-6">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0 text-white"><i class="ti ti-printer me-2"></i>Filter Laporan Kunjungan</h5>
      </div>
      <div class="card-body">
        <form action="cetak_laporan.php" method="GET" target="_blank">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Bulan</label>
                <select name="bulan" class="form-select" required>
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
                <select name="tahun" class="form-select" required>
                    <?php
                    $tahun_sekarang = date('Y');
                    for($i = 2024; $i <= $tahun_sekarang + 1; $i++){
                        echo "<option value='$i'>$i</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="ti ti-printer me-2"></i> Cetak Laporan
                </button>
            </div>

        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
      <div class="alert alert-info">
          <h5><i class="ti ti-info-circle me-2"></i>Informasi</h5>
          <p>Fitur ini digunakan untuk mencetak rekapitulasi data kunjungan per bulan sebagai laporan pertanggungjawaban.</p>
          <hr>
          <p class="mb-0">Pastikan data kunjungan sudah diverifikasi dan diselesaikan agar masuk dalam laporan.</p>
      </div>
  </div>
</div>

<?php include 'template/footer.php'; ?>