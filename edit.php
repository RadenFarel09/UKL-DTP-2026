<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php?pesan=belum_login"); 
    exit;
}

$error = '';
if (!isset($_GET['id'])) { header("Location: index.php"); exit; }
$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id_transaksi = ?"); # Buat` query untuk mengambil data transaksi berdasarkan id_transaksi yang dikirim melalui parameter GET
$stmt->execute([$id]);
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$transaksi) die("Data tidak ditemukan!");

if ($_SESSION['role'] != 'admin' && $transaksi['id_user'] != $_SESSION['id_user']) { #Jika seorang anak mencoba mengedit catatan transaksi milik anggota keluarga lain, maka akses ditolak
    die("Akses ditolak! Anda tidak berhak mengubah catatan milik anggota keluarga lain.");
}

$kategori_list = $pdo->query("SELECT * FROM kategori ORDER BY jenis ASC, nama_kategori ASC")->fetchAll(PDO::FETCH_ASSOC); # Buat ambil semua data kategori dari tabel kategori untuk ditampilkan di dropdown form edit transaksi
$dompet_list   = $pdo->query("SELECT * FROM dompet ORDER BY nama_dompet ASC")->fetchAll(PDO::FETCH_ASSOC); #Buat ambil semua data dompet dari tabel dompet untuk ditampilkan di dropdown form edit transaksi

if ($_SERVER['REQUEST_METHOD'] == 'POST') { # Buat proses update data transaksi ke database jika form edit transaksi dikirimkan
    $tanggal     = $_POST['tanggal'];
    $keterangan  = trim($_POST['keterangan']);
    $nominal     = $_POST['nominal'];
    $id_kategori = $_POST['id_kategori'];
    $id_dompet   = $_POST['id_dompet'];

    if (empty($tanggal) || empty($keterangan) || empty($nominal)) { # Buat validasi jika ada kolom yang kosong, maka tampilkan pesan error
        $error = "Semua kolom wajib diisi!";
    } elseif ($nominal <= 0) {
        $error = "Nominal harus lebih dari 0!";
    } else { # Buat query update data transaksi ke database jika semua kolom sudah diisi dengan benar
        $stmt = $pdo->prepare("UPDATE transaksi SET tanggal=?, keterangan=?, nominal=?, id_kategori=?, id_dompet=? WHERE id_transaksi=?");
        if ($stmt->execute([$tanggal, $keterangan, $nominal, $id_kategori, $id_dompet, $id])) {
            header("Location: index.php?pesan=edit");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi - DompetKu</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; padding: 0; }
        body { background-color: #f1f5f9; color: #0f172a; line-height: 1.6; }
        .navbar { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #0d9488; }
        .navbar h2 { font-size: 22px; font-weight: 800; }
        .navbar span { color: #2dd4bf; }
        .container { max-width: 700px; margin: 32px auto; padding: 0 20px; }
        .card { background: #ffffff; border-radius: 16px; padding: 28px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.06); }
        .card-header { margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #e2e8f0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group.full { grid-column: span 2; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13.5px; }
        input, select { width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: #0d9488; color: white; }
        .btn-secondary { background: #e2e8f0; color: #334155; margin-left: 8px; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-weight: 600; }
        @media (max-width: 650px) { .form-grid { grid-template-columns: 1fr; } .form-group.full { grid-column: span 1; } }
    </style>
</head>
<body>
<div class="navbar">
    <h2>Dompet<span>Ku</span></h2>
    <small>Edit Catatan Transaksi</small>
</div>
<div class="container">
    <div class="card">
        <div class="card-header"><h3>Edit Catatan Transaksi</h3></div>
        <?php if ($error): ?><div class="alert-error"><?= $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tanggal Transaksi *</label>
                    <input type="date" name="tanggal" value="<?= $transaksi['tanggal']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Nominal (Rp) *</label>
                    <input type="number" name="nominal" min="1" value="<?= (int)$transaksi['nominal']; ?>" required>
                </div>
                <div class="form-group full">
                    <label>Keterangan Transaksi *</label>
                    <input type="text" name="keterangan" value="<?= htmlspecialchars($transaksi['keterangan']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="id_kategori" required>
                        <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?= $kat['id_kategori']; ?>" <?= $kat['id_kategori'] == $transaksi['id_kategori'] ? 'selected' : ''; ?>>
                                [<?= $kat['jenis']; ?>] <?= htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Dompet / Sumber Dana *</label>
                    <select name="id_dompet" required>
                        <?php foreach ($dompet_list as $dom): ?>
                            <option value="<?= $dom['id_dompet']; ?>" <?= $dom['id_dompet'] == $transaksi['id_dompet'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($dom['nama_dompet']); ?> (<?= $dom['tipe_akun']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
