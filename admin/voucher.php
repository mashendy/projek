<?php
session_start();
include '../config/config.php';

// Proteksi
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login/login.php");
    exit;
}

// ================= FUNCTION GENERATE KODE =================
function generateKodeVoucher($conn) {
    do {
        $kode = 'DISC-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $cek = mysqli_query($conn, "SELECT * FROM voucher WHERE kode_voucher='$kode'");
    } while (mysqli_num_rows($cek) > 0);
    return $kode;
}

// ================= TAMBAH =================
if (isset($_POST['tambah'])) {
    $kode = generateKodeVoucher($conn);
    $potongan = (int)$_POST['potongan'];
    $kuota = (int)$_POST['kuota'];
    $status = $_POST['status'];

    if (empty($potongan) || empty($kuota)) {
        echo "<script>alert('Semua field wajib diisi!');</script>";
    } elseif ($potongan > 50) { // Validasi Max 50%
        echo "<script>alert('Gagal! Potongan maksimal hanya boleh 50%');</script>";
    } elseif ($potongan <= 0 || $kuota <= 0) {
        echo "<script>alert('Potongan dan kuota harus lebih dari 0!');</script>";
    } else {
        mysqli_query($conn, "INSERT INTO voucher (kode_voucher, potongan, kuota, status) 
                            VALUES ('$kode', '$potongan', '$kuota', '$status')");
        echo "<script>alert('Voucher berhasil ditambahkan!'); window.location='voucher.php';</script>";
    }
}

// ================= EDIT =================
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id_voucher'];
    $kode = mysqli_real_escape_string($conn, $_POST['kode_voucher']);
    $potongan = (int)$_POST['potongan'];
    $kuota = (int)$_POST['kuota'];
    $status = $_POST['status'];

    if (empty($kode) || empty($potongan) || empty($kuota)) {
        echo "<script>alert('Semua field wajib diisi!');</script>";
    } elseif ($potongan > 50) { // Validasi Max 50%
        echo "<script>alert('Gagal! Potongan maksimal hanya boleh 50%');</script>";
    } elseif ($potongan <= 0 || $kuota <= 0) {
        echo "<script>alert('Potongan dan kuota harus lebih dari 0!');</script>";
    } else {
        mysqli_query($conn, "UPDATE voucher SET 
            kode_voucher='$kode',
            potongan='$potongan',
            kuota='$kuota',
            status='$status'
            WHERE id_voucher='$id'");
        echo "<script>alert('Voucher berhasil diupdate!'); window.location='voucher.php';</script>";
    }
}

// ================= HAPUS =================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM voucher WHERE id_voucher='$id'");
    echo "<script>alert('Voucher berhasil dihapus!'); window.location='voucher.php';</script>";
}

// Ambil data voucher
$data = mysqli_query($conn, "SELECT * FROM voucher ORDER BY id_voucher DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Voucher</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="sidebar.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .table th { background-color: #f1f3f5; font-weight: 600; }
        .kode-voucher { 
            font-family: monospace; 
            font-weight: bold; 
            letter-spacing: 1px;
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 6px;
        }
        .modal-content { border-radius: 16px; }
        .btn-action { min-width: 85px; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Kelola Voucher</h2>
            <p class="text-muted">Kelola kode diskon (Maksimal 50%)</p>
        </div>
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#tambahModal">
            <i class="fas fa-plus me-2"></i> Tambah Voucher
        </button>
    </div>

    <div class="card border-0">
        <div class="card-body p-4">
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search"></i></span>
                    <input type="search" id="searchVoucher" class="form-control border-start-0" placeholder="Cari kode atau status...">
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode Voucher</th>
                            <th>Potongan</th>
                            <th>Kuota</th>
                            <th>Status</th>
                            <th width="18%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while ($d = mysqli_fetch_assoc($data)): 
                        ?>
                        <tr class="<?= ($d['status'] == 'nonaktif') ? 'table-secondary' : '' ?>">
                            <td><?= $no++; ?></td>
                            <td><span class="kode-voucher"><?= htmlspecialchars($d['kode_voucher']); ?></span></td>
                            <td><strong class="text-danger"><?= $d['potongan']; ?>%</strong></td>
                            <td><span class="badge bg-info"><?= number_format($d['kuota']); ?> kuota</span></td>
                            <td>
                                <span class="badge bg-<?= ($d['status'] == 'aktif') ? 'success' : 'danger' ?>">
                                    <?= ucfirst($d['status']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btn-action text-black" data-bs-toggle="modal" data-bs-target="#edit<?= $d['id_voucher']; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="?hapus=<?= $d['id_voucher']; ?>" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Yakin ingin menghapus voucher ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>

                        <div class="modal fade" id="edit<?= $d['id_voucher']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title fw-bold">Edit Voucher</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_voucher" value="<?= $d['id_voucher']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Kode Voucher</label>
                                                <input type="text" name="kode_voucher" class="form-control fw-bold" value="<?= htmlspecialchars($d['kode_voucher']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Potongan (%)</label>
                                                <input type="number" name="potongan" class="form-control input-potongan" value="<?= $d['potongan']; ?>" min="1" max="50" required>
                                                <small class="text-muted">Maksimal potongan 50%</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Kuota</label>
                                                <input type="number" name="kuota" class="form-control" value="<?= $d['kuota']; ?>" min="1" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="aktif" <?= ($d['status']=='aktif') ? 'selected' : '' ?>>Aktif</option>
                                                    <option value="nonaktif" <?= ($d['status']=='nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit" class="btn btn-success rounded-pill px-4">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Tambah Voucher Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">KODE OTOMATIS</label>
                        <input type="text" class="form-control bg-light" value="DISC-XXXXXX" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Potongan (%) <span class="text-danger">*</span></label>
                        <input type="number" name="potongan" class="form-control input-potongan" placeholder="Contoh: 25" min="1" max="50" required>
                        <small class="text-info">Batas aman: Maksimal 50%</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kuota <span class="text-danger">*</span></label>
                        <input type="number" name="kuota" class="form-control" placeholder="Contoh: 100" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary rounded-pill px-4">Simpan Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    // Search Functionality
    document.getElementById('searchVoucher').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('.table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

    // Instant Validation Max 50%
    document.querySelectorAll('.input-potongan').forEach(input => {
        input.addEventListener('input', function() {
            if (parseInt(this.value) > 50) {
                alert('Maksimal potongan harga adalah 50%!');
                this.value = 50;
            }
        });
    });
</script>
</body>
</html>