<?php
session_start();
include '../konek.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: loginAdmin.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    
    if (isset($_COOKIE['admin_remember'])) {
        setcookie('admin_remember', '', time() - 3600, '/');
    }

    header("Location: loginAdmin.php");
    exit;
}

$admin = [
    'name' => $_SESSION['admin_name'] ?? 'Admin',
    'id' => $_SESSION['admin_id'] ?? '1'
];

function getBookStats($conn) {
    $stats = [
        'total_buku' => 0,
        'dipinjam' => 0,
        'anggota' => 0,
        'pengembalian' => 0,
        'keterlambatan' => 0
    ];

    $result = $conn->query("SELECT COUNT(*) as total FROM buku");
    if ($result) {
        $stats['total_buku'] = $result->fetch_assoc()['total'];
    }

    $anggota = $conn->query("SELECT COUNT(*) as totalAnggota FROM anggota");
    if($anggota) {
        $stats['total_anggota'] = $anggota->fetch_assoc()['totalAnggota'];
    }

    $peminjaman = $conn->query("SELECT COUNT(*) as totalPeminjaman FROM peminjaman WHERE status_peminjaman = 'dipinjam'");
    if($peminjaman) {
        $stats['totalPeminjaman'] = $peminjaman->fetch_assoc()['totalPeminjaman'];
    }

    $pengembalian = $conn->query("SELECT COUNT(*) as totalPengembalian FROM peminjaman WHERE status_peminjaman = 'dikembalikan'");
    if($pengembalian) {
        $stats['pengembalian'] = $pengembalian->fetch_assoc()['totalPengembalian'];
    }

    $terlambat_query = $conn->query("SELECT COUNT(*) as totalTerlambat FROM peminjaman WHERE status_peminjaman = 'dipinjam' AND Batas_waktu < CURDATE()");
    if ($terlambat_query) {
        $stats['keterlambatan'] = $terlambat_query->fetch_assoc()['totalTerlambat'];
    }

    return $stats;
}

function getLendingStats($conn) {
    $stats = [
        'labels' => [],
        'total_buku' => [],
        'total_peminjaman' => [],
        'total_pengembalian' => []
    ];

    $check_column = $conn->query("SHOW COLUMNS FROM buku LIKE 'created_at'");
    $has_created_at = $check_column && $check_column->num_rows > 0;

    $total_buku_result = $conn->query("SELECT COUNT(*) as total FROM buku");
    $total_buku_current = $total_buku_result ? $total_buku_result->fetch_assoc()['total'] : 0;

    for ($i = 5; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $month_name = date('M', strtotime("-$i months"));
        $year = date('Y', strtotime("-$i months"));
        $month = date('m', strtotime("-$i months"));

        $stats['labels'][] = $month_name;

        if ($has_created_at) {
            $buku_query = $conn->query("SELECT COUNT(*) as total FROM buku WHERE DATE_FORMAT(created_at, '%Y-%m') <= '$date'");
            $total_buku = $buku_query ? $buku_query->fetch_assoc()['total'] : 0;
        } else {
            $total_buku = $total_buku_current;
        }
        $stats['total_buku'][] = $total_buku;
        
        $peminjaman_query = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE YEAR(tanggal_pinjam) = $year AND MONTH(tanggal_pinjam) = $month");
        $total_peminjaman = $peminjaman_query ? $peminjaman_query->fetch_assoc()['total'] : 0;
        $stats['total_peminjaman'][] = $total_peminjaman;

        $pengembalian_query = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE YEAR(tanggal_kembali) = $year AND MONTH(tanggal_kembali) = $month AND status_peminjaman = 'dikembalikan'");
        $total_pengembalian = $pengembalian_query ? $pengembalian_query->fetch_assoc()['total'] : 0;
        $stats['total_pengembalian'][] = $total_pengembalian;
    }
    
    return $stats;
}

