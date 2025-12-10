<?php
include '../koneksi.php';
$id = $_GET['id'];
$q = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE id_kunjungan='$id'");
$d = mysqli_fetch_array($q);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lembar Disposisi</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .box { border: 1px solid black; padding: 20px; width: 800px; margin: auto; }
        .kop { text-align: center; border-bottom: 3px double black; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px; border: 1px solid black; vertical-align: top; }
    </style>
</head>
<body onload="window.print()">
    <div class="box">
        <div class="kop">
            <h3>LEMBAR DISPOSISI KUNJUNGAN KERJA</h3>
            <p>Sekretariat DPRD</p>
        </div>
        <table>
            <tr>
                <td width="50%">
                    <strong>Surat Dari:</strong><br> <?= $d['nama_instansi_tamu']; ?><br><br>
                    <strong>No. Surat:</strong> <?= $d['no_skk'] ?? '-'; ?><br>
                    <strong>Tgl Surat:</strong> <?= $d['tgl_skk'] ?? '-'; ?>
                </td>
                <td width="50%">
                    <strong>Diterima Tgl:</strong> <?= date('d-m-Y', strtotime($d['created_at'])); ?><br><br>
                    <strong>Agenda No:</strong> <?= $d['id_kunjungan']; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Perihal:</strong><br>
                    <?= $d['materi_kunjungan']; ?>
                </td>
            </tr>
            <tr>
                <td height="200px"><strong>Diteruskan Kepada:</strong></td>
                <td><strong>Instruksi / Catatan:</strong></td>
            </tr>
        </table>
    </div>
</body>
</html>