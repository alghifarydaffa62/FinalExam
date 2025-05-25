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
    'name' => $_SESSION['user_name'] ?? 'Anggota',
    'id' => $_SESSION['user_id'] ?? '1'
];

$statuses = ['Semua', 'Tersedia', 'Dipinjam'];

$status_filter = $_GET['status'] ?? 'Semua';
$search_query = $_GET['search'] ?? '';

function getBookData($conn, $status_filter = 'Semua', $search_query = '') {
    $books = [];

    $sql = "SELECT ID, Judul, Penulis, Tahun, Jumlah_halaman, ISBN, Stok FROM buku WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search_query)) {
        $sql .= " AND (Judul LIKE ? OR Penulis LIKE ? OR ID LIKE ?)";
        $search_param = "%$search_query%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "sss";
    }

    if ($status_filter == 'Tersedia') {
        $sql .= " AND Stok > 0";
    } elseif ($status_filter == 'Dipinjam') {
        $sql .= " AND Stok = 0";
    }
    
    $sql .= " ORDER BY Judul ASC";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $status = ($row['Stok'] > 0) ? 'Tersedia' : 'Dipinjam';
            
            $books[] = [
                'id' => $row['ID'],
                'ID' => $row['ID'],
                'title' => $row['Judul'],
                'Judul' => $row['Judul'],
                'author' => $row['Penulis'],
                'Penulis' => $row['Penulis'],
                'year' => $row['Tahun'],
                'Tahun' => $row['Tahun'],
                'pages' => $row['Jumlah_halaman'],
                'halaman' => $row['Jumlah_halaman'],
                'isbn' => $row['ISBN'],
                'ISBN' => $row['ISBN'],
                'stock' => $row['Stok'],
                'Stok' => $row['Stok'],
                'status' => $status
            ];
        }
    }
    
    return $books;
}

$books = getBookData($conn, $status_filter, $search_query);

