<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: ../login/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Verifikasi Tiket</title>
<link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body { background: #f8f9fa; }

.navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-custom {
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
}

.badge-checkin {
    font-size: 1rem;
    padding: 8px 18px;
}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center" href="#">
            <div class="bg-white text-primary p-2 rounded-3 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                <i class="fas fa-keyboard"></i>
            </div>
            <span>Verifikasi<span class="opacity-75">Tiket</span></span>
        </a>

        <div class="ms-auto d-flex align-items-center">
            <div class="text-white me-3 d-none d-md-block">
                <small class="opacity-75">Mode Input Manual</small>
            </div>
            <a href="dashboard.php" class="btn btn-light btn-sm px-3 rounded-pill shadow-sm text-primary fw-bold me-2">
                <i class="fas fa-camera me-1"></i> Scan QR
            </a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm px-3 rounded-pill fw-bold">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">

<div id="notif"></div>

<div class="row g-4">

<!-- INPUT -->
<div class="col-lg-5">
    <div class="card card-custom p-4 text-center">
        <h4 class="mb-3 text-primary">
            <i class="fas fa-keyboard me-2"></i> Input Kode
        </h4>

        <input type="text" id="kodeInput" class="form-control mb-3"
            placeholder="Masukkan kode tiket..." autofocus>
    </div>
</div>

<!-- HASIL -->
<div class="col-lg-7">
    <div class="card card-custom">
        <div class="card-header bg-white">
            <h5><i class="fas fa-clipboard-list me-2"></i> Detail Tiket</h5>
        </div>

        <div class="card-body" id="hasil">
            <div class="text-center text-muted py-5">
                <i class="fas fa-ticket-alt fa-5x mb-3 opacity-25"></i>
                <p>Masukkan kode untuk verifikasi</p>
            </div>
        </div>
    </div>
</div>

</div>
</div>

<script>
const input = document.getElementById("kodeInput");

let timer;
input.addEventListener("input", function() {
    clearTimeout(timer);

    let kode = this.value.trim();

    if (kode.length < 5) return;

    timer = setTimeout(() => {

        fetch("proses_kode.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "kode=" + encodeURIComponent(kode)
        })
        .then(res => res.text()) // 🔥 ambil raw dulu
        .then(text => {
                let notif = document.getElementById("notif");
                let hasil = document.getElementById("hasil");
                let res;
                try {
                    res = JSON.parse(text);
                } catch (e) {
                    notif.innerHTML = `
                        <div class="alert alert-danger shadow">
                            Respon server tidak valid.
                        </div>
                    `;
                    hasil.innerHTML = `<div class="text-center text-muted py-5"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p>Response tidak dapat dibaca.</p></div>`;
                    return;
                }

                notif.innerHTML = `
                    <div class="alert alert-${getColor(res.status)} shadow">
                        ${res.message}
                    </div>
                `;

                if (res.status === "success") {
                    let d = res.data;

                    hasil.innerHTML = `
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Event</strong><br>${d.nama_event}
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Tanggal</strong><br>${d.tanggal_event}
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Jenis Tiket</strong><br>${d.nama_tiket}
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Nama</strong><br>${d.nama_pemesan}
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Status</strong><br>
                                <span class="badge bg-success">SUDAH CHECK-IN</span>
                            </div>
                        </div>
                    `;
                } else {
                    hasil.innerHTML = `
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-times-circle fa-4x mb-3"></i>
                            <p>${res.message}</p>
                        </div>
                    `;
                }

            })
            .catch(err => {
                console.error(err);
                let notif = document.getElementById("notif");
                let hasil = document.getElementById("hasil");
                notif.innerHTML = `
                    <div class="alert alert-danger shadow">
                        Gagal memproses kode. Coba lagi.
                    </div>
                `;
                hasil.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-exclamation-triangle fa-4x mb-3"></i>
                        <p>Terjadi kesalahan jaringan atau server.</p>
                    </div>
                `;
            });

    }, 500);

});

function getColor(status) {
    if (status === "success") return "success";
    if (status === "error") return "danger";
    if (status === "warning") return "warning";
    return "info";
}
</script>

</body>
</html>