<?php
session_start();
include '../config/config.php';

// Proteksi
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login/login.php");
    exit;
}

function getVenueCapacity($conn, $id_event) {
    $id_event = (int)$id_event;
    $result = mysqli_query($conn, "
        SELECT v.kapasitas
        FROM event e
        JOIN venue v ON e.id_venue = v.id_venue
        WHERE e.id_event = '$id_event'
    ");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['kapasitas'];
    }
    return 0;
}

function getEventTicketUsage($conn, $id_event, $exclude_tiket_id = null) {
    $id_event = (int)$id_event;
    $exclude_sql = '';
    if ($exclude_tiket_id !== null) {
        $exclude_sql = " AND id_tiket != '" . (int)$exclude_tiket_id . "'";
    }
    $result = mysqli_query($conn, "SELECT COALESCE(SUM(kuota), 0) AS total FROM tiket WHERE id_event = '$id_event' $exclude_sql");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['total'];
    }
    return 0;
}

// ================= TAMBAH =================
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $harga = (int)$_POST['harga'];
    $kuota = (int)$_POST['kuota'];
    $id_event = (int)$_POST['id_event'];

    if (empty($nama) || empty($harga) || empty($kuota) || empty($id_event)) {
        echo "<script>alert('Semua field wajib diisi!');</script>";
    } elseif ($harga <= 0 || $kuota <= 0) {
        echo "<script>alert('Harga dan kuota harus lebih dari 0!');</script>";
    } else {
        $cek_event = mysqli_query($conn, "SELECT * FROM event WHERE id_event = '$id_event'");
        if (mysqli_num_rows($cek_event) == 0) {
            echo "<script>alert('Event tidak valid atau tidak ditemukan.');</script>";
        } else {
            $cek_nama = mysqli_query($conn, "SELECT * FROM tiket WHERE id_event = '$id_event' AND nama_tiket = '$nama'");
            if (mysqli_num_rows($cek_nama) > 0) {
                echo "<script>alert('Nama tiket sudah digunakan untuk event ini.');</script>";
            } else {
                $kapasitas_venue = getVenueCapacity($conn, $id_event);
                $used_kuota = getEventTicketUsage($conn, $id_event);

                if ($kapasitas_venue <= 0) {
                    echo "<script>alert('Event tidak valid atau venue belum tersedia.');</script>";
                } elseif ($used_kuota + $kuota > $kapasitas_venue) {
                    echo "<script>alert('Total kuota tiket untuk event ini melebihi kapasitas venue ($kapasitas_venue orang).');</script>";
                } else {
                    mysqli_query($conn, "INSERT INTO tiket (nama_tiket, harga, kuota, id_event) 
                                        VALUES ('$nama', '$harga', '$kuota', '$id_event')");
                    echo "<script>alert('Tiket berhasil ditambahkan!'); window.location='tiket.php';</script>";
                }
            }
        }
    }
}

// ================= EDIT =================
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id_tiket'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $harga = (int)$_POST['harga'];
    $kuota = (int)$_POST['kuota'];
    $id_event = (int)$_POST['id_event'];

    if (empty($nama) || empty($harga) || empty($kuota) || empty($id_event)) {
        echo "<script>alert('Semua field wajib diisi!');</script>";
    } elseif ($harga <= 0 || $kuota <= 0) {
        echo "<script>alert('Harga dan kuota harus lebih dari 0!');</script>";
    } else {
        $cek_event = mysqli_query($conn, "SELECT * FROM event WHERE id_event = '$id_event'");
        if (mysqli_num_rows($cek_event) == 0) {
            echo "<script>alert('Event tidak valid atau tidak ditemukan.');</script>";
        } else {
            $cek_nama = mysqli_query($conn, "SELECT * FROM tiket WHERE id_event = '$id_event' AND nama_tiket = '$nama' AND id_tiket != '$id'");
            if (mysqli_num_rows($cek_nama) > 0) {
                echo "<script>alert('Nama tiket sudah digunakan untuk event ini.');</script>";
            } else {
                $kapasitas_venue = getVenueCapacity($conn, $id_event);
                $used_kuota = getEventTicketUsage($conn, $id_event, $id);

                if ($kapasitas_venue <= 0) {
                    echo "<script>alert('Event tidak valid atau venue belum tersedia.');</script>";
                } elseif ($used_kuota + $kuota > $kapasitas_venue) {
                    echo "<script>alert('Total kuota tiket untuk event ini melebihi kapasitas venue ($kapasitas_venue orang).');</script>";
                } else {
                    mysqli_query($conn, "UPDATE tiket SET 
                        nama_tiket='$nama',
                        harga='$harga',
                        kuota='$kuota',
                        id_event='$id_event'
                        WHERE id_tiket='$id'");
                    echo "<script>alert('Tiket berhasil diupdate!'); window.location='tiket.php';</script>";
                }
            }
        }
    }
}

