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

// Contoh data buku yang dipinjam
$books = [
    [
        'id' => 1,
        'title' => 'Belajar PHP untuk Pemula',
        'isbn' => '978-602-123-456-7',
        'author' => 'John Doe',
        'tanggal_pinjam' => '2024-05-15',
        'tanggal_kembali' => '2024-05-22',
        'status' => 'Dipinjam'
    ],
    [
        'id' => 2,
        'title' => 'JavaScript Modern Development',
        'isbn' => '978-602-987-654-3',
        'author' => 'Jane Smith',
        'tanggal_pinjam' => '2024-05-10',
        'tanggal_kembali' => '2024-05-17',
        'status' => 'Terlambat'
    ],
    [
        'id' => 3,
        'title' => 'Database Design Fundamentals',
        'isbn' => '978-602-555-111-2',
        'author' => 'Robert Johnson',
        'tanggal_pinjam' => '2024-05-18',
        'tanggal_kembali' => '2024-05-25',
        'status' => 'Dipinjam'
    ]
];

$selected_status = $_GET['status'] ?? 'Semua Status';

// Fungsi untuk memproses peminjaman buku baru
if (isset($_POST['pinjam_buku'])) {
    $judul_buku = $_POST['judul_buku'] ?? '';
    $isbn_buku = $_POST['isbn_buku'] ?? '';
    
    if (!empty($judul_buku) && !empty($isbn_buku)) {
        // Di sini biasanya Anda akan menyimpan ke database
        // Untuk contoh, kita akan redirect dengan pesan sukses
        $_SESSION['success_message'] = "Buku berhasil dipinjam!";
        header("Location: peminjaman.php");
        exit;
    } else {
        $error_message = "Mohon lengkapi semua data buku yang akan dipinjam.";
    }
}
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
                    <a href="loginAnggota.php" class="flex items-center px-3 py-3 hover:bg-blue-700 text-black mt-60">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-2">Logout</span>
                 </a>
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

                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-medium">Manajemen Koleksi Buku</h2>
                    <button onclick="openPinjamModal()" class="bg-blue-600 text-white px-4 py-2 rounded-md flex items-center text-sm hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Pinjam Buku Baru
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium">Daftar Buku Dipinjam</h3>
                    </div>

                    <div class="p-4 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <select class="border border-gray-300 rounded-md px-3 py-1 text-sm" onchange="filterByStatus(this.value)">
                                <option value="all" <?php echo $selected_status == 'Semua Status' ? 'selected' : ''; ?>>Semua Status</option>
                                <option value="dipinjam" <?php echo $selected_status == 'Dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
                                <option value="terlambat" <?php echo $selected_status == 'Terlambat' ? 'selected' : ''; ?>>Terlambat</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                                    <th class="px-6 py-3 text-left">Judul</th>
                                    <th class="px-6 py-3 text-left">ISBN</th>
                                    <th class="px-6 py-3 text-left">Penulis</th>
                                    <th class="px-6 py-3 text-left">Tanggal Pinjam</th>
                                    <th class="px-6 py-3 text-left">Tanggal Kembali</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                    <th class="px-6 py-3 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($books as $book): ?>
                                <tr>
                                    <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($book['isbn']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td class="px-6 py-4"><?php echo date('d/m/Y', strtotime($book['tanggal_pinjam'])); ?></td>
                                    <td class="px-6 py-4"><?php echo date('d/m/Y', strtotime($book['tanggal_kembali'])); ?></td>
                                    <td class="px-6 py-4">
                                        <?php if ($book['status'] == 'Dipinjam'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Dipinjam</span>
                                        <?php elseif ($book['status'] == 'Terlambat'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <a href="detail-buku.php?id=<?php echo $book['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm">Detail</a>
                                            <a href="kembalikan.php?id=<?php echo $book['id']; ?>" class="text-green-600 hover:text-green-800 text-sm">Kembalikan</a>
                                        </div>
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

    <!-- Modal Pinjam Buku Baru -->
    <div id="pinjamModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg w-full max-w-md">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Pinjam Buku Baru</h3>
                        <button onclick="closePinjamModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Judul Buku</label>
                            <input type="text" name="judul_buku" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan judul buku" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                            <input type="text" name="isbn_buku" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan ISBN buku" required>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Penulis</label>
                            <input type="text" name="penulis_buku" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan nama penulis">
                        </div>
                        
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="closePinjamModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Batal</button>
                            <button type="submit" name="pinjam_buku" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Pinjam Buku</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openPinjamModal() {
            document.getElementById('pinjamModal').classList.remove('hidden');
        }
        
        function closePinjamModal() {
            document.getElementById('pinjamModal').classList.add('hidden');
        }
        
        function filterByStatus(status) {
            if (status === 'all') {
                window.location.href = 'peminjaman.php';
            } else {
                window.location.href = 'peminjaman.php?status=' + status;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Peminjaman page loaded');
            
            // Close modal when clicking outside
            document.getElementById('pinjamModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closePinjamModal();
                }
            });
        });
    </script>
</body>
</html>