$books_per_page = 8;
$total_books = count($books);
$total_pages = ceil($total_books / $books_per_page);
$current_page = isset($_GET['page']) ? max(1, min($total_pages, intval($_GET['page']))) : 1;
$offset = ($current_page - 1) * $books_per_page;
$current_page_books = array_slice($books, $offset, $books_per_page);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - SiPerpus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#FFFAEC]">
    <div class="flex h-screen">
        <div class="w-64 bg-[#DFD0B8] flex-shrink-0">
            <div class="bg-[#DFD0B8] p-4 flex items-center space-x-3 text-black border-b border-[#FFFAEC]">
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
                <a href="pengembalian.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-history w-6"></i>
                    <span class="ml-2">Pengembalian</span>
                </a>
                <a href="data-buku.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
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
                    <div class="font-bold text-lg">Data Buku</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <form action="" method="get">
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                                <input type="text" name="search" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64" placeholder="Cari buku..." value="<?php echo htmlspecialchars($search_query); ?>">
                                <button type="submit" class="absolute right-2 top-2 text-gray-500">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
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
                <div class="flex justify-between items-center mb-6">
                    <div class="text-sm">
                        <a href="dashboard.php" class="text-[#948979] hover:text-[#948979]">Dashboard</a> / 
                        <span class="text-gray-600">Data Buku</span>
                    </div>
                </div>

                <h2 class="text-xl font-medium mb-6">Katalog Buku</h2>

                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Filter</h3>
                        <a href="data-buku.php" class="text-[#DFD0B8] hover:text-[#948979] text-sm">Reset Filter</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" class="w-full md:w-64 border border-gray-300 rounded-lg px-4 py-2" onchange="window.location.href='?status='+this.value+'&search=<?php echo urlencode($search_query); ?>'">
                                <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $status == $status_filter ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <?php if (empty($current_page_books)): ?>
                <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                    <i class="fas fa-book text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-600 mb-2">Tidak ada buku ditemukan</h3>
                    <p class="text-gray-500">Coba ubah filter atau kata kunci pencarian Anda.</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <?php foreach ($current_page_books as $book): ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
                        <div class="bg-gray-200 h-48 flex items-center justify-center">
                            <i class="fas fa-book text-gray-400 text-4xl"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-medium text-lg truncate" title="<?php echo htmlspecialchars($book['title']); ?>"><?php echo htmlspecialchars($book['title']); ?></h4>
                            <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($book['author']); ?></p>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs text-gray-500"><?php echo htmlspecialchars($book['year']); ?></span>
                                <span class="text-xs <?php echo $book['status'] == 'Tersedia' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> px-2 py-1 rounded-full"><?php echo htmlspecialchars($book['status']); ?></span>
                            </div>
                            <div class="flex justify-center">
                                <button onclick="showBookDetail('<?php echo $book['id']; ?>')" class="text-[#948979] hover:text-[#948979] text-sm font-medium">
                                    <i class="fas fa-info-circle mr-1"></i>Detail Buku
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($total_pages > 1): ?>
                <div class="flex justify-center mt-6">
                    <div class="flex space-x-1">
                        <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>" class="px-4 py-2 bg-white rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>" class="px-4 py-2 <?php echo $i == $current_page ? 'bg-[#948979]  text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> rounded-lg border <?php echo $i == $current_page ? 'border-[#948979]' : 'border-[#948979]'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>" class="px-4 py-2 bg-white rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Selanjutnya</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($books)): ?>
                <div class="mt-4 text-sm text-gray-600">
                    Menampilkan <?php echo count($current_page_books); ?> dari <?php echo $total_books; ?> buku
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <div id="bookDetailModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg w-full max-w-2xl max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Detail Buku</h3>
                        <button onclick="closeBookDetail()" class="text-gray-500 hover:text-gray-700 text-xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <div class="bg-gray-200 h-64 rounded-lg flex items-center justify-center">
                                <i class="fas fa-book text-gray-400 text-6xl"></i>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <div class="space-y-4">
                                <div>
                                    <h4 id="modal-title" class="text-2xl font-bold text-gray-800 mb-2"></h4>
                                    <p id="modal-author" class="text-lg text-gray-600"></p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">ID Buku</span>
                                        <p id="modal-id" class="text-gray-800"></p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">ISBN</span>
                                        <p id="modal-isbn" class="text-gray-800"></p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Tahun Terbit</span>
                                        <p id="modal-year" class="text-gray-800"></p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Jumlah Halaman</span>
                                        <p id="modal-pages" class="text-gray-800"></p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Stok</span>
                                        <p id="modal-stok" class="text-gray-800"></p>
                                    </div>
                                </div>
                                
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Status</span>
                                    <p id="modal-status" class="inline-block px-3 py-1 text-sm rounded-full mt-1"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
                        <button onclick="closeBookDetail()" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const booksData = <?php echo json_encode($books); ?>;
        
        function showBookDetail(bookId) {
            const book = booksData.find(b => b.id === bookId);
            
            if (book) {
                document.getElementById('modal-title').textContent = book.title;
                document.getElementById('modal-author').textContent = book.author;
                document.getElementById('modal-id').textContent = book.id;
                document.getElementById('modal-isbn').textContent = book.isbn;
                document.getElementById('modal-year').textContent = book.year;
                document.getElementById('modal-pages').textContent = book.pages + ' halaman';
                document.getElementById('modal-stok').textContent = book.stock;
                
                const statusElement = document.getElementById('modal-status');
                statusElement.textContent = book.status;
                if (book.status === 'Tersedia') {
                    statusElement.className = 'inline-block px-3 py-1 text-sm rounded-full mt-1 bg-green-100 text-green-800';
                } else {
                    statusElement.className = 'inline-block px-3 py-1 text-sm rounded-full mt-1 bg-yellow-100 text-yellow-800';
                }
                
                document.getElementById('bookDetailModal').classList.remove('hidden');
            }
        }
        
        function closeBookDetail() {
            document.getElementById('bookDetailModal').classList.add('hidden');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Halaman Data Buku loaded - Total buku:', booksData.length);
            
            document.getElementById('bookDetailModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeBookDetail();
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeBookDetail();
                }
            });
        });
    </script>
</body>
</html>