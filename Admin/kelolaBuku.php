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

$book_stats = [
    'total_buku' => $_SESSION['total_books'] ?? 150,
    'dipinjam' => $_SESSION['books_borrowed'] ?? 45,
    'tersedia' => ($_SESSION['total_books'] ?? 150) - ($_SESSION['books_borrowed'] ?? 45)
];

$books = [
    
];

if (isset($_POST['add_book'])) {
    $new_book = [
        'id' => (string)(count($books) + 1), 
        'judul' => $_POST['judul'],
        'penulis' => $_POST['penulis'],
        'tahun' => $_POST['tahun'],
        'isbn' => $_POST['isbn'],
        'stok' => (int)$_POST['stok'],
        'status' => (int)$_POST['stok'] > 0 ? 'Tersedia' : 'Dipinjam'
    ];
    $books[] = $new_book;
    $_SESSION['success_message'] = "Buku berhasil ditambahkan!";
    header("Location: kelolaBuku.php");
    exit;
}

if (isset($_POST['edit_book'])) {
    $edit_id = $_POST['edit_id'];
    foreach ($books as &$book) {
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
    $books = array_filter($books, function($book) use ($delete_id) {
        return $book['id'] !== $delete_id;
    });
    $_SESSION['success_message'] = "Buku berhasil dihapus!";
    header("Location: kelolaBuku.php");
    exit;
}

$search_query = $_GET['search'] ?? '';
if (!empty($search_query)) {
    $books = array_filter($books, function($book) use ($search_query) {
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
                <a href="dashboardAdmin.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="kelolaBuku.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Buku</span>
                </a>
                <a href="kelolaKeterlambatan.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-clock w-6"></i>
                    <span class="ml-2">Keterlambatan</span>
                </a>
                <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-2">Anggota</span>
                </a>
                    <a href="loginAdmin.php" class="flex items-center px-3 py-3 hover:bg-[#948979] text-black mt-60">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-2">Logout</span>
                 </a>

                </a>
            </nav>
        </div>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-[#DFD0B8] shadow-sm z-10">
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

            <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAEC]">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-medium">Daftar Buku Perpustakaan</h2>
                    <button onclick="addBook()" class="bg-[#393E46] text-white px-4 py-2 rounded-lg hover:bg-[#948979] flex items-center">
                        <i class="fas fa-plus mr-2"></i> Tambah Buku
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Buku</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $book_stats['total_buku']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku dalam koleksi</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Buku Dipinjam</h3>
                        <p class="text-3xl font-bold text-orange-600"><?php echo $book_stats['dipinjam']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku dipinjam anggota</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Buku Tersedia</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo $book_stats['tersedia']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku belum dipinjam</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-end mb-6">
                        <select class="bg-gray-100 rounded px-3 py-1 text-sm">
                            <option value="10">10 per halaman</option>
                            <option value="25">25 per halaman</option>
                            <option value="50">50 per halaman</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-lg">ID</th>
                                    <th class="px-4 py-3">Judul</th>
                                    <th class="px-4 py-3">Penulis</th>
                                    <th class="px-4 py-3">Tahun</th>
                                    <th class="px-4 py-3">ISBN</th>
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
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($book['tahun']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($book['isbn']); ?></td>
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
                                                <button onclick="viewBook(<?php echo htmlspecialchars(json_encode($book)); ?>)" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="editBook(<?php echo $book['id']; ?>)" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="confirmDelete(<?php echo $book['id']; ?>, '<?php echo htmlspecialchars($book['judul']); ?>')" class="text-red-600 hover:text-red-900" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <div class="text-sm text-gray-500">
                            Menampilkan 1 - <?php echo count($books); ?> dari <?php echo $book_stats['total_buku']; ?> buku
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
            </main>
        </div>
    </div>

    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto max-h-96 overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Tambah Buku Baru</h3>
                <button onclick="closeAddModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addForm" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" id="addJudul" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Penulis <span class="text-red-500">*</span></label>
                    <input type="text" name="penulis" id="addPenulis" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun" id="addTahun" class="w-full px-3 py-2 border border-gray-300 rounded-md" min="1900" max="2024" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ISBN <span class="text-red-500">*</span></label>
                    <input type="text" name="isbn" id="addIsbn" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-500">*</span></label>
                    <input type="number" name="stok" id="addStok" class="w-full px-3 py-2 border border-gray-300 rounded-md" min="0" required>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg">Batal</button>
                    <button type="submit" name="add_book" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Detail Buku</h3>
                <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="bookDetails" class="space-y-3">

            </div>
            <div class="flex justify-end mt-6">
                <button onclick="closeViewModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg">Tutup</button>
            </div>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto max-h-96 overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Edit Buku</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" method="POST" class="space-y-4">
                <input type="hidden" name="edit_id" id="editId">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" id="editJudul" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Penulis <span class="text-red-500">*</span></label>
                    <input type="text" name="penulis" id="editPenulis" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun" id="editTahun" class="w-full px-3 py-2 border border-gray-300 rounded-md" min="1900" max="2024" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ISBN <span class="text-red-500">*</span></label>
                    <input type="text" name="isbn" id="editIsbn" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-500">*</span></label>
                    <input type="number" name="stok" id="editStok" class="w-full px-3 py-2 border border-gray-300 rounded-md" min="0" required>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg">Batal</button>
                    <button type="submit" name="edit_book" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-auto">
            <h3 class="text-lg font-medium mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus buku "<span id="bookTitle"></span>"? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="delete_id" id="deleteId">
                    <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-lg">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentEditId = null;

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
                <div><strong>ID:</strong> ${book.id}</div>
                <div><strong>Judul:</strong> ${book.judul}</div>
                <div><strong>Penulis:</strong> ${book.penulis}</div>
                <div><strong>Tahun:</strong> ${book.tahun}</div>
                <div><strong>ISBN:</strong> ${book.isbn}</div>
                <div><strong>Stok:</strong> ${book.stok}</div>
                <div><strong>Status:</strong> <span class="px-2 py-1 rounded-full text-xs ${book.status === 'Tersedia' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'}">${book.status}</span></div>
            `;
            
            modal.classList.remove('hidden');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        function editBook(id) {
            currentEditId = id;
            const books = <?php echo json_encode($books); ?>;
            const book = books.find(b => b.id === id.toString());
            
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
            currentEditId = null;
        }

        function confirmDelete(id, title) {
            document.getElementById('bookTitle').textContent = title;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

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
                alert('Tahun harus antara 1900 dan ' + currentYear);
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
                alert('Tahun harus antara 1900 dan ' + currentYear);
                e.preventDefault();
                return;
            }

            if (stok < 0) {
                alert('Stok tidak boleh negatif');
                e.preventDefault();
                return;
            }
        });

        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === addModal) {
                closeAddModal();
            } else if (event.target === viewModal) {
                closeViewModal();
            } else if (event.target === editModal) {
                closeEditModal();
            } else if (event.target === deleteModal) {
                closeDeleteModal();
            }
        };

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