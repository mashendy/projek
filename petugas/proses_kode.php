<?php
error_reporting(0);

session_start();
include '../config/config.php';

header('Content-Type: application/json');

$kode = $_POST['kode'] ?? '';

if ($kode == '') {
    echo json_encode(['status'=>'error','message'=>'Kode kosong']);
    exit;
}

$kodeEsc = mysqli_real_escape_string($conn, $kode);

$q = mysqli_query($conn, "SELECT a.*, t.nama_tiket, e.nama_event, 
    DATE_FORMAT(e.tanggal, '%d %M %Y') AS tanggal_event,
    o.status AS order_status,
    u.nama AS nama_pemesan
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    JOIN orders o ON od.id_order = o.id_order
    JOIN users u ON o.id_user = u.id_user
    WHERE a.kode_tiket = '$kodeEsc' LIMIT 1");

if ($q && mysqli_num_rows($q) > 0) {
    $data = mysqli_fetch_assoc($q);

    if ($data['status_checkin'] == 'sudah') {
        echo json_encode(['status'=>'info','message'=>'Tiket sudah check-in', 'data' => $data]);
        exit;
    }

    if ($data['order_status'] != 'paid') {
        echo json_encode(['status'=>'warning','message'=>'Tiket belum dibayar', 'data' => $data]);
        exit;
    }

    $now = date('Y-m-d H:i:s');

    mysqli_query($conn, "UPDATE attendee SET 
        status_checkin='sudah',
        waktu_checkin='$now'
        WHERE id_attendee=".$data['id_attendee']);

    $data['status_checkin'] = 'sudah';
    $data['waktu_checkin'] = $now;

    echo json_encode([
        'status'=>'success',
        'message'=>'✅ Check-in berhasil!',
        'data'=>$data
    ]);

} else {
    echo json_encode(['status'=>'error','message'=>'❌ Kode tidak ditemukan']);
}
