<?php
session_start();
include '../config/config.php'; // FIX PATH

// cek apakah form dikirim
if(isset($_POST['email']) && isset($_POST['password'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        $isValidPassword = false;

        if (password_verify($password, $data['password'])) {
            $isValidPassword = true;
        } elseif ($password == $data['password']) {
            // Migrasi password lama yang tersimpan dalam teks biasa ke hash.
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$hashedPassword' WHERE id_user='{$data['id_user']}'");
            $isValidPassword = true;
        }

        if ($isValidPassword) {
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['role'] = $data['role'];

            if ($data['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($data['role'] == 'petugas') {
                header("Location: ../petugas/dashboard.php");
            } else {
                header("Location: ../user/dashboard.php");
            }
        } else {
            echo "<script>alert('Password salah');window.location='login.php';</script>";
        }
    }else{
        echo "<script>alert('Email tidak ditemukan');window.location='login.php';</script>";
    }

}else{
    echo "<script>alert('Akses tidak valid');window.location='login.php';</script>";
}
?>