<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold fs-4 text-white d-flex align-items-center" href="dashboard.php">
            <i class="fas fa-tachometer-alt me-2"></i>
            Admin Dashboard
        </a>

        <div class="d-flex align-items-center ms-auto">
            <span class="text-white me-3">
                <i class="fas fa-user-circle me-2"></i>
                Halo, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></strong>
            </span>
            <a href="../login/logout.php" class="btn btn-light btn-sm text-dark">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar py-4 px-3">
            <h5 class="text-muted mb-4 px-3 fw-semibold">MENU UTAMA</h5>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home me-3"></i> Dashboard
                </a>
                <a href="venue.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'venue.php' ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt me-3"></i> Kelola Lokasi
                </a>
                <a href="event.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'event.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt me-3"></i> Kelola Event
                </a>
                <a href="tiket.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'tiket.php' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt me-3"></i> Kelola Tiket
                </a>
                <a href="voucher.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'voucher.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tags me-3"></i> Kelola Voucher
                </a>
                <a href="transaksi.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'transaksi.php' ? 'active' : ''; ?>">
                    <i class="fas fa-receipt me-3"></i> Transaksi
                </a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 py-4">
