<?php
// Start session for user authentication
session_start();

// Simple authentication check (would be more robust in production)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user information
$user = [
    'name' => $_SESSION['user_name'] ?? 'Anggota',
    'id' => $_SESSION['user_id'] ?? '1'
];

// Mock data for filter options
$categories = ['Semua', 'Fiksi', 'Non-Fiksi', 'Pendidikan', 'Bisnis', 'Pengembangan Diri', 'Filsafat', 'Sejarah', 'Sains', 'Teknologi'];
$languages = ['Semua', 'Indonesia', 'Inggris', 'Arab', 'Jepang', 'Mandarin'];
$statuses = ['Semua', 'Tersedia', 'Dipinjam', 'Dipesan', 'Diperbaiki'];

// Get filter values from request
$category_filter = $_GET['category'] ?? 'Semua';
$language_filter = $_GET['language'] ?? 'Semua';
$status_filter = $_GET['status'] ?? 'Semua';
$search_query = $_GET['search'] ?? '';

// Mock data for books
$books = [
    [
        'id' => 'BK-1001',
        'title' => 'Harry Potter dan Batu Bertuah',
        'author' => 'J.K. Rowling',
        'publisher' => 'Gramedia',
        'year' => '2001',
        'category' => 'Fiksi',
        'language' => 'Indonesia',
        'status' => 'Tersedia',
        'location' => 'Rak A-12',
        'description' => 'Buku pertama dari seri Harry Potter yang menceritakan awal petualangan Harry di Sekolah Sihir Hogwarts.',
        'cover' => 'harry_potter.jpg'
    ],
    [
        'id' => 'BK-1002',
        'title' => 'Laskar Pelangi',
        'author' => 'Andrea Hirata',
        'publisher' => 'Bentang Pustaka',
        'year' => '2005',
        'category' => 'Fiksi',
        'language' => 'Indonesia',
        'status' => 'Dipinjam',
        'location' => 'Rak B-05',
        'description' => 'Novel yang menceritakan kehidupan 10 anak dari keluarga miskin yang bersekolah di sebuah sekolah Muhammadiyah di Belitung.',
        'cover' => 'laskar_pelangi.jpg'
    ],
    [
        'id' => 'BK-1003',
        'title' => 'Bumi Manusia',
        'author' => 'Pramoedya Ananta Toer',
        'publisher' => 'Lentera Dipantara',
        'year' => '1980',
        'category' => 'Fiksi',
        'language' => 'Indonesia',
        'status' => 'Tersedia',
        'location' => 'Rak B-06',
        'description' => 'Novel pertama dari Tetralogi Buru yang menceritakan perjuangan Minke pada masa kolonial Belanda.',
        'cover' => 'bumi_manusia.jpg'
    ],
    [
        'id' => 'BK-1004',
        'title' => 'Filosofi Teras',
        'author' => 'Henry Manampiring',
        'publisher' => 'Kompas',
        'year' => '2018',
        'category' => 'Filsafat',
        'language' => 'Indonesia',
        'status' => 'Tersedia',
        'location' => 'Rak C-09',
        'description' => 'Buku yang membahas filsafat Stoa dan bagaimana menerapkannya dalam kehidupan sehari-hari.',
        'cover' => 'filosofi_teras.jpg'
    ],
    [
        'id' => 'BK-1005',
        'title' => 'Atomic Habits',
        'author' => 'James Clear',
        'publisher' => 'Penguin Random House',
        'year' => '2018',
        'category' => 'Pengembangan Diri',
        'language' => 'Inggris',
        'status' => 'Dipinjam',
        'location' => 'Rak D-02',
        'description' => 'Buku yang membahas tentang bagaimana membangun kebiasaan baik dan menghilangkan kebiasaan buruk.',
        'cover' => 'atomic_habits.jpg'
    ],
    [
        'id' => 'BK-1006',
        'title' => 'Laut Bercerita',
        'author' => 'Leila S. Chudori',
        'publisher' => 'Kepustakaan Populer Gramedia',
        'year' => '2017',
        'category' => 'Fiksi',
        'language' => 'Indonesia',
        'status' => 'Dipinjam',
        'location' => 'Rak B-07',
        'description' => 'Novel yang mengisahkan tentang aktivis mahasiswa yang hilang di masa Orde Baru.',
        'cover' => 'laut_bercerita.jpg'
    ],
    [
        'id' => 'BK-1007',
        'title' => 'Sapiens: Riwayat Singkat Umat Manusia',
        'author' => 'Yuval Noah Harari',
        'publisher' => 'Gramedia',
        'year' => '2017',
        'category' => 'Sejarah',
        'language' => 'Indonesia',
        'status' => 'Tersedia',
        'location' => 'Rak E-03',
        'description' => 'Buku yang membahas sejarah manusia dari munculnya spesies Homo sapiens hingga revolusi kognitif, pertanian, dan teknologi.',
        'cover' => 'sapiens.jpg'
    ],
    [
        'id' => 'BK-1008',
        'title' => 'Matematika Diskrit',
        'author' => 'Rinaldi Munir',
        'publisher' => 'Informatika',
        'year' => '2016',
        'category' => 'Pendidikan',
        'language' => 'Indonesia',
        'status' => 'Tersedia',
        'location' => 'Rak F-01',
        'description' => 'Buku yang membahas konsep-konsep dasar matematika diskrit untuk mahasiswa ilmu komputer.',
        'cover' => 'matematika_diskrit.jpg'
    ],
    [
        'id' => 'BK-1009',
        'title' => 'Python Crash Course',
        'author' => 'Eric Matthes',
        'publisher' => 'No Starch Press',
        'year' => '2019',
        'category' => 'Teknologi',
        'language' => 'Inggris',
        'status' => 'Tersedia',
        'location' => 'Rak G-04',
        'description' => 'Buku panduan pemrograman Python untuk pemula yang ingin mempelajari dasar-dasar pemrograman.',
        'cover' => 'python_crash_course.jpg'
    ],
    [
        'id' => 'BK-1010',
        'title' => 'Rich Dad Poor Dad',
        'author' => 'Robert T. Kiyosaki',
        'publisher' => 'Gramedia',
        'year' => '2016',
        'category' => 'Bisnis',
        'language' => 'Indonesia',
        'status' => 'Dipinjam',
        'location' => 'Rak D-05',
        'description' => 'Buku yang membahas cara berpikir orang kaya dan orang miskin tentang uang dan investasi.',
        'cover' => 'rich_dad_poor_dad.jpg'
    ]
];

