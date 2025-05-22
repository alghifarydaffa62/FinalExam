<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user = [
    'name' => $_SESSION['user_name'] ?? 'Anggota',
    'id' => $_SESSION['user_id'] ?? '1'
];

$message = '';
$message_type = '';

// Proses pengembalian buku
if (isset($_POST['kembalikan_buku']) && isset($_POST['id_buku'])) {
    $id_buku = $_POST['id_buku'];
    
    // Update session counters
    $_SESSION['total_borrowed'] = ($_SESSION['total_borrowed'] ?? 5) - 1;
    $_SESSION['total_returned'] = ($_SESSION['total_returned'] ?? 3) + 1;
    
    $message = "Buku dengan ID {$id_buku} berhasil dikembalikan!";
    $message_type = 'success';
    
    // Redirect untuk mencegah double submission
    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1&id=" . urlencode($id_buku));
    exit;
}

// Handle success message dari redirect
if (isset($_GET['success']) && isset($_GET['id'])) {
    $message = "Buku dengan ID " . htmlspecialchars($_GET['id']) . " berhasil dikembalikan!";
    $message_type = 'success';
}

// Data buku yang sedang dipinjam
$borrowed_books = [
    [
        'id_buku' => 'BK-1001',
        'judul' => 'Harry Potter dan Batu Bertuah',
        'penerbit' => 'Gramedia Pustaka Utama',
        'isbn' => '978-979-22-0000-1',
        'jumlah_halaman' => 320,
        'tahun_terbit' => 2001
    ],
    [
        'id_buku' => 'BK-1002',
        'judul' => 'Laskar Pelangi',
        'penerbit' => 'Bentang Pustaka',
        'isbn' => '978-979-22-0000-2',
        'jumlah_halaman' => 529,
        'tahun_terbit' => 2005
    ],
    [
        'id_buku' => 'BK-1003',
        'judul' => 'Bumi Manusia',
        'penerbit' => 'Hasta Mitra',
        'isbn' => '978-979-22-0000-3',
        'jumlah_halaman' => 535,
        'tahun_terbit' => 1980
    ],
    [
        'id_buku' => 'BK-1004',
        'judul' => 'Filosofi Teras',
        'penerbit' => 'Kompas Gramedia',
        'isbn' => '978-979-22-0000-4',
        'jumlah_halaman' => 320,
        'tahun_terbit' => 2018
    ],
    [
        'id_buku' => 'BK-1005',
        'judul' => 'Ayat-Ayat Cinta',
        'penerbit' => 'Republika',
        'isbn' => '978-979-22-0000-5',
        'jumlah_halaman' => 416,
        'tahun_terbit' => 2004
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Buku - SiPerpus</title>
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

        <div class="flex-1 flex flex-col overflow-hidden">
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

            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="flex justify-between items-center mb-6">
                    <div class="text-sm">
                        <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">Dashboard</a> / 
                        <span class="text-gray-600">Pengembalian</span>
                    </div>
                </div>

                <h2 class="text-xl font-medium mb-6">Pengembalian Buku</h2>

                <?php if ($message): ?>
                <div class="mb-6 px-4 py-3 rounded-lg <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium">Tabel Yang Dipinjam</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Buku</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerbit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Halaman</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun Terbit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($borrowed_books as $book): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium"><?php echo htmlspecialchars($book['id_buku']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['judul']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['penerbit']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['isbn']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><?php echo htmlspecialchars($book['jumlah_halaman']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><?php echo htmlspecialchars($book['tahun_terbit']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="post" action="" class="inline" onsubmit="return confirmReturn('<?php echo htmlspecialchars($book['judul']); ?>')">
                                            <input type="hidden" name="id_buku" value="<?php echo htmlspecialchars($book['id_buku']); ?>">
                                            <button type="submit" name="kembalikan_buku" class="bg-green-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-green-700 transition-colors duration-200">
                                                <i class="fas fa-undo mr-1"></i>Kembalikan
                                            </button>
                                        </form>
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

        // Fungsi konfirmasi sebelum mengembalikan buku
        function confirmReturn(judulBuku) {
            return confirm('Apakah Anda yakin ingin mengembalikan buku "' + judulBuku + '"?');
        }

        // Auto hide success message after 5 seconds
        setTimeout(function() {
            const successMessage = document.querySelector('.bg-green-100');
            if (successMessage) {
                successMessage.style.transition = 'opacity 0.5s';
                successMessage.style.opacity = '0';
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 500);
            }
        }, 5000);
    </script>
</body>
</html>