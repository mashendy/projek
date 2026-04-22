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

// 📅 Data seputar Event dan Tiket
$total_event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM event"))['total'] ?? 0;
$total_venue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM venue"))['total'] ?? 0;
$total_tiket = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tiket"))['total'] ?? 0;

// 🎟️ Total kuota dan terpakai
$kuota_stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COALESCE(SUM(t.kuota), 0) as total_kuota,
        COALESCE((SELECT SUM(od.qty) FROM order_detail od), 0) as total_terjual
    FROM tiket t
")) ?? ['total_kuota' => 0, 'total_terjual' => 0];

$total_kuota = (int)$kuota_stats['total_kuota'];
$total_terjual = (int)$kuota_stats['total_terjual'];
$sisa_kuota = $total_kuota - $total_terjual;

// 🏆 Event paling laris (top 5)
$top_events = mysqli_query($conn, "
    SELECT 
        e.id_event,
        e.nama_event,
        DATE_FORMAT(e.tanggal, '%d %M %Y') as tanggal_event,
        COALESCE(SUM(od.qty), 0) as total_terjual,
        COALESCE(SUM(t.kuota), 0) as total_kuota,
        COALESCE(SUM(od.qty), 0) * 100 / COALESCE(SUM(t.kuota), 1) as persentase_sold
    FROM event e
    LEFT JOIN tiket t ON e.id_event = t.id_event
    LEFT JOIN order_detail od ON t.id_tiket = od.id_tiket
    GROUP BY e.id_event, e.nama_event, e.tanggal
    ORDER BY total_terjual DESC
    LIMIT 5
");
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

        /* Print Styles */
        @media print {
            body {
                background: white;
            }
            .sidebar, .navbar, .quick-btn, .btn-print {
                display: none !important;
            }
            .container {
                max-width: 100% !important;
                padding: 0 !important;
            }
            .card {
                page-break-inside: avoid;
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }
            .card-stat:hover {
                transform: none !important;
                box-shadow: none !important;
            }
            h2, h5, h6 {
                page-break-after: avoid;
                color: #000;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f5f5f5;
                font-weight: bold;
            }
            .d-none-print {
                display: none !important;
            }
            .text-bg-primary, .text-bg-success, .text-bg-warning, .text-bg-danger, .text-bg-info, .text-bg-secondary, .text-bg-dark {
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .badge {
                margin: 0 2px;
            }
            .progress {
                display: none;
            }
            .row {
                page-break-inside: avoid;
            }
            @page {
                size: A4;
                margin: 1cm;
            }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 fw-bold text-dark">Dashboard Admin</h2>
                    <button onclick="window.print()" class="btn btn-primary btn-print d-none-print" title="Cetak Dashboard">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>

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

                <!-- Project Info Section -->
                <div class="row g-4 mt-2">
                    <!-- Total Event -->
                    <div class="col-md-3">
                        <div class="card card-stat text-bg-info shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 opacity-75">Total Event</h6>
                                        <h2 class="mb-0 fw-bold"><?= number_format($total_event); ?></h2>
                                    </div>
                                    <i class="fas fa-calendar-alt stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Venue -->
                    <div class="col-md-3">
                        <div class="card card-stat text-bg-danger shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 opacity-75">Total Venue</h6>
                                        <h2 class="mb-0 fw-bold"><?= number_format($total_venue); ?></h2>
                                    </div>
                                    <i class="fas fa-map-pin stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tiket -->
                    <div class="col-md-3">
                        <div class="card card-stat text-bg-secondary shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 opacity-75">Total Tiket</h6>
                                        <h2 class="mb-0 fw-bold"><?= number_format($total_tiket); ?></h2>
                                    </div>
                                    <i class="fas fa-ticket-alt stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tiket Terjual -->
                    <div class="col-md-3">
                        <div class="card card-stat text-bg-dark shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 opacity-75">Tiket Terjual</h6>
                                        <h2 class="mb-0 fw-bold"><?= number_format($total_terjual); ?></h2>
                                    </div>
                                    <i class="fas fa-check-circle stat-icon"></i>
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

                <!-- Top Events Paling Laris -->
                <div class="mt-5 mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-muted fw-semibold">🏆 Event Paling Laris</h5>
                    </div>
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr class="border-bottom">
                                            <th class="text-muted small">Event</th>
                                            <th class="text-muted small text-center" width="100">Tanggal</th>
                                            <th class="text-muted small text-center" width="80">Terjual</th>
                                            <th class="text-muted small text-center" width="80">Kuota</th>
                                            <th class="text-muted small text-center" width="120">Persentase</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        if (mysqli_num_rows($top_events) > 0) {
                                            while ($event = mysqli_fetch_assoc($top_events)) {
                                                $persentase = $event['persentase_sold'] !== null ? round((float)$event['persentase_sold']) : 0;
                                                $badge_class = $persentase >= 90 ? 'danger' : ($persentase >= 70 ? 'warning' : 'success');
                                        ?>
                                        <tr class="align-middle">
                                            <td class="fw-semibold">
                                                <?= htmlspecialchars($event['nama_event']); ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark"><?= $event['tanggal_event']; ?></span>
                                            </td>
                                            <td class="text-center fw-bold text-primary">
                                                <?= number_format($event['total_terjual']); ?>
                                            </td>
                                            <td class="text-center">
                                                <?= number_format($event['total_kuota']); ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center gap-2 justify-content-center">
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar bg-<?= $badge_class ?>" role="progressbar" style="width: <?= $persentase ?>%" aria-valuenow="<?= $persentase ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="badge bg-<?= $badge_class ?>" style="min-width: 45px;"><?= $persentase ?>%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada data event</td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mt-2 mb-5">
                    <!-- Kuota Tiket Overview -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
                            <h6 class="fw-bold mb-3 small text-muted">📊 OVERVIEW KUOTA TIKET</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded">
                                        <small class="text-muted d-block mb-1">Total Kuota</small>
                                        <h5 class="mb-0 fw-bold text-primary"><?= number_format($total_kuota); ?></h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded">
                                        <small class="text-muted d-block mb-1">Terjual</small>
                                        <h5 class="mb-0 fw-bold text-danger"><?= number_format($total_terjual); ?></h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded">
                                        <small class="text-muted d-block mb-1">Sisa Kuota</small>
                                        <h5 class="mb-0 fw-bold text-success"><?= number_format($sisa_kuota); ?></h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded">
                                        <small class="text-muted d-block mb-1">Persentase Sold</small>
                                        <h5 class="mb-0 fw-bold"><?= $total_kuota > 0 ? round(($total_terjual / $total_kuota) * 100) : 0; ?>%</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height: 24px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $total_kuota > 0 ? round(($total_terjual / $total_kuota) * 100) : 0; ?>%;" aria-valuenow="<?= $total_terjual ?>" aria-valuemin="0" aria-valuemax="<?= $total_kuota ?>">
                                        <?= $total_kuota > 0 ? round(($total_terjual / $total_kuota) * 100) : 0; ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trend Chart -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
                            <h6 class="fw-bold mb-3 small text-muted">TREN PENJUALAN</h6>
                            <div style="height: 200px;">
                                <canvas id="lineChartKecil"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Bayar Chart -->
                <div class="row g-4 mb-5">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
                            <h6 class="fw-bold mb-3 small text-muted">STATUS BAYAR</h6>
                            <div style="height: 200px;">
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

// Fungsi print dengan header standar
function printDashboard() {
    const printWindow = window.open('', '', 'width=900,height=600');
    const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Dashboard Admin</title>
            <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    padding: 20px;
                    font-family: Arial, sans-serif;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    border-bottom: 2px solid #333;
                    padding-bottom: 10px;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .header p {
                    margin: 5px 0;
                    color: #666;
                }
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 15px;
                    margin: 20px 0;
                }
                .stat-box {
                    border: 1px solid #ddd;
                    padding: 15px;
                    border-radius: 5px;
                    text-align: center;
                }
                .stat-label {
                    color: #666;
                    font-size: 12px;
                    margin-bottom: 5px;
                }
                .stat-value {
                    font-size: 24px;
                    font-weight: bold;
                    color: #333;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 10px;
                    text-align: left;
                }
                th {
                    background-color: #f5f5f5;
                    font-weight: bold;
                }
                .footer {
                    margin-top: 30px;
                    text-align: right;
                    font-size: 12px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Laporan Dashboard Admin</h1>
                <p>Event Ticket Management System</p>
                <p>Tanggal: ${new Date().toLocaleDateString('id-ID', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'})}</p>
            </div>
            <div class="content" id="printContent">
                ${document.querySelector('.container').innerHTML}
            </div>
            <div class="footer">
                <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
            </div>
        </body>
        </html>
    `;
    printWindow.document.write(html);
    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
    }, 250);
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
