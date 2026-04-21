<?php
session_start();
include '../config/config.php';

if (!isset($_SESSION['login'])) {
    header('Location: ../login/login.php');
    exit;
}

if ($_SESSION['role'] != 'user') {
    echo 'Akses ditolak!';
    exit;
}

$id_order = isset($_GET['id_order']) ? intval($_GET['id_order']) : 0;
if ($id_order <= 0) {
    header('Location: riwayat.php');
    exit;
}

$id_user = $_SESSION['id_user'];
$orderQuery = mysqli_query($conn, "SELECT * FROM orders WHERE id_order = '$id_order' AND id_user = '$id_user'");
if (mysqli_num_rows($orderQuery) == 0) {
    header('Location: riwayat.php');
    exit;
}

$order = mysqli_fetch_assoc($orderQuery);

$details = mysqli_query($conn, "SELECT od.qty, od.subtotal, t.nama_tiket, e.nama_event, DATE_FORMAT(e.tanggal, '%d %M %Y') AS tanggal_event FROM order_detail od JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE od.id_order = '$id_order'");

$ticketList = [];
$eventName = '-';
$eventDate = '';
while ($detail = mysqli_fetch_assoc($details)) {
    $ticketList[] = htmlspecialchars($detail['nama_tiket']) . ' <span class="badge bg-light text-dark border">x' . intval($detail['qty']) . '</span>';
    $eventName = htmlspecialchars($detail['nama_event']);
    $eventDate = $detail['tanggal_event'];
}

$notice = '';
$noticeType = 'success';
$attendeeCodes = [];
$attendeeError = '';

// --- Fungsi Generator & Creator Attendee (Tetap Sama) ---
function generateAttendeeCode($orderId, $idDetail) {
    try { $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)); } 
    catch (Exception $e) { $random = strtoupper(substr(uniqid('', true), -8)); }
    return 'ATT-' . $orderId . '-' . $idDetail . '-' . $random;
}

function getAttendeeCodes($conn, $orderId) {
    $codes = [];
    $sql = "SELECT a.*, t.nama_tiket, e.nama_event, DATE_FORMAT(e.tanggal, '%d %M %Y') AS tanggal_event, od.qty FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE od.id_order = '$orderId'";
    $result = mysqli_query($conn, $sql);
    while ($row = @mysqli_fetch_assoc($result)) { $codes[] = $row; }
    return $codes;
}

function createAttendeeCodesForOrder($conn, $orderId) {
    $codes = [];
    $detailQuery = mysqli_query($conn, "SELECT od.id_detail, od.qty, t.nama_tiket, e.nama_event FROM order_detail od JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE od.id_order = '$orderId'");
    while ($detail = mysqli_fetch_assoc($detailQuery)) {
        $existing = mysqli_query($conn, "SELECT COUNT(*) AS count FROM attendee WHERE id_detail = '" . intval($detail['id_detail']) . "'");
        $countRow = mysqli_fetch_assoc($existing);
        $toCreate = max(0, intval($detail['qty']) - intval($countRow['count']));
        for ($i = 0; $i < $toCreate; $i++) {
            $code = generateAttendeeCode($orderId, $detail['id_detail']);
            mysqli_query($conn, "INSERT INTO attendee (id_detail, kode_tiket, status_checkin) VALUES ('" . intval($detail['id_detail']) . "', '" . mysqli_real_escape_string($conn, $code) . "', 'belum')");
        }
    }
    return getAttendeeCodes($conn, $orderId);
}

if (isset($_POST['bayar'])) {
    if ($order['status'] !== 'paid') {
        mysqli_query($conn, "UPDATE orders SET status = 'paid' WHERE id_order = '$id_order'");
        $order['status'] = 'paid';
        $attendeeCodes = createAttendeeCodesForOrder($conn, $id_order);
        $notice = 'Pembayaran Berhasil! Kode tiket telah diterbitkan.';
    }
}

if ($order['status'] === 'paid') {
    $attendeeCodes = getAttendeeCodes($conn, $id_order);
    if (empty($attendeeCodes)) { $attendeeCodes = createAttendeeCodesForOrder($conn, $id_order); }
}

$paymentQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode("ORDER-".$order['id_order']."-".$order['total']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout #<?= $order['id_order']; ?></title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Inter', sans-serif; font-size: 0.85rem; }
        @media (min-width: 992px) { .container-custom { max-width: 850px; margin: auto; } }
        
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .ticket-pass {
            border-left: 5px solid #198754;
            background: #fff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .qr-small { width: 80px; height: 80px; border: 1px solid #eee; padding: 2px; }
        .info-label { font-size: 0.75rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .method-card {
            cursor: pointer;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 8px;
            transition: 0.2s;
        }
        .form-check-input:checked + .method-card { border-color: #0d6efd; background: #f0f7ff; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container container-custom py-4">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Detail Pembayaran #<?= $order['id_order']; ?></h5>
        <a href="riwayat.php" class="btn btn-sm btn-outline-secondary">Riwayat</a>
    </div>

    <?php if ($notice): ?>
        <div class="alert alert-success py-2 px-3 small mb-3 border-0 shadow-sm"><?= $notice; ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-body p-3">
                    <div class="info-label">Event</div>
                    <div class="fw-bold text-truncate mb-2"><?= $eventName; ?></div>
                    
                    <div class="info-label">Waktu</div>
                    <div class="small mb-2"><?= $eventDate; ?></div>

                    <hr class="my-2 opacity-50">
                    
                    <div class="info-label">Item Tiket</div>
                    <div class="small mb-3"><?= implode('<br>', $ticketList); ?></div>

                    <div class="bg-light p-2 rounded d-flex justify-content-between align-items-center">
                        <span class="fw-bold small">Total Tagihan:</span>
                        <span class="fw-bold text-primary">Rp <?= number_format($order['total']); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($order['status'] === 'paid'): ?>
                <div class="text-center">
                    <div class="badge bg-success w-100 py-2 mb-2"><i class="fas fa-check-circle me-1"></i> Terbayar</div>
                    <a href="riwayat.php" class="btn btn-primary w-100">Lihat Riwayat <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-7">
            <?php if ($order['status'] !== 'paid'): ?>
                <div class="card">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3">Metode Pembayaran</h6>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="w-100 mb-2">
                                    <input type="radio" name="payment_method" value="cash" class="form-check-input d-none" id="payCash" checked>
                                    <div class="method-card d-flex align-items-center gap-3">
                                        <i class="fas fa-money-bill-wave text-success fs-4"></i>
                                        <div>
                                            <div class="fw-bold small">Bayar Tunai</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Bayar di kasir terdekat</div>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="w-100">
                                    <input type="radio" name="payment_method" value="qr" class="form-check-input d-none" id="payQr">
                                    <div class="method-card d-flex align-items-center gap-3">
                                        <i class="fas fa-qrcode text-primary fs-4"></i>
                                        <div>
                                            <div class="fw-bold small">Scan QRIS</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Otomatis & Instan</div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div id="qrBox" class="text-center d-none border rounded p-3 mb-3 bg-white">
                                <img src="<?= $paymentQrUrl; ?>" class="img-fluid mb-2" style="max-width: 150px;">
                                <div class="small text-muted">Scan QR di atas untuk membayar</div>
                            </div>

                            <button type="submit" name="bayar" class="btn btn-primary w-100 fw-bold py-2">Bayar Sekarang</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <h6 class="fw-bold mb-2">E-Ticket Attendee</h6>
                <?php foreach ($attendeeCodes as $code): ?>
                    <div class="ticket-pass shadow-sm">
                        <?php $qrData = json_encode(['type' => 'attendee', 'code' => $code['kode_tiket']]); ?>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($qrData); ?>" class="qr-small">
                        <div class="flex-grow-1">
                            <div class="info-label"><?= htmlspecialchars($code['nama_tiket']); ?></div>
                            <div class="fw-bold text-primary mb-1"><?= $code['kode_tiket']; ?></div>
                            <div class="badge bg-warning text-dark px-2" style="font-size: 0.65rem;">READY TO CHECK-IN</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrRadio = document.getElementById('payQr');
        const cashRadio = document.getElementById('payCash');
        const qrBox = document.getElementById('qrBox');

        function toggleQr() {
            qrBox.classList.toggle('d-none', !qrRadio.checked);
        }

        if(qrRadio) {
            qrRadio.addEventListener('change', toggleQr);
            cashRadio.addEventListener('change', toggleQr);
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>