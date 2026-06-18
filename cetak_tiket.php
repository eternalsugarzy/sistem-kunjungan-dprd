<?php
include 'koneksi.php';

// Amankan input dari URL
$kode = isset($_GET['kode']) ? mysqli_real_escape_string($koneksi, $_GET['kode']) : '';

// Ambil data kunjungan (JOIN dengan ruangan agar detail tujuannya lebih lengkap)
$query = mysqli_query($koneksi, "SELECT k.*, r.nama_ruangan FROM kunjungan k 
                                 LEFT JOIN ruangan r ON k.id_ruangan = r.id_ruangan 
                                 WHERE k.kode_booking='$kode'");
$d = mysqli_fetch_array($query);

if (!$d) {
    echo "<script>alert('Tiket tidak ditemukan!'); window.close();</script>";
    exit;
}

// Logika Cek QR Code (Lokal vs API Fallback)
$qr_path = "";
if (!empty($d['qr_code_path']) && file_exists($d['qr_code_path'])) {
    $qr_path = $d['qr_code_path'];
} else {
    // Jika admin belum membuat file fisik QR, generate langsung dari API untuk dicetak
    $qr_path = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($d['kode_booking']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>E-Ticket Kunjungan - <?= $kode; ?></title>
    <style>
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            background-color: #e9ecef; 
            color: #333; 
            text-align: center; 
            margin: 0;
            padding: 40px 20px;
        }
        
        /* Desain Kontainer Tiket */
        .ticket-box { 
            background: #fff; 
            width: 750px; 
            margin: 0 auto; 
            border: 2px solid #212529; 
            border-radius: 12px; 
            display: flex; /* Membagi jadi kiri dan kanan */
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        
        /* Sisi Kiri: Detail Informasi */
        .ticket-details {
            flex: 2;
            padding: 25px;
            text-align: left;
            border-right: 3px dashed #ced4da;
            position: relative;
        }
        
        /* Sisi Kanan: QR Code */
        .ticket-qr {
            flex: 1;
            background-color: #f8f9fa;
            padding: 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Bagian Header Tiket */
        .header { 
            display: flex; 
            align-items: center; 
            border-bottom: 2px solid #212529; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        .header img {
            height: 60px;
            margin-right: 15px;
        }
        .header-text h2 {
            margin: 0;
            font-size: 22px;
            color: #212529;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #6c757d;
        }

        /* Tabel Informasi Rapi */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
            font-size: 15px;
        }
        .info-table .label {
            width: 35%;
            font-weight: bold;
            color: #495057;
        }
        .info-table .value {
            font-weight: bold;
            color: #212529;
        }

        /* Styling Area QR */
        .ticket-qr img {
            width: 160px;
            height: 160px;
            border: 1px solid #ced4da;
            padding: 5px;
            background: #fff;
            border-radius: 8px;
        }
        .kode-badge {
            margin-top: 20px;
            font-size: 22px;
            font-weight: bold;
            font-family: monospace;
            background: #212529;
            color: #fff;
            padding: 8px 20px;
            border-radius: 6px;
            letter-spacing: 1px;
        }

        /* Catatan Kaki */
        .footer-note {
            margin-top: 25px;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
            padding-top: 15px;
        }

        /* Tombol Cetak & Tutup */
        .btn-action {
            padding: 10px 25px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin: 0 10px 30px;
        }
        .btn-print { background: #212529; color: #fff; }
        .btn-close { background: #dee2e6; color: #212529; }

        /* Pengaturan Cetak (Printer) */
        @media print { 
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; } 
            .ticket-box { width: 100%; box-shadow: none; border: 2px solid #000; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    
    <div class="no-print">
        <button class="btn-action btn-print" onclick="window.print()">Cetak Ulang</button>
        <button class="btn-action btn-close" onclick="window.close()">Tutup Jendela</button>
    </div>
    
    <div class="ticket-box">
        
        <div class="ticket-details">
            <div class="header">
                <img src="assets/images/logo.png" alt="Logo DPRD">
                <div class="header-text">
                    <h2>E-Ticket Kunjungan (Smart Guest)</h2>
                    <p>SEKRETARIAT DPRD KOTA BANJARMASIN</p>
                </div>
            </div>
            
            <table class="info-table">
                <tr>
                    <td class="label">Instansi Pemohon</td>
                    <td class="value">: <?= $d['nama_instansi_tamu']; ?></td>
                </tr>
                <tr>
                    <td class="label">Tgl. Pelaksanaan</td>
                    <td class="value">: <?= date('d F Y', strtotime($d['tgl_kunjungan'])); ?></td>
                </tr>
                <tr>
                    <td class="label">Waktu Kedatangan</td>
                    <td class="value">: <?= substr($d['waktu_kunjungan'], 0, 5); ?> WITA</td>
                </tr>
                <tr>
                    <td class="label">Jumlah Rombongan</td>
                    <td class="value">: <?= $d['jumlah_peserta_rencana']; ?> Orang</td>
                </tr>
                <tr>
                    <td class="label">Tujuan Ruangan</td>
                    <td class="value">: <?= !empty($d['nama_ruangan']) ? $d['nama_ruangan'] : 'Menunggu Arahan'; ?></td>
                </tr>
                <tr>
                    <td class="label">Status Tiket</td>
                    <td class="value">: <?= strtoupper($d['status_kegiatan']); ?></td>
                </tr>
            </table>

            <div class="footer-note">
                <strong>Catatan Penting:</strong> Cetak dokumen ini atau tunjukkan file digitalnya (melalui layar HP) kepada Petugas Keamanan dan Resepsionis untuk melakukan proses <em>Check-In</em> dan penerbitan Kartu Tamu Sementara.
            </div>
        </div>

        <div class="ticket-qr">
            <div style="font-size: 14px; font-weight: bold; color: #495057; margin-bottom: 15px;">SCAN AREA</div>
            
            <img src="<?= $qr_path; ?>" alt="QR Code Validasi">
            
            <div class="kode-badge"><?= $d['kode_booking']; ?></div>
            
            <div style="font-size: 11px; color: #6c757d; margin-top: 15px; text-align: center;">
                Validasi Keaslian Tiket
            </div>
        </div>

    </div>
</body>
</html>