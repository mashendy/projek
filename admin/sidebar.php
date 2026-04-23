<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold fs-4 text-white d-flex align-items-center" href="dashboard.php">
            <div class="bg-white rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                <i class="fas fa-tachometer-alt text-primary" style="font-size: 0.9rem;"></i>
            </div>
            <span>Admin<span class="fw-light">Panel</span></span>
        </a>

        <div class="d-flex align-items-center ms-auto">
            <div class="text-white me-3 d-none d-sm-block">
                <small class="opacity-75 d-block text-end" style="font-size: 0.7rem;">Logged in as</small>
                <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></strong>
            </div>
            <a href="../login/logout.php" class="btn btn-light btn-sm fw-bold px-3 shadow-sm" style="border-radius: 8px;">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar py-3 px-0">
            <p class="sidebar-heading px-4">Menu Utama</p>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action <?= $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home me-3"></i> Dashboard
                </a>
                <a href="venue.php" class="list-group-item list-group-item-action <?= $current_page == 'venue.php' ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt me-3"></i> Kelola Lokasi
                </a>
                <a href="event.php" class="list-group-item list-group-item-action <?= $current_page == 'event.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt me-3"></i> Kelola Event
                </a>
                <a href="tiket.php" class="list-group-item list-group-item-action <?= $current_page == 'tiket.php' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt me-3"></i> Kelola Tiket
                </a>
                
                <p class="sidebar-heading px-4">Marketing</p>
                <a href="voucher.php" class="list-group-item list-group-item-action <?= $current_page == 'voucher.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tags me-3"></i> Kelola Voucher
                </a>
                
                <p class="sidebar-heading px-4">Laporan</p>
                <a href="transaksi.php" class="list-group-item list-group-item-action <?= $current_page == 'transaksi.php' ? 'active' : ''; ?>">
                    <i class="fas fa-receipt me-3"></i> Transaksi
                </a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 py-4 px-4">