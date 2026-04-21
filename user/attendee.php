<?php
session_start();
include '../config/config.php';

// Proteksi Login
if(!isset($_SESSION['login'])){
    header("Location: ../login/login.php");
    exit;
}

if($_SESSION['role'] != 'user'){
    echo "Akses ditolak!";
    exit;
}

// Cek ID Order
if(!isset($_GET['id_order'])){
    echo "Order tidak ditemukan!";
    exit;
}

$id_order = intval($_GET['id_order']);

// ================= FITUR AUTO RELOAD (LOGIKA SERVER) =================
// Jika ada request 'cek_ajax', kirimkan jumlah tiket yang sudah di-scan saja
if(isset($_GET['cek_ajax'])){
    $cek = mysqli_query($conn, "SELECT COUNT(*) as total FROM attendee a 
                                JOIN order_detail od ON a.id_detail = od.id_detail 
                                WHERE od.id_order = '$id_order' AND a.status_checkin = 'sudah'");
    $res = mysqli_fetch_assoc($cek);
    echo $res['total'];
    exit;
}
// =====================================================================

// Ambil data attendee untuk tampilan utama
$query = mysqli_query($conn, "
    SELECT a.kode_tiket, a.status_checkin, a.waktu_checkin,
    t.nama_tiket, e.nama_event
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    WHERE od.id_order = '$id_order'
");

// Hitung jumlah awal yang sudah di-scan untuk perbandingan di JavaScript
$initial_checkin = 0;
$data_array = [];
while($row = mysqli_fetch_assoc($query)){
    if($row['status_checkin'] == 'sudah') $initial_checkin++;
    $data_array[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Tiket | EventTix</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .ticket-card {
            background: white; border-radius: 15px; overflow: hidden; border: none;
            position: relative; max-width: 400px; margin: auto; transition: 0.3s;
        }
        .ticket-used { opacity: 0.7; filter: grayscale(1); }
        .ticket-card::before, .ticket-card::after {
            content: ""; position: absolute; top: 45%; width: 16px; height: 16px;
            background: #f0f2f5; border-radius: 50%; transform: translateY(-50%); z-index: 1;
        }
        .ticket-card::before { left: -8px; }
        .ticket-card::after { right: -8px; }
        .qr-wrapper { background: #f8f9fa; border-radius: 10px; padding: 10px; display: inline-block; }
        .badge-status { font-size: 0.7rem; letter-spacing: 1px; padding: 5px 12px; }
        @media print {
            .btn, .navbar, .back-nav { display: none !important; }
            .ticket-card { border: 1px solid #ddd; box-shadow: none; opacity: 1 !important; filter: none !important; }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-4 back-nav">
        <div>
            <h4 class="fw-bold mb-0 text-dark">E-Tiket Saya</h4>
            <p class="text-muted small mb-0">Order #<?= $id_order; ?></p>
        </div>
        <a href="riwayat.php" class="btn btn-sm btn-white bg-white border rounded-pill px-3 shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <?php if(count($data_array) > 0): ?>
        <div class="row g-4 justify-content-center">
            <?php foreach($data_array as $row): ?>
                <?php 
                $isUsed = ($row['status_checkin'] == 'sudah');
                $qrPayload = json_encode(['type' => 'attendee', 'code' => $row['kode_tiket']]); 
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card ticket-card shadow-sm h-100 <?= $isUsed ? 'ticket-used' : ''; ?>" id="ticket-<?= htmlspecialchars($row['kode_tiket']); ?>">
                        <div class="card-body text-center p-4">
                            
                            <div class="mb-3">
                                <?php if($isUsed): ?>
                                    <span class="badge rounded-pill mb-2 badge-status bg-danger">
                                        <i class="fas fa-times-circle me-1"></i> SUDAH TERPAKAI
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill mb-2 badge-status bg-success animate-pulse">
                                        <i class="fas fa-check-circle me-1"></i> SIAP DI-SCAN
                                    </span>
                                <?php endif; ?>

                                <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($row['nama_event']); ?></h5>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($row['nama_tiket']); ?></p>
                            </div>

                            <hr class="my-3" style="border-top: 2px dashed #ddd; opacity: 0.5;">

                            <div class="qr-wrapper mb-3">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($qrPayload); ?>" 
                                     alt="QR Code" class="img-fluid" style="width: 150px; <?= $isUsed ? 'opacity: 0.2;' : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <p class="mb-0" style="font-size: 0.65rem; color: #aaa; letter-spacing: 2px;">KODE TIKET UNIK</p>
                                <h5 class="fw-bold text-uppercase mb-0 <?= $isUsed ? 'text-decoration-line-through text-muted' : ''; ?>" style="letter-spacing: 2px;">
                                    <?= htmlspecialchars($row['kode_tiket']); ?>
                                </h5>
                            </div>

                            <?php if($row['waktu_checkin']): ?>
                                <div class="alert alert-light py-1 px-2 mb-2" style="font-size: 0.7rem;">
                                    Used on: <?= date('d M Y, H:i', strtotime($row['waktu_checkin'])); ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-3">
                                <?php if(!$isUsed): ?>
                                    <button type="button" class="btn btn-primary rounded-pill w-100 py-2 shadow-sm" 
                                            onclick="printAttendee('ticket-<?= htmlspecialchars($row['kode_tiket']); ?>')">
                                        <i class="fas fa-print me-2"></i> Cetak E-Tiket
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary rounded-pill w-100 py-2 disabled">
                                        <i class="fas fa-ban me-2"></i> Tiket Tidak Valid
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-ticket-alt fa-3x text-muted opacity-25 mb-3"></i>
            <p class="text-muted">Data tiket tidak ditemukan.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ================= SCRIPT AUTO RELOAD =================
const idOrder = <?= $id_order; ?>;
let lastCheckinCount = <?= $initial_checkin; ?>;

function checkTicketStatus() {
    // Memanggil file ini sendiri dengan parameter 'cek_ajax'
    fetch(`?id_order=${idOrder}&cek_ajax=1`)
        .then(response => response.text())
        .then(currentCount => {
            currentCount = parseInt(currentCount);
            
            // Jika jumlah yang sudah di-scan di database bertambah
            if (currentCount > lastCheckinCount) {
                // Beri sedikit jeda agar user bisa melihat status berubah (opsional)
                setTimeout(() => {
                    window.location.reload(); 
                }, 500);
            }
        })
        .catch(err => console.log('Error checking status:', err));
}

// Cek setiap 3 detik
setInterval(checkTicketStatus, 3000);

// ================= SCRIPT PRINT =================
function printAttendee(cardId) {
    const card = document.getElementById(cardId);
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Print E-Tiket</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 30px; text-align: center; }
                    .ticket-card { border: 2px solid #eee; border-radius: 15px; padding: 30px; display: inline-block; width: 100%; max-width: 400px; }
                    hr { border-top: 2px dashed #ddd; margin: 20px 0; opacity: 1; }
                    .btn { display:none !important; }
                </style>
            </head>
            <body>
                ${card.innerHTML}
                <script>
                    window.onload = function() { window.print(); window.close(); }
                <\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
}
</script>
</body>
</html>