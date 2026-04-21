<?php
session_start();
include '../config/config.php';

// Proteksi login user
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'user') {
    header("Location: ../login/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$error = '';
$success = '';

$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($userQuery);

if (!$user) {
    echo "<script>alert('Data pengguna tidak ditemukan.');window.location='dashboard.php';</script>";
    exit;
}

$nama = $user['nama'];
$email = $user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($nama === '' || $email === '') {
        $error = 'Nama dan email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif ($password !== '' && $password !== $password_confirm) {
        $error = 'Password konfirmasi tidak cocok.';
    } else {
        $emailEsc = mysqli_real_escape_string($conn, $email);
        $namaEsc = mysqli_real_escape_string($conn, $nama);

        $cekEmail = mysqli_query($conn, "SELECT id_user FROM users WHERE email='$emailEsc' AND id_user != '$id_user'");
        if (mysqli_num_rows($cekEmail) > 0) {
            $error = 'Email sudah digunakan.';
        } else {
            $updateFields = ["nama='$namaEsc'", "email='$emailEsc'"];
            if ($password !== '') {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateFields[] = "password='$hashedPassword'";
            }

            $updateSql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id_user='$id_user'";
            if (mysqli_query($conn, $updateSql)) {
                $_SESSION['nama'] = $nama;
                $success = 'Profil berhasil diperbarui.';
            } else {
                $error = 'Terjadi kesalahan sistem.';
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
    <title>Edit Profil</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fd; font-family: 'Inter', sans-serif; font-size: 0.85rem; }
        
        /* Batasi lebar maksimal di desktop agar compact */
        @media (min-width: 992px) {
            .container-profile { max-width: 700px; margin: auto; }
        }

        .profile-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px; /* Lebih kecil dari p-5 */
            color: white;
        }

        .profile-header h5 { font-weight: 700; margin-bottom: 5px; }
        .profile-header p { font-size: 0.8rem; opacity: 0.9; margin-bottom: 0; }

        .form-label { font-weight: 600; color: #444; font-size: 0.8rem; }
        .form-control { font-size: 0.85rem; padding: 0.6rem 0.8rem; border-radius: 8px; }
        
        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 8px;
            font-size: 0.85rem;
        }
        
        .section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #333;
            border-left: 4px solid #667eea;
            padding-left: 10px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container container-profile py-4">
    <div class="card profile-card">
        <div class="profile-header text-center text-md-start">
            <div class="d-md-flex align-items-center justify-content-between">
                <div>
                    <h5><i class="fas fa-user-circle me-2"></i>Pengaturan Profil</h5>
                    <p>Perbarui informasi akun Anda di sini.</p>
                </div>
                <div class="mt-2 mt-md-0">
                    <span class="badge bg-white text-primary small px-3">User Aktif</span>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success py-2 small"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="section-title mb-3 mt-2">Informasi Dasar</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($nama) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                </div>

                <div class="section-title mb-3 mt-4">Keamanan</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Isi hanya jika ingin ganti">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirm" class="form-control" placeholder="Ulangi password baru">
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
                    <a href="dashboard.php" class="text-decoration-none text-muted small order-2 order-md-1">
                        <i class="fas fa-chevron-left me-1"></i> Kembali ke Beranda
                    </a>
                    <button type="submit" class="btn btn-save text-white order-1 order-md-2 w-100 w-md-auto">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>