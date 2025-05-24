<?php
session_start();
include '../konek.php';

if (isset($_GET['logout'])) {
    session_destroy();
    
    if (isset($_COOKIE['admin_remember'])) {
        setcookie('admin_remember', '', time() - 3600, '/');
    }

    header("Location: loginAnggota.php");
    exit;
}

$user = [
    'name' => $_SESSION['member_name'] ?? 'Anggota',
    'id' => $_SESSION['member_id'] ?? '1'
];

function getBookStats($conn) {
    $stats = [
        'total_buku' => 0,
        'total_borrowed' => 0
    ];

    $result = $conn->query("SELECT COUNT(*) as total FROM buku");
    if ($result) {
        $stats['total_buku'] = $result->fetch_assoc()['total'];
    }
    
    return $stats;
}

$book_stats = getBookStats($conn);

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
<body class="bg-[#FFFAEC]">
    <div class="flex h-screen">
        <div class="w-64 bg-[#DFD0B8] flex-shrink-0">
            <div class="bg-[#DFD0B8] p-4 flex items-center space-x-3 text-black border-b border-gray-200">
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
                <a href="peminjaman.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-book-open w-6"></i>
                    <span class="ml-2">Peminjaman</span>
                </a>
                <a href="pengembalian.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-history w-6"></i>
                    <span class="ml-2">Pengembalian</span>
                </a>
                <a href="data-buku.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Data Buku</span>
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
                    <div class="font-bold text-lg">Dashboard Anggota</div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <span><?php echo htmlspecialchars($user['name']); ?></span>
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAEC]">
                <h2 class="text-lg font-medium mb-6">Selamat datang, <?php echo htmlspecialchars($user['name']); ?>!</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col items-center justify-center hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-4">Pinjam</h3>
                        <a href="pinjam-buku.php" class="bg-[#393E46] text-white px-4 py-2 rounded-lg hover:bg-[#948979]">Pinjam Buku</a>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col items-center justify-center hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-4">Kembali</h3>
                        <a href="kembalikan-buku.php" class="bg-[#393E46] text-white px-4 py-2 rounded-lg hover:bg-[#948979]">Kembalikan Buku</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Peminjaman</h3>
                        <p class="text-3xl font-bold text-[#948979]"><?php echo $book_stats['total_borrowed']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku yang sedang dipinjam</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Buku</h3>
                        <p class="text-3xl font-bold text-[#948979]"><?php echo $book_stats['total_buku']; ?></p>
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