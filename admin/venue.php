<?php
session_start();
include '../config/config.php';

// Proteksi
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login/login.php");
    exit;
}

// ================= TAMBAH =================
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_venue']);
    $provinsi = mysqli_real_escape_string($conn, $_POST['provinsi']);
    $kota = mysqli_real_escape_string($conn, $_POST['kota']);
    $kapasitas = (int)$_POST['kapasitas'];

    if (empty($nama) || empty($provinsi) || empty($kota) || empty($kapasitas)) {
        echo "<script>alert('Semua field wajib diisi!');</script>";
    } else {
        $alamat = $kota . ", " . $provinsi;
        mysqli_query($conn, "INSERT INTO venue (nama_venue, alamat, kapasitas) 
                            VALUES ('$nama', '$alamat', '$kapasitas')");
        echo "<script>alert('Venue berhasil ditambahkan!'); window.location='venue.php';</script>";
    }
}

// ================= EDIT =================
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id_venue'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_venue']);
    $provinsi = mysqli_real_escape_string($conn, $_POST['provinsi']);
    $kota = mysqli_real_escape_string($conn, $_POST['kota']);
    $kapasitas = (int)$_POST['kapasitas'];

    if (empty($nama) || empty($provinsi) || empty($kota) || empty($kapasitas)) {
        echo "<script>alert('Semua field wajib diisi!');</script>";
    } else {
        $alamat = $kota . ", " . $provinsi;
        mysqli_query($conn, "UPDATE venue SET 
            nama_venue='$nama',
            alamat='$alamat',
            kapasitas='$kapasitas'
            WHERE id_venue='$id'");
        echo "<script>alert('Venue berhasil diupdate!'); window.location='venue.php';</script>";
    }
}

// ================= HAPUS =================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM venue WHERE id_venue='$id'");
    echo "<script>alert('Venue berhasil dihapus!'); window.location='venue.php';</script>";
}

