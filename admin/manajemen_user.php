<?php
// 1. Set Menu Aktif (Supaya dropdown Data Master terbuka)
$page = 'master'; 

// 2. Panggil Template
include 'template/header.php';
include 'template/sidebar.php';

// --- PROTEKSI HALAMAN ---
// Jika yang login BUKAN admin, tendang ke dashboard
if ($_SESSION['level'] != 'admin') {
    echo "<script>
            alert('Akses Ditolak! Anda bukan Administrator.');
            window.location='dashboard.php';
          </script>";
    exit; // Stop script agar konten di bawah tidak dimuat
}
// ------------------------

// ---------------------------------------------------------
// LOGIC PHP (CRUD USER)
// ---------------------------------------------------------

// A. TAMBAH USER BARU
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $user = mysqli_real_escape_string($koneksi, $_POST['username']);
    $pass = $_POST['password']; // Password disimpan biasa (sesuai request)
    $level = $_POST['level'];
    
    // Cek apakah username sudah ada?
    $cek = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$user'");
    if(mysqli_num_rows($cek) > 0){
        echo "<script>alert('Gagal! Username sudah digunakan.');</script>";
    } else {
        $query = "INSERT INTO admin (nama_pengguna, username, password, level) VALUES ('$nama', '$user', '$pass', '$level')";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('User Berhasil Ditambah!'); window.location='manajemen_user.php';</script>";
        }
    }
}

// B. EDIT USER
if (isset($_POST['edit'])) {
    $id = $_POST['id_admin'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $user = mysqli_real_escape_string($koneksi, $_POST['username']);
    $pass = $_POST['password'];
    $level = $_POST['level'];
    
    // Logika Password: Jika kolom password diisi, maka update password.
    // Jika kosong, berarti pakai password lama (jangan diubah).
    if(!empty($pass)){
        $query = "UPDATE admin SET nama_pengguna='$nama', username='$user', password='$pass', level='$level' WHERE id_admin='$id'";
    } else {
        $query = "UPDATE admin SET nama_pengguna='$nama', username='$user', level='$level' WHERE id_admin='$id'";
    }
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data User Diupdate!'); window.location='manajemen_user.php';</script>";
    }
}

// C. HAPUS USER
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    
    // Proteksi: Jangan biarkan admin menghapus akunnya sendiri saat sedang login
    if($id == $_SESSION['id_admin']){
        echo "<script>alert('BAHAYA: Anda tidak bisa menghapus akun yang sedang Anda gunakan!'); window.location='manajemen_user.php';</script>";
    } else {
        if (mysqli_query($koneksi, "DELETE FROM admin WHERE id_admin='$id'")) {
            echo "<script>alert('User Berhasil Dihapus!'); window.location='manajemen_user.php';</script>";
        }
    }
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Manajemen Pengguna (Admin)</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Data Master</li>
          <li class="breadcrumb-item">User</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Daftar Akun Admin & Petugas</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="ti ti-user-plus"></i> Tambah User
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered">
            <thead class="bg-light">
              <tr>
                <th width="5%">No</th>
                <th>Nama Pengguna</th>
                <th>Username</th>
                <th>Level Akses</th>
                <th class="text-center" width="15%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $query = mysqli_query($koneksi, "SELECT * FROM admin ORDER BY id_admin ASC");
              while ($d = mysqli_fetch_array($query)) {
              ?>
              <tr>
                <td><?= $no++; ?></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avtar avtar-s bg-light-primary text-primary me-2">
                            <i class="ti ti-user"></i>
                        </div>
                        <b><?= $d['nama_pengguna']; ?></b>
                    </div>
                </td>
                <td><?= $d['username']; ?></td>
                <td>
                    <?php if($d['level'] == 'admin'): ?>
                        <span class="badge bg-primary">Administrator</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Petugas Biasa</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $d['id_admin']; ?>">
                        <i class="ti ti-pencil"></i>
                    </button>
                    
                    <?php if($d['id_admin'] != $_SESSION['id_admin']): ?>
                    <a href="manajemen_user.php?aksi=hapus&id=<?= $d['id_admin']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?')">
                        <i class="ti ti-trash"></i>
                    </a>
                    <?php endif; ?>
                </td>
              </tr>

              <div class="modal fade" id="modalEdit<?= $d['id_admin']; ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Data User</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="id_admin" value="<?= $d['id_admin']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" value="<?= $d['nama_pengguna']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= $d['username']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="text" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                                <small class="text-muted">Isi hanya jika ingin mengganti password.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Level Akses</label>
                                <select name="level" class="form-select">
                                    <option value="admin" <?= ($d['level']=='admin')?'selected':''; ?>>Administrator (Full Akses)</option>
                                    <option value="petugas" <?= ($d['level']=='petugas')?'selected':''; ?>>Petugas (Terbatas)</option>
                                </select>
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
        <h5 class="modal-title">Tambah User Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Nama Lengkap</label>
                  <input type="text" name="nama" class="form-control" placeholder="Contoh: Staff Humas" required>
              </div>
              
              <div class="mb-3">
                  <label class="form-label">Username</label>
                  <input type="text" name="username" class="form-control" placeholder="Username untuk login" required>
              </div>
              
              <div class="mb-3">
                  <label class="form-label">Password</label>
                  <input type="text" name="password" class="form-control" placeholder="Password" required>
              </div>
              
              <div class="mb-3">
                  <label class="form-label">Level Akses</label>
                  <select name="level" class="form-select">
                      <option value="petugas">Petugas Biasa</option>
                      <option value="admin">Administrator</option>
                  </select>
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

<?php 
// 3. Panggil Template Footer
include 'template/footer.php'; 
?>