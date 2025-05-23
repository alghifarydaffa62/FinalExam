<?php
session_start();

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

$message = '';
$message_type = '';

if (isset($_POST['kembalikan_buku']) && isset($_POST['id_buku'])) {
    $id_buku = $_POST['id_buku'];

    $_SESSION['total_borrowed'] = ($_SESSION['total_borrowed'] ?? 5) - 1;
    $_SESSION['total_returned'] = ($_SESSION['total_returned'] ?? 3) + 1;
    
    $message = "Buku dengan ID {$id_buku} berhasil dikembalikan!";
    $message_type = 'success';

    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1&id=" . urlencode($id_buku));
    exit;
}

if (isset($_GET['success']) && isset($_GET['id'])) {
    $message = "Buku dengan ID " . htmlspecialchars($_GET['id']) . " berhasil dikembalikan!";
    $message_type = 'success';
}

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
                <a href="dashboard.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="peminjaman.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-book-open w-6"></i>
                    <span class="ml-2">Peminjaman</span>
                </a>
                <a href="pengembalian.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
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
                    <div class="font-bold text-lg">Pengembalian Buku</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64" placeholder="Cari buku...">
                            <button class="absolute right-2 top-2 text-gray-500">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                            <div class="text-sm">
                                <div class="font-medium"><?php echo htmlspecialchars($user['name']); ?></div>
                                <div class="text-gray-500 text-xs">Anggota</div>
                        </div>
                    </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 ">
                <div class="flex justify-between items-center mb-6">
                    <div class="text-sm">
                        <a href="dashboard.php" class="text-[#948979] hover:text-[#948979]">Dashboard</a> / 
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
                        <h3 class="text-lg font-medium">Buku Yang Dipinjam</h3>
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

        function confirmReturn(judulBuku) {
            return confirm('Apakah Anda yakin ingin mengembalikan buku "' + judulBuku + '"?');
        }

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