<?php
session_start();
include '../config/config.php';

// Proteksi login
if(!isset($_SESSION['login']) || $_SESSION['role'] != 'user'){
    header("Location: ../login/login.php");
    exit;
}

if (isset($_GET['id_order'])) {
    $id_order = mysqli_real_escape_string($conn, $_GET['id_order']);
    $id_user = $_SESSION['id_user'];

    // Pastikan order tersebut milik user yang sedang login dan statusnya masih pending
    $check = mysqli_query($conn, "SELECT status, id_voucher FROM orders WHERE id_order = '$id_order' AND id_user = '$id_user'");
    $data = mysqli_fetch_assoc($check);

    if ($data && $data['status'] == 'pending') {
        mysqli_begin_transaction($conn);
        $cancelOrder = mysqli_query($conn, "UPDATE orders SET status = 'cancel' WHERE id_order = '$id_order'");

        $voucherRestored = true;
        if (!empty($data['id_voucher'])) {
            $voucherId = intval($data['id_voucher']);
            $voucherRestored = mysqli_query($conn, "UPDATE voucher SET kuota = kuota + 1 WHERE id_voucher = '$voucherId'");
        }

        $ticketRestored = true;
        $detailQuery = mysqli_query($conn, "SELECT id_tiket, qty FROM order_detail WHERE id_order = '$id_order'");
        while ($detail = mysqli_fetch_assoc($detailQuery)) {
            $ticketRestored = $ticketRestored && mysqli_query($conn, "UPDATE tiket SET kuota = kuota + " . intval($detail['qty']) . " WHERE id_tiket = '" . intval($detail['id_tiket']) . "'");
        }

        if ($cancelOrder && $voucherRestored && $ticketRestored) {
            mysqli_commit($conn);
            echo "<script>alert('Pesanan berhasil dibatalkan. Kuota tiket dan voucher telah dikembalikan.'); window.location='riwayat.php';</script>";
        } else {
            mysqli_rollback($conn);
            echo "<script>alert('Gagal membatalkan pesanan. Silakan coba lagi.'); window.location='riwayat.php';</script>";
        }
    } else {
        echo "<script>alert('Pesanan tidak dapat dibatalkan.'); window.location='riwayat.php';</script>";
    }
} else {
    header("Location: riwayat.php");
}
?>