// ================= HAPUS =================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM tiket WHERE id_tiket='$id'");
    echo "<script>alert('Tiket berhasil dihapus!'); window.location='tiket.php';</script>";
}

// Ambil data tiket + event
$data = mysqli_query($conn, "
    SELECT tiket.*, event.nama_event 
    FROM tiket 
    JOIN event ON tiket.id_event = event.id_event 
    ORDER BY event.tanggal ASC, tiket.nama_tiket ASC
");

// Ambil daftar event untuk dropdown
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="sidebar.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .table th { background-color: #f1f3f5; font-weight: 600; }
        .modal-content { border-radius: 16px; }
        .harga { font-weight: 600; color: #28a745; }
        .btn-action { min-width: 85px; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container">
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
                    <input type="search" id="searchTiket" class="form-control" placeholder="Cari nama tiket atau event..." aria-label="Search Ticket">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
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
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($d['nama_tiket']); ?></strong></td>
                            <td><?= htmlspecialchars($d['nama_event']); ?></td>
                            <td class="harga">Rp <?= number_format($d['harga']); ?></td>
                            <td>
                                <span class="badge bg-info"><?= number_format($d['kuota']); ?> tiket</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btn-action" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#edit<?= $d['id_tiket']; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="?hapus=<?= $d['id_tiket']; ?>" 
                                   class="btn btn-danger btn-sm btn-action"
                                   onclick="return confirm('Yakin ingin menghapus tiket ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="edit<?= $d['id_tiket']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" class="needs-validation" novalidate>
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Tiket</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_tiket" value="<?= $d['id_tiket']; ?>">

                                            <div class="mb-3">
                                                <label class="form-label">Nama Tiket <span class="text-danger">*</span></label>
                                                <input type="text" name="nama_tiket" class="form-control" 
                                                       value="<?= htmlspecialchars($d['nama_tiket']); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                                <input type="number" name="harga" class="form-control" 
                                                       value="<?= $d['harga']; ?>" min="1" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Kuota <span class="text-danger">*</span></label>
                                                <input type="number" name="kuota" class="form-control" 
                                                       value="<?= $d['kuota']; ?>" min="1" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Event <span class="text-danger">*</span></label>
                                                <select name="id_event" class="form-select" required>
                                                    <?php 
                                                    $event2 = mysqli_query($conn, "SELECT * FROM event ORDER BY tanggal");
                                                    while ($e = mysqli_fetch_assoc($event2)) { 
                                                    ?>
                                                    <option value="<?= $e['id_event']; ?>" 
                                                        <?= ($e['id_event'] == $d['id_event']) ? 'selected' : '' ?>>
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

<!-- Modal Tambah Tiket -->
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tiket Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Tiket <span class="text-danger">*</span></label>
                        <input type="text" name="nama_tiket" class="form-control" placeholder="VIP, Regular, Early Bird, dll" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="harga" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kuota <span class="text-danger">*</span></label>
                        <input type="number" name="kuota" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Event <span class="text-danger">*</span></label>
                        <select name="id_event" class="form-select" required>
                            <option value="">Pilih Event</option>
                            <?php 
                            mysqli_data_seek($event, 0); // reset pointer
                            while ($e = mysqli_fetch_assoc($event)) { 
                            ?>
                                <option value="<?= $e['id_event']; ?>">
                                    <?= htmlspecialchars($e['nama_event']); ?>
                                </option>
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
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
function setupTableSearch(inputId, tableSelector) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('input', () => {
        const filter = input.value.toLowerCase();
        document.querySelectorAll(tableSelector).forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
}

setupTableSearch('searchTiket', '.table tbody tr');

// Bootstrap Validation
(() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

</body>
</html>
