<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user'])) { # SAMA seperti index.php
    header("Location: login.php?pesan=belum_login");
    exit;
}

$error = '';

$kategori_list = $pdo->query("SELECT * FROM kategori ORDER BY jenis ASC, nama_kategori ASC")->fetchAll(PDO::FETCH_ASSOC); # Ambil semua data kategori dari tabel kategori untuk ditampilkan di dropdown form tambah transaksi
$dompet_list   = $pdo->query("SELECT * FROM dompet ORDER BY nama_dompet ASC")->fetchAll(PDO::FETCH_ASSOC); # Ambil semua data dompet dari tabel dompet untuk ditampilkan di dropdown form tambah transaksi

if ($_SERVER['REQUEST_METHOD'] == 'POST') { # Jika form tambah transaksi dikirimkan, maka lakukan proses simpan transaksi ke database
    $tanggal     = $_POST['tanggal'];
    $keterangan  = trim($_POST['keterangan']);
    $nominal     = $_POST['nominal'];
    $id_kategori = $_POST['id_kategori'];
    $id_dompet   = $_POST['id_dompet'];
    $id_user     = $_SESSION['id_user'];

    if (empty($tanggal) || empty($keterangan) || empty($nominal) || empty($id_kategori) || empty($id_dompet)) {
        $error = "Semua kolom wajib diisi!";
    } elseif ($nominal <= 0) {
        $error = "Nominal transaksi harus lebih dari Rp 0!";
    } else {
        $sql = "INSERT INTO transaksi (tanggal, keterangan, nominal, id_kategori, id_dompet, id_user) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$tanggal, $keterangan, $nominal, $id_kategori, $id_dompet, $id_user])) {
            header("Location: index.php?pesan=tambah");
            exit;
        } else {
            $error = "Gagal menyimpan transaksi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Transaksi - DompetKu</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; margin: 0; padding: 0; }
        body { background-color: #f1f5f9; color: #0f172a; line-height: 1.6; }
        .navbar { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 18px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #0d9488; }
        .navbar h2 { font-size: 22px; font-weight: 800; }
        .navbar span { color: #2dd4bf; }
        .navbar small { background: rgba(255,255,255,0.12); color: #e2e8f0; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .container { max-width: 720px; margin: 36px auto; padding: 0 20px; }
        .card { background: #ffffff; border-radius: 16px; padding: 28px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(15,23,42,0.06); }
        .card-header { margin-bottom: 22px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; }
        .card-header h3 { font-size: 18px; font-weight: 700; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-group.full { grid-column: span 2; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13.5px; color: #334155; }
        input, select { width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #fff; }
        input:focus, select:focus { outline: none; border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,0.15); }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; transition: 0.2s; }
        .btn-primary { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white; }
        .btn-secondary { background: #e2e8f0; color: #334155; margin-left: 8px; }
        .alert-error { background: #fee2e2; color: #991b1b; border-left: 5px solid #dc2626; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
        @media (max-width: 650px) { .form-grid { grid-template-columns: 1fr; } .form-group.full { grid-column: span 1; } }
    </style>
</head>
<body>

<div class="navbar">
    <h2>Dompet<span>Ku</span></h2>
    <small>Catat Pemasukan / Pengeluaran</small>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Catat Transaksi Baru</h3>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tanggal Transaksi *</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Nominal (Rp) *</label>
                    <input type="number" name="nominal" min="1" placeholder="Contoh: 25000" required>
                </div>
                <div class="form-group full">
                    <label>Keterangan Transaksi *</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Beli nasi goreng atau Terima gaji" required>
                </div>
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="id_kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?= $kat['id_kategori']; ?>">
                                [<?= $kat['jenis']; ?>] <?= htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Dompet / Sumber Dana *</label>
                    <select name="id_dompet" required>
                        <option value="">-- Pilih Dompet --</option>
                        <?php foreach ($dompet_list as $dom): ?>
                            <option value="<?= $dom['id_dompet']; ?>">
                                <?= htmlspecialchars($dom['nama_dompet']); ?> (<?= $dom['tipe_akun']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-top: 22px;">
                <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>