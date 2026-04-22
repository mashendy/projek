<?php
session_start();
include '../config/config.php';

// Proteksi login admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login/login.php");
    exit;
}

// Get filter event
$selected_event = isset($_GET['event']) ? (int)$_GET['event'] : 0;

$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'] ?? 0;
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE status = 'paid'"))['total'] ?? 0;
$total_paid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'paid'"))['total'] ?? 0;
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status != 'paid'"))['total'] ?? 0;

// Build query based on filter
$orders_filter = "";
if ($selected_event > 0) {
    $orders_filter = " AND EXISTS (
        SELECT 1 FROM order_detail od
        JOIN tiket t ON od.id_tiket = t.id_tiket
        WHERE od.id_order = o.id_order AND t.id_event = '$selected_event'
    )";
}

$orders = mysqli_query($conn, "SELECT o.*, u.nama AS user_name, u.email AS user_email
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    WHERE 1=1 $orders_filter
    ORDER BY o.id_order DESC");

// Get all events with quota info
$events = mysqli_query($conn, "SELECT 
    e.id_event, 
    e.nama_event, 
    DATE_FORMAT(e.tanggal, '%d %M %Y') as tanggal_event,
    COALESCE(SUM(t.kuota), 0) as total_kuota,
    COALESCE((SELECT SUM(od.qty) FROM order_detail od 
              JOIN tiket t2 ON od.id_tiket = t2.id_tiket 
              WHERE t2.id_event = e.id_event), 0) as kuota_terpakai
    FROM event e
    LEFT JOIN tiket t ON e.id_event = t.id_event
    GROUP BY e.id_event, e.nama_event, e.tanggal
    ORDER BY e.tanggal DESC");
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

    <!-- Event Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <label for="eventFilter" class="form-label fw-bold">Filter Berdasarkan Event</label>
                    <select id="eventFilter" name="event" class="form-select" onchange="this.form.submit();">
                        <option value="0">Semua Event</option>
                        <?php
                        // Reset pointer untuk form
                        mysqli_data_seek($events, 0);
                        while ($evt = mysqli_fetch_assoc($events)) {
                            $selected = ($selected_event == $evt['id_event']) ? 'selected' : '';
                            echo "<option value='" . $evt['id_event'] . "' $selected>" 
                                . htmlspecialchars($evt['nama_event']) . " (" . $evt['tanggal_event'] . ")"
                                . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Event Quota Info -->
    <?php
    mysqli_data_seek($events, 0);
    if ($selected_event > 0) {
        while ($evt = mysqli_fetch_assoc($events)) {
            if ($evt['id_event'] == $selected_event) {
                $sisa_kuota = (int)$evt['total_kuota'] - (int)$evt['kuota_terpakai'];
                $persentase = $evt['total_kuota'] > 0 ? round(((int)$evt['kuota_terpakai'] / (int)$evt['total_kuota']) * 100) : 0;
                ?>
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">📊 Info Kuota Event</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-3"><?= htmlspecialchars($evt['nama_event']) ?></h4>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded">
                                    <small class="text-muted d-block">Total Kuota</small>
                                    <h4 class="mb-0 text-primary"><?= number_format((int)$evt['total_kuota']) ?></h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded">
                                    <small class="text-muted d-block">Kuota Terpakai</small>
                                    <h4 class="mb-0 text-danger"><?= number_format((int)$evt['kuota_terpakai']) ?></h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded">
                                    <small class="text-muted d-block">Sisa Kuota</small>
                                    <h4 class="mb-0 text-success"><?= number_format($sisa_kuota) ?></h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded">
                                    <small class="text-muted d-block">Persentase Terpakai</small>
                                    <h4 class="mb-0"><?= $persentase ?>%</h4>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar" style="width: <?= $persentase ?>%;" aria-valuenow="<?= $persentase ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= $persentase ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                break;
            }
        }
    }
    ?>

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

    <!-- All Events Quota Overview -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">📅 Ringkasan Kuota Semua Event</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Tanggal</th>
                            <th>Total Kuota</th>
                            <th>Terpakai</th>
                            <th>Sisa</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($events, 0);
                        while ($evt = mysqli_fetch_assoc($events)) {
                            $sisa = (int)$evt['total_kuota'] - (int)$evt['kuota_terpakai'];
                            $persen = $evt['total_kuota'] > 0 ? round(((int)$evt['kuota_terpakai'] / (int)$evt['total_kuota']) * 100) : 0;
                            $class_badge = $persen >= 90 ? 'danger' : ($persen >= 70 ? 'warning' : 'success');
                        ?>
                        <tr>
                            <td>
                                <a href="?event=<?= $evt['id_event'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($evt['nama_event']) ?>
                                </a>
                            </td>
                            <td><?= $evt['tanggal_event'] ?></td>
                            <td><strong><?= number_format((int)$evt['total_kuota']) ?></strong></td>
                            <td><?= number_format((int)$evt['kuota_terpakai']) ?></td>
                            <td>
                                <span class="badge bg-<?= $sisa <= 0 ? 'danger' : 'success' ?>">
                                    <?= number_format($sisa) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $class_badge ?>"><?= $persen ?>%</span>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
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
                                $details = mysqli_query($conn, "SELECT od.qty, od.subtotal, t.nama_tiket, e.id_event, e.nama_event, DATE_FORMAT(e.tanggal, '%d %M %Y') AS tanggal_event FROM order_detail od JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE od.id_order = '" . $order['id_order'] . "'");
                                $ticketList = [];
                                $eventNames = [];
                                $eventIds = [];
                                $totalQty = 0;
                                while ($detail = mysqli_fetch_assoc($details)) {
                                    $ticketList[] = htmlspecialchars($detail['nama_tiket']) . ' x' . intval($detail['qty']);
                                    $eventNames[] = htmlspecialchars($detail['nama_event']) . ' (' . $detail['tanggal_event'] . ')';
                                    $eventIds[] = $detail['id_event'];
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