$book_stats = getBookStats($conn);
$lending_stats = getLendingStats($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SiPerpus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="bg-[#FFFAEC]">
    <div class="flex h-screen">
        <div class="w-64 bg-[#DFD0B8] shadow-md">
            <div class="p-4 flex items-center space-x-3 border-b border-[#FFFAEC]">
                <div class="bg-[#393E46] p-2 rounded">
                    <span class="font-bold text-white">SP</span>
                </div>
                <div class="text-sm leading-tight">
                    <div class="font-bold">SiPerpus</div>
                    <div class="text-xs">Sistem Perpustakaan Digital</div>
                </div>
            </div>
            <nav class="mt-4">
                <a href="dashboardAdmin.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="kelolaBuku.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Buku</span>
                </a>
                <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-2">Anggota</span>
                </a>
                <a href="daftarPeminjaman.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Daftar Peminjaman</span>
                </a>
                <a href="?logout=1" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black mt-auto">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-2">Logout</span>
                </a>
            </nav>
        </div>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-[#DFD0B8] shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="font-bold text-lg">Dashboard</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="text-gray-500">
                                <i class="fas fa-bell"></i>
                            </button>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                            <div class="text-sm">
                                <div class="font-medium"><?php echo htmlspecialchars($admin['name']); ?></div>
                                <div class="text-gray-500 text-xs">Admin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAEC]">
                <h2 class="text-lg font-medium mb-6">Selamat datang, <?php echo htmlspecialchars($admin['name']); ?>!</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col items-center justify-center hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-4">Tambah Buku</h3>
                        <a href="kelolaBuku.php" class="bg-[#393E46] text-white px-4 py-2 rounded-lg hover:bg-[#948979]">Tambah Buku</a>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col items-center justify-center hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-4">Data Anggota</h3>
                        <a href="kelolaAnggota.php" class="bg-[#393E46] text-white px-4 py-2 rounded-lg ">Data Anggota</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Buku</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $book_stats['total_buku']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku dalam koleksi</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Pinjam</h3>
                        <p class="text-3xl font-bold text-orange-600"><?php echo $book_stats['totalPeminjaman']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku sedang dipinjam</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Anggota</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo $book_stats['total_anggota']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Anggota terdaftar</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Pengembalian</h3>
                        <p class="text-3xl font-bold text-blue-900"><?php echo $book_stats['pengembalian']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku dikembalikan</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Keterlambatan</h3>
                        <p class="text-3xl font-bold text-red-600"><?php echo $book_stats['keterlambatan']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Pengembalian terlambat</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-medium mb-4">Statistik Peminjaman</h3>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex justify-end space-x-4 mb-4">
                            <button id="btn6bulan" class="text-xs bg-blue-100 text-[#393E46] px-3 py-1 rounded-full">6 bulan</button>
                            <button id="btn3bulan" class="text-xs bg-gray-200 text-gray-800 px-3 py-1 rounded-full">3 bulan</button>
                            <button id="btn1bulan" class="text-xs bg-gray-200 text-gray-800 px-3 py-1 rounded-full">1 bulan</button>
                        </div>
                        <div style="height: 250px;">
                            <canvas id="lendingStatsChart"></canvas>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        let chart;
        const fullData = {
            labels: <?php echo json_encode($lending_stats['labels']); ?>,
            datasets: [
                {
                    label: 'Jumlah Buku',
                    data: <?php echo json_encode($lending_stats['total_buku']); ?>,
                    borderColor: 'rgb(37, 99, 235)', 
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(37, 99, 235)'
                },
                {
                    label: 'Total Peminjaman',
                    data: <?php echo json_encode($lending_stats['total_peminjaman']); ?>,
                    borderColor: 'rgb(234, 88, 12)', 
                    backgroundColor: 'rgba(234, 88, 12, 0.1)',
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(234, 88, 12)'
                },
                {
                    label: 'Total Pengembalian',
                    data: <?php echo json_encode($lending_stats['total_pengembalian']); ?>,
                    borderColor: 'rgb(30, 58, 138)', // blue-900 equivalent
                    backgroundColor: 'rgba(30, 58, 138, 0.1)',
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(30, 58, 138)'
                }
            ]
        };

        function initChart() {
            const ctx = document.getElementById('lendingStatsChart').getContext('2d');
            
            const config = {
                type: 'line',
                data: fullData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        }
                    }
                }
            };
            
            chart = new Chart(ctx, config);
        }

        function updateChart(months) {
            const startIndex = 6 - months;
            const filteredData = {
                labels: fullData.labels.slice(startIndex),
                datasets: fullData.datasets.map(dataset => ({
                    ...dataset,
                    data: dataset.data.slice(startIndex)
                }))
            };
            
            chart.data = filteredData;
            chart.update();
        }

        function setActiveButton(activeBtn) {
            document.querySelectorAll('#btn6bulan, #btn3bulan, #btn1bulan').forEach(btn => {
                btn.className = 'text-xs bg-gray-200 text-gray-800 px-3 py-1 rounded-full';
            });
            activeBtn.className = 'text-xs bg-blue-100 text-[#393E46] px-3 py-1 rounded-full';
        }

        document.addEventListener('DOMContentLoaded', function() {
            initChart();

            document.getElementById('btn6bulan').addEventListener('click', function() {
                updateChart(6);
                setActiveButton(this);
            });

            document.getElementById('btn3bulan').addEventListener('click', function() {
                updateChart(3);
                setActiveButton(this);
            });

            document.getElementById('btn1bulan').addEventListener('click', function() {
                updateChart(1);
                setActiveButton(this);
            });
        });
    </script>
</body>
</html>