<?php
session_start();
include '../config/config.php';

// proteksi login
if(!isset($_SESSION['login'])){
    header("Location: ../login/login.php");
    exit;
}

if($_SESSION['role'] != 'user'){
    echo "Akses ditolak!";
    exit;
}

$id_user = $_SESSION['id_user'];
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE id_user = '$id_user' ORDER BY id_order DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembelian | EventTix</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        
        .table-responsive { border-radius: 12px; overflow: hidden; }
        
        /* Thumbnail Style */
        .img-thumbnail-event {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        .img-mobile-event {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 12px;
        }
        
        @media (max-width: 768px) {
            .desktop-table { display: none; }
            .mobile-card { display: block; }
            h3 { font-size: 1.4rem; }
        }
        
        @media (min-width: 769px) {
            .mobile-card { display: none; }
        }

        .order-card {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #eee;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .status-badge { font-size: 0.75rem; padding: 5px 10px; border-radius: 50px; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-3 py-md-5">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Riwayat Pembelian</h3>
        <p class="text-muted small">Kelola tiket dan status pembayaran Anda.</p>
    </div>

    <?php if(mysqli_num_rows($orders) > 0): ?>
        <div class="mb-4">
            <div class="input-group input-group-sm shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="search" id="searchRiwayat" class="form-control border-start-0" placeholder="Cari event atau status...">
            </div>
        </div>

        <div class="desktop-table shadow-sm">
            <div class="table-responsive bg-white">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th class="ps-3" colspan="2">Event</th>
                            <th>Tiket</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $today = date('Y-m-d');
                        while ($order = mysqli_fetch_assoc($orders)): 
                            $id_order = $order['id_order'];
                            $details = mysqli_query($conn, "
                                SELECT od.qty, od.subtotal, t.nama_tiket, e.nama_event, e.tanggal, e.foto 
                                FROM order_detail od 
                                JOIN tiket t ON od.id_tiket = t.id_tiket 
                                JOIN event e ON t.id_event = e.id_event 
                                WHERE od.id_order = '$id_order'
                            ");
                            
                            $ticketInfo = [];
                            $eventName = "";
                            $eventDate = "";
                            $eventFoto = "";
                            while($d = mysqli_fetch_assoc($details)){
                                $eventName = $d['nama_event'];
                                $eventDate = $d['tanggal'];
                                $eventFoto = $d['foto'];
                                $ticketInfo[] = $d['nama_tiket'] . " (x" . $d['qty'] . ")";
                            }

                            $fotoPath = "../assets/img/event/" . ($eventFoto ? $eventFoto : 'default.jpg');
                        ?>
                        <tr class="order-item">
                            <td class="ps-3" style="width: 60px;">
                                <img src="<?= $fotoPath ?>" class="img-thumbnail-event shadow-sm" onerror="this.src='../bootstrap/image/image.png'">
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= $eventName ?></div>
                                <small class="text-muted"><?= date('d M Y', strtotime($eventDate)) ?></small>
                            </td>
                            <td><small><?= implode(", ", $ticketInfo) ?></small></td>
                            <td class="fw-bold text-primary">Rp <?= number_format($order['total']) ?></td>
                            <td>
                                <?php if($order['status']=='pending'): ?> <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif($order['status']=='confirmed'): ?> <span class="badge bg-info text-dark">Confirmed</span>
                                <?php elseif($order['status']=='cancel'): ?> <span class="badge bg-danger">Dibatalkan</span>
                                <?php else: ?> <span class="badge bg-success">Paid</span> <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($order['status'] === 'paid'): ?>
                                    <?php if($eventDate < $today): ?>
                                        <button class="btn btn-sm btn-light text-muted" disabled>Selesai</button>
                                    <?php else: ?>
                                        <a href="attendee.php?id_order=<?= $id_order ?>" class="btn btn-sm btn-info text-white rounded-pill px-3">Attendee</a>
                                    <?php endif; ?>
                                <?php elseif($order['status'] === 'pending'): ?>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="bayar.php?id_order=<?= $id_order ?>" class="btn btn-sm btn-primary rounded-pill px-3">Bayar</a>
                                        <a href="batal_order.php?id_order=<?= $id_order ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Batalkan pesanan ini?')">Batal</a>
                                    </div>
                                <?php elseif($order['status'] === 'cancel'): ?>
                                    <span class="text-danger small fw-bold"><i class="fas fa-times-circle"></i> Dibatalkan</span>
                                <?php else: ?>
                                    <a href="bayar.php?id_order=<?= $id_order ?>" class="btn btn-sm btn-primary rounded-pill px-3">Bayar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mobile-card">
            <?php 
            mysqli_data_seek($orders, 0); 
            while ($order = mysqli_fetch_assoc($orders)): 
                $id_order = $order['id_order'];
                $details = mysqli_query($conn, "SELECT e.nama_event, e.tanggal, e.foto FROM order_detail od JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE od.id_order = '$id_order' LIMIT 1");
                $det = mysqli_fetch_assoc($details);
                $fotoPathMobile = "../assets/img/event/" . ($det['foto'] ? $det['foto'] : 'default.jpg');
            ?>
            <div class="order-card order-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center">
                        <img src="<?= $fotoPathMobile ?>" class="img-mobile-event shadow-sm" onerror="this.src='../bootstrap/image/image.png'">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><?= $det['nama_event'] ?></h6>
                            <small class="text-muted"><?= date('d M Y', strtotime($det['tanggal'])) ?></small>
                        </div>
                    </div>
                    <?php if($order['status']=='pending'): ?> <span class="badge bg-warning text-dark status-badge">Pending</span>
                    <?php elseif($order['status']=='confirmed'): ?> <span class="badge bg-info text-dark status-badge">Wait</span>
                    <?php elseif($order['status']=='cancel'): ?> <span class="badge bg-danger status-badge">Dibatalkan</span>
                    <?php else: ?> <span class="badge bg-success status-badge">Paid</span> <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="fw-bold text-primary">Rp <?= number_format($order['total']) ?></div>
                    <div>
                        <?php if($order['status'] === 'paid'): ?>
                            <?php if($det['tanggal'] < $today): ?>
                                <span class="text-muted small">Selesai</span>
                            <?php else: ?>
                                <a href="attendee.php?id_order=<?= $id_order ?>" class="btn btn-sm btn-info text-white rounded-pill px-3">E-Tiket</a>
                            <?php endif; ?>
                        <?php elseif($order['status'] === 'cancel'): ?>
                            <span class="text-danger small fw-bold"><i class="fas fa-times-circle"></i> Dibatalkan</span>
                        <?php elseif($order['status'] === 'pending'): ?>
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <a href="bayar.php?id_order=<?= $id_order ?>" class="btn btn-sm btn-primary rounded-pill px-4">Bayar</a>
                                <a href="batal_order.php?id_order=<?= $id_order ?>" class="btn btn-sm btn-outline-danger rounded-pill px-4" onclick="return confirm('Batalkan pesanan ini?')">Batal</a>
                            </div>
                        <?php else: ?>
                            <a href="bayar.php?id_order=<?= $id_order ?>" class="btn btn-sm btn-primary rounded-pill px-4">Bayar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-receipt fa-3x text-light mb-3"></i>
            <p class="text-muted">Belum ada transaksi.</p>
            <a href="dashboard.php" class="btn btn-primary rounded-pill">Cari Event</a>
        </div>
    <?php endif; ?>

    <div class="mt-4 text-center">
        <a href="dashboard.php" class="btn btn-link text-decoration-none text-muted"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('searchRiwayat').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.order-item').forEach(item => {
            item.style.display = item.textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });
</script>
</body>
</html>