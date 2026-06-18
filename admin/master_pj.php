<?php
// ==========================================
// PENGATURAN DEBUG ERROR (WAJIB PALING ATAS)
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'master'; 
include 'template/header.php';
include 'template/sidebar.php';

// PAKSA SEMBUNYIKAN LOADER CSS
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

// ---------------------------------------------------------
// LOGIC PHP (CRUD & PROSES FILE TTD)
// ---------------------------------------------------------

// PERSIAPAN FOLDER PENYIMPANAN
$target_dir = "../uploads/ttd/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// 1. ACTION: TAMBAH DATA PJ
if (isset($_POST['tambah'])) {
    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama_pj']);
    $nip     = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $pangkat = mysqli_real_escape_string($koneksi, $_POST['pangkat_golongan']); // Tangkap input pangkat
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $hp      = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $nama_file_ttd = "";

    // A. Proses TTD dari Canvas Pad
    if (!empty($_POST['ttd_canvas'])) {
        $img_data = $_POST['ttd_canvas'];
        $img_data = str_replace('data:image/png;base64,', '', $img_data);
        $img_data = str_replace(' ', '+', $img_data);
        $data_decode = base64_decode($img_data);
        
        $nama_file_ttd = "PJ_TTD_C_" . time() . "_" . rand(100,999) . ".png";
        file_put_contents($target_dir . $nama_file_ttd, $data_decode);
    } 
    // B. Proses TTD dari Upload File Manual
    elseif (!empty($_FILES['ttd_file']['name'])) {
        $file_name = $_FILES['ttd_file']['name'];
        $file_tmp  = $_FILES['ttd_file']['tmp_name'];
        $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
        
        $nama_file_ttd = "PJ_TTD_F_" . time() . "_" . rand(100,999) . "." . $ext;
        move_uploaded_file($file_tmp, $target_dir . $nama_file_ttd);
    }
    
    // Insert beserta Pangkat Golongan
    $query = "INSERT INTO penanggung_jawab (nama_pj, nip, pangkat_golongan, jabatan, no_hp, file_ttd) VALUES ('$nama', '$nip', '$pangkat', '$jabatan', '$hp', '$nama_file_ttd')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('PJ Berhasil Ditambah!'); window.location='master_pj.php';</script>";
    }
}

// 2. LOGIC PHP: EDIT / UPDATE DATA PJ (PERBAIKAN FITUR PENGAMAN TTD)
if (isset($_POST['edit'])) {
    $id      = mysqli_real_escape_string($koneksi, $_POST['id_pj']);
    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama_pj']);
    $nip     = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $pangkat = mysqli_real_escape_string($koneksi, $_POST['pangkat_golongan']); // Tangkap update pangkat
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $hp      = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    
    // Ambil data TTD lama dari database sebagai cadangan jika tidak diubah
    $q_old = mysqli_query($koneksi, "SELECT file_ttd FROM penanggung_jawab WHERE id_pj='$id'");
    $row_old = mysqli_fetch_assoc($q_old);
    $nama_file_ttd = $row_old['file_ttd'] ?? '';

    // A. JIKA ADMIN MERUBAH TTD PAKAI CANVAS PAD
    if (!empty($_POST['ttd_canvas_edit'])) {
        // Simpan langsung string Base64 canvas baru ke database
        $nama_file_ttd = mysqli_real_escape_string($koneksi, $_POST['ttd_canvas_edit']);
    } 
    // B. JIKA ADMIN MERUBAH TTD PAKAI UPLOAD FILE MANUAL (.PNG/.JPG)
    elseif (!empty($_FILES['ttd_file_edit']['name'])) {
        $file_name = $_FILES['ttd_file_edit']['name'];
        $file_tmp  = $_FILES['ttd_file_edit']['tmp_name'];
        $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
        
        $nama_file_ttd = "PJ_TTD_F_" . time() . "_" . rand(100,999) . "." . $ext;
        move_uploaded_file($file_tmp, "../uploads/ttd/" . $nama_file_ttd);
    }
    
    // Jalankan Query Update beserta Pangkat Golongan
    $query = "UPDATE penanggung_jawab SET 
                nama_pj='$nama', 
                nip='$nip', 
                pangkat_golongan='$pangkat',
                jabatan='$jabatan', 
                no_hp='$hp', 
                file_ttd='$nama_file_ttd' 
              WHERE id_pj='$id'";
              
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('PJ Berhasil Diupdate!'); window.location='master_pj.php';</script>";
    } else {
        echo "<script>alert('Gagal Update: " . mysqli_error($koneksi) . "');</script>";
    }
}

