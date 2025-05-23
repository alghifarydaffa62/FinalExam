<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: loginAdmin.php");
    exit;
}

// Initialize books array in session if not exists
if (!isset($_SESSION['books'])) {
    $_SESSION['books'] = [
        [
            'id' => '1',
            'judul' => 'Pemrograman Web dengan PHP',
            'penulis' => 'John Doe',
            'tahun' => '2020',
            'isbn' => '1234567890',
            'stok' => 5,
            'status' => 'Tersedia'
        ],
        [
            'id' => '2',
            'judul' => 'Belajar Laravel untuk Pemula',
            'penulis' => 'Jane Smith',
            'tahun' => '2021',
            'isbn' => '0987654321',
            'stok' => 3,
            'status' => 'Tersedia'
        ],
        [
            'id' => '3',
            'judul' => 'Pengenalan Machine Learning',
            'penulis' => 'Robert Johnson',
            'tahun' => '2019',
            'isbn' => '1122334455',
            'stok' => 0,
            'status' => 'Dipinjam'
        ]
    ];
}

$admin = [
    'name' => $_SESSION['admin_name'] ?? 'Admin',
    'id' => $_SESSION['admin_id'] ?? '1'
];

// Calculate book statistics
$total_books = count($_SESSION['books']);
$books_borrowed = 0;
$books_available = 0;

foreach ($_SESSION['books'] as $book) {
    if ($book['status'] === 'Dipinjam') {
        $books_borrowed += $book['stok'];
    } else {
        $books_available += $book['stok'];
    }
}

$book_stats = [
    'total_buku' => $total_books,
    'dipinjam' => $books_borrowed,
    'tersedia' => $books_available
];

// Handle form submissions
if (isset($_POST['add_book'])) {
    $new_book = [
        'id' => (string)(count($_SESSION['books']) + 1),
        'judul' => $_POST['judul'],
        'penulis' => $_POST['penulis'],
        'tahun' => $_POST['tahun'],
        'isbn' => $_POST['isbn'],
        'stok' => (int)$_POST['stok'],
        'status' => (int)$_POST['stok'] > 0 ? 'Tersedia' : 'Dipinjam'
    ];
    $_SESSION['books'][] = $new_book;
    $_SESSION['success_message'] = "Buku berhasil ditambahkan!";
    header("Location: kelolaBuku.php");
    exit;
}

if (isset($_POST['edit_book'])) {
    $edit_id = $_POST['edit_id'];
    foreach ($_SESSION['books'] as &$book) {
        if ($book['id'] === $edit_id) {
            $book['judul'] = $_POST['judul'];
            $book['penulis'] = $_POST['penulis'];
            $book['tahun'] = $_POST['tahun'];
            $book['isbn'] = $_POST['isbn'];
            $book['stok'] = (int)$_POST['stok'];
            $book['status'] = (int)$_POST['stok'] > 0 ? 'Tersedia' : 'Dipinjam';
            break;
        }
    }
    $_SESSION['success_message'] = "Buku berhasil diperbarui!";
    header("Location: kelolaBuku.php");
    exit;
}

if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $_SESSION['books'] = array_filter($_SESSION['books'], function($book) use ($delete_id) {
        return $book['id'] !== $delete_id;
    });
    $_SESSION['success_message'] = "Buku berhasil dihapus!";
    header("Location: kelolaBuku.php");
    exit;
}

