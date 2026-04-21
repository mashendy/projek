<?php
session_start();
include '../config/config.php';

$error = '';
$success = '';
$nama = '';
$email = '';

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $role = 'user';

    if ($nama === '' || $email === '' || $password === '' || $passwordConfirm === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Password tidak cocok.';
    } else {
        $emailEsc = mysqli_real_escape_string($conn, $email);
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$emailEsc'");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Email sudah terdaftar.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $namaEsc = mysqli_real_escape_string($conn, $nama);
            $query = mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$namaEsc', '$emailEsc', '$hashedPassword', '$role')");
            if ($query) {
                $success = 'Berhasil! Silakan login.';
                $nama = ''; $email = '';
            } else {
                $error = 'Gagal mendaftar.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | HenTix</title>
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
            margin: 0;
        }
        .register-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: transparent;
            border: none;
            padding: 35px 30px 10px;
        }
        .brand-logo {
            width: 70px;
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
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            font-weight: 700;
            color: #636e72;
            margin-bottom: 6px;
        }
        .input-group {
            background-color: #f1f3f5;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            overflow: hidden;
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
            font-size: 0.9rem;
            font-weight: 500;
        }
        .form-control:focus {
            background: transparent;
            box-shadow: none;
        }
        .btn-register {
            padding: 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
            filter: brightness(1.1);
        }
        .alert {
            font-size: 0.85rem;
            border-radius: 12px;
            border: none;
            font-weight: 500;
        }
        .small-text { font-size: 0.85rem; font-weight: 500; }
        .text-purple { color: #764ba2; }

        @media (max-width: 576px) {
            .container { padding: 0 20px; }
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            
            <div class="card register-card px-2">
                <div class="card-header text-center">
                    <img src="../bootstrap/image/image.png" alt="Logo HenTix" class="brand-logo">
                    <h4 class="mb-1">Daftar Akun</h4>
                    <p class="text-muted small-text">Bergabunglah dengan HenTix sekarang</p>
                </div>

                <div class="card-body p-4 pt-2">
                    <?php if ($error): ?>
                        <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success mb-3"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($nama) ?>" placeholder="Nama Lengkap" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" placeholder="nama@email.com" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="••••" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Konfirmasi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                                    <input type="password" name="password_confirm" class="form-control" placeholder="••••" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="register" class="btn btn-primary btn-register w-100 text-white mb-3">
                            Daftar Sekarang
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0 small-text text-muted">
                            Sudah punya akun? 
                            <a href="login.php" class="fw-bold text-decoration-none text-purple">Masuk di sini</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <small style="font-size: 0.75rem; color: rgba(255,255,255,0.7); font-weight: 500; letter-spacing: 0.5px;">
                    &copy; 2026 HENTIX &bull; ALL RIGHTS RESERVED
                </small>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 