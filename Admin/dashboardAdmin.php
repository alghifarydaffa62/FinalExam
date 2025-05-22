<?php
session_start();

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: loginAdmin.php");
//     exit;
// }

$admin = [
    'name' => $_SESSION['admin_name'] ?? 'Admin',
    'id' => $_SESSION['admin_id'] ?? '1'
];

$stats = [
    'total_buku' => $_SESSION['total_books'] ?? 150,
    'total_pinjam' => $_SESSION['total_borrowed'] ?? 45,
    'total_anggota' => $_SESSION['total_members'] ?? 89,
    'keterlambatan' => $_SESSION['late_returns'] ?? 12
];

$lending_stats = [
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    'peminjaman' => [45, 52, 48, 55, 59, 54],
    'pengembalian_tepat' => [40, 45, 42, 48, 50, 47],
    'pengembalian_terlambat' => [5, 7, 6, 7, 9, 7]
];
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
<body class="bg-blue-100">
    <div class="flex h-screen">
        <div class="w-64 bg-white flex-shrink-0">
            <div class="bg-white p-4 flex items-center space-x-3 text-black border-b border-gray-200">
                <div class="bg-blue-800 p-2 rounded">
                    <span class="font-bold text-white">SP</span>
                </div>
                <div class="text-sm leading-tight">
                    <div class="font-bold">SiPerpus</div>
                    <div class="text-xs">Sistem Perpustakaan Digital</div>
                </div>
            </div>

            <nav class="mt-4">
                <a href="dashboardAdmin.php" class="flex items-center px-4 py-3 bg-blue-700 text-white">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="kelolaBuku.php" class="flex items-center px-4 py-3 hover:bg-blue-700 text-black">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Buku</span>
                </a>
                <a href="kelolaKeterlambatan.php" class="flex items-center px-4 py-3 hover:bg-blue-700 text-black">
                    <i class="fas fa-clock w-6"></i>
                    <span class="ml-2">Keterlambatan</span>
                </a>
                <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 hover:bg-blue-700 text-black">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-2">Anggota</span>
                </a>
                <a href="logoutAdmin.php" class="flex items-center px-3 py-3 hover:bg-blue-700 text-black mt-60">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-2">Logout</span>
                </a>
            </nav>
        </div>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="font-bold text-lg">Dashboard</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64" placeholder="Cari...">
                            <button class="absolute right-2 top-2 text-gray-500">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="relative">
                            <button class="text-gray-500">
                                <i class="fas fa-bell"></i>
                            </button>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <h2 class="text-lg font-medium mb-6">Selamat datang, <?php echo htmlspecialchars($admin['name']); ?>!</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col items-center justify-center hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-4">Tambah Buku</h3>
                        <a href="kelolaBuku.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Tambah Buku</a>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col items-center justify-center hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-4">Tambah Anggota</h3>
                        <a href="kelolaAnggota.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Tambah Anggota</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Buku</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total_buku']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku dalam koleksi</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Pinjam</h3>
                        <p class="text-3xl font-bold text-orange-600"><?php echo $stats['total_pinjam']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku sedang dipinjam</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Anggota</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo $stats['total_anggota']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Anggota terdaftar</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Keterlambatan</h3>
                        <p class="text-3xl font-bold text-red-600"><?php echo $stats['keterlambatan']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Pengembalian terlambat</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-medium mb-4">Statistik Peminjaman</h3>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex justify-end space-x-4 mb-4">
                            <button class="text-xs bg-gray-200 text-gray-800 px-3 py-1 rounded-full">6 bulan</button>
                            <button class="text-xs bg-blue-100 text-blue-800 px-3 py-1 rounded-full">3 bulan</button>
                            <button class="text-xs bg-gray-200 text-gray-800 px-3 py-1 rounded-full">1 bulan</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('lendingStatsChart').getContext('2d');
            
            const chartData = {
                labels: <?php echo json_encode($lending_stats['labels']); ?>,
                datasets: [
                    {
                        label: 'Total Peminjaman',
                        data: <?php echo json_encode($lending_stats['peminjaman']); ?>,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgb(239, 68, 68)'
                    },
                    {
                        label: 'Pengembalian Tepat Waktu',
                        data: <?php echo json_encode($lending_stats['pengembalian_tepat']); ?>,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgb(59, 130, 246)'
                    },
                    {
                        label: 'Pengembalian Terlambat',
                        data: <?php echo json_encode($lending_stats['pengembalian_terlambat']); ?>,
                        borderColor: 'rgb(139, 92, 246)',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgb(139, 92, 246)'
                    }
                ]
            };
            
            const config = {
                type: 'line',
                data: chartData,
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
                                stepSize: 25
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
            
            new Chart(ctx, config);
        });
    </script>
</body>
</html>