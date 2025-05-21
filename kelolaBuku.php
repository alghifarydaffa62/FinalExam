<?php
// Start session for admin authentication
session_start();

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: loginAdmin.php");
//     exit;
// }

// Get admin information
$admin = [
    'name' => $_SESSION['admin_name'] ?? 'Admin',
    'id' => $_SESSION['admin_id'] ?? '1'
];

// Mock data for book statistics
$book_stats = [
    'total_buku' => $_SESSION['total_books'] ?? 150,
    'dipinjam' => $_SESSION['books_borrowed'] ?? 45,
    'tersedia' => ($_SESSION['total_books'] ?? 150) - ($_SESSION['books_borrowed'] ?? 45),
    'kategori' => $_SESSION['book_categories'] ?? 12
];

// Mock data for books
$books = [
    [
        'id' => '1',
        'judul' => 'Harry Potter dan Batu Bertuah',
        'penulis' => 'J.K. Rowling',
        'kategori' => 'Fiksi',
        'tahun' => '1997',
        'isbn' => '9786020379784',
        'stok' => 5,
        'status' => 'Tersedia'
    ],
    [
        'id' => '2',
        'judul' => 'Laskar Pelangi',
        'penulis' => 'Andrea Hirata',
        'kategori' => 'Novel',
        'tahun' => '2005',
        'isbn' => '9789792248616',
        'stok' => 3,
        'status' => 'Tersedia'
    ],
    [
        'id' => '3',
        'judul' => 'Filosofi Teras',
        'penulis' => 'Henry Manampiring',
        'kategori' => 'Filsafat',
        'tahun' => '2018',
        'isbn' => '9786024246945',
        'stok' => 0,
        'status' => 'Dipinjam'
    ],
    [
        'id' => '4',
        'judul' => 'Bumi Manusia',
        'penulis' => 'Pramoedya Ananta Toer',
        'kategori' => 'Novel',
        'tahun' => '1980',
        'isbn' => '9799731234',
        'stok' => 2,
        'status' => 'Tersedia'
    ],
    [
        'id' => '5',
        'judul' => 'Rich Dad Poor Dad',
        'penulis' => 'Robert T. Kiyosaki',
        'kategori' => 'Keuangan',
        'tahun' => '1997',
        'isbn' => '9786020333175',
        'stok' => 1,
        'status' => 'Tersedia'
    ],
    [
        'id' => '6',
        'judul' => 'Atomic Habits',
        'penulis' => 'James Clear',
        'kategori' => 'Pengembangan Diri',
        'tahun' => '2018',
        'isbn' => '9786020633176',
        'stok' => 0,
        'status' => 'Dipinjam'
    ]
];

// Mock data for categories with counts
$categories = [
    ['nama' => 'Novel', 'jumlah' => 45],
    ['nama' => 'Fiksi', 'jumlah' => 38],
    ['nama' => 'Pendidikan', 'jumlah' => 27],
    ['nama' => 'Sejarah', 'jumlah' => 15],
    ['nama' => 'Keuangan', 'jumlah' => 12],
    ['nama' => 'Pengembangan Diri', 'jumlah' => 10],
    ['nama' => 'Filsafat', 'jumlah' => 5],
    ['nama' => 'Lainnya', 'jumlah' => 8]
];

// Handle search if present
$search_query = $_GET['search'] ?? '';
if (!empty($search_query)) {
    // In a real application, you would search through database
    // For this mock, we'll just pretend to filter
    // (the data remains the same for demonstration)
}

