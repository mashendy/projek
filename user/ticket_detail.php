<?php
session_start();
include '../config/config.php';

// proteksi login user
if(!isset($_SESSION['login'])){
    header('Location: ../login/login.php');
    exit;
}

if($_SESSION['role'] != 'user'){
    echo 'Akses ditolak!';
    exit;
}

$id_tiket = isset($_GET['id_tiket']) ? intval($_GET['id_tiket']) : 0;
$id_event = isset($_GET['id_event']) ? intval($_GET['id_event']) : 0;
if($id_tiket <= 0){
    echo 'Tiket tidak ditemukan.';
    exit;
}

$ticketQuery = mysqli_query($conn, "SELECT t.*, e.nama_event, e.tanggal, v.nama_venue FROM tiket t JOIN event e ON t.id_event = e.id_event JOIN venue v ON e.id_venue = v.id_venue WHERE t.id_tiket = '$id_tiket'");
if(mysqli_num_rows($ticketQuery) == 0){
    echo 'Tiket tidak ditemukan.';
    exit;
}
$ticket = mysqli_fetch_assoc($ticketQuery);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tiket</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h3 class="mb-1"><?= htmlspecialchars($ticket['nama_tiket']); ?></h3>
                            <p class="text-muted mb-0">Event: <?= htmlspecialchars($ticket['nama_event']); ?></p>
                        </div>
                        <a href="<?= $id_event ? 'order.php?id=' . intval($id_event) : 'dashboard.php'; ?>" class="btn btn-secondary">Kembali</a>
                    </div>

                    <div class="row gx-4 gy-4">
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-2"><strong>Venue</strong></p>
                                <p class="mb-3"><?= htmlspecialchars($ticket['nama_venue']); ?></p>
                                <p class="mb-2"><strong>Tanggal</strong></p>
                                <p class="mb-3"><?= date('d M Y', strtotime($ticket['tanggal'])); ?></p>
                                <p class="mb-2"><strong>Harga</strong></p>
                                <p class="mb-3">Rp <?= number_format($ticket['harga']); ?></p>
                                <p class="mb-2"><strong>Kuota</strong></p>
                                <p class="mb-0"><?= intval($ticket['kuota']); ?> tiket tersisa</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h5 class="mb-3">Detail Tiket</h5>
                                <p class="text-muted">Informasi lengkap mengenai tiket yang Anda pilih.</p>
                                <a href="order.php?id=<?= intval($ticket['id_event']); ?>" class="btn btn-success w-100">Pesan Tiket</a>
                            </div>
                        </div>
                    </div>
                </div>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-2"><strong>Venue</strong></p>
                                <p class="mb-3"><?= htmlspecialchars($ticket['nama_venue']); ?></p>
                                <p class="mb-2"><strong>Tanggal</strong></p>
                                <p class="mb-3"><?= date('d M Y', strtotime($ticket['tanggal'])); ?></p>
                                <p class="mb-2"><strong>Harga</strong></p>
                                <p class="mb-3">Rp <?= number_format($ticket['harga']); ?></p>
                                <p class="mb-2"><strong>Kuota</strong></p>
                                <p class="mb-0"><?= intval($ticket['kuota']); ?> tiket tersisa</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h5 class="mb-3">Detail Tiket</h5>
                                <p class="text-muted">Informasi lengkap mengenai tiket yang Anda pilih.</p>
                                <a href="order.php?id=<?= intval($ticket['id_event']); ?>" class="btn btn-success w-100">Pesan Tiket</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