// 3. ACTION: HAPUS DATA PJ
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    $q_file = mysqli_query($koneksi, "SELECT file_ttd FROM penanggung_jawab WHERE id_pj='$id'");
    $data_file = mysqli_fetch_assoc($q_file);
    if($data_file && !empty($data_file['file_ttd'])){
        $path = $target_dir . $data_file['file_ttd'];
        if(file_exists($path)) unlink($path);
    }

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
          <h5 class="m-b-5">Data Penanggung Jawab (PJ)</h5>
        </div>
        <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item text-muted">Data Master</li>
          <li class="breadcrumb-item text-muted">PJ</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card shadow-sm border-dark">
      <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white">Daftar Pejabat / Penerima Tamu</h5>
        <button class="btn btn-light text-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="ti ti-plus me-1"></i> Tambah PJ
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
              <tr>
                <th width="5%" class="text-center">No</th>
                <th width="22%">Nama & Gelar</th>
                <th width="17%">Jabatan</th>
                <th width="15%">Pangkat / Gol.</th>
                <th width="13%">NIP</th>
                <th width="10%">No HP</th>
                <th width="10%" class="text-center">TTE</th>
                <th class="text-center" width="8%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $query = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab ORDER BY nama_pj ASC");
              if (mysqli_num_rows($query) > 0) {
                  while ($d = mysqli_fetch_array($query)) {
              ?>
              <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td><b><?= htmlspecialchars($d['nama_pj']); ?></b></td>
                <td><?= htmlspecialchars($d['jabatan']); ?></td>
                <td><?= htmlspecialchars($d['pangkat_golongan'] ?? '-'); ?></td>
                <td><span class="font-monospace text-muted"><?= htmlspecialchars($d['nip'] ?: '-'); ?></span></td>
                <td><?= htmlspecialchars($d['no_hp'] ?: '-'); ?></td>
                <td class="text-center">
    <?php if(!empty($d['file_ttd'])): ?>
        <?php 
        // Cek apakah data yang tersimpan adalah teks enkripsi Base64 dari Canvas
        if (strpos($d['file_ttd'], 'data:image') !== false || substr($d['file_ttd'], 0, 4) === 'data') {
            echo '<img src="' . $d['file_ttd'] . '" alt="TTD Canvas" style="height: 45px; max-width: 100px; object-fit: contain; border: 1px dashed #ccc; padding: 2px; background: #fff;">';
        } else {
            // Jika file gambar biasa hasil upload file
            echo '<img src="uploads/ttd/' . $d['file_ttd'] . '" onerror="this.src=\'../uploads/ttd/' . $d['file_ttd'] . '\';" alt="TTD File" style="height: 45px; max-width: 100px; object-fit: contain; border: 1px dashed #ccc; padding: 2px; background: #fff;">';
        }
        ?>
    <?php else: ?>
        <span class="badge bg-light text-muted border">Belum ada</span>
    <?php endif; ?>
                </td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $d['id_pj']; ?>">
                            <i class="ti ti-pencil"></i>
                        </button>
                        <a href="master_pj.php?aksi=hapus&id=<?= $d['id_pj']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">
                            <i class="ti ti-trash"></i>
                        </a>
                    </div>
                </td>
              </tr>

              <div class="modal fade" id="modalEdit<?= $d['id_pj']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                      <h5 class="modal-title fw-bold text-white">Edit Penanggung Jawab</h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="form-edit-pj">
                        <div class="modal-body">
                            <input type="hidden" name="id_pj" value="<?= $d['id_pj']; ?>">
                            
                            <div class="mb-2">
                                <label class="form-label mb-1 fw-bold">Nama Lengkap & Gelar *</label>
                                <input type="text" name="nama_pj" class="form-control form-control-sm border-dark" value="<?= htmlspecialchars($d['nama_pj']); ?>" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label mb-1 fw-bold">Jabatan Struktural *</label>
                                <input type="text" name="jabatan" class="form-control form-control-sm border-dark" value="<?= htmlspecialchars($d['jabatan']); ?>" required>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="form-label mb-1 fw-bold">Pangkat / Golongan *</label>
                                    <input type="text" name="pangkat_golongan" class="form-control form-control-sm border-dark" placeholder="Cth: IV/c" value="<?= htmlspecialchars($d['pangkat_golongan'] ?? ''); ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1 fw-bold">NIP</label>
                                    <input type="text" name="nip" class="form-control form-control-sm border-dark" value="<?= htmlspecialchars($d['nip']); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label mb-1 fw-bold">No HP</label>
                                <input type="text" name="no_hp" class="form-control form-control-sm border-dark" value="<?= htmlspecialchars($d['no_hp']); ?>">
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size:12px;">Perbarui Spesimen TTD (Biarkan kosong jika tidak diganti)</label>
                                <ul class="nav nav-tabs tabs-mini mb-2" role="tablist">
                                    <li class="nav-item">
                                        <button type="button" class="nav-link active py-1 btn-draw-tab-edit" data-bs-toggle="tab" data-bs-target="#draw-panel-edit<?= $d['id_pj']; ?>">[-] Gambar</button>
                                    </li>
                                    <li class="nav-item">
                                        <button type="button" class="nav-link py-1" data-bs-toggle="tab" data-bs-target="#upload-panel-edit<?= $d['id_pj']; ?>">[↑] Upload</button>
                                    </li>
                                </ul>
                                <div class="tab-content border border-dark rounded bg-white p-2" style="min-height: 125px;">
                                    <div class="tab-pane fade show active text-center" id="draw-panel-edit<?= $d['id_pj']; ?>" role="tabpanel">
                                        <canvas class="signature-pad-edit border border-secondary rounded bg-light" style="width: 100%; height: 95px; cursor: crosshair;"></canvas>
                                        <button type="button" class="btn btn-sm btn-light border text-danger py-0 px-2 mt-1 clear-btn-edit" style="font-size:10px;">[X] Hapus</button>
                                        <input type="hidden" name="ttd_canvas_edit" class="ttd_canvas_input_edit">
                                    </div>
                                    <div class="tab-pane fade" id="upload-panel-edit<?= $d['id_pj']; ?>" role="tabpanel">
                                        <input type="file" name="ttd_file_edit" class="form-control form-control-sm" accept="image/*">
                                        <small class="text-muted d-block mt-1" style="font-size:10px;">Format: PNG/JPG (Transparan direkomendasikan)</small>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="edit" class="btn btn-dark btn-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                  </div>
                </div>
              </div>
              <?php 
                  }
              } else {
                  echo '<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data penanggung jawab.</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold text-white">Tambah PJ Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data" id="formTambahPJ">
          <div class="modal-body">
              
              <div class="mb-2">
                  <label class="form-label mb-1 fw-bold">Nama Lengkap & Gelar *</label>
                  <input type="text" name="nama_pj" class="form-control form-control-sm border-dark" placeholder="Contoh: Iwan Fitriady, S.H., M.H." required>
              </div>

              <div class="mb-2">
                  <label class="form-label mb-1 fw-bold">Jabatan Struktural *</label>
                  <input type="text" name="jabatan" class="form-control form-control-sm border-dark" placeholder="Contoh: Sekretaris DPRD" required>
              </div>

              <div class="row mb-2">
                  <div class="col-6">
                      <label class="form-label mb-1 fw-bold">Pangkat / Golongan *</label>
                      <input type="text" name="pangkat_golongan" class="form-control form-control-sm border-dark" placeholder="Contoh: Pembina Utama Muda" required>
                  </div>
                  <div class="col-6">
                      <label class="form-label mb-1 fw-bold">NIP</label>
                      <input type="text" name="nip" class="form-control form-control-sm border-dark" placeholder="198...">
                  </div>
              </div>
              
              <div class="mb-3">
                  <label class="form-label mb-1 fw-bold">No HP</label>
                  <input type="text" name="no_hp" class="form-control form-control-sm border-dark" placeholder="08...">
              </div>

              <div class="mb-2">
                  <label class="form-label fw-bold text-dark mb-1" style="font-size:12px;">Spesimen Tanda Tangan (TTE) *</label>
                  <ul class="nav nav-tabs tabs-mini mb-2" role="tablist">
                      <li class="nav-item">
                          <button type="button" class="nav-link active py-1" data-bs-toggle="tab" data-bs-target="#draw-panel-add" type="button" role="tab">[-] Gambar</button>
                      </li>
                      <li class="nav-item">
                          <button type="button" class="nav-link py-1" data-bs-toggle="tab" data-bs-target="#upload-panel-add" type="button" role="tab">[↑] Upload</button>
                      </li>
                  </ul>
                  <div class="tab-content border border-dark rounded bg-white p-2" style="min-height: 125px;">
                      <div class="tab-pane fade show active text-center" id="draw-panel-add" role="tabpanel">
                          <canvas id="signature-pad-add" class="border border-secondary rounded bg-light" style="width: 100%; height: 95px; cursor: crosshair;"></canvas>
                          <button type="button" id="clear-btn-add" class="btn btn-sm btn-light border text-danger py-0 px-2 mt-1" style="font-size:10px;">[X] Hapus</button>
                          <input type="hidden" name="ttd_canvas" id="ttd_canvas_input_add">
                      </div>
                      <div class="tab-pane fade" id="upload-panel-add" role="tabpanel">
                          <input type="file" name="ttd_file" class="form-control form-control-sm" accept="image/*">
                          <small class="text-muted d-block mt-1" style="font-size:10px;">Format: PNG/JPG (Transparan direkomendasikan)</small>
                      </div>
                  </div>
              </div>

          </div>
          <div class="modal-footer bg-light">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
              <button type="submit" name="tambah" class="btn btn-dark btn-sm">Simpan Data</button>
          </div>
      </form>
    </div>
  </div>
</div>

<style>
.tabs-mini .nav-link { font-size: 11px; font-family: monospace; color: #555; background: #f8f9fa; border: 1px solid #ced4da; margin-right: 3px; }
.tabs-mini .nav-link.active { background: #212529 !important; color: #fff !important; border-color: #212529; }
</style>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
    
    // Fungsi Utama Inisialisasi Canvas Pad
    function initSignaturePad(canvas, clearBtn, hiddenInput) {
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        // Atur paksa dimensi internal mengikuti ukuran aslinya di layar
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        
        // Setelan kuas gambar
        ctx.strokeStyle = "#0f172a"; 
        ctx.lineWidth = 3; 
        ctx.lineCap = "round";

        let drawing = false;

        function getPos(e) {
            let rect = canvas.getBoundingClientRect();
            return { 
                x: (e.clientX || e.touches[0].clientX) - rect.left, 
                y: (e.clientY || e.touches[0].clientY) - rect.top 
            };
        }

        // Event Mouse
        canvas.addEventListener('mousedown', (e) => { drawing = true; let p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
        canvas.addEventListener('mousemove', (e) => { if(!drawing) return; let p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
        window.addEventListener('mouseup', () => drawing = false);

        // Event Touchscreen HP
        canvas.addEventListener('touchstart', (e) => { drawing = true; let p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); e.preventDefault(); });
        canvas.addEventListener('touchmove', (e) => { if(!drawing) return; let p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); e.preventDefault(); });
        window.addEventListener('touchend', () => drawing = false);

        // Tombol hapus coretan di canvas
        clearBtn.addEventListener('click', () => { 
            ctx.clearRect(0, 0, canvas.width, canvas.height); 
            hiddenInput.value = ""; 
        });
    }

    // I. ENGINE UNTUK MODAL TAMBAH PJ
    const modalTambahEl = document.getElementById('modalTambah');
    if (modalTambahEl) {
        modalTambahEl.addEventListener('shown.bs.modal', function () {
            const canvasAdd = document.getElementById('signature-pad-add');
            const clearBtnAdd = document.getElementById('clear-btn-add');
            const ttdInputAdd = document.getElementById('ttd_canvas_input_add');
            initSignaturePad(canvasAdd, clearBtnAdd, ttdInputAdd);
        });
    }

    const formAdd = document.getElementById('formTambahPJ');
    if(formAdd) {
        formAdd.addEventListener('submit', function() {
            const canvasAdd = document.getElementById('signature-pad-add');
            const ttdInputAdd = document.getElementById('ttd_canvas_input_add');
            const blank = document.createElement('canvas'); 
            blank.width = canvasAdd.width; blank.height = canvasAdd.height;
            if (canvasAdd.toDataURL() !== blank.toDataURL()) ttdInputAdd.value = canvasAdd.toDataURL();
        });
    }

    // II. ENGINE UNTUK MODAL EDIT PJ
    document.addEventListener('shown.bs.modal', function (event) {
        const modal = event.target;
        if (modal.id.startsWith('modalEdit')) {
            const canvasEdit = modal.querySelector('.signature-pad-edit');
            const clearBtnEdit = modal.querySelector('.clear-btn-edit');
            const ttdInputEdit = modal.querySelector('.ttd_canvas_input_edit');
            
            if (canvasEdit && clearBtnEdit && ttdInputEdit) {
                initSignaturePad(canvasEdit, clearBtnEdit, ttdInputEdit);
            }
        }
    });

    // Menangani penggantian Tab agar ukuran canvas menyesuaikan secara instan
    document.addEventListener('shown.bs.tab', function (event) {
        const tabButton = event.target;
        const modal = tabButton.closest('.modal');
        if (modal && modal.id.startsWith('modalEdit')) {
            const canvasEdit = modal.querySelector('.signature-pad-edit');
            if (canvasEdit) {
                const ctxEdit = canvasEdit.getContext('2d');
                canvasEdit.width = canvasEdit.offsetWidth;
                canvasEdit.height = canvasEdit.offsetHeight;
                ctxEdit.strokeStyle = "#0f172a"; ctxEdit.lineWidth = 3; ctxEdit.lineCap = "round";
            }
        }
    });

    // INTERSEPT SUBMIT FORM EDIT
    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (form.classList.contains('form-edit-pj')) {
            const canvasEdit = form.querySelector('.signature-pad-edit');
            const ttdInputEdit = form.querySelector('.ttd_canvas_input_edit');
            
            if (canvasEdit && ttdInputEdit) {
                const blank = document.createElement('canvas'); 
                blank.width = canvasEdit.width; 
                blank.height = canvasEdit.height;
                
                if (canvasEdit.toDataURL() !== blank.toDataURL()) {
                    ttdInputEdit.value = canvasEdit.toDataURL();
                }
            }
        }
    });

});
</script>

<?php include 'template/footer.php'; ?>