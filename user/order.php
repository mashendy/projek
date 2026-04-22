<?php
session_start();
include '../config/config.php';

// Proteksi login user
if(!isset($_SESSION['login'])){
    header('Location: ../login/login.php');
    exit;
}

if($_SESSION['role'] != 'user'){
    echo 'Akses ditolak!';
    exit;
}

$id_event = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id_event <= 0){
    header('Location: dashboard.php');
    exit;
}

// Ambil data event
$eventQuery = mysqli_query($conn, "SELECT e.*, v.nama_venue FROM event e JOIN venue v ON e.id_venue = v.id_venue WHERE e.id_event = '$id_event'");
if(mysqli_num_rows($eventQuery) == 0){
    echo 'Event tidak ditemukan.';
    exit;
}
$event = mysqli_fetch_assoc($eventQuery);

// =========================================================================
// PENGATURAN LIMIT PEMBELIAN MANUAL (Tanpa Tabel Baru)
// Silakan ubah nama tiket dan jumlah maksimalnya di sini
// =========================================================================
$limit_per_tiket = [
    'VIP'        => 2,  // Maksimal 2 tiket
    'VVIP'       => 1,  // Maksimal 1 tiket
    'Presale'    => 4,  // Maksimal 4 tiket
    'Regular'    => 5   // Maksimal 5 tiket
];
$default_limit = 5; // Jika nama tiket tidak ada di daftar atas, limitnya ini
// =========================================================================

$message = '';
$messageType = '';
$orderSummary = false;

// Proses Beli
if(isset($_POST['beli'])){
    $id_tiket = isset($_POST['id_tiket']) ? intval($_POST['id_tiket']) : 0;
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 0;
    $voucherCode = isset($_POST['voucher']) ? trim($_POST['voucher']) : '';

    if($id_tiket <= 0){
        $message = 'Gagal! Silakan pilih jenis tiket terlebih dahulu.';
        $messageType = 'danger';
    } elseif($qty < 1){
        $message = 'Gagal! Jumlah pesanan minimal adalah 1 tiket.';
        $messageType = 'danger';
    } else {
        $ticketQuery = mysqli_query($conn, "SELECT * FROM tiket WHERE id_tiket = '$id_tiket' AND id_event = '$id_event'");

        if(mysqli_num_rows($ticketQuery) == 0){
            $message = 'Tiket tidak ditemukan untuk event ini.';
            $messageType = 'danger';
        } else {
            $ticket = mysqli_fetch_assoc($ticketQuery);
            
            // Logika Cek Limit berdasarkan nama tiket
            $nama_tk = $ticket['nama_tiket'];
            $max_boleh_beli = isset($limit_per_tiket[$nama_tk]) ? $limit_per_tiket[$nama_tk] : $default_limit;

            if($qty > $max_boleh_beli){
                $message = "Maaf, pembelian tiket $nama_tk dibatasi maksimal $max_boleh_beli per transaksi agar semua kebagian.";
                $messageType = 'danger';
            } elseif($qty > $ticket['kuota']){
                $message = 'Stok tiket tidak cukup.';
                $messageType = 'danger';
            } else {
                $subtotal = $qty * $ticket['harga'];
                $discount = 0;
                $voucherMessage = '';
                $voucherId = 'NULL';

                // Proses Voucher
                if($voucherCode !== ''){
                    $vStr = mysqli_real_escape_string($conn, $voucherCode);
                    $vQ = mysqli_query($conn, "SELECT * FROM voucher WHERE kode_voucher = '$vStr' AND status = 'aktif' AND kuota > 0");
                    if(mysqli_num_rows($vQ) == 0){
                        $message = 'Voucher tidak valid.';
                        $messageType = 'danger';
                    } else {
                        $vData = mysqli_fetch_assoc($vQ);
                        $discount = floor($subtotal * $vData['potongan'] / 100);
                        $subtotal = max(0, $subtotal - $discount);
                        $voucherId = intval($vData['id_voucher']);
                        mysqli_query($conn, "UPDATE voucher SET kuota = kuota - 1 WHERE id_voucher = '$voucherId'");
                        $voucherMessage = 'Diskon Rp ' . number_format($discount) . ' diterapkan. ';
                    }
                }

                if($messageType !== 'danger'){
                    $id_user = intval($_SESSION['id_user']);
                    $qOrder = "INSERT INTO orders (id_user, tanggal_order, total, status, id_voucher) 
                               VALUES ('$id_user', NOW(), '$subtotal', 'pending', " . ($voucherId !== 'NULL' ? "'$voucherId'" : 'NULL') . ")";
                    
                    if(mysqli_query($conn, $qOrder)){
                        $id_order = mysqli_insert_id($conn);
                        mysqli_query($conn, "INSERT INTO order_detail (id_order, id_tiket, qty, subtotal) VALUES ('$id_order', '$id_tiket', '$qty', '$subtotal')");
                        mysqli_query($conn, "UPDATE tiket SET kuota = kuota - $qty WHERE id_tiket = '$id_tiket'");

                        $message = 'Berhasil memesan tiket! ' . $voucherMessage;
                        $messageType = 'success';
                        $orderSummary = true;
                    } else {
                        $message = 'Terjadi kesalahan sistem.';
                        $messageType = 'danger';
                    }
                }
            }
        }
    }
}

