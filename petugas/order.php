<?php
session_start();
include '../config/config.php';

// Proteksi login petugas/admin
if (!isset($_SESSION['login']) || ($_SESSION['role'] != 'petugas' && $_SESSION['role'] != 'admin')) {
    header('Location: ../login/login.php');
    exit;
}

// PERUBAHAN PADA ORDER BY:
// Menggunakan DESC agar waktu_checkin terbaru muncul di urutan paling atas (Baris pertama tabel)
$query = "SELECT a.kode_tiket, a.waktu_checkin, a.status_checkin,
                 o.id_order, o.total, o.status as status_bayar,
                 u.nama as nama_pembeli,
                 t.nama_tiket,
                 e.nama_event
          FROM attendee a
          JOIN order_detail od ON a.id_detail = od.id_detail
          JOIN orders o ON od.id_order = o.id_order
          JOIN users u ON o.id_user = u.id_user 
          JOIN tiket t ON od.id_tiket = t.id_tiket
          JOIN event e ON t.id_event = e.id_event
          WHERE o.status = 'paid' AND a.status_checkin = 'sudah'
          ORDER BY a.waktu_checkin DESC"; // DESC = Terbaru di atas, ASC = Terlama di atas

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
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        .table-card { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .badge-paid { background-color: #d1e7dd; color: #0f5132; font-weight: 600; }
        .kode-text { font-family: 'Courier New', Courier, monospace; font-weight: bold; color: #0d6efd; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="fas fa-clipboard-check me-2"></i> Data Validasi Masuk</a>
        <div class="d-flex">
            <a href="dashboard.php" class="btn btn-light btn-sm rounded-pill px-3 me-2">Kembali ke Scanner</a>
            <button onclick="window.print()" class="btn btn-outline-light btn-sm rounded-pill px-3"><i class="fas fa-print"></i></button>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="card table-card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-bold text-dark">Log Kehadiran & Bukti Bayar</h5>
                    <p class="text-muted small mb-0">Menampilkan data check-in terbaru di posisi teratas</p>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Waktu Masuk</th>
                            <th>Nama Pembeli</th>
                            <th>Event / Tiket</th>
                            <th>Kode Tiket</th>
                            <th>Total Bayar</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="ps-3 small">
                                        <div class="fw-bold text-primary"><?= date('H:i:s', strtotime($row['waktu_checkin'])) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= date('d/m/Y', strtotime($row['waktu_checkin'])) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['nama_pembeli']) ?></div>
                                        <div class="text-muted small">Order #<?= $row['id_order'] ?></div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($row['nama_event']) ?></div>
                                        <span class="badge bg-info text-dark" style="font-size: 0.7rem;"><?= htmlspecialchars($row['nama_tiket']) ?></span>
                                    </td>
                                    <td><span class="kode-text"><?= $row['kode_tiket'] ?></span></td>
                                    <td class="fw-bold text-success">
                                        Rp <?= number_format($row['total'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-paid px-3 py-2">
                                            <i class="fas fa-check-circle me-1"></i> PAID
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <p>Belum ada data check-in hari ini.</p>
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