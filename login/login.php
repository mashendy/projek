<?php
session_start();
if (isset($_SESSION['login'])) {
    header("Location: landing.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | HenTix</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Inter', sans-serif;
            color: #2d3436;
        }
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: transparent;
            border: none;
            padding: 30px 30px 10px; /* Padding disesuaikan untuk logo */
        }
        /* Style untuk Logo */
        .brand-logo {
            width: 70px; /* Atur ukuran lebar logo */
            height: auto;
            margin-bottom: 15px;
            object-fit: contain;
        }
        .card-header h4 { 
            font-weight: 700; 
            letter-spacing: -0.5px;
            color: #1a1a1a;
        }
        .form-label { 
            font-size: 0.8rem; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            font-weight: 700;
            color: #636e72;
        }
        .input-group {
            background-color: #f1f3f5;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .input-group:focus-within {
            border-color: #667eea;
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        .input-group-text {
            background: transparent;
            border: none;
            padding-left: 15px;
            color: #a0a0a0;
        }
        .form-control {
            background: transparent;
            border: none;
            padding: 12px 15px 12px 5px;
            font-size: 0.95rem;
            font-weight: 500;
        }
        .form-control:focus {
            background: transparent;
            box-shadow: none;
        }
        .btn-login {
            padding: 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
            filter: brightness(1.1);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .small-text { font-size: 0.85rem; font-weight: 500; }
        .form-check-input:checked {
            background-color: #764ba2;
            border-color: #764ba2;
        }
        @media (max-width: 576px) {
            .container { padding: 0 20px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
            
            <div class="card login-card px-2">
                <div class="card-header text-center">
                    <img src="../bootstrap/image/image.png" alt="Logo HenTix" class="brand-logo">
                    
                    <h4 class="mb-1">Selamat Datang</h4>
                    <p class="text-muted small-text">Silakan masukkan akun HenTix Anda</p>
                </div>

                <div class="card-body p-4 pt-2">
                    <form action="login_proses.php" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check mb-0">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label small-text text-secondary" for="remember">Ingat saya</label>
                            </div>
                            <a href="#" class="small-text text-decoration-none fw-bold" style="color: #764ba2;">Lupa sandi?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-login w-100 text-white mb-3">
                            Login Sekarang
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0 small-text text-muted">
                            Belum punya akun? 
                            <a href="register.php" class="fw-bold text-decoration-none" style="color: #667eea;">Daftar Gratis</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <small style="font-size: 0.75rem; color: rgba(255,255,255,0.7); font-weight: 500; letter-spacing: 0.5px;">
                    &copy; 2026 HENTIX &bull; MADE WITH PASSION
                </small>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>