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
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            background: white;
        }
        /* Header lebih ramping */
        .card-header {
            background: transparent;
            border-bottom: none;
            padding: 20px 20px 5px;
        }
        .card-header h4 { font-size: 1.25rem; }
        .card-header p { font-size: 0.85rem; }

        /* Input lebih ringkas */
        .form-label { font-size: 0.85rem; margin-bottom: 4px; }
        .form-control {
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.9rem;
            border: 1px solid #dee2e6;
        }
        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8f9fa;
            font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: none;
        }
        
        /* Tombol lebih kecil */
        .btn-login {
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            margin-top: 10px;
        }
        
        .small-text { font-size: 0.8rem; }

        @media (max-width: 576px) {
            .container { padding: 0 25px; }
            .card-body { padding: 1.5rem !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-11 col-sm-8 col-md-5 col-lg-4">
            
            <div class="card login-card">
                <div class="card-header text-center">
                    <h4 class="mb-0 fw-bold text-dark">Selamat Datang</h4>
                    <p class="text-muted mb-0">Masuk untuk lanjut</p>
                </div>

                <div class="card-body p-4">
                    <form action="login_proses.php" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium text-secondary">Email</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" class="form-control border-start-0" placeholder="email@anda.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-secondary">Password</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check mb-0">
                                <input type="checkbox" class="form-check-input" id="remember" style="width: 14px; height: 14px;">
                                <label class="form-check-label small-text" for="remember">Ingat saya</label>
                            </div>
                            <a href="#" class="small-text text-decoration-none">Lupa sandi?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-login w-100 text-white shadow-sm">
                            Login Sekarang
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-0 small-text text-muted">
                            Belum punya akun? 
                            <a href="register.php" class="text-primary fw-bold text-decoration-none">Daftar</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <small style="font-size: 0.7rem; color: rgba(255,255,255,0.6);">© 2026 HenTix Team</small>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>