// Ambil tiket yang tersedia
$tickets = mysqli_query($conn, "SELECT * FROM tiket WHERE id_event = '$id_event' AND kuota > 0");
$availableTickets = [];
while ($row = mysqli_fetch_assoc($tickets)) {
    // Sisipkan info limit ke tiap data tiket agar bisa dibaca JavaScript
    $nama = $row['nama_tiket'];
    $row['limit_manual'] = isset($limit_per_tiket[$nama]) ? $limit_per_tiket[$nama] : $default_limit;
    $availableTickets[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tiket - HenTix</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Inter', sans-serif; font-size: 0.9rem; }
        @media (min-width: 992px) { .container-custom { max-width: 900px; margin: auto; } }
        .card { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #555; }
        .ticket-item { padding: 10px 15px; background: #fff; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px; }
        .total-box { background: #f0f7ff; border: 1px dashed #0d6efd; border-radius: 8px; padding: 12px; }
        .limit-tag { font-size: 0.7rem; color: #dc3545; font-weight: bold; background: #ffeef0; padding: 2px 5px; border-radius: 4px; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container container-custom py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1"><?= htmlspecialchars($event['nama_event']); ?></h4>
            <p class="text-muted mb-0 small"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($event['nama_venue']); ?></p>
        </div>
        <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    <div id="jsAlert"></div>

    <?php if($message): ?>
        <div class="alert alert-<?= $messageType; ?> py-2 small d-flex justify-content-between align-items-center">
            <span><?= $message; ?></span>
            <?php if($orderSummary): ?>
                <a href="riwayat.php" class="btn btn-xs btn-success py-0 px-2" style="font-size: 0.7rem;">Cek Riwayat</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Pilihan Tiket</h6>
                    <?php foreach($availableTickets as $ticket): ?>
                        <div class="ticket-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold small"><?= htmlspecialchars($ticket['nama_tiket']); ?></div>
                                <div class="text-primary small fw-bold">Rp <?= number_format($ticket['harga']); ?></div>
                                <div class="limit-tag">Maks: <?= $ticket['limit_manual']; ?> tiket</div>
                            </div>
                            <span class="badge bg-light text-dark border small">Sisa <?= $ticket['kuota']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <form id="orderForm" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Jenis Tiket</label>
                            <select id="ticketSelector" name="id_tiket" class="form-select" required>
                                <option value="" disabled selected>Pilih tiket...</option>
                                <?php foreach($availableTickets as $ticket): ?>
                                    <option value="<?= $ticket['id_tiket']; ?>" 
                                            data-kuota="<?= $ticket['kuota']; ?>" 
                                            data-price="<?= $ticket['harga']; ?>"
                                            data-max="<?= $ticket['limit_manual']; ?>">
                                        <?= htmlspecialchars($ticket['nama_tiket']); ?> (Maks: <?= $ticket['limit_manual']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label">Jumlah</label>
                                <input id="ticketQty" type="number" name="qty" class="form-control text-center" value="1" min="1">
                            </div>
                            <div class="col-8">
                                <label class="form-label">Voucher</label>
                                <input type="text" name="voucher" class="form-control text-uppercase" placeholder="Masukkan Kode">
                            </div>
                        </div>

                        <div class="total-box mt-3 mb-3 d-flex justify-content-between align-items-center">
                            <span class="small fw-bold">Estimasi Total:</span>
                            <span id="totalPrice" class="h6 fw-bold text-primary mb-0">Rp 0</span>
                        </div>

                        <button type="submit" name="beli" class="btn btn-primary w-100 fw-bold py-2">
                            Pesan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const orderForm = document.getElementById('orderForm');
        const selector = document.getElementById('ticketSelector');
        const qtyInput = document.getElementById('ticketQty');
        const totalDisplay = document.getElementById('totalPrice');
        const alertBox = document.getElementById('jsAlert');

        function calculate() {
            const opt = selector.selectedOptions[0];
            if (!opt || opt.value === "") return;

            const price = parseInt(opt.dataset.price);
            const kuota = parseInt(opt.dataset.kuota);
            const maxLimit = parseInt(opt.dataset.max); 
            
            let val = parseInt(qtyInput.value);

            // Validasi Input agar tidak melebihi limit manual
            if (val > maxLimit) {
                alert('Maksimal pembelian untuk tiket ini adalah ' + maxLimit + ' tiket.');
                val = maxLimit;
                qtyInput.value = maxLimit;
            }
            
            // Tetap validasi kuota fisik
            if (val > kuota) {
                val = kuota;
                qtyInput.value = kuota;
            }

            if (isNaN(val) || val < 1) {
                totalDisplay.innerText = 'Rp 0';
                return;
            }

            totalDisplay.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(price * val);
        }

        orderForm.addEventListener('submit', function(e) {
            const opt = selector.selectedOptions[0];
            const qty = parseInt(qtyInput.value);
            
            alertBox.innerHTML = '';

            if (selector.value === "" || selector.value === null) {
                e.preventDefault();
                alert('Silakan pilih jenis tiket terlebih dahulu!');
                return false;
            }

            const maxLimit = parseInt(opt.dataset.max);
            if (qty > maxLimit) {
                e.preventDefault();
                alertBox.innerHTML = '<div class="alert alert-danger py-2 small">Gagal! Maksimal pembelian tiket ini adalah ' + maxLimit + '.</div>';
                return false;
            }
        });

        selector.addEventListener('change', calculate);
        qtyInput.addEventListener('input', calculate);
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>