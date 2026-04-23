<?php
session_start();
include '../config/config.php';

// Proteksi
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login/login.php");
    exit;
}

// Fungsi bantu untuk memisahkan Nama Tiket dan Limit
function parseTicketName($rawName) {
    if (strpos($rawName, '[L:') !== false) {
        $parts = explode('[L:', $rawName);
        $name = trim($parts[0]);
        $limit = (int)str_replace(']', '', $parts[1]);
        return ['nama' => $name, 'limit' => $limit];
    }
    return ['nama' => $rawName, 'limit' => 5];
}

function getVenueCapacity($conn, $id_event) {
    $id_event = (int)$id_event;
    $result = mysqli_query($conn, "SELECT v.kapasitas FROM event e JOIN venue v ON e.id_venue = v.id_venue WHERE e.id_event = '$id_event'");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['kapasitas'];
    }
    return 0;
}

function getEventTicketUsage($conn, $id_event, $exclude_tiket_id = null) {
    $id_event = (int)$id_event;
    $exclude_sql = $exclude_tiket_id !== null ? " AND id_tiket != '" . (int)$exclude_tiket_id . "'" : '';
    $result = mysqli_query($conn, "SELECT COALESCE(SUM(kuota), 0) AS total FROM tiket WHERE id_event = '$id_event' $exclude_sql");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['total'];
    }
    return 0;
}

// ================= TAMBAH =================
if (isset($_POST['tambah'])) {
    $nama_murni = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $limit_input = (int)$_POST['max_beli'];
    $nama = $nama_murni . " [L:" . $limit_input . "]";
    $harga = (int)$_POST['harga'];
    $kuota = (int)$_POST['kuota'];
    $id_event = (int)$_POST['id_event'];

    $kapasitas_venue = getVenueCapacity($conn, $id_event);
    $used_kuota = getEventTicketUsage($conn, $id_event);

    if ($used_kuota + $kuota > $kapasitas_venue) {
        echo "<script>alert('Total kuota tiket melebihi kapasitas venue ($kapasitas_venue orang).');</script>";
    } else {
        mysqli_query($conn, "INSERT INTO tiket (nama_tiket, harga, kuota, id_event) VALUES ('$nama', '$harga', '$kuota', '$id_event')");
        echo "<script>alert('Tiket berhasil ditambahkan!'); window.location='tiket.php';</script>";
    }
}

// ================= EDIT =================
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id_tiket'];
    $nama_murni = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $limit_input = (int)$_POST['max_beli'];
    $nama = $nama_murni . " [L:" . $limit_input . "]";
    $harga = (int)$_POST['harga'];
    $kuota = (int)$_POST['kuota'];
    $id_event = (int)$_POST['id_event'];

    $kapasitas_venue = getVenueCapacity($conn, $id_event);
    $used_kuota = getEventTicketUsage($conn, $id_event, $id);

    if ($used_kuota + $kuota > $kapasitas_venue) {
        echo "<script>alert('Total kuota tiket melebihi kapasitas venue ($kapasitas_venue orang).');</script>";
    } else {
        mysqli_query($conn, "UPDATE tiket SET nama_tiket='$nama', harga='$harga', kuota='$kuota', id_event='$id_event' WHERE id_tiket='$id'");
        echo "<script>alert('Tiket berhasil diupdate!'); window.location='tiket.php';</script>";
    }
}

// ================= HAPUS =================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM tiket WHERE id_tiket='$id'");
    echo "<script>alert('Tiket berhasil dihapus!'); window.location='tiket.php';</script>";
}

