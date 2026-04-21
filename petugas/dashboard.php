<?php
session_start();
include '../config/config.php';

// Proteksi login petugas
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'petugas') {
    header('Location: ../login/login.php');
    exit;
}

$notice = '';
$noticeType = 'success';
$scanResult = null;

// SCAN
if (isset($_POST['scan_code'])) {
    $code = trim($_POST['code']);
    if ($code === '') {
        $notice = 'Kode tidak boleh kosong.';
        $noticeType = 'warning';
    } else {
        $codeEsc = mysqli_real_escape_string($conn, $code);

        $q = mysqli_query($conn, "SELECT a.*, od.id_order, t.nama_tiket, e.nama_event, 
            DATE_FORMAT(e.tanggal, '%d %M %Y') AS tanggal_event, 
            o.status AS order_status 
            FROM attendee a
            JOIN order_detail od ON a.id_detail = od.id_detail
            JOIN tiket t ON od.id_tiket = t.id_tiket
            JOIN event e ON t.id_event = e.id_event
            JOIN orders o ON od.id_order = o.id_order
            WHERE a.kode_tiket = '$codeEsc' LIMIT 1");

        if ($q && mysqli_num_rows($q) > 0) {
            $scanResult = mysqli_fetch_assoc($q);

            if ($scanResult['status_checkin'] == 'sudah') {
                $notice = 'Tiket ini sudah pernah di-check-in sebelumnya';
                $noticeType = 'info';
            } elseif ($scanResult['order_status'] != 'paid') {
                $notice = 'Tiket ini belum dibayar!';
                $noticeType = 'warning';
            } else {
                $now = date('Y-m-d H:i:s');
                mysqli_query($conn, "UPDATE attendee SET 
                    status_checkin='sudah', 
                    waktu_checkin='$now' 
                    WHERE id_attendee = " . $scanResult['id_attendee']);

                $scanResult['status_checkin'] = 'sudah';
                $scanResult['waktu_checkin'] = $now;

                $notice = '✅ Check-in BERHASIL!';
                $noticeType = 'success';
            }
        } else {
            $notice = '❌ Kode tiket tidak ditemukan';
            $noticeType = 'danger';
        }
    }
}

// Statistik
$totalAttendees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendee"))['total'] ?? 0;
$checkedIn = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendee WHERE status_checkin='sudah'"))['total'] ?? 0;
$notCheckedIn = $totalAttendees - $checkedIn;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Tiket - Petugas</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        .scanner-card {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        #reader {
            border-radius: 15px;
            overflow: hidden;
            border: 4px solid #667eea;
        }
        .result-card {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        .stat-card {
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-8px);
        }
        .badge-checkin {
            font-size: 1.05rem;
            padding: 10px 20px;
        }
    </style>
</head>
<body>

<!-- Navbar Gradient (sama seperti Admin) -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center" href="scan.php">
            <div class="bg-white text-primary p-2 rounded-3 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                <i class="fas fa-qrcode"></i>
            </div>
            <span>Hen<span class="opacity-75">Tix</span> <small class="fw-light fs-6"></small></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPetugas" aria-controls="navPetugas" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navPetugas">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item ms-lg-3">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active fw-bold' : '' ?>" href="scan.php">
                        <i class="fas fa-camera me-1"></i> Scanner
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'kode.php' ? 'active fw-bold' : '' ?>" href="kode.php">
                        <i class="fas fa-keyboard me-1"></i> Input Kode
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'order.php' ? 'active fw-bold' : '' ?>" href="order.php">
                        <i class="fas fa-clipboard-check me-1"></i> Validasi Pembayaran
                    </a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center border-top border-lg-0 pt-3 pt-lg-0 mt-3 mt-lg-0">
                <div class="text-white me-3 d-none d-sm-block">
                    <small class="d-block opacity-75" style="font-size: 0.7rem; line-height: 1;">Selamat Bertugas,</small>
                    <span class="fw-semibold"><?= htmlspecialchars($_SESSION['nama'] ?? 'Petugas'); ?></span>
                </div>
                <a href="../login/logout.php" class="btn btn-light btn-sm px-3 rounded-pill shadow-sm text-primary fw-bold">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container py-5">

    <?php if ($notice): ?>
    <div class="alert alert-<?= $noticeType ?> alert-dismissible fade show shadow-sm" role="alert">
        <?= $notice ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Scanner -->
        <div class="col-lg-5">
            <div class="scanner-card card h-100">
                <div class="card-body text-center p-4">
                    <h4 class="mb-4 fw-semibold text-primary">
                        <i class="fas fa-camera me-2"></i> Scanner Kamera
                    </h4>
                    <div id="reader"></div>
                    <p class="small text-muted mt-3">
                        Arahkan kamera ke QR Code pada tiket<br>
                        Scan akan otomatis memproses
                    </p>
                </div>
            </div>
        </div>

        <!-- Hasil Scan & Statistik -->
        <div class="col-lg-7">
            <!-- Hasil Scan -->
            <div class="result-card card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-clipboard-list me-2"></i> Hasil Scan
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($scanResult): ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Event</strong><br>
                                <?= htmlspecialchars($scanResult['nama_event']) ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Tanggal Event</strong><br>
                                <?= $scanResult['tanggal_event'] ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Jenis Tiket</strong><br>
                                <?= htmlspecialchars($scanResult['nama_tiket']) ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Status Check-in</strong><br>
                                <span class="badge badge-checkin <?= $scanResult['status_checkin']=='sudah' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= strtoupper($scanResult['status_checkin']) ?>
                                </span>
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Kode Tiket</strong><br>
                                <div class="text-center mt-2">
                                    <img src="https://barcode.tec-it.com/barcode.ashx?data=<?= urlencode($scanResult['kode_tiket']); ?>&code=Code128&translate-esc=true&unit=Fit&dpi=96" alt="Barcode <?= htmlspecialchars($scanResult['kode_tiket']); ?>" class="img-fluid" style="max-height:100px;" />
                                    <div class="mt-2"><small class="text-monospace"><?= htmlspecialchars($scanResult['kode_tiket']); ?></small></div>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($scanResult['waktu_checkin'])): ?>
                        <div class="mt-3 pt-3 border-top">
                            <strong>Waktu Check-in:</strong> 
                            <?= date('d M Y H:i', strtotime($scanResult['waktu_checkin'])) ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-qrcode fa-5x mb-3 opacity-25"></i>
                            <p class="fs-5">Belum ada tiket yang discan</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="stat-card card text-center text-white bg-primary">
                        <div class="card-body py-4">
                            <h2 class="mb-1"><?= number_format($totalAttendees) ?></h2>
                            <p class="mb-0">Total Tiket</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card card text-center text-white bg-success">
                        <div class="card-body py-4">
                            <h2 class="mb-1"><?= number_format($checkedIn) ?></h2>
                            <p class="mb-0">Sudah Check-in</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card card text-center text-dark bg-warning">
                        <div class="card-body py-4">
                            <h2 class="mb-1"><?= number_format($notCheckedIn) ?></h2>
                            <p class="mb-0">Belum Check-in</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form untuk submit scan -->
<form method="POST" id="scanForm">
    <input type="hidden" name="code" id="code">
    <input type="hidden" name="scan_code" value="1">
</form>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../bootstrap/js/html5-qrcode.min.js"></script>
<script>
// Parse scanned value (support berbagai format)
function parseScannedValue(value) {
    if (!value) return '';
    value = value.trim();

    try {
        const parsed = JSON.parse(value);
        if (parsed.code) return parsed.code.trim();
        if (parsed.ticket) return parsed.ticket.trim();
    } catch (e) {}

    try {
        const url = new URL(value);
        if (url.searchParams.has('code')) return url.searchParams.get('code').trim();
        if (url.searchParams.has('ticket')) return url.searchParams.get('ticket').trim();
    } catch (e) {}

    return value;
}

function onScanSuccess(decodedText) {
    const parsedCode = parseScannedValue(decodedText);
    if (parsedCode) {
        document.getElementById('code').value = parsedCode;
        document.getElementById('scanForm').submit();
    }
}

// Inisialisasi Scanner
let scanner = new Html5QrcodeScanner("reader", {
    fps: 12,
    qrbox: { width: 280, height: 280 }
});

scanner.render(onScanSuccess).catch(err => {
    console.error('Scanner initialization failed:', err);
    document.getElementById('reader').innerHTML = `
        <div class="text-center p-4">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <p class="text-muted">Scanner gagal dimuat. Pastikan koneksi internet stabil dan izinkan akses kamera.</p>
            <small class="text-muted">Error: ${err.message}</small>
        </div>
    `;
});
</script>

</body>
</html>