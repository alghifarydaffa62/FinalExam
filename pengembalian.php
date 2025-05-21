<?php
// Start session for user authentication
session_start();

// Simple authentication check (would be more robust in production)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user information
$user = [
    'name' => $_SESSION['user_name'] ?? 'Anggota',
    'id' => $_SESSION['user_id'] ?? '1'
];

// Mock peminjaman data that would be processed on form submission
$peminjaman = null;
$message = '';
$message_type = '';

// Handle form submission for searching peminjaman
if (isset($_POST['cari_peminjaman'])) {
    $transaksi_id = $_POST['transaksi_id'] ?? '';
    $anggota_id = $_POST['anggota_id'] ?? '';
    
    // This would normally check the database, we'll mock it here
    if (!empty($transaksi_id) || !empty($anggota_id)) {
        // Mock found peminjaman
        $peminjaman = [
            'id' => $transaksi_id ?: 'T-'.rand(1000, 9999),
            'anggota_id' => $anggota_id ?: $user['id'],
            'anggota_nama' => $user['name'],
            'buku_judul' => 'Harry Potter dan Batu Bertuah',
            'buku_id' => 'BK-'.rand(1000, 9999),
            'tanggal_pinjam' => '15/05/2025',
            'tanggal_kembali' => '22/05/2025',
            'status' => 'Dipinjam'
        ];
        $message = 'Data peminjaman ditemukan.';
        $message_type = 'success';
    } else {
        $message = 'Mohon masukkan ID Transaksi atau ID Anggota.';
        $message_type = 'error';
    }
}

// Handle form submission for book return
if (isset($_POST['proses_pengembalian']) && isset($_POST['peminjaman_id'])) {
    // This would normally update the database, we'll mock it here
    $_SESSION['total_borrowed'] = ($_SESSION['total_borrowed'] ?? 1) - 1;
    $_SESSION['total_returned'] = ($_SESSION['total_returned'] ?? 2) + 1;
    
    $message = 'Buku berhasil dikembalikan!';
    $message_type = 'success';
    
    // Reset peminjaman after successful return
    $peminjaman = null;
}

// Mock data for recent returns
$recent_returns = [
    [
        'id' => 'T-1234',
        'buku_judul' => 'Laskar Pelangi',
        'anggota_nama' => 'Ahmad Fauzi',
        'tanggal_pinjam' => '10/05/2025',
        'tanggal_kembali' => '17/05/2025',
        'tanggal_dikembalikan' => '16/05/2025',
        'status' => 'Dikembalikan'
    ],
    [
        'id' => 'T-1235',
        'buku_judul' => 'Bumi Manusia',
        'anggota_nama' => 'Siti Nurhaliza',
        'tanggal_pinjam' => '05/05/2025',
        'tanggal_kembali' => '12/05/2025',
        'tanggal_dikembalikan' => '12/05/2025',
        'status' => 'Dikembalikan'
    ],
    [
        'id' => 'T-1236',
        'buku_judul' => 'Filosofi Teras',
        'anggota_nama' => 'Budi Santoso',
        'tanggal_pinjam' => '01/05/2025',
        'tanggal_kembali' => '08/05/2025',
        'tanggal_dikembalikan' => '07/05/2025',
        'status' => 'Dikembalikan'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Buku - SiPerpus</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-blue-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
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
                <a href="dashboard.php" class="flex items-center px-4 py-3 hover:bg-blue-600 text-gray-800">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="peminjaman.php" class="flex items-center px-4 py-3 hover:bg-blue-600 text-gray-800">
                    <i class="fas fa-book-open w-6"></i>
                    <span class="ml-2">Peminjaman</span>
                </a>
                <a href="pengembalian.php" class="flex items-center px-4 py-3 bg-blue-600 text-white">
                    <i class="fas fa-history w-6"></i>
                    <span class="ml-2">Pengembalian</span>
                </a>
                <a href="data-buku.php" class="flex items-center px-4 py-3 hover:bg-blue-600 text-gray-800">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Data Buku</span>
                </a>
               
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="font-bold text-lg">Pengembalian Buku</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64" placeholder="Cari buku...">
                            <button class="absolute right-2 top-2 text-gray-500">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span><?php echo htmlspecialchars($user['name']); ?></span>
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="flex justify-between items-center mb-6">
                    <div class="text-sm">
                        <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">Dashboard</a> / 
                        <span class="text-gray-600">Pengembalian</span>
                    </div>
                    <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Tambah Buku Baru
                    </a>
                </div>

                <h2 class="text-xl font-medium mb-6">Pengembalian Buku</h2>

                <?php if ($message): ?>
                <div class="mb-6 px-4 py-3 rounded-lg <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-medium mb-4">Cari Peminjaman</h3>
                    <form method="post" action="">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="transaksi_id" class="block text-sm font-medium text-gray-700 mb-1">ID Transaksi / Barcode</label>
                                <div class="relative">
                                    <input type="text" id="transaksi_id" name="transaksi_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 pr-10" placeholder="Masukkan ID Transaksi atau ID Barcode...">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-barcode text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="anggota_id" class="block text-sm font-medium text-gray-700 mb-1">ID Anggota</label>
                                <div class="relative">
                                    <input type="text" id="anggota_id" name="anggota_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 pr-10" placeholder="Masukkan ID Anggota...">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" name="cari_peminjaman" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                <i class="fas fa-search mr-2"></i>Kembalikan Buku
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Pengembalian Form (if peminjaman is found) -->
                <?php if ($peminjaman): ?>
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-medium mb-4">Proses Pengembalian</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">ID Transaksi</p>
                            <p class="font-medium"><?php echo htmlspecialchars($peminjaman['id']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">ID Anggota</p>
                            <p class="font-medium"><?php echo htmlspecialchars($peminjaman['anggota_id']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Nama Anggota</p>
                            <p class="font-medium"><?php echo htmlspecialchars($peminjaman['anggota_nama']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Judul Buku</p>
                            <p class="font-medium"><?php echo htmlspecialchars($peminjaman['buku_judul']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tanggal Pinjam</p>
                            <p class="font-medium"><?php echo htmlspecialchars($peminjaman['tanggal_pinjam']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tanggal Kembali</p>
                            <p class="font-medium"><?php echo htmlspecialchars($peminjaman['tanggal_kembali']); ?></p>
                        </div>
                    </div>
                    <form method="post" action="">
                        <input type="hidden" name="peminjaman_id" value="<?php echo htmlspecialchars($peminjaman['id']); ?>">
                        <div class="mt-4 flex justify-end">
                            <button type="submit" name="proses_pengembalian" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                <i class="fas fa-check-circle mr-2"></i>Proses Pengembalian
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Recent Returns -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium">Pengembalian Terbaru</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Peminjam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batas Kembali</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Kembali</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($recent_returns as $return): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($return['id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($return['buku_judul']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($return['anggota_nama']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($return['tanggal_pinjam']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($return['tanggal_kembali']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($return['tanggal_dikembalikan']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            <?php echo htmlspecialchars($return['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Halaman Pengembalian Buku loaded');
        });
    </script>
</body>
</html>