// Handle search
$search_query = $_GET['search'] ?? '';
$filtered_books = $_SESSION['books'];
if (!empty($search_query)) {
    $filtered_books = array_filter($_SESSION['books'], function($book) use ($search_query) {
        return stripos($book['judul'], $search_query) !== false ||
               stripos($book['penulis'], $search_query) !== false ||
               stripos($book['isbn'], $search_query) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - SiPerpus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-blue-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md">
            <div class="p-4 flex items-center space-x-3 border-b border-gray-200">
                <div class="bg-blue-800 p-2 rounded">
                    <span class="font-bold text-white">SP</span>
                </div>
                <div class="text-sm leading-tight">
                    <div class="font-bold">SiPerpus</div>
                    <div class="text-xs">Sistem Perpustakaan Digital</div>
                </div>
            </div>
            <nav class="mt-4">
                <a href="dashboardAdmin.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-100 hover:text-blue-800">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="kelolaBuku.php" class="flex items-center px-4 py-3 bg-blue-100 text-blue-800 font-medium">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Buku</span>
                </a>
                <a href="kelolaKeterlambatan.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-100 hover:text-blue-800">
                    <i class="fas fa-clock w-6"></i>
                    <span class="ml-2">Keterlambatan</span>
                </a>
                <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-100 hover:text-blue-800">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-2">Anggota</span>
                </a>
                <a href="logout.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-100 hover:text-blue-800 mt-auto">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-2">Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <div class="font-bold text-lg text-gray-800">Kelola Buku</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <form action="kelolaBuku.php" method="GET">
                                <input type="text" name="search" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                    placeholder="Cari buku..." value="<?php echo htmlspecialchars($search_query); ?>">
                                <button type="submit" class="absolute right-2 top-2 text-gray-500 hover:text-blue-700">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="relative">
                            <button class="text-gray-500 hover:text-blue-700">
                                <i class="fas fa-bell"></i>
                            </button>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                                <?php echo substr($admin['name'], 0, 1); ?>
                            </div>
                            <span class="text-sm"><?php echo htmlspecialchars($admin['name']); ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex justify-between items-center">
                        <span><?php echo $_SESSION['success_message']; ?></span>
                        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800">Daftar Buku Perpustakaan</h2>
                    <button onclick="addBook()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-plus mr-2"></i> Tambah Buku
                    </button>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-blue-500">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Buku</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $book_stats['total_buku']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku dalam koleksi</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-orange-500">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Buku Dipinjam</h3>
                        <p class="text-3xl font-bold text-orange-600"><?php echo $book_stats['dipinjam']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku dipinjam anggota</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-green-500">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Buku Tersedia</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo $book_stats['tersedia']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku belum dipinjam</p>
                    </div>
                </div>

                <!-- Books Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between p-4 border-b">
                        <div class="text-sm text-gray-500">
                            Menampilkan <span class="font-medium">1 - <?php echo count($filtered_books); ?></span> dari <span class="font-medium"><?php echo $book_stats['total_buku']; ?></span> buku
                        </div>
                        <select class="bg-gray-100 rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="10">10 per halaman</option>
                            <option value="25">25 per halaman</option>
                            <option value="50">50 per halaman</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">ID</th>
                                    <th class="px-6 py-3">Judul</th>
                                    <th class="px-6 py-3">Penulis</th>
                                    <th class="px-6 py-3">Tahun</th>
                                    <th class="px-6 py-3">ISBN</th>
                                    <th class="px-6 py-3">Stok</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($filtered_books as $book): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['id']); ?></td>
                                        <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($book['judul']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['penulis']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['tahun']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['isbn']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['stok']); ?></td>
                                        <td class="px-6 py-4">
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
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end space-x-2">
                                                <button onclick="viewBook(<?php echo htmlspecialchars(json_encode($book)); ?>)" 
                                                    class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-50" 
                                                    title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="editBook('<?php echo $book['id']; ?>')" 
                                                    class="text-yellow-600 hover:text-yellow-900 p-1 rounded-full hover:bg-yellow-50" 
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="confirmDelete('<?php echo $book['id']; ?>', '<?php echo htmlspecialchars(addslashes($book['judul'])); ?>')" 
                                                    class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-50" 
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($filtered_books)): ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">Tidak ada buku ditemukan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between p-4 border-t">
                        <div class="text-sm text-gray-500">
                            Menampilkan <span class="font-medium">1 - <?php echo count($filtered_books); ?></span> dari <span class="font-medium"><?php echo $book_stats['total_buku']; ?></span> buku
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200 disabled:opacity-50" disabled>
                                <i class="fas fa-chevron-left text-xs"></i>
                            </button>
                            <button class="bg-blue-600 text-white px-3 py-1 rounded-md">1</button>
                            <button class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">2</button>
                            <button class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">3</button>
                            <span class="text-gray-500">...</span>
                            <button class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">15</button>
                            <button class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Tambah Buku Baru</h3>
                <button onclick="closeAddModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addForm" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" id="addJudul" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Penulis <span class="text-red-500">*</span></label>
                    <input type="text" name="penulis" id="addPenulis" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun" id="addTahun" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="1900" max="<?php echo date('Y'); ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ISBN <span class="text-red-500">*</span></label>
                    <input type="text" name="isbn" id="addIsbn" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-500">*</span></label>
                    <input type="number" name="stok" id="addStok" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" required>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">Batal</button>
                    <button type="submit" name="add_book" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Book Modal -->
    <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Buku</h3>
                <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="bookDetails" class="space-y-3 text-sm">
                <!-- Details will be inserted here by JavaScript -->
            </div>
            <div class="flex justify-end mt-6">
                <button onclick="closeViewModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Buku</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" method="POST" class="space-y-4">
                <input type="hidden" name="edit_id" id="editId">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" id="editJudul" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Penulis <span class="text-red-500">*</span></label>
                    <input type="text" name="penulis" id="editPenulis" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun" id="editTahun" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="1900" max="<?php echo date('Y'); ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ISBN <span class="text-red-500">*</span></label>
                    <input type="text" name="isbn" id="editIsbn" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-500">*</span></label>
                    <input type="number" name="stok" id="editStok" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" required>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">Batal</button>
                    <button type="submit" name="edit_book" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-sm">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus buku "<span id="bookTitle" class="font-medium"></span>"? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="delete_id" id="deleteId">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function addBook() {
            document.getElementById('addForm').reset();
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function viewBook(book) {
            const modal = document.getElementById('viewModal');
            const details = document.getElementById('bookDetails');
            
            details.innerHTML = `
                <div class="flex">
                    <strong class="w-24">ID:</strong>
                    <span>${book.id}</span>
                </div>
                <div class="flex">
                    <strong class="w-24">Judul:</strong>
                    <span>${book.judul}</span>
                </div>
                <div class="flex">
                    <strong class="w-24">Penulis:</strong>
                    <span>${book.penulis}</span>
                </div>
                <div class="flex">
                    <strong class="w-24">Tahun:</strong>
                    <span>${book.tahun}</span>
                </div>
                <div class="flex">
                    <strong class="w-24">ISBN:</strong>
                    <span>${book.isbn}</span>
                </div>
                <div class="flex">
                    <strong class="w-24">Stok:</strong>
                    <span>${book.stok}</span>
                </div>
                <div class="flex">
                    <strong class="w-24">Status:</strong>
                    <span class="px-2 py-1 rounded-full text-xs ${book.status === 'Tersedia' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'}">
                        ${book.status}
                    </span>
                </div>
            `;
            
            modal.classList.remove('hidden');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        function editBook(id) {
            const books = <?php echo json_encode($_SESSION['books']); ?>;
            const book = books.find(b => b.id === id);
            
            if (book) {
                document.getElementById('editId').value = book.id;
                document.getElementById('editJudul').value = book.judul;
                document.getElementById('editPenulis').value = book.penulis;
                document.getElementById('editTahun').value = book.tahun;
                document.getElementById('editIsbn').value = book.isbn;
                document.getElementById('editStok').value = book.stok;
                
                document.getElementById('editModal').classList.remove('hidden');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function confirmDelete(id, title) {
            document.getElementById('bookTitle').textContent = title;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Form validation
        document.getElementById('addForm').addEventListener('submit', function(e) {
            const isbn = document.getElementById('addIsbn').value;
            const tahun = document.getElementById('addTahun').value;
            const stok = document.getElementById('addStok').value;

            if (!/^\d{10}(\d{3})?$/.test(isbn.replace(/[^0-9]/g, ''))) {
                alert('ISBN harus berupa 10 atau 13 digit angka');
                e.preventDefault();
                return;
            }

            const currentYear = new Date().getFullYear();
            if (tahun < 1900 || tahun > currentYear) {
                alert(`Tahun harus antara 1900 dan ${currentYear}`);
                e.preventDefault();
                return;
            }

            if (stok < 0) {
                alert('Stok tidak boleh negatif');
                e.preventDefault();
                return;
            }
        });

        document.getElementById('editForm').addEventListener('submit', function(e) {
            const isbn = document.getElementById('editIsbn').value;
            const tahun = document.getElementById('editTahun').value;
            const stok = document.getElementById('editStok').value;

            if (!/^\d{10}(\d{3})?$/.test(isbn.replace(/[^0-9]/g, ''))) {
                alert('ISBN harus berupa 10 atau 13 digit angka');
                e.preventDefault();
                return;
            }

            const currentYear = new Date().getFullYear();
            if (tahun < 1900 || tahun > currentYear) {
                alert(`Tahun harus antara 1900 dan ${currentYear}`);
                e.preventDefault();
                return;
            }

            if (stok < 0) {
                alert('Stok tidak boleh negatif');
                e.preventDefault();
                return;
            }
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === addModal) closeAddModal();
            if (event.target === viewModal) closeViewModal();
            if (event.target === editModal) closeEditModal();
            if (event.target === deleteModal) closeDeleteModal();
        };

        // Auto-hide success message after 5 seconds
        const successMessage = document.querySelector('.bg-green-100');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.remove();
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>