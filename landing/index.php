<?php
session_start();
include '../config/config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HenTix - Pesan Tiket Event Terbaik</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
        }

        body { 
            font-family: 'Inter', 'Segoe UI', sans-serif; 
            color: #2d3436;
            scroll-behavior: smooth;
        }

        /* Navbar Custom */
        .navbar {
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        
        /* Hero Section */
        .hero {
            background: var(--primary-gradient);
            color: white;
            padding: 140px 0 100px;
            clip-path: ellipse(150% 100% at 50% 0%);
        }
        .hero h1 {
            font-size: 3.8rem;
            font-weight: 800;
            letter-spacing: -1px;
        }
        .hero-img {
            transition: transform 0.5s ease;
            filter: drop-shadow(0 20px 30px rgba(0,0,0,0.2));
            border-radius: 40px;
        }
        .hero-img:hover {
            transform: scale(1.02) rotate(1deg);
        }

        /* Features */
        .feature-icon {
            width: 80px;
            height: 80px;
            background: white;
            color: #667eea;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            transition: 0.3s;
        }
        .col-md-4:hover .feature-icon {
            background: #667eea;
            color: white;
            transform: translateY(-5px);
        }

        /* Event Cards */
        .card-event {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: #ffffff;
        }
        .card-event:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 60px rgba(102, 126, 234, 0.2) !important;
        }
        .card-event img {
            height: 220px;
            object-fit: cover;
            transition: 0.5s;
        }
        .card-event:hover img {
            transform: scale(1.1);
        }

        .btn-booking {
            background: var(--primary-gradient);
            border: none;
            border-radius: 50px; /* Samakan jadi Pill */
            padding: 12px;
            font-weight: 600;
            color: white;
            transition: 0.3s;
        }
        .btn-booking:hover {
            opacity: 0.9;
            box-shadow: 0 8px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        /* Footer Modern */
        .footer {
            background: #0f172a;
            color: #cbd5e1;
            position: relative;
        }
        .footer h4, .footer h6 {
            color: #ffffff !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .footer .list-unstyled li a {
            color: #94a3b8 !important; 
            transition: 0.3s;
        }
        .footer .list-unstyled li a:hover {
            color: #ffffff !important;
            padding-left: 5px;
        }

        .btn-subscribe {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3" href="#">
            <i class="fas fa-ticket-alt me-2"></i>HenTix
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link px-3" href="#events">Event</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#why">Keunggulan</a></li>
                
                <?php if(isset($_SESSION['login'])): ?>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle btn btn-outline-light rounded-pill px-4" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> Akun Saya
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 animate slideIn">
                            <?php if($_SESSION['role'] == 'user'): ?>
                                <li><a class="dropdown-item" href="../user/dashboard.php"><i class="fas fa-th-large me-2"></i>Dashboard</a></li>
                            <?php elseif($_SESSION['role'] == 'admin'): ?>
                                <li><a class="dropdown-item" href="../admin/dashboard.php"><i class="fas fa-user-shield me-2"></i>Admin Panel</a></li>
                            <?php elseif($_SESSION['role'] == 'petugas'): ?>
                                <li><a class="dropdown-item" href="../petugas/scan.php"><i class="fas fa-qrcode me-2"></i>Scan Tiket</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../login/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="../login/login.php">Login</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" href="../login/register.php">Daftar Sekarang</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="mb-4">Temukan Event Seru &<br>Pesan Tiket Mudah</h1>
                <p class="lead mb-5 opacity-75">Platform tepercaya untuk mendapatkan tiket konser, seminar, dan workshop dalam satu klik.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#events" class="btn btn-light btn-lg px-5 py-3 rounded-pill fw-bold shadow">
                        Mulai Cari <i class="fas fa-search ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="../landing/image.png" alt="Hero Event" class="img-fluid hero-img shadow-lg" onerror="this.src='https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'">
            </div>
        </div>
    </div>
</section>

<section id="why" class="py-5 bg-white">
    <div class="container py-5">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="feature-icon mx-auto"><i class="fas fa-bolt"></i></div>
                <h4 class="fw-bold">Instan</h4>
                <p class="text-muted px-lg-4">E-tiket langsung dapat kode unik setelah pembayaran dikonfirmasi.</p>
            </div>
            <div class="col-md-4">
                <div class="feature-icon mx-auto"><i class="fas fa-shield-heart"></i></div>
                <h4 class="fw-bold">Aman</h4>
                <p class="text-muted px-lg-4">Sistem pembayaran terenkripsi untuk menjaga keamanan transaksi Anda.</p>
            </div>
            <div class="col-md-4">
                <div class="feature-icon mx-auto"><i class="fas fa-star"></i></div>
                <h4 class="fw-bold">Eksklusif</h4>
                <p class="text-muted px-lg-4">Dapatkan akses ke event pilihan yang hanya tersedia di EventTix.</p>
            </div>
        </div>
    </div>
</section>

<section id="events" class="py-5 bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold mb-0">Event Mendatang</h2>
                <p class="text-muted">Pilih pengalaman tak terlupakanmu hari ini</p>
            </div>
            <a href="<?= isset($_SESSION['login']) ? '../user/dashboard.php' : '../login/login.php' ?>" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
                Lihat Semua <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php
            $query = mysqli_query($conn, "
                SELECT event.*, venue.nama_venue 
                FROM event 
                JOIN venue ON event.id_venue = venue.id_venue 
                WHERE event.tanggal >= CURDATE() 
                ORDER BY event.tanggal ASC 
                LIMIT 6
            ");

            if(mysqli_num_rows($query) > 0):
                while($row = mysqli_fetch_assoc($query)):
                    $targetOrder = isset($_SESSION['login']) ? "../user/order.php?id=" . $row['id_event'] : "../login/login.php";
                    $gambar = !empty($row['foto']) ? "../assets/img/event/" . $row['foto'] : "../bootstrap/image/image.png";
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card card-event h-100 shadow-sm">
                    <div class="position-relative overflow-hidden">
                        <img src="<?= $gambar ?>" class="card-img-top" alt="<?= htmlspecialchars($row['nama_event']) ?>">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-white text-primary rounded-pill shadow-sm px-3 py-2">
                                <i class="far fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($row['tanggal'])) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h4 class="card-title fw-bold mb-3"><?= htmlspecialchars($row['nama_event']) ?></h4>
                        <p class="text-muted small mb-4">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i><?= htmlspecialchars($row['nama_venue']) ?>
                        </p>
                        <a href="<?= $targetOrder ?>" class="btn btn-booking mt-auto shadow-sm">
                            <i class="fas fa-ticket-alt me-2"></i> Pesan Tiket Sekarang
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="col-12 text-center py-5">
                <img src="https://illustrations.popsy.co/white/surreal-hourglass.svg" style="width: 150px" class="mb-4 opacity-50">
                <h5 class="text-muted">Oops! Belum ada event tersedia saat ini.</h5>
                <p class="text-muted small">Kembali lagi nanti untuk update event terbaru.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<footer class="footer pt-5 pb-4">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <h4 class="fw-bold mb-4"><i class="fas fa-ticket-alt me-2"></i>HenTix</h4>
                <p class="mb-4">Kami menghubungkan Anda dengan ribuan pengalaman luar biasa di seluruh Indonesia dengan sistem yang mudah dan terpercaya.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="fab fa-instagram fs-4 text-decoration-none"></a>
                    <a href="#" class="fab fa-twitter fs-4 text-decoration-none"></a>
                    <a href="#" class="fab fa-facebook fs-4 text-decoration-none"></a>
                    <a href="#" class="fab fa-youtube fs-4 text-decoration-none"></a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="fw-bold mb-4">Navigasi</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#events" class="text-decoration-none">Cari Event</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none">Promo Spesial</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none">Bantuan</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none">Syarat & Ketentuan</a></li>
                </ul>
            </div>
            <div class="col-lg-6 text-lg-end">
                <h6 class="fw-bold mb-4">Dapatkan Update Event</h6>
                <p class="small mb-3">Berlangganan untuk info konser & event menarik lainnya.</p>
                <div class="input-group mb-3 shadow-sm" style="max-width: 450px; margin-left: auto;">
                    <input type="email" class="form-control border-0 rounded-start-pill px-4" placeholder="Alamat Email Anda">
                    <button class="btn btn-subscribe rounded-end-pill px-4" type="button">Subscribe</button>
                </div>
            </div>
        </div>
        <hr class="my-5 opacity-10">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start small">
                © <?= date('Y') ?> HenTix. All rights reserved.
            </div>
            <div class="col-md-6 text-center text-md-end small mt-3 mt-md-0">
                Developed with <i class="fas fa-heart text-danger"></i> for Experience Seekers.
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Smooth Navbar Transition
    window.addEventListener('scroll', function() {
        const nav = document.querySelector('.navbar');
        if (window.scrollY > 80) {
            nav.classList.add('bg-primary', 'shadow-lg', 'py-2');
            nav.style.background = "linear-gradient(135deg, #667eea 0%, #764ba2 100%)";
        } else {
            nav.classList.remove('bg-primary', 'shadow-lg', 'py-2');
            nav.style.background = "transparent";
        }
    });
</script>
</body>
</html>