// Handle filter by category if present
$filter_category = $_GET['category'] ?? '';
if (!empty($filter_category)) {
    // In a real application, you would filter by category in database
    // For this mock, we'll just pretend to filter
    // (the data remains the same for demonstration)
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - SiPerpus</title>
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
                <a href="dashboardAdmin.php" class="flex items-center px-4 py-3 hover:bg-blue-700 text-black">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="kelolaBuku.php" class="flex items-center px-4 py-3 bg-blue-700 text-white">
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

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="font-bold text-lg">Kelola Buku</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <form action="kelolaBuku.php" method="GET">
                                <input type="text" name="search" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64" 
                                    placeholder="Cari buku..." value="<?php echo htmlspecialchars($search_query); ?>">
                                <button type="submit" class="absolute right-2 top-2 text-gray-500">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
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

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-medium">Daftar Buku Perpustakaan</h2>
                    <a href="tambahBuku.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                        <i class="fas fa-plus mr-2"></i> Tambah Buku
                    </a>
                </div>

                <!-- Book Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Buku</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $book_stats['total_buku']; ?></p>
                        <div class="flex items-center mt-2 text-sm">
                            <span class="text-green-500"><i class="fas fa-arrow-up mr-1"></i>3%</span>
                            <span class="text-gray-500 ml-2">dari bulan lalu</span>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Buku Dipinjam</h3>
                        <p class="text-3xl font-bold text-orange-600"><?php echo $book_stats['dipinjam']; ?></p>
                        <div class="flex items-center mt-2 text-sm">
                            <span class="text-green-500"><i class="fas fa-arrow-up mr-1"></i>5%</span>
                            <span class="text-gray-500 ml-2">dari bulan lalu</span>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Buku Tersedia</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo $book_stats['tersedia']; ?></p>
                        <div class="flex items-center mt-2 text-sm">
                            <span class="text-red-500"><i class="fas fa-arrow-down mr-1"></i>2%</span>
                            <span class="text-gray-500 ml-2">dari bulan lalu</span>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Kategori</h3>
                        <p class="text-3xl font-bold text-purple-600"><?php echo $book_stats['kategori']; ?></p>
                        <div class="flex items-center mt-2 text-sm">
                            <span class="text-gray-500">Total kategori buku</span>
                        </div>
                    </div>
                </div>

                <!-- Book List and Categories -->
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Book List Section (Main Content) -->
                    <div class="md:w-3/4">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <!-- Filters and Actions -->
                            <div class="flex flex-wrap items-center justify-between mb-6">
                                <div class="flex items-center space-x-2 mb-2 md:mb-0">
                                    <label class="text-sm text-gray-500">Filter:</label>
                                    <select name="category" class="bg-gray-100 rounded px-3 py-1 text-sm">
                                        <option value="">Semua Kategori</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category['nama']); ?>" 
                                                <?php echo ($filter_category === $category['nama']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['nama']); ?> 
                                                (<?php echo $category['jumlah']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select class="bg-gray-100 rounded px-3 py-1 text-sm">
                                        <option value="">Status</option>
                                        <option value="tersedia">Tersedia</option>
                                        <option value="dipinjam">Dipinjam</option>
                                    </select>
                                    <button class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm">
                                        Terapkan
                                    </button>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="text-sm text-gray-500 hover:text-blue-600">
                                        <i class="fas fa-download mr-1"></i> Export
                                    </button>
                                    <select class="bg-gray-100 rounded px-3 py-1 text-sm">
                                        <option value="10">10 per halaman</option>
                                        <option value="25">25 per halaman</option>
                                        <option value="50">50 per halaman</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Books Table -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-3 rounded-tl-lg">ID</th>
                                            <th class="px-4 py-3">Judul</th>
                                            <th class="px-4 py-3">Penulis</th>
                                            <th class="px-4 py-3">Kategori</th>
                                            <th class="px-4 py-3">Tahun</th>
                                            <th class="px-4 py-3">Stok</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3 rounded-tr-lg">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($books as $index => $book): ?>
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="px-4 py-3"><?php echo htmlspecialchars($book['id']); ?></td>
                                                <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($book['judul']); ?></td>
                                                <td class="px-4 py-3"><?php echo htmlspecialchars($book['penulis']); ?></td>
                                                <td class="px-4 py-3">
                                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                        <?php echo htmlspecialchars($book['kategori']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3"><?php echo htmlspecialchars($book['tahun']); ?></td>
                                                <td class="px-4 py-3"><?php echo htmlspecialchars($book['stok']); ?></td>
                                                <td class="px-4 py-3">
                                                    <?php if ($book['status'] === 'Tersedia'): ?>
                                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                            Tersedia
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">
                                                            Dipinjam
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex space-x-2">
                                                        <a href="detailBuku.php?id=<?php echo $book['id']; ?>" class="text-blue-600 hover:text-blue-900" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="editBuku.php?id=<?php echo $book['id']; ?>" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $book['id']; ?>)" class="text-red-600 hover:text-red-900" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="flex items-center justify-between mt-6">
                                <div class="text-sm text-gray-500">
                                    Menampilkan 1 - 6 dari 150 buku
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">
                                        <i class="fas fa-chevron-left text-xs"></i>
                                    </a>
                                    <a href="#" class="bg-blue-600 text-white px-3 py-1 rounded-md">1</a>
                                    <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">2</a>
                                    <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">3</a>
                                    <span class="text-gray-500">...</span>
                                    <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">15</a>
                                    <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Section (Sidebar) -->
                    <div class="md:w-1/4">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-medium">Kategori</h3>
                                <a href="kelolaKategori.php" class="text-blue-600 text-sm hover:underline">Kelola</a>
                            </div>
                            <div class="space-y-3">
                                <?php foreach ($categories as $category): ?>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <span class="w-2 h-2 rounded-full bg-blue-600 mr-2"></span>
                                            <span class="text-sm"><?php echo htmlspecialchars($category['nama']); ?></span>
                                        </div>
                                        <span class="text-sm text-gray-500"><?php echo $category['jumlah']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-4 pt-4 border-t">
                                <a href="tambahKategori.php" class="text-blue-600 text-sm flex items-center hover:underline">
                                    <i class="fas fa-plus mr-2"></i> Tambah Kategori
                                </a>
                            </div>
                        </div>

                        <!-- Recent Activities Widget -->
                        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                            <h3 class="font-medium mb-4">Aktivitas Terbaru</h3>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-book-open text-blue-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm">Buku <span class="font-medium">Atomic Habits</span> dipinjam oleh <span class="font-medium">Andi Wijaya</span></p>
                                        <p class="text-xs text-gray-500 mt-1">2 jam yang lalu</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-plus text-green-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm">Admin <span class="font-medium">menambahkan 3 buku baru</span> ke kategori Pendidikan</p>
                                        <p class="text-xs text-gray-500 mt-1">5 jam yang lalu</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-exclamation text-red-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm">Buku <span class="font-medium">Rich Dad Poor Dad</span> hampir habis stok</p>
                                        <p class="text-xs text-gray-500 mt-1">1 hari yang lalu</p>
                                    </div>
                                </div>
                            </div>
                            <a href="laporanAktivitas.php" class="text-blue-600 text-sm flex items-center mt-4 hover:underline">
                                Lihat semua aktivitas <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal (hidden by default) -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-auto">
            <h3 class="text-lg font-medium mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus buku ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg">Batal</button>
                <button id="confirmDeleteButton" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
            </div>
        </div>
    </div>

    <script>
        // Functions for delete confirmation modal
        function confirmDelete(id) {
            const modal = document.getElementById('deleteModal');
            const confirmButton = document.getElementById('confirmDeleteButton');
            
            modal.classList.remove('hidden');
            
            // Set up the confirm button to actually delete when clicked
            confirmButton.onclick = function() {
                // In a real application, you would make an AJAX request to delete the book
                // For this demo, we'll just close the modal
                alert('Buku dengan ID: ' + id + ' telah dihapus.');
                closeDeleteModal();
            };
        }
        
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        };
    </script>
</body>
</html>