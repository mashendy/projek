<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    /* Mengatur tinggi navbar agar tidak terlalu memakan tempat di HP */
    .navbar {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }

    /* Memperbaiki tampilan dropdown di mobile agar tidak berantakan */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: rgba(255, 255, 255, 0.05); /* Sedikit background agar menu terbaca */
            margin-top: 10px;
            padding: 15px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .nav-link {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .dropdown-menu {
            background-color: white !important;
            border: none;
            margin-top: 10px;
        }

        .navbar-brand {
            font-size: 1.25rem !important; /* Ukuran teks brand lebih pas di HP */
        }
    }
    
    /* Animasi halus untuk icon toggler */
    .navbar-toggler {
        border: none;
        outline: none !important;
        box-shadow: none !important;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark shadow sticky-top" 
     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container-fluid px-3 px-md-4">

        <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
            <i class="fas fa-ticket-alt me-2 text-warning"></i> HenTix
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navUser" aria-controls="navUser" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i> </button>

        <div class="collapse navbar-collapse" id="navUser">
            
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                   
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle fa-lg me-2"></i>
                        <span class="fw-semibold small"><?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        <li>
                            <a class="dropdown-item py-2" href="profile.php">
                                <i class="fas fa-user-edit me-2 text-muted"></i> Profil Saya
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="riwayat.php">
                                <i class="fas fa-history me-2 text-muted"></i> Riwayat Transaksi
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger fw-bold" href="../login/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

        </div>
    </div>
</nav>