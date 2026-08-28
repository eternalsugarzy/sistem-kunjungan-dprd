<?php
// ==========================================
// PENGATURAN DEBUG ERROR & INTEGRASI
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'buku_tamu';
include 'template/header.php';
include 'template/sidebar.php';

// PAKSA SEMBUNYIKAN LOADER CSS
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

// ---------------------------------------------------------
// LOGIC PHP
// ---------------------------------------------------------

// 1. TAMBAH PESERTA (DENGAN PERBAIKAN BASE64 CANVAS SIGNATURE)
if (isset($_POST['tambah_peserta'])) {
    $id_kunjungan = mysqli_real_escape_string($koneksi, $_POST['id_kunjungan']);
    $nama         = mysqli_real_escape_string($koneksi, $_POST['nama_peserta']);
    $jabatan      = mysqli_real_escape_string($koneksi, $_POST['jabatan_peserta']);
    $hp           = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    
    // Ambil instansi otomatis
    $cek_kunjungan = mysqli_query($koneksi, "SELECT nama_instansi_tamu FROM kunjungan WHERE id_kunjungan='$id_kunjungan'");
    $data_k = mysqli_fetch_assoc($cek_kunjungan);
    $instansi = $data_k ? $data_k['nama_instansi_tamu'] : 'Umum';

    $nama_file_ttd = "";

    // A. PROSES TANDA TANGAN DIGITAL DARI CANVAS (BASE64)
    if (!empty($_POST['ttd_canvas'])) {
        $img_data = $_POST['ttd_canvas'];
        if (strpos($img_data, 'data:image/png;base64,') !== false) {
            $img_data = str_replace('data:image/png;base64,', '', $img_data);
            $img_data = str_replace(' ', '+', $img_data);
            $data_decode = base64_decode($img_data);

            if (!file_exists('../uploads/ttd')) {
                mkdir('../uploads/ttd', 0777, true);
            }

            $nama_file_ttd = "TTD_CANVAS_" . time() . "_" . rand(100,999) . ".png";
            file_put_contents("../uploads/ttd/" . $nama_file_ttd, $data_decode);
        }
    } 
    // B. FALLBACK JIKA TAMU MEMILIH UPLOAD FILE MANUAL
    elseif (!empty($_FILES['ttd_file']['name'])) {
        $file_name = $_FILES['ttd_file']['name'];
        $file_tmp  = $_FILES['ttd_file']['tmp_name'];
        $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
        
        if (!file_exists('../uploads/ttd')) {
            mkdir('../uploads/ttd', 0777, true);
        }

        $nama_file_ttd = "TTD_FILE_" . time() . "_" . rand(100,999) . "." . $ext;
        move_uploaded_file($file_tmp, "../uploads/ttd/" . $nama_file_ttd);
    }

    // C. PROSES FOTO TAMU (KAMERA / UPLOAD)
    $nama_foto_tamu = "";

    // C1. Foto dari Kamera (Base64 Canvas)
    if (!empty($_POST['foto_camera'])) {
        $img_foto = $_POST['foto_camera'];
        if (strpos($img_foto, 'data:image/png;base64,') !== false) {
            $img_foto = str_replace('data:image/png;base64,', '', $img_foto);
            $img_foto = str_replace(' ', '+', $img_foto);
            $foto_decode = base64_decode($img_foto);

            if (!file_exists('../uploads/foto_tamu')) {
                mkdir('../uploads/foto_tamu', 0777, true);
            }
            $nama_foto_tamu = "FOTO_CAM_" . time() . "_" . rand(100,999) . ".png";
            file_put_contents("../uploads/foto_tamu/" . $nama_foto_tamu, $foto_decode);
        }
    }
    // C2. Foto dari Upload File (Fallback)
    elseif (!empty($_FILES['foto_tamu']['name'])) {
        $ft_name = $_FILES['foto_tamu']['name'];
        $ft_tmp  = $_FILES['foto_tamu']['tmp_name'];
        $ft_ext  = pathinfo($ft_name, PATHINFO_EXTENSION);
        
        if (!file_exists('../uploads/foto_tamu')) {
            mkdir('../uploads/foto_tamu', 0777, true);
        }
        $nama_foto_tamu = "FOTO_" . time() . "_" . rand(100,999) . "." . $ft_ext;
        move_uploaded_file($ft_tmp, "../uploads/foto_tamu/" . $nama_foto_tamu);
    }

    // Simpan ke database
    $query = "INSERT INTO buku_tamu 
              (id_kunjungan, nama_peserta, jabatan_peserta, no_hp, asal_instansi, tanda_tangan, foto_tamu, waktu_hadir) 
              VALUES 
              ('$id_kunjungan', '$nama', '$jabatan', '$hp', '$instansi', '$nama_file_ttd', '$nama_foto_tamu', NOW())";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>window.location='buku_tamu.php?id=$id_kunjungan';</script>";
    } else {
        echo "<script>alert('GAGAL: " . mysqli_error($koneksi) . "');</script>";
    }
}