// Apply filters if not "Semua"
if ($category_filter != 'Semua') {
    $books = array_filter($books, function($book) use ($category_filter) {
        return $book['category'] == $category_filter;
    });
}

if ($language_filter != 'Semua') {
    $books = array_filter($books, function($book) use ($language_filter) {
        return $book['language'] == $language_filter;
    });
}

if ($status_filter != 'Semua') {
    $books = array_filter($books, function($book) use ($status_filter) {
        return $book['status'] == $status_filter;
    });
}

// Apply search if provided
if (!empty($search_query)) {
    $books = array_filter($books, function($book) use ($search_query) {
        return (stripos($book['title'], $search_query) !== false || 
                stripos($book['author'], $search_query) !== false ||
                stripos($book['id'], $search_query) !== false);
    });
}

// Pagination
$books_per_page = 8;
$total_books = count($books);
$total_pages = ceil($total_books / $books_per_page);
$current_page = isset($_GET['page']) ? max(1, min($total_pages, intval($_GET['page']))) : 1;
$offset = ($current_page - 1) * $books_per_page;
$books = array_slice($books, $offset, $books_per_page);

// Handle form submission for book reservation
$message = '';
$message_type = '';
if (isset($_POST['reserve_book'])) {
    $book_id = $_POST['book_id'] ?? '';
    if (!empty($book_id)) {
        $message = 'Buku berhasil dipesan! Silakan ambil di perpustakaan dalam 24 jam.';
        $message_type = 'success';
    } else {
        $message = 'Gagal memesan buku. Silakan coba lagi.';
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - SiPerpus</title>
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
                <a href="dashboard.php" class="flex items-center px-4 py-3 hover:bg-blue-600 text-gray-800">
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
                <a href="data-buku.php" class="flex items-center px-4 py-3 bg-blue-600 text-white">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Data Buku</span>
                </a>
               
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="font-bold text-lg">Data Buku</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <form action="" method="get">
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

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="flex justify-between items-center mb-6">
                    <div class="text-sm">
                        <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">Dashboard</a> / 
                        <span class="text-gray-600">Data Buku</span>
                    </div>
                </div>

                <h2 class="text-xl font-medium mb-6">Katalog Buku</h2>

                <?php if ($message): ?>
                <div class="mb-6 px-4 py-3 rounded-lg <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex flex-wrap justify-between items-center">
                        <h3 class="text-lg font-medium mb-4">Filter</h3>
                        <a href="data-buku.php" class="text-blue-600 hover:text-blue-800 text-sm">Reset Filter</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select id="category" name="category" class="w-full border border-gray-300 rounded-lg px-4 py-2" onchange="window.location.href='?category='+this.value+'&language=<?php echo $language_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>'">
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $category == $category_filter ? 'selected' : ''; ?>><?php echo htmlspecialchars($category); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-1">Bahasa</label>
                            <select id="language" name="language" class="w-full border border-gray-300 rounded-lg px-4 py-2" onchange="window.location.href='?category=<?php echo $category_filter; ?>&language='+this.value+'&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>'">
                                <?php foreach ($languages as $language): ?>
                                <option value="<?php echo htmlspecialchars($language); ?>" <?php echo $language == $language_filter ? 'selected' : ''; ?>><?php echo htmlspecialchars($language); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2" onchange="window.location.href='?category=<?php echo $category_filter; ?>&language=<?php echo $language_filter; ?>&status='+this.value+'&search=<?php echo urlencode($search_query); ?>'">
                                <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $status == $status_filter ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Book List -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <?php foreach ($books as $book): ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
                        <div class="bg-gray-200 h-48 flex items-center justify-center">
                            <i class="fas fa-book text-gray-400 text-4xl"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-medium text-lg truncate" title="<?php echo htmlspecialchars($book['title']); ?>"><?php echo htmlspecialchars($book['title']); ?></h4>
                            <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($book['author']); ?></p>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full"><?php echo htmlspecialchars($book['category']); ?></span>
                                <span class="text-xs <?php echo $book['status'] == 'Tersedia' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> px-2 py-1 rounded-full"><?php echo htmlspecialchars($book['status']); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <a href="detail-buku.php?id=<?php echo $book['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm">Detail</a>
                                <?php if ($book['status'] == 'Tersedia'): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book['id']); ?>">
                                    <button type="submit" name="reserve_book" class="bg-blue-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-blue-700">Pesan</button>
                                </form>
                                <?php else: ?>
                                <button disabled class="bg-gray-300 text-gray-600 px-3 py-1 rounded-lg text-sm cursor-not-allowed">Pesan</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="flex justify-center mt-6">
                    <div class="flex space-x-1">
                        <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>&category=<?php echo $category_filter; ?>&language=<?php echo $language_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>" class="px-4 py-2 bg-white rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&category=<?php echo $category_filter; ?>&language=<?php echo $language_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>" class="px-4 py-2 <?php echo $i == $current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> rounded-lg border <?php echo $i == $current_page ? 'border-blue-600' : 'border-gray-300'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>&category=<?php echo $category_filter; ?>&language=<?php echo $language_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>" class="px-4 py-2 bg-white rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Selanjutnya</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Halaman Data Buku loaded');
        });
    </script>
</body>
</html>