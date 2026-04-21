<?php
session_start();
include '../config/config.php';

// Proteksi
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login/login.php");
    exit;
}

$today = date('Y-m-d');
$message = '';
$message_type = '';

// Folder penyimpanan foto
$target_dir = "../assets/img/event/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// ================= TAMBAH =================
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tanggal = $_POST['tanggal'];
    $id_venue = (int)$_POST['id_venue'];
    
    // Handle Upload Foto
    $foto_name = "";
    if ($_FILES['foto']['name']) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = time() . "_" . rand(100, 999) . "." . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_name);
    }

    if (empty($nama) || empty($tanggal) || empty($id_venue)) {
        $message = 'Semua field wajib diisi!';
        $message_type = 'warning';
    } else {
        $cek = mysqli_query($conn, "SELECT 1 FROM event WHERE nama_event = '$nama' AND tanggal = '$tanggal' LIMIT 1");
        if (mysqli_num_rows($cek) > 0) {
            $message = 'Event dengan nama dan tanggal yang sama sudah ada.';
            $message_type = 'danger';
        } else {
            mysqli_query($conn, "INSERT INTO event (nama_event, tanggal, id_venue, foto) 
                                VALUES ('$nama', '$tanggal', '$id_venue', '$foto_name')");
            echo "<script>alert('Event berhasil ditambahkan!'); window.location='event.php';</script>";
            exit;
        }
    }
}

// ================= EDIT =================
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id_event'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tanggal = $_POST['tanggal'];
    $id_venue = (int)$_POST['id_venue'];
    $foto_lama = $_POST['foto_lama'];

    // Handle Upload Foto Baru
    if ($_FILES['foto']['name']) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_baru = time() . "_" . rand(100, 999) . "." . $ext;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_baru)) {
            if ($foto_lama && file_exists($target_dir . $foto_lama)) {
                unlink($target_dir . $foto_lama);
            }
            $foto_final = $foto_baru;
        }
    } else {
        $foto_final = $foto_lama;
    }

    mysqli_query($conn, "UPDATE event SET 
                nama_event='$nama',
                tanggal='$tanggal',
                id_venue='$id_venue',
                foto='$foto_final'
                WHERE id_event='$id'");
    echo "<script>alert('Event berhasil diupdate!'); window.location='event.php';</script>";
    exit;
}

// ================= HAPUS =================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $res = mysqli_query($conn, "SELECT foto FROM event WHERE id_event='$id'");
    $row = mysqli_fetch_assoc($res);
    if ($row['foto'] && file_exists($target_dir . $row['foto'])) {
        unlink($target_dir . $row['foto']);
    }
    
    mysqli_query($conn, "DELETE FROM event WHERE id_event='$id'");
    echo "<script>alert('Event berhasil dihapus!'); window.location='event.php';</script>";
}

$data = mysqli_query($conn, "SELECT event.*, venue.nama_venue FROM event JOIN venue ON event.id_venue = venue.id_venue ORDER BY event.tanggal ASC");
$venue = mysqli_query($conn, "SELECT * FROM venue ORDER BY nama_venue ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Event | Admin</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="sidebar.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .img-preview { width: 50px; height: 50px; object-fit: cover; border-radius: 10px; }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #eee; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        .btn-primary { background-color: #0d6efd; border: none; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container py-4 py-md-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h2 class="fw-bold mb-1">Kelola Event</h2>
            <p class="text-muted small">Atur jadwal, lokasi, dan informasi event Anda.</p>
        </div>
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#tambahModal">
            <i class="fas fa-plus me-2"></i> Tambah Event
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Foto</th>
                            <th>Nama Event</th>
                            <th>Tanggal</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while ($d = mysqli_fetch_assoc($data)): 
                            $status_class = ($d['tanggal'] < $today) ? 'bg-danger' : (($d['tanggal'] == $today) ? 'bg-warning text-dark' : 'bg-success');
                            $status_text = ($d['tanggal'] < $today) ? 'Selesai' : (($d['tanggal'] == $today) ? 'Hari Ini' : 'Akan Datang');
                        ?>
                        <tr>
                            <td class="ps-4"><?= $no++; ?></td>
                            <td>
                                <img src="../assets/img/event/<?= $d['foto'] ? $d['foto'] : 'default.jpg' ?>" 
                                     class="img-preview shadow-sm" 
                                     onerror="this.src='../bootstrap/image/image.png'">
                            </td>
                            <td><strong><?= htmlspecialchars($d['nama_event']); ?></strong></td>
                            <td><?= date('d M Y', strtotime($d['tanggal'])); ?></td>
                            <td><span class="text-muted"><i class="fas fa-map-marker-alt me-1 small"></i> <?= htmlspecialchars($d['nama_venue']); ?></span></td>
                            <td><span class="badge <?= $status_class ?> rounded-pill px-3"><?= $status_text ?></span></td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal" data-bs-target="#edit<?= $d['id_event']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="?hapus=<?= $d['id_event']; ?>" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Hapus event ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="edit<?= $d['id_event']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title fw-bold">Edit Event</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_event" value="<?= $d['id_event']; ?>">
                                            <input type="hidden" name="foto_lama" value="<?= $d['foto']; ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Nama Event</label>
                                                <input type="text" name="nama_event" class="form-control" value="<?= $d['nama_event']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Tanggal</label>
                                                <input type="date" name="tanggal" class="form-control" value="<?= $d['tanggal']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Venue</label>
                                                <select name="id_venue" class="form-select" required>
                                                    <?php 
                                                    mysqli_data_seek($venue, 0);
                                                    while ($v = mysqli_fetch_assoc($venue)) { 
                                                        $sel = ($v['id_venue'] == $d['id_venue']) ? 'selected' : '';
                                                        echo "<option value='{$v['id_venue']}' $sel>{$v['nama_venue']}</option>";
                                                    } ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Ganti Foto</label>
                                                <input type="file" name="foto" class="form-control" accept="image/*">
                                                <div class="mt-2 small text-muted">Abaikan jika tidak ingin mengubah foto.</div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Tambah Event Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Event</label>
                        <input type="text" name="nama_event" class="form-control" placeholder="Contoh: Konser Jazz Nasional" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" min="<?= $today ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Venue</label>
                        <select name="id_venue" class="form-select" required>
                            <option value="">Pilih Lokasi Venue</option>
                            <?php 
                            mysqli_data_seek($venue, 0);
                            while ($v = mysqli_fetch_assoc($venue)) { 
                                echo "<option value='{$v['id_venue']}'>{$v['nama_venue']}</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Foto Poster Event</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>