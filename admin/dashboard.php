<?php
session_start();
include '../config/config.php';

// 🔐 Proteksi login
if (!isset($_SESSION['login'])) {
    header("Location: ../login/login.php");
    exit;
}

// 👑 Proteksi hanya admin
if ($_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses ditolak!');window.location='../landing.php';</script>";
    exit;
}

// 📊 Ambil data statistik
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'] ?? 0;
$total_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'] ?? 0;
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total) as total FROM orders WHERE status='paid'
"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="icon" type="image/x-icon" href="../bootstrap/image/image.png">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="sidebar.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Navbar dengan gradient sama seperti login */
        .navbar {
            background: var(--primary-gradient) !important;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .sidebar {
            background: white;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.08);
            min-height: 100vh;
            border-right: 1px solid #eee;
        }

        .card-stat {
            border: none;
            border-radius: 16px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card-stat:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
        }

        .stat-icon {
            font-size: 3rem;
            opacity: 0.85;
        }

        .list-group-item.active {
            background: var(--primary-gradient) !important;
            border: none;
            color: white;
        }

        .quick-btn {
            transition: all 0.3s ease;
        }

        .quick-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>
            <div class="container">
                <h2 class="mb-4 fw-bold text-dark">Dashboard Admin</h2>

                <div class="row g-4">
                    <!-- Total User -->
                    <div class="col-md-4">
                        <div class="card card-stat text-bg-primary shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 opacity-75">Total User</h6>
                                        <h2 class="mb-0 fw-bold"><?= number_format($total_user); ?></h2>
                                    </div>
                                    <i class="fas fa-users stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>  

                    <!-- Total Order -->
                    <div class="col-md-4">
                        <div class="card card-stat text-bg-success shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 opacity-75">Total Order</h6>
                                        <h2 class="mb-0 fw-bold"><?= number_format($total_order); ?></h2>
                                    </div>
                                    <i class="fas fa-shopping-cart stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Pendapatan -->
                    <div class="col-md-4">
                        <div class="card card-stat text-bg-warning shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 opacity-75">Total Pendapatan</h6>
                                        <h2 class="mb-0 fw-bold">Rp <?= number_format($total_pendapatan); ?></h2>
                                    </div>
                                    <i class="fas fa-money-bill-wave stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Access -->
                <div class="mt-5">
                    <h5 class="mb-3 text-muted fw-semibold">Quick Access</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="../admin/venue.php" class="btn btn-outline-primary quick-btn w-100 py-3 border-2">
                                <i class="fas fa-map-marker-alt fa-2x mb-2"></i><br>
                                Kelola Lokasi
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="event.php" class="btn btn-outline-success quick-btn w-100 py-3 border-2">
                                <i class="fas fa-calendar-alt fa-2x mb-2"></i><br>
                                Kelola Event
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="tiket.php" class="btn btn-outline-info quick-btn w-100 py-3 border-2">
                                <i class="fas fa-ticket-alt fa-2x mb-2"></i><br>
                                Kelola Tiket
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="voucher.php" class="btn btn-outline-warning quick-btn w-100 py-3 border-2">
                                <i class="fas fa-tags fa-2x mb-2"></i><br>
                                Kelola Voucher
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm p-3" style="border-radius: 15px;">
                            <h6 class="fw-bold mb-3 small text-muted">TREN PENJUALAN</h6>
                            <div style="height: 180px;">
                                <canvas id="lineChartKecil"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm p-3" style="border-radius: 15px;">
                            <h6 class="fw-bold mb-3 small text-muted">STATUS BAYAR</h6>
                            <div style="height: 180px;">
                                <canvas id="donutChartKecil"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Data untuk Line Chart (Tren Penjualan)
const lineCtx = document.getElementById('lineChartKecil').getContext('2d');
const lineChartKecil = new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
        datasets: [{
            label: 'Penjualan',
            data: [12, 19, 3, 5, 2, 3, 7],
            backgroundColor: 'rgba(102, 126, 234, 0.2)',
            borderColor: 'rgba(102, 126, 234, 1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.7)',
                titleFont: { size: 14 },
                bodyFont: { size: 12 },
                padding: 8,
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#666' }
            },
            y: {
                grid: { color: '#eee' },
                ticks: { color: '#666', beginAtZero: true }
            }
        }
    }
});

// Data untuk Donut Chart (Status Bayar)
const donutCtx = document.getElementById('donutChartKecil').getContext('2d');
const donutChartKecil = new Chart(donutCtx, {
    type: 'doughnut',
    data: {
        labels: ['Lunas', 'Belum Lunas'],
        datasets: [{
            data: [75, 25],
            backgroundColor: ['#d1e7dd', '#f8d7da'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.7)',
                titleFont: { size: 14 },
                bodyFont: { size: 12 },
                padding: 8,
            }
        },
        cutout: '70%',  
         scales: {
            x: { display: false },
            y: { display: false }
        }    
    }
});

// Fungsi untuk memperbarui Donut Chart berdasarkan data baru
function updateDonutChart(data) {
    donutChartKecil.data.datasets[0].data = [data.lunas, data.belumLunas];
    donutChartKecil.update();
}

// Fungsi untuk memperbarui Line Chart berdasarkan data baru
function updateLineChart(data) {
    lineChartKecil.data.labels = data.labels;
    lineChartKecil.data.datasets[0].data = data.penjualan;
    lineChartKecil.update();
}
    
// Contoh penggunaan fungsi update (Anda bisa menggantinya dengan data dinamis dari server)
setTimeout(() => {
    const newData = {
        lunas: 80,
        belumLunas: 20,
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
        penjualan: [15, 25, 10, 8, 5, 12, 20]
    };
    updateDonutChart(newData);
    updateLineChart(newData);
}, 1000);

</script>
</body>
</html>
