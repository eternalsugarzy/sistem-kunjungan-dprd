<?php
$page = 'master'; 
include 'template/header.php';
include 'template/sidebar.php';

// --- LOGIC PHP ---

// 1. TAMBAH
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_pj'];
    $nip = $_POST['nip'];
    $jabatan = $_POST['jabatan'];
    $hp = $_POST['no_hp'];
    
    $query = "INSERT INTO penanggung_jawab (nama_pj, nip, jabatan, no_hp) VALUES ('$nama', '$nip', '$jabatan', '$hp')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('PJ Berhasil Ditambah!'); window.location='master_pj.php';</script>";
    }
}

// 2. EDIT
if (isset($_POST['edit'])) {
    $id = $_POST['id_pj'];
    $nama = $_POST['nama_pj'];
    $nip = $_POST['nip'];
    $jabatan = $_POST['jabatan'];
    $hp = $_POST['no_hp'];
    
    $query = "UPDATE penanggung_jawab SET nama_pj='$nama', nip='$nip', jabatan='$jabatan', no_hp='$hp' WHERE id_pj='$id'";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('PJ Berhasil Diupdate!'); window.location='master_pj.php';</script>";
    }
}

// 3. HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    if (mysqli_query($koneksi, "DELETE FROM penanggung_jawab WHERE id_pj='$id'")) {
        echo "<script>alert('Data Dihapus!'); window.location='master_pj.php';</script>";
    }
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Data Penanggung Jawab (PJ)</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Data Master</li>
          <li class="breadcrumb-item">PJ</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Daftar Pejabat / Penerima Tamu</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="ti ti-plus"></i> Tambah PJ
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered">
            <thead class="bg-light">
              <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>NIP</th>
                <th>Jabatan</th>
                <th>No HP</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $query = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab ORDER BY nama_pj ASC");
              while ($d = mysqli_fetch_array($query)) {
              ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><b><?= $d['nama_pj']; ?></b></td>
                <td><?= $d['nip']; ?></td>
                <td><?= $d['jabatan']; ?></td>
                <td><?= $d['no_hp']; ?></td>
                <td class="text-center">
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $d['id_pj']; ?>">
                        <i class="ti ti-pencil"></i>
                    </button>
                    <a href="master_pj.php?aksi=hapus&id=<?= $d['id_pj']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">
                        <i class="ti ti-trash"></i>
                    </a>
                </td>
              </tr>

              <div class="modal fade" id="modalEdit<?= $d['id_pj']; ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Penanggung Jawab</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="id_pj" value="<?= $d['id_pj']; ?>">
                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_pj" class="form-control" value="<?= $d['nama_pj']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>NIP</label>
                                <input type="text" name="nip" class="form-control" value="<?= $d['nip']; ?>">
                            </div>
                            <div class="mb-3">
                                <label>Jabatan</label>
                                <input type="text" name="jabatan" class="form-control" value="<?= $d['jabatan']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>No HP</label>
                                <input type="text" name="no_hp" class="form-control" value="<?= $d['no_hp']; ?>">
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
        <h5 class="modal-title">Tambah PJ Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <div class="mb-3">
                  <label>Nama Lengkap</label>
                  <input type="text" name="nama_pj" class="form-control" placeholder="Contoh: Budi Santoso, S.H." required>
              </div>
              <div class="mb-3">
                  <label>NIP</label>
                  <input type="number" name="nip" class="form-control" placeholder="198..." >
              </div>
              <div class="mb-3">
                  <label>Jabatan</label>
                  <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Kabag Humas" required>
              </div>
              <div class="mb-3">
                  <label>No HP</label>
                  <input type="number" name="no_hp" class="form-control" placeholder="08...">
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