$data = mysqli_query($conn, "SELECT tiket.*, event.nama_event FROM tiket JOIN event ON tiket.id_event = event.id_event ORDER BY event.tanggal ASC, tiket.nama_tiket ASC");
$event = mysqli_query($conn, "SELECT * FROM event ORDER BY tanggal ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tiket</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .table th { background-color: #f1f3f5; font-weight: 600; }
        .modal-content { border-radius: 16px; }
        .harga { font-weight: 600; color: #28a745; }
        .btn-action { min-width: 85px; }
        .badge-limit { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Kelola Tiket</h2>
            <p class="text-muted">Daftar tiket untuk setiap event</p>
        </div>
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#tambahModal">
            <i class="fas fa-plus me-2"></i> Tambah Tiket
        </button>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="search" id="searchTiket" class="form-control" placeholder="Cari nama tiket, event, harga, atau jumlah kuota...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tabelTiket">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Tiket</th>
                            <th>Event</th>
                            <th>Harga</th>
                            <th>Kuota</th>
                            <th width="18%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while ($d = mysqli_fetch_assoc($data)) { 
                            $info = parseTicketName($d['nama_tiket']);
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <strong><?= htmlspecialchars($info['nama']); ?></strong><br>
                                <span class="badge badge-limit"><i class="fas fa-user-lock me-1"></i> Max Beli: <?= $info['limit']; ?></span>
                            </td>
                            <td><?= htmlspecialchars($d['nama_event']); ?></td>
                            <td class="harga">Rp <?= number_format($d['harga']); ?></td>
                            <td>
                                <?php if($d['kuota'] <= 0): ?>
                                    <span class="badge bg-danger">Habis (0)</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark"><?= number_format($d['kuota']); ?> tiket</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal" data-bs-target="#edit<?= $d['id_tiket']; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="?hapus=<?= $d['id_tiket']; ?>" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Yakin ingin menghapus tiket ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>

                        <div class="modal fade" id="edit<?= $d['id_tiket']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Tiket</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_tiket" value="<?= $d['id_tiket']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Nama Tiket</label>
                                                <input type="text" name="nama_tiket" class="form-control" value="<?= htmlspecialchars($info['nama']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Max Beli Per User</label>
                                                <input type="number" name="max_beli" class="form-control" value="<?= $info['limit']; ?>" min="1" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Harga (Rp)</label>
                                                <input type="number" name="harga" class="form-control" value="<?= $d['harga']; ?>" min="1" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Kuota</label>
                                                <input type="number" name="kuota" class="form-control" value="<?= $d['kuota']; ?>" min="0" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Event</label>
                                                <select name="id_event" class="form-select" required>
                                                    <?php 
                                                    mysqli_data_seek($event, 0);
                                                    while ($e = mysqli_fetch_assoc($event)) { ?>
                                                    <option value="<?= $e['id_event']; ?>" <?= ($e['id_event'] == $d['id_event']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($e['nama_event']); ?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit" class="btn btn-success">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Tiket Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Tiket</label>
                        <input type="text" name="nama_tiket" class="form-control" placeholder="VIP, Regular, dll" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Beli Per User</label>
                        <input type="number" name="max_beli" class="form-control" value="5" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga (Rp)</label>
                        <input type="number" name="harga" class="form-control" placeholder="100000" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kuota</label>
                        <input type="number" name="kuota" class="form-control" placeholder="50" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Event</label>
                        <select name="id_event" class="form-select" required>
                            <option value="">Pilih Event</option>
                            <?php 
                            mysqli_data_seek($event, 0);
                            while ($e = mysqli_fetch_assoc($event)) { ?>
                                <option value="<?= $e['id_event']; ?>"><?= htmlspecialchars($e['nama_event']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan Tiket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    // SEARCH FUNCTION AKURAT UNTUK SEMUA KOLOM (TERMASUK ANGKA 0)
    document.getElementById('searchTiket').addEventListener('input', function() {
        let filter = this.value.trim().toLowerCase();
        let rows = document.querySelectorAll('#tabelTiket tbody tr');

        rows.forEach(row => {
            // Ambil teks dari kolom Nama Tiket, Event, Harga, dan Kuota
            let namaTiket = row.cells[1].innerText.toLowerCase();
            let eventName = row.cells[2].innerText.toLowerCase();
            let harga     = row.cells[3].innerText.toLowerCase();
            let kuota     = row.cells[4].innerText.toLowerCase();

            // Gabungkan teks kolom untuk dicari secara spesifik
            let combinedText = `${namaTiket} ${eventName} ${harga} ${kuota}`;

            if (combinedText.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>