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


$books = [
    
];

$selected_category = $_GET['category'] ?? 'Semua Kategori';
$selected_status = $_GET['status'] ?? 'Semua Status';


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Koleksi Buku - SiPerpus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
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
                <a href="peminjaman.php" class="flex items-center px-4 py-3 bg-blue-600 text-white">
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
                    <div class="font-bold text-lg">Peminjaman Buku</div>
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

            <main class="flex-1 overflow-y-auto p-6">
                <div class="text-sm text-gray-500 mb-4">
                    Dashboard / <span class="text-gray-700">Peminjaman</span>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-medium">Manajemen Koleksi Buku</h2>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-md flex items-center text-sm">
                        <i class="fas fa-plus mr-2"></i> Tambah Buku Baru
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium">Daftar Buku Dipinjam</h3>
                    </div>

                    <div class="p-4 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <select class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                                <option value="all" <?php echo $selected_category == 'Semua Kategori' ? 'selected' : ''; ?>>Semua Kategori</option>
                                <option value="fiction">Fiksi</option>
                                <option value="non-fiction">Non-Fiksi</option>
                                <option value="education">Pendidikan</option>
                            </select>
                            
                            <select class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                                <option value="all" <?php echo $selected_status == 'Semua Status' ? 'selected' : ''; ?>>Semua Status</option>
                                <option value="available">Tersedia</option>
                                <option value="borrowed">Dipinjam</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                                    <th class="px-6 py-3 text-left">Judul</th>
                                    <th class="px-6 py-3 text-left">Buku/ISBN</th>
                                    <th class="px-6 py-3 text-left">Kategori</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                    <th class="px-6 py-3 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($books)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-book text-gray-300 text-5xl mb-4"></i>
                                            <p class="mb-2">Tidak ada buku yang sedang dipinjam</p>
                                            <p class="text-sm">Untuk meminjam buku, silahkan pilih buku dari katalog</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['isbn']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['category']); ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($book['status'] == 'Tersedia'): ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Tersedia</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Dipinjam</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a href="detail-buku.php?id=<?php echo $book['id']; ?>" class="text-blue-600 hover:text-blue-800">Detail</a>
                                                <a href="edit-buku.php?id=<?php echo $book['id']; ?>" class="text-gray-600 hover:text-gray-800">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Peminjaman page loaded');
        });
    </script>
</body>
</html>