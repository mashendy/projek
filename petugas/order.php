<?php
session_start();
include '../config/config.php';

// Proteksi login petugas/admin
if (!isset($_SESSION['login']) || ($_SESSION['role'] != 'petugas' && $_SESSION['role'] != 'admin')) {
    header('Location: ../login/login.php');
    exit;
}

// PERUBAHAN PADA QUERY:
// Mengambil t.harga (harga asli per tiket) menggantikan o.total (total belanjaan)
$query = "SELECT a.kode_tiket, a.waktu_checkin, a.status_checkin,
                 o.id_order, o.status as status_bayar,
                 u.nama as nama_pembeli,
                 t.nama_tiket, t.harga, -- Ambil harga dari tabel tiket
                 e.nama_event
          FROM attendee a
          JOIN order_detail od ON a.id_detail = od.id_detail
          JOIN orders o ON od.id_order = o.id_order
          JOIN users u ON o.id_user = u.id_user 
          JOIN tiket t ON od.id_tiket = t.id_tiket
          JOIN event e ON t.id_event = e.id_event
          WHERE o.status = 'paid' AND a.status_checkin = 'sudah'
          ORDER BY a.waktu_checkin DESC"; 

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Validasi Pembayaran - Petugas</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
         body {
            background: #f8f9fa;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.85rem; /* Menyesuaikan agar lebih compact seperti halaman sebelumnya */
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        .table-card { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .badge-paid { background-color: #d1e7dd; color: #0f5132; font-weight: 600; font-size: 0.75rem; }
        .kode-text { font-family: 'Courier New', Courier, monospace; font-weight: bold; color: #0d6efd; }
        .table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="fas fa-clipboard-check me-2"></i> Log Validasi</a>
        <div class="d-flex">
            <a href="dashboard.php" class="btn btn-light btn-sm rounded-pill px-3 me-2">Kembali ke Scanner</a>
            <button onclick="window.print()" class="btn btn-outline-light btn-sm rounded-pill px-3"><i class="fas fa-print"></i></button>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="card table-card">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-dark">Data Check-in Masuk</h6>
            <p class="text-muted small mb-0">Menampilkan harga per tiket (satuan) yang di-scan</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Waktu</th>
                            <th>Pembeli</th>
                            <th>Event / Tiket</th>
                            <th>Kode Tiket</th>
                            <th>Harga Tiket</th> <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-primary"><?= date('H:i:s', strtotime($row['waktu_checkin'])) ?></div>
                                        <div class="text-muted small"><?= date('d/m/Y', strtotime($row['waktu_checkin'])) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['nama_pembeli']) ?></div>
                                        <div class="text-muted small" style="font-size: 0.7rem;">Order #<?= $row['id_order'] ?></div>
                                    </td>
                                    <td>
                                        <div class="text-truncate fw-bold" style="max-width: 180px;"><?= htmlspecialchars($row['nama_event']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($row['nama_tiket']) ?></div>
                                    </td>
                                    <td><span class="kode-text"><?= $row['kode_tiket'] ?></span></td>
                                    <td class="fw-bold text-dark">
                                        Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-paid px-2 py-1">
                                            <i class="fas fa-check-circle me-1"></i> VALID
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 opacity-25"></i>
                                    <p class="small">Belum ada data check-in.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>