// 2. HAPUS PESERTA
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id_tamu = mysqli_real_escape_string($koneksi, $_GET['id_tamu']);
    $id_kunjungan = mysqli_real_escape_string($koneksi, $_GET['id_kunjungan']);
    
    $q_file = mysqli_query($koneksi, "SELECT tanda_tangan, foto_tamu FROM buku_tamu WHERE id_tamu='$id_tamu'");
    $data_file = mysqli_fetch_assoc($q_file);
    if($data_file && !empty($data_file['tanda_tangan'])){
        $path = "../uploads/ttd/" . $data_file['tanda_tangan'];
        if(file_exists($path)) unlink($path);
    }
    if($data_file && !empty($data_file['foto_tamu'])){
        $path_foto = "../uploads/foto_tamu/" . $data_file['foto_tamu'];
        if(file_exists($path_foto)) unlink($path_foto);
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
          <h5 class="m-b-5">Buku Tamu Digital</h5>
        </div>
        <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item text-muted">Buku Tamu</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php if (!isset($_GET['id'])) { ?>
    <div class="row">
      <div class="col-sm-12">
        <div class="alert alert-primary d-flex align-items-center shadow-sm" role="alert">
            <i class="ti ti-info-circle me-2 f-20"></i>
            <div>Pilih jadwal kunjungan instansi yang melaksanakan agenda kedatangan hari ini untuk mengisi daftar kehadiran.</div>
        </div>
        <div class="card shadow-sm border-dark">
          <div class="card-header bg-dark text-white">
            <h5 class="mb-0 text-white"><i class="ti ti-address-book me-2"></i>Jadwal Kunjungan Siap Laksana</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0 align-middle">
                <thead class="table-light border-bottom border-dark">
                  <tr>
                    <th>Tgl &amp; Jam</th>
                    <th>Kode Booking</th>
                    <th>Instansi Tamu</th>
                    <th>Status</th>
                    <th class="text-center" width="15%">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // PERBAIKAN QUERY: Menambahkan 'sedang berkunjung' ke dalam urutan filter.
                  // ORDER BY FIELD digunakan agar yang 'sedang berkunjung' muncul paling atas.
                  $query = mysqli_query($koneksi, "
                      SELECT * FROM kunjungan 
                      WHERE status_kegiatan IN ('dijadwalkan', 'sedang berkunjung', 'selesai') 
                      ORDER BY FIELD(status_kegiatan, 'sedang berkunjung', 'dijadwalkan', 'selesai'), tgl_kunjungan DESC
                  ");

                  if ($query && mysqli_num_rows($query) > 0) {
                      while ($d = mysqli_fetch_array($query)) {
                          $stat = strtolower($d['status_kegiatan']);
                          
                          // Penentuan Warna Badge Dinamis
                          if ($stat == 'sedang berkunjung') {
                              $badge_status = '<span class="badge bg-info text-white"><i class="ti ti-loader rotate-refresh me-1"></i>Sedang Berkunjung</span>';
                          } elseif ($stat == 'dijadwalkan') {
                              $badge_status = '<span class="badge bg-primary">Dijadwalkan</span>';
                          } else {
                              $badge_status = '<span class="badge bg-success">Selesai</span>';
                          }
                  ?>
                  <tr>
                    <td>
                        <span class="fw-bold"><i class="ti ti-calendar me-1"></i><?= date('d M Y', strtotime($d['tgl_kunjungan'])); ?></span><br>
                        <span class="badge bg-light-secondary text-dark mt-1 border"><?= substr($d['waktu_kunjungan'], 0, 5); ?> WITA</span>
                    </td>
                    <td class="font-monospace fw-bold text-dark"><?= htmlspecialchars($d['kode_booking']); ?></td>
                    <td>
                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($d['nama_instansi_tamu']); ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($d['materi_kunjungan']); ?></small>
                    </td>
                    <td><?= $badge_status; ?></td>
                    <td class="text-center">
                        <a href="buku_tamu.php?id=<?= $d['id_kunjungan']; ?>" class="btn btn-dark btn-sm w-100 fw-bold">
                            <i class="ti ti-pencil me-1"></i>Isi Buku Tamu
                        </a>
                    </td>
                  </tr>
                  <?php 
                      }
                  } else {
                      echo '<tr><td colspan="5" class="text-center text-muted py-4"><i class="ti ti-folder-off f-24 d-block mb-2"></i>Tidak ada jadwal kunjungan aktif untuk saat ini.</td></tr>';
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
    // ================================================================
    // MODE 2: FORM INPUT PESERTA & DAFTAR HADIR REAL-TIME
    // ================================================================
    $id_kunjungan = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // PERBAIKAN: Join dengan ruangan agar nama ruangan tampil valid
    $q_info = mysqli_query($koneksi, "
        SELECT k.*, r.nama_ruangan 
        FROM kunjungan k 
        LEFT JOIN ruangan r ON k.id_ruangan = r.id_ruangan 
        WHERE k.id_kunjungan='$id_kunjungan'
    ");
    $info = mysqli_fetch_array($q_info);
    
    if(!$info) {
        echo "<script>alert('Data kunjungan tidak ditemukan!'); window.location.href='buku_tamu.php';</script>";
        exit;
    }

    $booking_code = $info['kode_booking'];
    $instansi_name = $info['nama_instansi_tamu'];
    $tgl_kunjungan = date('d F Y', strtotime($info['tgl_kunjungan']));
    $waktu_ops = substr($info['waktu_kunjungan'], 0, 5);
    $ruangan_ops = !empty($info['nama_ruangan']) ? $info['nama_ruangan'] : 'Menunggu Arahan';
?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="bg-dark text-white border border-dark p-2 rounded text-center fw-bold shadow-sm" style="font-size: 13px;">
                Jadwal Aktif: <span class="text-warning"><?= $booking_code; ?></span> — <?= $instansi_name; ?> | <?= $tgl_kunjungan; ?>, <?= $waktu_ops; ?> WITA | <?= $ruangan_ops; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-5 col-md-12 mb-4">
            <div class="card border border-dark shadow-sm h-100">
                <div class="card-header bg-light border-bottom border-dark py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="ti ti-user-plus me-2"></i>Input Peserta Hadir</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="formSignature">
                        <input type="hidden" name="id_kunjungan" value="<?= $id_kunjungan; ?>">
                        
                        <div class="mb-2">
                            <label class="form-label text-dark fw-bold mb-1" style="font-size:13px;">Nama Peserta *</label>
                            <input type="text" name="nama_peserta" class="form-control form-control-sm border-dark" placeholder="Nama Lengkap" required autofocus>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label text-dark fw-bold mb-1" style="font-size:13px;">Asal Instansi *</label>
                            <input type="text" class="form-control form-control-sm border-dark bg-light" value="<?= htmlspecialchars($instansi_name); ?>" readonly>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label text-dark fw-bold mb-1" style="font-size:13px;">Jabatan *</label>
                            <input type="text" name="jabatan_peserta" class="form-control form-control-sm border-dark" placeholder="Contoh: Anggota / Staf" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold mb-1" style="font-size:13px;">Nomor HP</label>
                            <input type="text" name="no_hp" class="form-control form-control-sm border-dark" placeholder="08xx-xxxx-xxxx">
                        </div>

                        <div class="mb-3 border border-dark p-2 rounded bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-dark fw-bold mb-0" style="font-size:13px;">Foto Tamu *</label>
                            </div>
                            
                            <ul class="nav nav-tabs tabs-mini mb-2" id="fotoTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active py-1 border-dark" id="camera-tab" data-bs-toggle="tab" data-bs-target="#camera-panel" type="button" role="tab">[📷] Kamera</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-1 border-dark" id="upload-foto-tab" data-bs-toggle="tab" data-bs-target="#upload-foto-panel" type="button" role="tab">[↑] Upload</button>
                                </li>
                            </ul>

                            <div class="tab-content border border-dark rounded bg-white p-2" style="min-height: 160px;">
                                <div class="tab-pane fade show active text-center" id="camera-panel" role="tabpanel">
                                    <div class="mb-2">
                                        <select id="camera-select" class="form-select form-select-sm border-dark mb-2" style="font-size:12px;">
                                            <option value="">-- Pilih Kamera --</option>
                                        </select>
                                        <button type="button" id="btn-detect-camera" class="btn btn-sm btn-outline-dark fw-bold w-100 mb-2" onclick="detectCameras()">
                                            <i class="ti ti-device-desktop-search me-1"></i> Deteksi Kamera Tersedia
                                        </button>
                                    </div>
                                    <div id="camera-container" class="position-relative mb-2">
                                        <video id="camera-video" class="border border-secondary rounded bg-dark" style="width:100%; max-height:180px; display:none;" autoplay playsinline></video>
                                        <canvas id="camera-canvas" style="display:none;"></canvas>
                                        <img id="camera-preview" class="border border-success rounded" style="width:100%; max-height:180px; display:none; object-fit:contain;" />
                                    </div>
                                    <div id="camera-buttons">
                                        <button type="button" id="btn-start-camera" class="btn btn-sm btn-dark fw-bold" onclick="startCamera()" disabled>
                                            <i class="ti ti-camera me-1"></i> Aktifkan Kamera
                                        </button>
                                        <button type="button" id="btn-capture" class="btn btn-sm btn-success fw-bold" style="display:none;" onclick="capturePhoto()">
                                            <i class="ti ti-camera me-1"></i> Ambil Foto
                                        </button>
                                        <button type="button" id="btn-retake" class="btn btn-sm btn-warning fw-bold" style="display:none;" onclick="retakePhoto()">
                                            <i class="ti ti-refresh me-1"></i> Ulangi
                                        </button>
                                    </div>
                                    <input type="hidden" name="foto_camera" id="foto_camera_input">
                                </div>
                                <div class="tab-pane fade" id="upload-foto-panel" role="tabpanel">
                                    <small class="text-muted d-block mb-2">Pilih file foto dari perangkat:</small>
                                    <input type="file" name="foto_tamu" class="form-control form-control-sm border-dark" accept="image/*">
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Format: .PNG / .JPG</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 border border-dark p-2 rounded bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-dark fw-bold mb-0" style="font-size:13px;">Tanda Tangan Digital *</label>
                            </div>
                            
                            <ul class="nav nav-tabs tabs-mini mb-2" id="ttdTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active py-1 border-dark" id="draw-tab" data-bs-toggle="tab" data-bs-target="#draw-panel" type="button" role="tab">[-] Gambar</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-1 border-dark" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-panel" type="button" role="tab">[↑] Upload</button>
                                </li>
                            </ul>

                            <div class="tab-content border border-dark rounded bg-white p-2" style="min-height: 140px;">
                                <div class="tab-pane fade show active text-center" id="draw-panel" role="tabpanel">
                                    <small class="text-muted d-block mb-1">Klik tahan &amp; gerak sentuhan untuk menggambar TTD:</small>
                                    <canvas id="signature-pad" class="border border-secondary rounded bg-light" style="width: 100%; height: 110px; cursor: crosshair; touch-action: none;"></canvas>
                                    <button type="button" id="clear-btn" class="btn btn-sm btn-light border border-dark text-danger font-monospace py-0 px-2 mt-1" style="font-size:11px;">[X] Hapus Canvas</button>
                                    <input type="hidden" name="ttd_canvas" id="ttd_canvas_input">
                                </div>
                                <div class="tab-pane fade" id="upload-panel" role="tabpanel">
                                    <small class="text-muted d-block mb-2">Pilih file gambar hasil foto/scan tanda tangan asli:</small>
                                    <input type="file" name="ttd_file" class="form-control form-control-sm border-dark" accept="image/*">
                                    <small class="text-muted d-block mt-1 font-italic" style="font-size: 10px;">Format: .PNG / .JPG</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" name="tambah_peserta" class="btn btn-dark text-white py-2 fw-bold" style="border-radius:4px;">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Data Peserta
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center border-top border-dark pt-3">
                        <a href="buku_tamu.php" class="btn btn-outline-dark btn-sm w-100 fw-bold" style="border-radius:4px;"><i class="ti ti-arrow-left me-1"></i> Kembali ke Daftar Jadwal</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7 col-md-12 mb-4">
            <div class="card border border-dark shadow-sm h-100">
                <div class="card-header bg-dark text-white border-bottom border-dark d-flex justify-content-between align-items-center py-2">
                    <h5 class="mb-0 fw-bold text-white"><i class="ti ti-users me-2"></i>Daftar Hadir Real-time</h5>
                    <a href="cetak_absensi.php?id=<?= $id_kunjungan; ?>" target="_blank" class="btn btn-sm btn-light text-dark fw-bold">
                        <i class="ti ti-printer me-1"></i> Cetak Absensi
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light border-bottom border-dark">
                                <tr>
                                    <th width="8%" class="text-center">No</th>
                                    <th>Nama &amp; Jabatan</th>
                                    <th>No HP</th>
                                    <th width="15%" class="text-center">Foto</th>
                                    <th width="20%" class="text-center">TTD</th>
                                    <th class="text-center" width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $q_tamu = mysqli_query($koneksi, "SELECT * FROM buku_tamu WHERE id_kunjungan='$id_kunjungan' ORDER BY id_tamu DESC");
                                
                                if($q_tamu && mysqli_num_rows($q_tamu) > 0){
                                    while($t = mysqli_fetch_array($q_tamu)){
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td>
                                        <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($t['nama_peserta']); ?></h6>
                                        <small class="text-muted"><em><?= htmlspecialchars($t['jabatan_peserta']); ?></em></small>
                                    </td>
                                    <td class="font-monospace text-muted" style="font-size:12px;"><?= htmlspecialchars($t['no_hp'] ?: '-'); ?></td>
                                    <td class="text-center">
                                        <?php if(!empty($t['foto_tamu'])): ?>
                                            <div class="d-inline-block bg-white border border-secondary rounded p-1" style="max-width: 60px;">
                                                <img src="../uploads/foto_tamu/<?= $t['foto_tamu']; ?>" alt="Foto" style="height: 40px; object-fit: contain;">
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if(!empty($t['tanda_tangan'])): ?>
                                            <div class="d-inline-block bg-white border border-secondary rounded p-1" style="max-width: 80px;">
                                                <img src="../uploads/ttd/<?= $t['tanda_tangan']; ?>" alt="TTD" style="height: 30px; object-fit: contain;">
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="buku_tamu.php?aksi=hapus&id_tamu=<?= $t['id_tamu']; ?>&id_kunjungan=<?= $id_kunjungan; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Hapus nama peserta ini?')" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    } 
                                } else {
                                    echo '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada peserta yang mengisi daftar hadir.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<style>
.border-dark { border: 2px solid #2e2e2e !important; }
.border-bottom-dark { border-bottom: 2px solid #2e2e2e !important; }
.tabs-mini .nav-link { font-size: 11px; font-family: monospace; color: #555; background: #f8f9fa; margin-right: 3px; border-bottom: none;}
.tabs-mini .nav-link.active { background: #fff !important; color: #000 !important; font-weight: bold; border-bottom: 2px solid white; margin-bottom: -1px; position: relative; z-index: 1;}
/* Animasi kecil untuk status sedang berkunjung */
@keyframes spin { 100% { transform: rotate(360deg); } }
.rotate-refresh { display: inline-block; animation: spin 2s linear infinite; }
</style>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
    const canvas = document.getElementById('signature-pad');
    if(!canvas) return;

    const ctx = canvas.getContext('2d');
    const form = document.getElementById('formSignature');
    const clearBtn = document.getElementById('clear-btn');
    const ttdInput = document.getElementById('ttd_canvas_input');

    // Menyetel resolusi internal
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;

    ctx.strokeStyle = "#1e293b";
    ctx.lineWidth = 3;
    ctx.lineCap = "round";

    let drawing = false;

    function getMousePos(e) {
        let rect = canvas.getBoundingClientRect();
        return {
            x: (e.clientX || e.touches[0].clientX) - rect.left,
            y: (e.clientY || e.touches[0].clientY) - rect.top
        };
    }

    function startDrawing(e) {
        drawing = true;
        let pos = getMousePos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function draw(e) {
        if (!drawing) return;
        let pos = getMousePos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function stopDrawing() {
        drawing = false;
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDrawing);

    canvas.addEventListener('touchstart', startDrawing, {passive: false});
    canvas.addEventListener('touchmove', draw, {passive: false});
    window.addEventListener('touchend', stopDrawing);

    clearBtn.addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ttdInput.value = "";
    });

    form.addEventListener('submit', function() {
        const blank = document.createElement('canvas');
        blank.width = canvas.width;
        blank.height = canvas.height;
        
        if (canvas.toDataURL() !== blank.toDataURL()) {
            ttdInput.value = canvas.toDataURL();
        }
    });
});
</script>

<!-- SCRIPT KAMERA FOTO TAMU -->
<script type="text/javascript">
let cameraStream = null;

// Deteksi semua kamera yang tersedia di perangkat
function detectCameras() {
    navigator.mediaDevices.getUserMedia({ video: true })
    .then(function(tempStream) {
        // Stop temporary stream, hanya butuh izin
        tempStream.getTracks().forEach(function(t) { t.stop(); });

        navigator.mediaDevices.enumerateDevices()
        .then(function(devices) {
            const select = document.getElementById('camera-select');
            select.innerHTML = '<option value="">-- Pilih Kamera --</option>';
            let count = 0;
            devices.forEach(function(device) {
                if (device.kind === 'videoinput') {
                    count++;
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.text = device.label || ('Kamera ' + count);
                    select.appendChild(option);
                }
            });
            if (count === 0) {
                alert('Tidak ada kamera yang terdeteksi di perangkat ini.');
            } else {
                // Auto-select kamera pertama
                select.selectedIndex = 1;
                document.getElementById('btn-start-camera').disabled = false;
                alert(count + ' kamera berhasil terdeteksi. Silakan pilih lalu klik Aktifkan Kamera.');
            }
        });
    })
    .catch(function(err) {
        alert('Tidak dapat mengakses kamera: ' + err.message + '\nPastikan browser mengizinkan akses kamera.');
    });
}

// Enable/disable tombol aktifkan berdasarkan pilihan dropdown
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('camera-select');
    if (sel) {
        sel.addEventListener('change', function() {
            document.getElementById('btn-start-camera').disabled = (this.value === '');
        });
    }
});

function startCamera() {
    const video = document.getElementById('camera-video');
    const btnStart = document.getElementById('btn-start-camera');
    const btnCapture = document.getElementById('btn-capture');
    const preview = document.getElementById('camera-preview');
    const selectedDeviceId = document.getElementById('camera-select').value;

    if (!selectedDeviceId) {
        alert('Silakan pilih kamera terlebih dahulu!');
        return;
    }

    if (preview) { preview.style.display = 'none'; }
    document.getElementById('foto_camera_input').value = '';

    // Stop stream lama jika ada
    if (cameraStream) {
        cameraStream.getTracks().forEach(function(track) { track.stop(); });
    }

    navigator.mediaDevices.getUserMedia({
        video: { deviceId: { exact: selectedDeviceId }, width: { ideal: 640 }, height: { ideal: 480 } }
    })
    .then(function(stream) {
        cameraStream = stream;
        video.srcObject = stream;
        video.style.display = 'block';
        btnStart.style.display = 'none';
        document.getElementById('btn-detect-camera').style.display = 'none';
        document.getElementById('camera-select').style.display = 'none';
        btnCapture.style.display = 'inline-block';
        document.getElementById('btn-retake').style.display = 'none';
    })
    .catch(function(err) {
        alert('Gagal membuka kamera: ' + err.message);
    });
}

function capturePhoto() {
    const video = document.getElementById('camera-video');
    const canvas = document.getElementById('camera-canvas');
    const preview = document.getElementById('camera-preview');
    const fotoInput = document.getElementById('foto_camera_input');
    const btnCapture = document.getElementById('btn-capture');
    const btnRetake = document.getElementById('btn-retake');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    const dataURL = canvas.toDataURL('image/png');
    fotoInput.value = dataURL;

    preview.src = dataURL;
    preview.style.display = 'block';
    video.style.display = 'none';
    btnCapture.style.display = 'none';
    btnRetake.style.display = 'inline-block';

    if (cameraStream) {
        cameraStream.getTracks().forEach(function(track) { track.stop(); });
        cameraStream = null;
    }
}

function retakePhoto() {
    document.getElementById('camera-preview').style.display = 'none';
    document.getElementById('foto_camera_input').value = '';
    document.getElementById('btn-retake').style.display = 'none';
    document.getElementById('btn-start-camera').style.display = 'inline-block';
    document.getElementById('btn-detect-camera').style.display = 'inline-block';
    document.getElementById('camera-select').style.display = 'block';
    startCamera();
}
</script>

<?php include 'template/footer.php'; ?>