<?php
session_start();
include '../config/config.php';

// Proteksi login admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login/login.php");
    exit;
}

$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'] ?? 0;
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE status = 'paid'"))['total'] ?? 0;
$total_paid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'paid'"))['total'] ?? 0;
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status != 'paid'"))['total'] ?? 0;

$orders = mysqli_query($conn, "SELECT o.*, u.nama AS user_name, u.email AS user_email
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    ORDER BY o.id_order DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Admin</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="sidebar.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .table th { background-color: #f1f3f5; }
        .badge-status { min-width: 90px; }
        .summary-card { border: none; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Transaksi</h2>
            <p class="text-muted">Lihat transaksi pengguna dan total pendapatan.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card summary-card bg-white shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Order</h6>
                        <h3 class="mb-0"><?= number_format($total_orders); ?></h3>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card bg-white shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Pendapatan Paid</h6>
                        <h3 class="mb-0">Rp <?= number_format($total_revenue); ?></h3>
                    </div>
                    <i class="fas fa-wallet fa-2x text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card bg-white shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Order Paid</h6>
                        <h3 class="mb-0"><?= number_format($total_paid); ?></h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card bg-white shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Order Pending</h6>
                        <h3 class="mb-0"><?= number_format($total_pending); ?></h3>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="search" id="searchTransaksi" class="form-control" placeholder="Cari order, user, event, tiket, atau status..." aria-label="Cari Transaksi">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Order</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Tiket</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($orders) > 0): ?>
                            <?php $no = 1; while($order = mysqli_fetch_assoc($orders)): ?>
                                <?php
                                $details = mysqli_query($conn, "SELECT od.qty, od.subtotal, t.nama_tiket, e.nama_event, DATE_FORMAT(e.tanggal, '%d %M %Y') AS tanggal_event FROM order_detail od JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE od.id_order = '" . $order['id_order'] . "'");
                                $ticketList = [];
                                $eventNames = [];
                                $totalQty = 0;
                                while ($detail = mysqli_fetch_assoc($details)) {
                                    $ticketList[] = htmlspecialchars($detail['nama_tiket']) . ' x' . intval($detail['qty']);
                                    $eventNames[] = htmlspecialchars($detail['nama_event']) . ' (' . $detail['tanggal_event'] . ')';
                                    $totalQty += intval($detail['qty']);
                                }
                                $eventNames = array_unique($eventNames);
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>#<?= intval($order['id_order']); ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($order['user_name']); ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($order['user_email']); ?></small>
                                    </td>
                                    <td><?= implode('<br>', $eventNames); ?></td>
                                    <td><?= implode('<br>', $ticketList); ?></td>
                                    <td><?= number_format($totalQty); ?></td>
                                    <td>Rp <?= number_format($order['total']); ?></td>
                                    <td>
                                        <?php if ($order['status'] == 'paid'): ?>
                                            <span class="badge bg-success badge-status">Paid</span>
                                        <?php elseif ($order['status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark badge-status">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark badge-status"><?= htmlspecialchars(ucfirst($order['status'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($order['tanggal_order'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    const input = document.getElementById('searchTransaksi');
    if (!input) return;
    input.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('table tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
})();
</script>
</body>
</html>
