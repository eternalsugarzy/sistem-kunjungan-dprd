<?php
$page = 'master'; // Supaya menu Data Master terbuka otomatis
include 'template/header.php';
include 'template/sidebar.php';

// --- LOGIC PHP ---

// 1. TAMBAH DATA
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_ruangan'];
    $lantai = $_POST['lantai'];
    $kapasitas = $_POST['kapasitas'];
    
    $query = "INSERT INTO ruangan (nama_ruangan, lantai, kapasitas) VALUES ('$nama', '$lantai', '$kapasitas')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data Berhasil Ditambah!'); window.location='master_ruangan.php';</script>";
    }
}

// 2. EDIT DATA
if (isset($_POST['edit'])) {
    $id = $_POST['id_ruangan'];
    $nama = $_POST['nama_ruangan'];
    $lantai = $_POST['lantai'];
    $kapasitas = $_POST['kapasitas'];
    
    $query = "UPDATE ruangan SET nama_ruangan='$nama', lantai='$lantai', kapasitas='$kapasitas' WHERE id_ruangan='$id'";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data Berhasil Diubah!'); window.location='master_ruangan.php';</script>";
    }
}

// 3. HAPUS DATA
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    if (mysqli_query($koneksi, "DELETE FROM ruangan WHERE id_ruangan='$id'")) {
        echo "<script>alert('Data Dihapus!'); window.location='master_ruangan.php';</script>";
    }
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Data Master Ruangan</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Data Master</li>
          <li class="breadcrumb-item">Ruangan</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Daftar Ruangan DPRD</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="ti ti-plus"></i> Tambah Ruangan
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered">
            <thead class="bg-light">
              <tr>
                <th>No</th>
                <th>Nama Ruangan</th>
                <th>Posisi Lantai</th>
                <th>Kapasitas</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $query = mysqli_query($koneksi, "SELECT * FROM ruangan ORDER BY nama_ruangan ASC");
              while ($d = mysqli_fetch_array($query)) {
              ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><b><?= $d['nama_ruangan']; ?></b></td>
                <td><?= $d['lantai']; ?></td>
                <td><?= $d['kapasitas']; ?> Orang</td>
                <td class="text-center">
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $d['id_ruangan']; ?>">
                        <i class="ti ti-pencil"></i>
                    </button>
                    <a href="master_ruangan.php?aksi=hapus&id=<?= $d['id_ruangan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">
                        <i class="ti ti-trash"></i>
                    </a>
                </td>
              </tr>

              <div class="modal fade" id="modalEdit<?= $d['id_ruangan']; ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Ruangan</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="id_ruangan" value="<?= $d['id_ruangan']; ?>">
                            <div class="mb-3">
                                <label>Nama Ruangan</label>
                                <input type="text" name="nama_ruangan" class="form-control" value="<?= $d['nama_ruangan']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Posisi Lantai</label>
                                <select name="lantai" class="form-select">
                                    <option value="Lantai 1" <?= ($d['lantai']=='Lantai 1')?'selected':''; ?>>Lantai 1</option>
                                    <option value="Lantai 2" <?= ($d['lantai']=='Lantai 2')?'selected':''; ?>>Lantai 2</option>
                                    <option value="Lantai 3" <?= ($d['lantai']=='Lantai 3')?'selected':''; ?>>Lantai 3</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Kapasitas (Orang)</label>
                                <input type="number" name="kapasitas" class="form-control" value="<?= $d['kapasitas']; ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
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

<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Ruangan Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <div class="mb-3">
                  <label>Nama Ruangan</label>
                  <input type="text" name="nama_ruangan" class="form-control" placeholder="Contoh: Ruang Komisi 1" required>
              </div>
              <div class="mb-3">
                  <label>Posisi Lantai</label>
                  <select name="lantai" class="form-select">
                      <option value="Lantai 1">Lantai 1</option>
                      <option value="Lantai 2">Lantai 2</option>
                      <option value="Lantai 3">Lantai 3</option>
                  </select>
              </div>
              <div class="mb-3">
                  <label>Kapasitas (Orang)</label>
                  <input type="number" name="kapasitas" class="form-control" placeholder="0" required>
              </div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include 'template/footer.php'; ?>