<?php
// 1. Set Halaman Aktif untuk Sidebar
$page = 'data_kunjungan';

// 2. Panggil Template Header & Sidebar
include 'template/header.php';
include 'template/sidebar.php';

// ---------------------------------------------------------
// LOGIC PHP (HAPUS & SELESAI)
// ---------------------------------------------------------

// A. HAPUS DATA
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $id = mysqli_real_escape_string($koneksi, $id);
    
    // Ambil nama file dulu untuk dihapus dari folder uploads
    $cek_file = mysqli_query($koneksi, "SELECT file_surat_permohonan FROM kunjungan WHERE id_kunjungan='$id'");
    if(mysqli_num_rows($cek_file) > 0){
        $data_file = mysqli_fetch_assoc($cek_file);
        $path_file = "../uploads/" . $data_file['file_surat_permohonan'];
        
        // Hapus file fisik jika ada
        if (file_exists($path_file)) {
            unlink($path_file);
        }
    }
    
    // Hapus data di database
    $query_hapus = "DELETE FROM kunjungan WHERE id_kunjungan='$id'";
    if (mysqli_query($koneksi, $query_hapus)) {
        echo "<script>alert('Data Berhasil Dihapus!'); window.location='data_kunjungan.php';</script>";
    }
}

// B. TANDAI SELESAI
if (isset($_GET['aksi']) && $_GET['aksi'] == 'selesai') {
    $id = $_GET['id'];
    $id = mysqli_real_escape_string($koneksi, $id);
    
    $query_selesai = "UPDATE kunjungan SET status_kegiatan='selesai' WHERE id_kunjungan='$id'";
    mysqli_query($koneksi, $query_selesai);
    echo "<script>window.location='data_kunjungan.php';</script>";
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Data Semua Kunjungan</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Data Kunjungan</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header">
        <h5>Arsip Data Kunjungan</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered" id="tabelKunjungan">
            <thead class="bg-light">
              <tr>
                <th>No</th>
                <th>Kode & Tgl</th>
                <th>Instansi</th>
                <th>Status</th>
                <th>Lokasi & PJ</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              // JOIN TABEL AGAR NAMA RUANGAN & PJ MUNCUL
              $query = "SELECT kunjungan.*, ruangan.nama_ruangan, penanggung_jawab.nama_pj 
                        FROM kunjungan 
                        LEFT JOIN ruangan ON kunjungan.id_ruangan = ruangan.id_ruangan
                        LEFT JOIN penanggung_jawab ON kunjungan.id_pj = penanggung_jawab.id_pj
                        ORDER BY kunjungan.tgl_kunjungan DESC";
                        
              $result = mysqli_query($koneksi, $query);
              
              while ($d = mysqli_fetch_array($result)) {
              ?>
              <tr>
                <td><?= $no++; ?></td>
                <td>
                    <span class="badge bg-light-secondary text-dark border"><?= $d['kode_booking']; ?></span><br>
                    <small><?= date('d-m-Y', strtotime($d['tgl_kunjungan'])); ?></small>
                </td>
                <td>
                    <b><?= $d['nama_instansi_tamu']; ?></b><br>
                    <small class="text-muted"><?= $d['materi_kunjungan']; ?></small>
                </td>
                <td>
                    <?php 
                        if($d['status_kegiatan'] == 'pending') echo '<span class="badge bg-warning">Menunggu</span>';
                        elseif($d['status_kegiatan'] == 'dijadwalkan') echo '<span class="badge bg-primary">Dijadwalkan</span>';
                        elseif($d['status_kegiatan'] == 'selesai') echo '<span class="badge bg-success">Selesai</span>';
                        else echo '<span class="badge bg-danger">Batal/Ditolak</span>';
                    ?>
                </td>
                <td>
                    <?php if($d['status_kegiatan'] == 'dijadwalkan' || $d['status_kegiatan'] == 'selesai'): ?>
                        <small><b>Ruang:</b> <?= $d['nama_ruangan']; ?></small><br>
                        <small><b>PJ:</b> <?= $d['nama_pj']; ?></small>
                    <?php else: ?>
                        <small>-</small>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $d['id_kunjungan']; ?>" title="Detail">
                            <i class="ti ti-eye"></i>
                        </button>
                        
                        <?php if($d['status_kegiatan'] == 'dijadwalkan'): ?>
                        <a href="data_kunjungan.php?aksi=selesai&id=<?= $d['id_kunjungan']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Tandai kegiatan ini selesai?')" title="Selesai">
                            <i class="ti ti-check"></i>
                        </a>
                        <?php endif; ?>
                        
                        <a href="data_kunjungan.php?aksi=hapus&id=<?= $d['id_kunjungan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini secara permanen?')" title="Hapus">
                            <i class="ti ti-trash"></i>
                        </a>
                    </div>
                </td>
              </tr>

              <div class="modal fade" id="modalDetail<?= $d['id_kunjungan']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Detail Kunjungan: <?= $d['kode_booking']; ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Instansi:</strong> <br> <?= $d['nama_instansi_tamu']; ?></p>
                                <p><strong>Alamat:</strong> <br> <?= $d['alamat_instansi']; ?></p>
                                <p><strong>Email / HP:</strong> <br> <?= $d['email_pemohon']; ?> / <?= $d['no_hp'] ?? '-'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Waktu Kunjungan:</strong> <br> <?= date('d F Y', strtotime($d['tgl_kunjungan'])); ?> Pukul <?= $d['waktu_kunjungan']; ?></p>
                                <p><strong>Jumlah Peserta:</strong> <br> <?= $d['jumlah_peserta_rencana']; ?> Orang</p>
                                <p><strong>Tujuan:</strong> <br> <?= $d['materi_kunjungan']; ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="alert alert-light border">
                            <strong>Status:</strong> <?= strtoupper($d['status_kegiatan']); ?><br>
                            <strong>Ruangan:</strong> <?= $d['nama_ruangan'] ?? '-'; ?><br>
                            <strong>Penanggung Jawab:</strong> <?= $d['nama_pj'] ?? '-'; ?>
                        </div>
                        
                        <div class="mt-3">
                            <strong>Lampiran Surat:</strong><br>
                            <a href="../uploads/<?= $d['file_surat_permohonan']; ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="ti ti-download"></i> Lihat Surat Permohonan
                            </a>
                        </div>

                        <?php if($d['status_kegiatan'] == 'dijadwalkan' || $d['status_kegiatan'] == 'selesai'): ?>
                        <hr>
                        <h6 class="fw-bold mt-3"><i class="ti ti-printer me-2"></i>Cetak Dokumen Administrasi:</h6>
                        <div class="d-flex gap-2">
                            <a href="cetak_disposisi.php?id=<?= $d['id_kunjungan']; ?>" target="_blank" class="btn btn-warning btn-sm">
                                <i class="ti ti-file-description me-1"></i> Lembar Disposisi
                            </a>
                            <a href="cetak_surat.php?id=<?= $d['id_kunjungan']; ?>" target="_blank" class="btn btn-primary btn-sm">
                                <i class="ti ti-mail me-1"></i> Surat Balasan Resmi
                            </a>
                        </div>
                        <?php endif; ?>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                  </div>
                </div>
              </div>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// 3. Panggil Template Footer
include 'template/footer.php';
?>