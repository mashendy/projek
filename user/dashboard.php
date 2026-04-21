<?php
session_start();
include '../config/config.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'user') {
    header("Location: ../login/login.php");
    exit;
}

$today = date('Y-m-d');
// Query mengambil semua event, diurutkan dari yang terbaru
$data = mysqli_query($conn, "
    SELECT event.*, venue.nama_venue 
    FROM event 
    JOIN venue ON event.id_venue = venue.id_venue 
    ORDER BY tanggal DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>HenTix | Dashboard User</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        .hero-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            border: none;
        }
        .card-event {
            border-radius: 15px;
            transition: transform 0.2s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            overflow: hidden;
            position: relative;
        }
        
        /* Gaya untuk Event yang sudah lewat */
        .event-passed {
            filter: grayscale(1);
            opacity: 0.7;
        }
        .badge-passed {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
            background: #dc3545;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .event-img-container {
            height: 180px;
            width: 100%;
            overflow: hidden;
            background: #eee;
        }
        .event-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .modal-event-img {
            width: 100%;
            border-radius: 10px;
            max-height: 300px;
            object-fit: cover;
        }
        
        @media (min-width: 992px) {
            .card-event:not(.event-passed):hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-3 py-md-5">

    <div class="hero-card card shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8 text-center text-lg-start">
                    <h2 class="fw-bold mb-1">Hai, <?= htmlspecialchars($_SESSION['nama']); ?>! 👋</h2>
                    <p class="mb-3 opacity-75">Sudah siap untuk petualangan baru?</p>
                </div>
                <div class="col-lg-4 text-center text-lg-end">
                    <a href="riwayat.php" class="btn btn-light btn-sm rounded-pill px-4 fw-bold">
                        <i class="fas fa-history me-1"></i> Riwayat Pesanan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-3 align-items-center">
        <div class="col-12 col-md-6">
            <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Daftar Event</h5>
        </div>
        <div class="col-12 col-md-6">
            <div class="input-group text-muted">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="search" id="searchEvent" class="form-control border-start-0" placeholder="Cari nama event...">
            </div>
        </div>
    </div>

    <div class="row g-3" id="eventCards">
        <?php if (mysqli_num_rows($data) > 0): ?>
            <?php while ($d = mysqli_fetch_assoc($data)) { 
                // Cek apakah tanggal sudah lewat
                $isPassed = (strtotime($d['tanggal']) < strtotime($today));

                // Ambil data tiket
                $ticketResult = mysqli_query($conn, "SELECT * FROM tiket WHERE id_event = '{$d['id_event']}'");
                $tickets = [];
                $hasAvailableTicket = false;
                while ($ticketRow = mysqli_fetch_assoc($ticketResult)) {
                    $tickets[] = $ticketRow;
                    if (intval($ticketRow['kuota']) > 0) $hasAvailableTicket = true;
                }
                
                $eventLink = "order.php?id=" . $d['id_event'];
                $foto_path = "../assets/img/event/" . ($d['foto'] ? $d['foto'] : 'default_event.jpg');
            ?>
            <div class="col-12 col-md-6 col-lg-4 event-item">
                <div class="card card-event h-100 <?= $isPassed ? 'event-passed' : ''; ?>">
                    
                    <?php if($isPassed): ?>
                        <div class="badge-passed"><i class="fas fa-times-circle me-1"></i> Selesai</div>
                    <?php endif; ?>

                    <div class="event-img-container">
                        <img src="<?= $foto_path ?>" class="event-img" alt="Event" onerror="this.src='../bootstrap/image/image.png'">
                    </div>

                    <div class="card-body d-flex flex-column p-3">
                        <h6 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($d['nama_event']); ?></h6>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-map-marker-alt text-danger me-2 small"></i> 
                                <span class="text-muted small"><?= htmlspecialchars($d['nama_venue']); ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-day text-primary me-2 small"></i> 
                                <span class="text-muted small"><?= date('d M Y', strtotime($d['tanggal'])); ?></span>
                            </div>
                            <?php if ($isPassed): ?>
                                <div class="text-danger small mt-2"><i class="fas fa-exclamation-circle me-1"></i> Event ini sudah lewat dan tidak dapat dipesan.</div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-auto">
                            <?php if ($isPassed): ?>
                                <button type="button" class="btn btn-secondary btn-sm w-100 rounded-pill fw-bold" disabled>
                                    Event Selesai
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn <?= $hasAvailableTicket ? 'btn-outline-primary' : 'btn-light'; ?> btn-sm w-100 rounded-pill fw-bold" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#eventModal<?= $d['id_event']; ?>"
                                        <?= !$hasAvailableTicket ? 'disabled' : ''; ?>>
                                    <?= $hasAvailableTicket ? 'Lihat Detail' : 'Tiket Habis'; ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="eventModal<?= $d['id_event']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0">
                        <div class="modal-header border-0 pb-0">
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-4">
                                <div class="col-lg-5">
                                    <img src="<?= $foto_path ?>" class="modal-event-img shadow-sm mb-3" onerror="this.src='../bootstrap/image/image.png'">
                                    <div class="p-3 bg-light rounded-3">
                                        <h5 class="fw-bold text-primary mb-2"><?= htmlspecialchars($d['nama_event']); ?></h5>
                                        <div class="small text-muted mb-1"><i class="fas fa-map-pin me-1"></i> <?= htmlspecialchars($d['nama_venue']); ?></div>
                                        <div class="small text-muted"><i class="fas fa-calendar me-1"></i> <?= date('d F Y', strtotime($d['tanggal'])); ?></div>
                                        <?php if ($isPassed): ?>
                                            <div class="mt-3 alert alert-danger py-2 small mb-0">
                                                <i class="fas fa-exclamation-triangle me-1"></i> Event ini sudah lewat dan tidak dapat dipesan.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <p class="fw-bold mb-3 small text-uppercase text-muted">Kategori Tiket:</p>
                                    <div class="list-group list-group-flush border rounded-3 mb-4">
                                        <?php if (count($tickets) > 0): ?>
                                            <?php foreach ($tickets as $ticket): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                                    <div>
                                                        <div class="fw-bold small"><?= htmlspecialchars($ticket['nama_tiket']); ?></div>
                                                        <div class="text-success fw-bold">Rp<?= number_format($ticket['harga']) ?></div>
                                                    </div>
                                                    <span class="badge <?= (intval($ticket['kuota']) > 0 && !$isPassed) ? 'bg-success' : 'bg-danger'; ?> rounded-pill shadow-sm">
                                                        <?php 
                                                            if($isPassed) echo 'Selesai';
                                                            else echo intval($ticket['kuota']) > 0 ? 'Tersedia' : 'Habis';
                                                        ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="p-4 text-center small text-muted">Belum ada informasi tiket.</div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!$isPassed && $hasAvailableTicket): ?>
                                        <a href="<?= htmlspecialchars($eventLink) ?>" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow">
                                            Pesan Tiket Sekarang
                                        </a>
                                        <a href="" class="btn btn-animate btn-light w-100 rounded-pill py-2 mt-2 fw-bold" data-bs-dismiss="modal">
                                            Batal
                                        </a>
                                    <?php else: ?>
                                        <div class="alert alert-warning text-center py-2 rounded-pill small">
                                            <i class="fas fa-info-circle me-1"></i> 
                                            <?= $isPassed ? 'Acara ini sudah berakhir' : 'Tiket sudah tidak tersedia'; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted opacity-25 mb-3"></i>
                <p class="text-muted">Belum ada event yang dipublikasikan.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fitur Live Search
    document.getElementById('searchEvent').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.event-item').forEach(item => {
            const title = item.querySelector('.card-title').textContent.toLowerCase();
            item.style.display = title.includes(query) ? '' : 'none';
        });
    });
</script>
</body>
</html>