// Ambil data venue
$data = mysqli_query($conn, "SELECT * FROM venue ORDER BY nama_venue ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Venue</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="sidebar.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .table th { background-color: #f1f3f5; }
        .btn-action { min-width: 80px; }
        .modal-content { border-radius: 16px; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Kelola Venue</h2>
            <p class="text-muted">Daftar lokasi venue yang tersedia</p>
        </div>
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#tambahModal">
            <i class="fas fa-plus me-2"></i>Tambah Venue
        </button>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="search" id="searchVenue" class="form-control" placeholder="Cari nama venue, kota, atau provinsi..." aria-label="Search Venue" oninput="filterVenueTable()">
                </div>
            </div>
            <div class="table-responsive">
                <table id="venueTable" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Venue</th>
                            <th>Alamat</th>
                            <th width="15%">Kapasitas</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while ($d = mysqli_fetch_assoc($data)) { 
                            $alamatParts = explode(', ', $d['alamat'], 2);
                            $kota_lama = $alamatParts[0] ?? '';
                            $provinsi_lama = $alamatParts[1] ?? '';
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($d['nama_venue']); ?></strong></td>
                            <td><?= htmlspecialchars($d['alamat']); ?></td>
                            <td><?= number_format($d['kapasitas']); ?> orang</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btn-action" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#edit<?= $d['id_venue']; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="?hapus=<?= $d['id_venue']; ?>" 
                                   class="btn btn-danger btn-sm btn-action"
                                   onclick="return confirm('Yakin ingin menghapus venue ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="edit<?= $d['id_venue']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" class="needs-validation" novalidate>
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Venue</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_venue" value="<?= $d['id_venue']; ?>">

                                            <div class="mb-3">
                                                <label class="form-label">Nama Venue <span class="text-danger">*</span></label>
                                                <input type="text" name="nama_venue" class="form-control" 
                                                       value="<?= htmlspecialchars($d['nama_venue']); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                                                <select name="provinsi" class="form-select provinsi-edit" 
                                                        data-selected-provinsi="<?= htmlspecialchars($provinsi_lama); ?>" required>
                                                    <option value="">Pilih Provinsi</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Kota <span class="text-danger">*</span></label>
                                                <select name="kota" class="form-select kota-edit" 
                                                        data-selected-kota="<?= htmlspecialchars($kota_lama); ?>" required>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Kapasitas <span class="text-danger">*</span></label>
                                                <input type="number" name="kapasitas" class="form-control" 
                                                       value="<?= $d['kapasitas']; ?>" required>
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

<!-- Modal Tambah Venue -->
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Venue Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Venue <span class="text-danger">*</span></label>
                        <input type="text" name="nama_venue" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                        <select name="provinsi" id="provinsi" class="form-select provinsi-add" required>
                            <option value="">Pilih Provinsi</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kota <span class="text-danger">*</span></label>
                        <select name="kota" id="kota" class="form-select" required>
                            <option value="">Pilih Kota</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kapasitas <span class="text-danger">*</span></label>
                        <input type="number" name="kapasitas" class="form-control" placeholder="Contoh: 500" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan Venue</button>
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
// Data Kota
const dataKota = {
    "Aceh": ["Banda Aceh", "Langsa", "Lhokseumawe", "Sabang", "Bireuen", "Meulaboh", "Sigli", "Takengon"],
    "Sumatera Utara": ["Medan", "Binjai", "Pematangsiantar", "Tanjungbalai", "Sibolga", "Tebing Tinggi", "Padangsidempuan", "Gunungsitoli"],
    "Sumatera Barat": ["Padang", "Bukittinggi", "Payakumbuh", "Solok", "Sawahlunto", "Padang Panjang", "Pariaman", "Sijunjung"],
    "Riau": ["Pekanbaru", "Dumai", "Tembilahan", "Bengkalis", "Rengat", "Siak", "Batu Sangkar", "Rokan Hulu"],
    "Jambi": ["Jambi", "Sungai Penuh", "Muara Bungo", "Sarolangun", "Kerinci", "Tanjung Jabung", "Batanghari", "Merangin"],
    "Sumatera Selatan": ["Palembang", "Prabumulih", "Lubuklinggau", "Pagar Alam", "Martapura", "Pali", "Lahat", "Musi Banyuasin"],
    "Bengkulu": ["Bengkulu", "Curup", "Manna", "Argamakmur", "Rejang Lebong", "Kaur", "Seluma", "Mukomuko"],
    "Lampung": ["Bandar Lampung", "Metro", "Pringsewu", "Kotabumi", "Liwa", "Liwa", "Sukadana", "Krui"],
    "Kepulauan Bangka Belitung": ["Pangkalpinang", "Toboali", "Sungai Liat", "Muntok", "Belinyu", "Simpang Rimba", "Koba", "Sungailiat"],
    "Kepulauan Riau": ["Batam", "Tanjungpinang", "Bintan", "Karimun", "Anambas", "Tarempa", "Lingga", "Natuna"],
    "DKI Jakarta": ["Jakarta Pusat", "Jakarta Barat", "Jakarta Selatan", "Jakarta Timur", "Jakarta Utara"],
    "Jawa Barat": ["Bandung", "Bekasi", "Bogor", "Depok", "Cirebon", "Sukabumi", "Tasikmalaya", "Pangandaran"],
    "Jawa Tengah": ["Semarang", "Solo", "Magelang", "Pekalongan", "Purwokerto", "Tegal", "Salatiga", "Kudus", "Pati", "Surakarta"],
    "DI Yogyakarta": ["Yogyakarta", "Bantul", "Sleman", "Kulon Progo", "Gunungkidul"],
    "Jawa Timur": ["Surabaya", "Malang", "Kediri", "Jember", "Madiun", "Banyuwangi", "Bojonegoro", "Pasuruan"],
    "Banten": ["Serang", "Tangerang", "Cilegon", "Pandeglang", "Lebak", "Sukabumi", "Tangerang Selatan", "Citeureup"],
    "Bali": ["Denpasar", "Gianyar", "Badung", "Bangli", "Karangasem", "Tabanan", "Jembrana", "Klungkung"],
    "Nusa Tenggara Barat": ["Mataram", "Sumbawa Besar", "Bima", "Praya", "Dompu", "Sumbawa", "Selong", "Taliwang"],
    "Nusa Tenggara Timur": ["Kupang", "Ende", "Maumere", "Waingapu", "Larantuka", "Kupang", "Labuan Bajo", "Atambua"],
    "Kalimantan Barat": ["Pontianak", "Singkawang", "Ketapang", "Sanggau", "Sambas", "Sintang", "Palangkaraya", "Putussibau"],
    "Kalimantan Tengah": ["Palangka Raya", "Sampit", "Pangkalan Bun", "Kuala Kapuas", "Buntok", "Katingan", "Muara Teweh", "Palangkaraya"],
    "Kalimantan Selatan": ["Banjarmasin", "Banjarbaru", "Martapura", "Kandangan", "Barabai", "Amuntai", "Kota Baru", "Pelaihari"],
    "Kalimantan Timur": ["Samarinda", "Balikpapan", "Bontang", "Tenggarong", "Sangatta", "Batu Ampar", "Tarakan", "Samboja"],
    "Kalimantan Utara": ["Tanjung Selor", "Tarakan", "Nunukan", "Malinau", "Bulungan", "Tana Tidung", "Tideng Pale"],
    "Sulawesi Utara": ["Manado", "Bitung", "Tomohon", "Kotamobagu", "Minahasa", "Gorontalo", "Bolaang Mongondow"],
    "Sulawesi Tengah": ["Palu", "Donggala", "Banggai", "Poso", "Parigi Moutong", "Luwuk", "Bitung", "Mori"],
    "Sulawesi Selatan": ["Makassar", "Parepare", "Palopo", "Bone", "Bulukumba", "Pangkajene", "Maros", "Gowa"],
    "Sulawesi Tenggara": ["Kendari", "Bau-Bau", "Kolaka", "Raha", "Unaaha", "Baubau", "Bombana", "Muna"],
    "Sulawesi Barat": ["Mamuju", "Polewali", "Pasangkayu", "Majene", "Mamasa", "Donggala"],
    "Gorontalo": ["Gorontalo", "Limboto", "Boalemo", "Pohuwato", "Tilamuta"],
    "Maluku": ["Ambon", "Tual", "Masohi", "Saumlaki", "Namlea", "Bula", "Pirua"],
    "Maluku Utara": ["Ternate", "Tidore", "Sofifi", "Tobelo", "Halmahera", "Taliabu"],
    "Papua": ["Jayapura", "Timika", "Merauke", "Nabire", "Biak", "Sentani", "Wamena"],
    "Papua Barat": ["Manokwari", "Sorong", "Fakfak", "Kaimana", "Bintuni", "Raja Ampat", "Tambrauw"]
};

function populateProvinceOptions(selectElement, selectedValue = '') {
    let html = '<option value="">Pilih Provinsi</option>';
    Object.keys(dataKota).forEach(prov => {
        const selected = prov === selectedValue ? ' selected' : '';
        html += `<option value="${prov}"${selected}>${prov}</option>`;
    });
    selectElement.innerHTML = html;
}

function populateKotaOptions(selectElement, provinsi, selectedKota = '') {
    let html = '<option value="">Pilih Kota</option>';
    if (provinsi && dataKota[provinsi]) {
        dataKota[provinsi].forEach(kota => {
            const selected = kota === selectedKota ? ' selected' : '';
            html += `<option value="${kota}"${selected}>${kota}</option>`;
        });
    }
    selectElement.innerHTML = html;
}

// Untuk Modal Tambah
const provinsiAdd = document.querySelector('.provinsi-add');
if (provinsiAdd) {
    populateProvinceOptions(provinsiAdd);
    provinsiAdd.addEventListener('change', function() {
        populateKotaOptions(document.getElementById('kota'), this.value);
    });
}

// Untuk Modal Edit (semua modal edit)
document.querySelectorAll('.provinsi-edit').forEach(function(provinsiSelect) {
    const selectedProvinsi = provinsiSelect.dataset.selectedProvinsi || '';
    const kotaSelect = provinsiSelect.closest('form').querySelector('.kota-edit');
    const selectedKota = kotaSelect ? kotaSelect.dataset.selectedKota : '';

    populateProvinceOptions(provinsiSelect, selectedProvinsi);
    populateKotaOptions(kotaSelect, selectedProvinsi, selectedKota);

    provinsiSelect.addEventListener('change', function() {
        populateKotaOptions(kotaSelect, this.value);
    });
});

function filterVenueTable() {
    const input = document.getElementById('searchVenue');
    if (!input) return;

    const filter = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#venueTable tbody > tr');

    rows.forEach(row => {
        let rowText = '';
        row.querySelectorAll('td').forEach(cell => {
            rowText += ' ' + cell.textContent.toLowerCase();
        });

        row.style.display = rowText.includes(filter) ? '' : 'none';
    });
}

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
