<?php
$page = 'buku_tamu';
include 'template/header.php';
include 'template/sidebar.php';

// ---------------------------------------------------------
// LOGIC PHP
// ---------------------------------------------------------

// 1. TAMBAH PESERTA
if (isset($_POST['tambah_peserta'])) {
    $id_kunjungan = $_POST['id_kunjungan'];
    
    // Ambil data input teks
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_peserta']);
    $jabatan  = mysqli_real_escape_string($koneksi, $_POST['jabatan_peserta']);
    $hp       = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    
    // Ambil instansi otomatis
    $cek_kunjungan = mysqli_query($koneksi, "SELECT nama_instansi_tamu FROM kunjungan WHERE id_kunjungan='$id_kunjungan'");
    $data_k = mysqli_fetch_assoc($cek_kunjungan);
    $instansi = $data_k['nama_instansi_tamu'];

    // --- PROSES UPLOAD TTD ---
    $nama_file_ttd = "";
    
    if(!empty($_FILES['ttd']['name'])){
        $file_name = $_FILES['ttd']['name'];
        $file_tmp  = $_FILES['ttd']['tmp_name'];
        $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
        
        // Buat folder khusus TTD jika belum ada
        if (!file_exists('../uploads/ttd')) {
            mkdir('../uploads/ttd', 0777, true);
        }

        // Rename agar unik
        $nama_file_ttd = "TTD_" . time() . "_" . rand(100,999) . "." . $ext;
        $target_file   = "../uploads/ttd/" . $nama_file_ttd;

        // Pindahkan file
        move_uploaded_file($file_tmp, $target_file);
    }
    // -------------------------

    // Simpan ke database
    $query = "INSERT INTO buku_tamu 
              (id_kunjungan, nama_peserta, jabatan_peserta, no_hp, asal_instansi, tanda_tangan, waktu_hadir) 
              VALUES 
              ('$id_kunjungan', '$nama', '$jabatan', '$hp', '$instansi', '$nama_file_ttd', NOW())";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>window.location='buku_tamu.php?id=$id_kunjungan';</script>";
    } else {
        echo "<script>alert('GAGAL: " . mysqli_error($koneksi) . "');</script>";
    }
}

// 2. HAPUS PESERTA
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id_tamu = $_GET['id_tamu'];
    $id_kunjungan = $_GET['id_kunjungan'];
    
    // Hapus file fisik TTD dulu agar hemat penyimpanan
    $q_file = mysqli_query($koneksi, "SELECT tanda_tangan FROM buku_tamu WHERE id_tamu='$id_tamu'");
    $data_file = mysqli_fetch_assoc($q_file);
    if(!empty($data_file['tanda_tangan'])){
        $path = "../uploads/ttd/" . $data_file['tanda_tangan'];
        if(file_exists($path)){
            unlink($path);
        }
    }

    mysqli_query($koneksi, "DELETE FROM buku_tamu WHERE id_tamu='$id_tamu'");
    echo "<script>window.location='buku_tamu.php?id=$id_kunjungan';</script>";
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Buku Tamu Digital</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Buku Tamu</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php if (!isset($_GET['id'])) { ?>

    <div class="row">
      <div class="col-sm-12">
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i> Pilih jadwal kunjungan yang aktif hari ini.
        </div>
        <div class="card">
          <div class="card-header">
            <h5>Jadwal Kunjungan Siap Laksana</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover table-bordered">
                <thead class="bg-light">
                  <tr>
                    <th>Tgl & Jam</th>
                    <th>Kode Booking</th>
                    <th>Instansi Tamu</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $query = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE status_kegiatan='dijadwalkan' ORDER BY tgl_kunjungan DESC");
                  if (mysqli_num_rows($query) > 0) {
                      while ($d = mysqli_fetch_array($query)) {
                  ?>
                  <tr>
                    <td>
                        <?= date('d-m-Y', strtotime($d['tgl_kunjungan'])); ?><br>
                        <span class="badge bg-light-secondary text-dark"><?= $d['waktu_kunjungan']; ?></span>
                    </td>
                    <td><?= $d['kode_booking']; ?></td>
                    <td>
                        <b><?= $d['nama_instansi_tamu']; ?></b><br>
                        <small class="text-muted"><?= $d['materi_kunjungan']; ?></small>
                    </td>
                    <td><span class="badge bg-primary">Dijadwalkan</span></td>
                    <td class="text-center">
                        <a href="buku_tamu.php?id=<?= $d['id_kunjungan']; ?>" class="btn btn-primary btn-sm">
                            <i class="ti ti-pencil-plus"></i> Isi Buku Tamu
                        </a>
                    </td>
                  </tr>
                  <?php 
                      }
                  } else {
                      echo '<tr><td colspan="5" class="text-center text-muted">Tidak ada jadwal aktif.</td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php } else { 
    // MODE 2: FORM INPUT PESERTA
    $id_kunjungan = $_GET['id'];
    $q_info = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE id_kunjungan='$id_kunjungan'");
    $info = mysqli_fetch_array($q_info);
?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="text-white mb-0">Input Peserta Hadir</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 border-bottom pb-2">
                        <small class="text-muted">Instansi:</small><br>
                        <strong><?= $info['nama_instansi_tamu']; ?></strong>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_kunjungan" value="<?= $id_kunjungan; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Peserta</label>
                            <input type="text" name="nama_peserta" class="form-control" placeholder="Nama Lengkap" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan_peserta" class="form-control" placeholder="Contoh: Anggota / Staf" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor HP</label>
                            <input type="text" name="no_hp" class="form-control" placeholder="08..." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload Tanda Tangan (Foto/Scan)</label>
                            <input type="file" name="ttd" class="form-control" accept="image/*">
                            <small class="text-muted">Format: JPG/PNG</small>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="tambah_peserta" class="btn btn-success">
                                <i class="ti ti-plus"></i> Tambahkan
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <a href="buku_tamu.php" class="btn btn-outline-secondary btn-sm w-100">Kembali ke Daftar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Daftar Hadir Real-time</h5>
                    
                    <a href="cetak_absensi.php?id=<?= $id_kunjungan; ?>" target="_blank" class="btn btn-secondary btn-sm">
                        <i class="ti ti-printer"></i> Cetak Absensi
                    </a>
                    
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama & Jabatan</th>
                                    <th>No HP</th>
                                    <th>TTD</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $q_tamu = mysqli_query($koneksi, "SELECT * FROM buku_tamu WHERE id_kunjungan='$id_kunjungan' ORDER BY id_tamu DESC");
                                while($t = mysqli_fetch_array($q_tamu)){
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <span class="fw-bold"><?= $t['nama_peserta']; ?></span><br>
                                        <small class="text-muted"><?= $t['jabatan_peserta']; ?></small>
                                    </td>
                                    <td><?= $t['no_hp']; ?></td>
                                    <td>
                                        <?php if(!empty($t['tanda_tangan'])): ?>
                                            <img src="../uploads/ttd/<?= $t['tanda_tangan']; ?>" alt="TTD" style="height: 40px; border: 1px solid #ccc; padding: 2px;">
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="buku_tamu.php?aksi=hapus&id_tamu=<?= $t['id_tamu']; ?>&id_kunjungan=<?= $id_kunjungan; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus nama ini?')">
                                            <i class="ti ti-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                                
                                <?php if(mysqli_num_rows($q_tamu) == 0): ?>
                                    <tr><td colspan="5" class="text-center text-muted">Belum ada peserta yang diinput.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php } ?>

<?php include 'template/footer.php'; ?>