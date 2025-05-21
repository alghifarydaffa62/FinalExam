<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginAnggota.php");
    exit;
}

$user = [
    'name' => $_SESSION['user_name'] ?? 'Anggota',
    'id' => $_SESSION['user_id'] ?? '1'
];

$stats = [
    'total_borrowed' => $_SESSION['total_borrowed'] ?? 1,
    'total_returned' => $_SESSION['total_returned'] ?? 2,
    'total_buku' => $_SESSION['total_books'] ?? 150
];

$borrowing_history = [
    [
        'title' => 'Harry Potter dan Batu Bertuah',
        'borrow_date' => '15/05/2025',
        'due_date' => '22/05/2025',
        'status' => 'Dipinjam'
    ],
    [
        'title' => 'Laskar Pelangi',
        'borrow_date' => '10/05/2025',
        'due_date' => '17/05/2025',
        'status' => 'Dikembalikan'
    ],
    [
        'title' => 'Bumi Manusia',
        'borrow_date' => '05/05/2025',
        'due_date' => '12/05/2025',
        'status' => 'Dikembalikan'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - SiPerpus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="dashboard.php" class="flex items-center px-4 py-3 bg-blue-600 text-white">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="peminjaman.php" class="flex items-center px-4 py-3 hover:bg-blue-600 text-gray-800">
                    <i class="fas fa-book-open w-6"></i>
                    <span class="ml-2">Peminjaman</span>
                </a>
                <a href="pengembalian.php" class="flex items-center px-4 py-3 hover:bg-blue-600 text-gray-800">
                    <i class="fas fa-history w-6"></i>
                    <span class="ml-2">Pengembalian</span>
                </a>
                <a href="data-buku.php" class="flex items-center px-4 py-3 hover:bg-blue-600 text-gray-800">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Data Buku</span>
                </a>
                
            </nav>
        </div>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="font-bold text-lg">Dashboard Anggota</div>
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

            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <h2 class="text-lg font-medium mb-6">Selamat datang, <?php echo htmlspecialchars($user['name']); ?>!</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col items-center justify-center hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-4">Pinjam</h3>
                        <a href="pinjam-buku.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Pinjam Buku</a>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col items-center justify-center hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-4">Kembali</h3>
                        <a href="kembalikan-buku.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Kembalikan Buku</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Peminjaman</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total_borrowed']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku yang sedang dipinjam</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Dikembalikan</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo $stats['total_returned']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku yang sudah dikembalikan</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Buku</h3>
                        <p class="text-3xl font-bold text-purple-600"><?php echo $stats['total_buku']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku yang tersedia di perpustakaan</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-medium mb-4">Buku yang Dipinjam</h3>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Kembali</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($borrowing_history as $index => $book): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['borrow_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['due_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($book['status'] == 'Dipinjam'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Dipinjam</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Dikembalikan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($book['status'] == 'Dipinjam'): ?>
                                            <a href="kembalikan-buku.php?id=<?php echo $index; ?>" class="text-blue-600 hover:text-blue-800">Kembalikan</a>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-medium mb-4">Rekomendasi Buku</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php
                        $recommendations = [
                            ['title' => 'Filosofi Teras', 'author' => 'Henry Manampiring', 'genre' => 'Filsafat', 'id' => 1],
                            ['title' => 'Atomic Habits', 'author' => 'James Clear', 'genre' => 'Pengembangan Diri', 'id' => 2],
                            ['title' => 'Laut Bercerita', 'author' => 'Leila S. Chudori', 'genre' => 'Fiksi', 'id' => 3],
                        ];
                        foreach ($recommendations as $book): ?>
                        <div class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition">
                            <div class="bg-gray-200 h-32 rounded-lg mb-3 flex items-center justify-center">
                                <i class="fas fa-book text-gray-400 text-4xl"></i>
                            </div>
                            <h4 class="font-medium"><?php echo htmlspecialchars($book['title']); ?></h4>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($book['author']); ?></p>
                            <div class="mt-3 flex justify-between items-center">
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full"><?php echo htmlspecialchars($book['genre']); ?></span>
                                <a href="detail-buku.php?id=<?php echo $book['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm">Detail</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard Anggota loaded');
        });
    </script>
</body>
</html>
