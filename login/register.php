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
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .card-register {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        /* Header Ramping */
        .card-body { padding: 1.5rem !important; }
        h4 { font-size: 1.2rem; font-weight: 700; }
        
        /* Input Ringkas */
        .form-label { font-size: 0.8rem; margin-bottom: 3px; color: #555; }
        .form-control {
            padding: 7px 12px;
            font-size: 0.85rem;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: none;
        }

        /* Alert Kecil */
        .alert { 
            padding: 8px 12px; 
            font-size: 0.8rem; 
            border-radius: 8px; 
        }

        /* Tombol */
        .btn-register {
            padding: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
        }

        @media (max-width: 576px) {
            .container { padding: 0 20px; }
            .card-body { padding: 1.25rem !important; }
        }
    </style>
</head>
<body>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-11 col-sm-9 col-md-6 col-lg-4">
            <div class="card card-register">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h4 class="mb-1 text-dark">Daftar Akun</h4>
                        <p class="text-muted small mb-0">Lengkapi data di bawah ini</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger mb-3"><?= $error ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success mb-3"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-2">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($nama) ?>" placeholder="Nama anda" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" placeholder="email@gmail.com" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="••••" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Konfirmasi</label>
                                <input type="password" name="password_confirm" class="form-control" placeholder="••••" required>
                            </div>
                        </div>

                        <button type="submit" name="register" class="btn btn-primary btn-register w-100 text-white shadow-sm mt-1">
                            Daftar Sekarang
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">
                            Sudah punya akun? 
                            <a href="login.php" class="text-primary fw-bold text-decoration-none">Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>