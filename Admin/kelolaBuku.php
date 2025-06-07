<?php
session_start();
include '../konek.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: loginAdmin.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();

    if (isset($_COOKIE['admin_remember'])) {
        setcookie('admin_remember', '', time() - 3600, '/');
    }

    header("Location: loginAdmin.php");
    exit;
}

$admin = [
    'name' => $_SESSION['admin_name'] ?? 'Admin',
    'id' => $_SESSION['admin_id'] ?? '1'
];

function getBookStats($conn)
{
    $stats = [
        'total_buku' => 0,
        'dipinjam' => 0,
        'tersedia' => 0,
        'dikembalikan' => 0
    ];

    $result = $conn->query("SELECT COUNT(*) as total FROM buku");
    if ($result) {
        $stats['total_buku'] = $result->fetch_assoc()['total'];
    }

    $dikembalikan = $conn->query("SELECT COUNT(*) as totalPengembalian FROM peminjaman WHERE status_peminjaman = 'dikembalikan'");
    if ($dikembalikan) {
        $stats['totalPengembalian'] = $dikembalikan->fetch_assoc()['totalPengembalian'];
    }

    $result = $conn->query("SELECT SUM(Stok) as tersedia FROM buku WHERE Stok > 0");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['tersedia'] = $row['tersedia'] ?? 0;
    }

    // Untuk admin, kita perlu total peminjaman global, bukan per NRP
    $peminjaman = $conn->query("SELECT COUNT(*) as totalPeminjaman FROM peminjaman WHERE status_peminjaman = 'dipinjam'");
    if ($peminjaman) {
        $stats['totalPeminjaman'] = $peminjaman->fetch_assoc()['totalPeminjaman'];
    }

    return $stats;
}

function uploadCover($file) {
    $target_dir = "../uploads/covers/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;

    $allowed_types = array("jpg", "jpeg", "png", "gif");
    if (!in_array($imageFileType, $allowed_types)) {
        return array('success' => false, 'message' => 'Hanya file JPG, JPEG, PNG & GIF yang diizinkan.');
    }

    if ($file["size"] > 5000000) {
        return array('success' => false, 'message' => 'File terlalu besar. Maksimal 5MB.');
    }

    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return array('success' => false, 'message' => 'File bukan gambar yang valid.');
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return array('success' => true, 'filename' => $new_filename);
    } else {
        return array('success' => false, 'message' => 'Gagal mengupload file.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_book'])) {
        $id_buku = trim($_POST['id_buku']);
        $judul = trim($_POST['judul']);
        $penulis = trim($_POST['penulis']);
        $tahun = (int) $_POST['tahun'];
        $isbn = trim($_POST['isbn']);
        $halaman = (int) $_POST['halaman'];
        $stok = (int) $_POST['stok'];
        $cover = null;

        // Handle cover upload
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
            $upload_result = uploadCover($_FILES['cover']);
            if ($upload_result['success']) {
                $cover = $upload_result['filename'];
            } else {
                $_SESSION['error_message'] = $upload_result['message'];
                header("Location: kelolaBuku.php");
                exit;
            }
        }

        $check_stmt = $conn->prepare("SELECT ID FROM buku WHERE ID = ?");
        $check_stmt->bind_param("s", $id_buku);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error_message'] = "ID Buku sudah ada! Gunakan ID yang berbeda.";
        } else {
            $stmt = $conn->prepare("INSERT INTO buku (ID, Judul, Penulis, Tahun, Jumlah_halaman, ISBN, Stok, Cover) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssisiss", $id_buku, $judul, $penulis, $tahun, $halaman, $isbn, $stok, $cover);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Buku berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan buku: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();

        header("Location: kelolaBuku.php");
        exit;
    }

    if (isset($_POST['edit_book'])) {
        $edit_id = $_POST['edit_id'];
        $judul = trim($_POST['judul']);
        $penulis = trim($_POST['penulis']);
        $tahun = (int) $_POST['tahun'];
        $isbn = trim($_POST['isbn']);
        $halaman = (int) $_POST['halaman'];
        $stok = (int) $_POST['stok'];
        $cover = $_POST['current_cover'] ?? null;

        // Handle cover upload
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
            $upload_result = uploadCover($_FILES['cover']);
            if ($upload_result['success']) {
                // Hapus cover lama jika ada
                if ($cover && file_exists("../uploads/covers/" . $cover)) { // Pastikan path benar
                    unlink("../uploads/covers/" . $cover);
                }
                $cover = $upload_result['filename'];
            } else {
                $_SESSION['error_message'] = $upload_result['message'];
                header("Location: kelolaBuku.php");
                exit;
            }
        }

        $stmt = $conn->prepare("UPDATE buku SET Judul = ?, Penulis = ?, Tahun = ?, Jumlah_halaman = ?, ISBN = ?, Stok = ?, Cover = ? WHERE ID = ?");
        $stmt->bind_param("ssiisiss", $judul, $penulis, $tahun, $halaman, $isbn, $stok, $cover, $edit_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Buku berhasil diperbarui!";
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui buku: " . $conn->error;
        }
        $stmt->close();

        header("Location: kelolaBuku.php");
        exit;
    }

    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        // Ambil nama file cover sebelum menghapus record dari database
        $get_cover_stmt = $conn->prepare("SELECT Cover FROM buku WHERE ID = ?");
        $get_cover_stmt->bind_param("s", $delete_id);
        $get_cover_stmt->execute();
        $cover_result = $get_cover_stmt->get_result();
        $cover_filename = null;
        if ($row = $cover_result->fetch_assoc()) {
            $cover_filename = $row['Cover'];
        }
        $get_cover_stmt->close();

        $stmt = $conn->prepare("DELETE FROM buku WHERE ID = ?");
        $stmt->bind_param("s", $delete_id);

        if ($stmt->execute()) {
            // Hapus file cover dari server jika ada
            if ($cover_filename && file_exists("../uploads/covers/" . $cover_filename)) { // Pastikan path benar
                unlink("../uploads/covers/" . $cover_filename);
            }
            $_SESSION['success_message'] = "Buku berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus buku: " . $conn->error;
        }
        $stmt->close();

        header("Location: kelolaBuku.php");
        exit;
    }
}

$book_stats = getBookStats($conn);

$search_query = $_GET['search'] ?? '';
$where_clause = "";
$params = [];
$types = "";

if (!empty($search_query)) {
    $where_clause = "WHERE Judul LIKE ? OR Penulis LIKE ? OR ISBN LIKE ? OR ID LIKE ?";
    $search_param = "%" . $search_query . "%";
    $params = [$search_param, $search_param, $search_param, $search_param];
    $types = "ssss";
}

$buku_per_halaman = 8;
$halaman_saat_ini = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($halaman_saat_ini - 1) * $buku_per_halaman;

// Get total books for pagination considering search
$total_buku_sql = "SELECT COUNT(*) as total FROM buku $where_clause";
$total_buku_stmt = $conn->prepare($total_buku_sql);
if (!empty($params)) {
    $total_buku_stmt->bind_param($types, ...$params);
}
$total_buku_stmt->execute();
$total_buku_result = $total_buku_stmt->get_result();
$total_buku = $total_buku_result->fetch_assoc()['total'];
$total_buku_stmt->close();

$total_halaman = ceil($total_buku / $buku_per_halaman);

$sql = "SELECT * FROM buku $where_clause ORDER BY Judul ASC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $types .= "ii"; // Add types for LIMIT and OFFSET
    $params[] = $buku_per_halaman;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $buku_per_halaman, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$books = [];

while ($row = $result->fetch_assoc()) {
    $books[] = [
        'id' => $row['ID'],
        'judul' => $row['Judul'],
        'penulis' => $row['Penulis'],
        'tahun' => $row['Tahun'],
        'isbn' => $row['ISBN'],
        'halaman' => $row['Jumlah_halaman'],
        'stok' => $row['Stok'],
        'cover' => $row['Cover'],
        'status' => $row['Stok'] > 0 ? 'Tersedia' : 'Habis'
    ];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - SiPerpus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom CSS untuk transisi sidebar */
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }

        @media (max-width: 767px) {
            .sidebar-hidden {
                transform: translateX(-100%);
            }
            .sidebar-visible {
                transform: translateX(0);
            }
            .overlay {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
        }
    </style>
</head>

<body class="bg-[#FFFAEC] flex h-screen">

    <div id="sidebarOverlay" class="hidden overlay md:hidden" onclick="toggleSidebar()"></div>

    <div id="sidebar"
        class="w-64 bg-[#DFD0B8] shadow-md flex-shrink-0 sidebar-transition md:relative absolute inset-y-0 left-0 z-50 md:translate-x-0 sidebar-hidden">
        <div class="p-4 flex items-center space-x-3 border-b border-[#FFFAEC]">
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
            <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                <i class="fas fa-users w-6"></i>
                <span class="ml-2">Anggota</span>
            </a>
            <a href="daftarPeminjaman.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                <i class="fas fa-book-reader w-6"></i>
                <span class="ml-2">Daftar Peminjaman</span>
            </a>
            <a href="?logout=1" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black mt-auto">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span class="ml-2">Logout</span>
            </a>
        </nav>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-[#DFD0B8] shadow-sm flex justify-between items-center p-4">
            <div class="flex items-center">
                <button class="text-gray-800 md:hidden mr-4" onclick="toggleSidebar()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="font-bold text-lg text-gray-800">Kelola Buku</div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button class="text-gray-500 hover:text-blue-700">
                        <i class="fas fa-bell"></i>
                    </button>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 lg:w-8 lg:h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500 text-xs lg:text-sm"></i>
                            </div>
                            <div class="text-xs lg:text-sm">
                                <div class="font-medium"><?php echo htmlspecialchars($admin['name']); ?></div>
                                <div class="text-gray-500 text-xs">Admin</div>
                            </div>
                        </div>
                </div>
            </div>
        </header>
    
        <main class="flex-1 overflow-y-auto p-6">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex justify-between items-center transition-opacity duration-300">
                    <span><?php echo $_SESSION['success_message']; ?></span>
                    <button onclick="closeAlert(this.parentElement)" class="text-green-700 hover:text-green-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex justify-between items-center transition-opacity duration-300">
                    <span><?php echo $_SESSION['error_message']; ?></span>
                    <button onclick="closeAlert(this.parentElement)" class="text-red-700 hover:text-red-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 md:mb-0">Daftar Buku Perpustakaan</h2>
                <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4 w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <form action="kelolaBuku.php" method="GET" class="w-full">
                            <input type="text" name="search"
                                class="w-full bg-gray-100 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Cari buku..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" class="absolute right-2 top-2 text-gray-500 hover:text-blue-700">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <button onclick="addBook()" class="bg-[#393E46] hover:bg-[#4a4f57] text-white px-4 py-2 rounded-lg flex items-center transition duration-200 w-full md:w-auto justify-center">
                        <i class="fas fa-plus mr-2"></i> Tambah Buku
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div
                    class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-blue-500">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Total Buku</h3>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $book_stats['total_buku']; ?></p>
                    <p class="text-sm text-gray-500 mt-1">Buku dalam koleksi</p>
                </div>
                <div
                    class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-orange-500">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Total Buku Dipinjam</h3>
                    <p class="text-3xl font-bold text-orange-600"><?php echo $book_stats['totalPeminjaman']; ?></p>
                    <p class="text-sm text-gray-500 mt-1">Buku dipinjam anggota</p>
                </div>
                <div
                    class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-green-500">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Buku Tersedia</h3>
                    <p class="text-3xl font-bold text-green-600"><?php echo $book_stats['tersedia']; ?></p>
                    <p class="text-sm text-gray-500 mt-1">Buku belum dipinjam</p>
                </div>
                <div
                    class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-blue-900">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Buku Dikembalikan</h3>
                    <p class="text-3xl font-bold text-blue-900"><?php echo $book_stats['totalPengembalian']; ?></p>
                    <p class="text-sm text-gray-500 mt-1">Pengembalian Buku</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b">
                    <div class="text-sm text-gray-500">
                        Menampilkan <span class="font-medium"><?php echo ($offset + 1) . ' - ' . min($offset + $buku_per_halaman, $total_buku); ?></span> dari <span
                            class="font-medium"><?php echo $total_buku; ?></span> buku
                    </div>
                    <select
                        class="bg-gray-100 rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hidden md:block">
                        <option value="8">8 per halaman</option>
                        <option value="10">10 per halaman</option>
                        <option value="15">15 per halaman</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">ID Buku</th>
                                <th class="px-6 py-3">Judul</th>
                                <th class="px-6 py-3">Penulis</th>
                                <th class="px-6 py-3">Tahun</th>
                                <th class="px-6 py-3">ISBN</th>
                                <th class="px-6 py-3">Jumlah Halaman</th>
                                <th class="px-6 py-3">Stok</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($books as $book): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($book['id']); ?></td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        <?php echo htmlspecialchars($book['judul']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($book['penulis']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($book['tahun']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($book['isbn']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($book['halaman']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($book['stok']); ?></td>
                                    <td class="px-6 py-4">
                                        <?php if ($book['status'] === 'Tersedia'): ?>
                                            <span class="bg-green-200 text-green-800 text-xs px-2 py-1 rounded-full">
                                                Tersedia
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                                Habis
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end space-x-2">
                                            <button
                                                onclick="viewBook(<?php echo htmlspecialchars(json_encode($book)); ?>)"
                                                class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-50"
                                                title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="editBook('<?php echo $book['id']; ?>')"
                                                class="text-yellow-600 hover:text-yellow-900 p-1 rounded-full hover:bg-yellow-50"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                onclick="confirmDelete('<?php echo $book['id']; ?>', '<?php echo htmlspecialchars(addslashes($book['judul'])); ?>')"
                                                class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-50"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($books)): ?>
                                <tr>
                                    <td colspan="10" class="px-6 py-4 text-center text-gray-500">Tidak ada buku ditemukan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between p-4 border-t">
                    <div class="text-sm text-gray-500">
                        Menampilkan <span class="font-medium"><?php echo ($offset + 1) . ' - ' . min($offset + $buku_per_halaman, $total_buku); ?></span> dari <span class="font-medium"><?php echo $total_buku; ?></span> buku
                    </div>
                    <div class="flex items-center space-x-2">
                        <?php if ($halaman_saat_ini > 1): ?>
                            <a href="?page=<?php echo $halaman_saat_ini - 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                        <?php else: ?>
                            <button class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md disabled:opacity-50" disabled>
                                <i class="fas fa-chevron-left text-xs"></i>
                            </button>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200 <?php echo ($i == $halaman_saat_ini) ? 'bg-[#393E46] text-white' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>

                        <?php if ($halaman_saat_ini < $total_halaman): ?>
                            <a href="?page=<?php echo $halaman_saat_ini + 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        <?php else: ?>
                            <button class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md disabled:opacity-50" disabled>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Tambah Buku Baru</h3>
                <button onclick="closeAddModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addForm" method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID Buku <span class="text-red-500">*</span></label>
                        <input type="text" name="id_buku" id="id_buku"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                        <input type="text" name="judul" id="addJudul"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penulis <span class="text-red-500">*</span></label>
                        <input type="text" name="penulis" id="addPenulis"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                        <input type="number" name="tahun" id="addTahun"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            min="1900" max="<?php echo date('Y'); ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ISBN <span class="text-red-500">*</span></label>
                        <input type="text" name="isbn" id="addIsbn"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Halaman <span class="text-red-500">*</span></label>
                        <input type="number" name="halaman" id="halaman"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            min="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-500">*</span></label>
                        <input type="number" name="stok" id="addStok"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            min="0" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cover Buku</label>
                        <input type="file" name="cover" id="addCover" accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG, GIF (Maks: 5MB)</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeAddModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">Batal</button>
                    <button type="submit" name="add_book"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-[999] flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Buku</h3>
                <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="bookDetails" class="space-y-3 text-sm">
            </div>
            <div class="flex justify-end mt-6">
                <button onclick="closeViewModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">Tutup</button>
            </div>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Buku</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="editId">
                <input type="hidden" name="current_cover" id="editCurrentCover">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID Buku <span class="text-red-500">*</span></label>
                        <input type="text" id="editIdBuku"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                        <input type="text" name="judul" id="editJudul"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penulis <span class="text-red-500">*</span></label>
                        <input type="text" name="penulis" id="editPenulis"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                        <input type="number" name="tahun" id="editTahun"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            min="1900" max="<?php echo date('Y'); ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ISBN <span class="text-red-500">*</span></label>
                        <input type="text" name="isbn" id="editIsbn"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Halaman <span class="text-red-500">*</span></label>
                        <input type="number" name="halaman" id="editHalaman"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            min="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-500">*</span></label>
                        <input type="number" name="stok" id="editStok"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            min="0" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cover Buku</label>
                        <div id="currentCoverPreview" class="mb-2"></div>
                        <input type="file" name="cover" id="editCover" accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah cover</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">Batal</button>
                    <button type="submit" name="edit_book"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-[999] flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-sm">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus buku "<span id="bookTitle"
                        class="font-medium"></span>"? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="delete_id" id="deleteId">
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const booksData = <?php echo json_encode($books); ?>;

        // Fungsi untuk generate ID Buku (gunakan format seperti "BK-001", "BK-002", dst.)
        function generateBookId() {
            // Logika untuk menghasilkan ID buku yang unik.
            // Anda bisa mengambil ID terakhir dari database dan menambahkannya,
            // atau menggunakan kombinasi tanggal dan waktu.
            // Untuk contoh ini, saya akan menggunakan timestamp sederhana.
            const timestamp = Date.now().toString().slice(-6); // Ambil 6 digit terakhir dari timestamp
            return `BK-${timestamp}`;
        }

        function addBook() {
            document.getElementById('addForm').reset();
            // Otomatis mengisi ID Buku saat modal tambah dibuka
            document.getElementById('id_buku').value = generateBookId(); 
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function viewBook(book) {
            const modal = document.getElementById('viewModal');
            const details = document.getElementById('bookDetails');
            
            let coverHtml = '';
            if (book.cover) {
                coverHtml = `
                    <div class="flex flex-col items-center mb-4">
                        <img src="../uploads/covers/${book.cover}" alt="Cover Buku" class="w-32 h-40 object-cover rounded shadow-md">
                        <span class="mt-2 text-sm text-gray-600">Cover Buku</span>
                    </div>
                `;
            } else {
                coverHtml = `<div class="text-center text-gray-500 mb-4">Tidak ada cover</div>`;
            }

            details.innerHTML = `
                ${coverHtml}
                <div class="grid grid-cols-2 gap-2">
                    <strong class="text-gray-700">ID Buku:</strong> <span>${book.id}</span>
                    <strong class="text-gray-700">Judul:</strong> <span>${book.judul}</span>
                    <strong class="text-gray-700">Penulis:</strong> <span>${book.penulis}</span>
                    <strong class="text-gray-700">Tahun:</strong> <span>${book.tahun}</span>
                    <strong class="text-gray-700">ISBN:</strong> <span>${book.isbn}</span>
                    <strong class="text-gray-700">Halaman:</strong> <span>${book.halaman}</span>
                    <strong class="text-gray-700">Stok:</strong> <span>${book.stok}</span>
                    <strong class="text-gray-700">Status:</strong>
                    <span>
                        <span class="px-2 py-1 rounded-full text-xs ${book.status === 'Tersedia' ? 'bg-green-100 text-green-800' : 'bg-red-500 text-white'}">
                            ${book.status}
                        </span>
                    </span>
                </div>
            `;

            modal.classList.remove('hidden');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        function editBook(id) {
            const book = booksData.find(b => b.id === id);

            if (book) {
                document.getElementById('editId').value = book.id;
                document.getElementById('editCurrentCover').value = book.cover || '';
                document.getElementById('editIdBuku').value = book.id;
                document.getElementById('editJudul').value = book.judul;
                document.getElementById('editPenulis').value = book.penulis;
                document.getElementById('editTahun').value = book.tahun;
                document.getElementById('editIsbn').value = book.isbn;
                document.getElementById('editHalaman').value = book.halaman;
                document.getElementById('editStok').value = book.stok;
                
                const previewDiv = document.getElementById('currentCoverPreview');
                if (book.cover) {
                    previewDiv.innerHTML = `
                        <div class="flex items-center space-x-2">
                            <img src="../uploads/covers/${book.cover}" alt="Current Cover" class="w-16 h-20 object-cover rounded">
                            <span class="text-sm text-gray-600">Cover saat ini</span>
                        </div>
                    `;
                } else {
                    previewDiv.innerHTML = '<span class="text-sm text-gray-500">Tidak ada cover</span>';
                }

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

        document.getElementById('addForm').addEventListener('submit', function (e) {
            const idBuku = document.getElementById('id_buku').value;
            const judul = document.getElementById('addJudul').value;
            const penulis = document.getElementById('addPenulis').value;
            const tahun = parseInt(document.getElementById('addTahun').value);
            const isbn = document.getElementById('addIsbn').value.replace(/[^0-9]/g, ''); 
            const halaman = parseInt(document.getElementById('halaman').value);
            const stok = parseInt(document.getElementById('addStok').value);

            if (!idBuku || !judul || !penulis || !isbn || !halaman || typeof stok !== 'number') {
                alert('Semua kolom bertanda * harus diisi.');
                e.preventDefault();
                return;
            }

            if (!/^\d{10}(\d{3})?$/.test(isbn)) {
                alert('ISBN harus berupa 10 atau 13 digit angka.');
                e.preventDefault();
                return;
            }

            const currentYear = new Date().getFullYear();
            if (tahun < 1900 || tahun > currentYear) {
                alert(`Tahun harus antara 1900 dan ${currentYear}.`);
                e.preventDefault();
                return;
            }
            if (halaman < 1) {
                alert('Jumlah halaman minimal 1.');
                e.preventDefault();
                return;
            }
            if (stok < 0) {
                alert('Stok tidak boleh negatif.');
                e.preventDefault();
                return;
            }
        });

        document.getElementById('editForm')?.addEventListener('submit', function (e) {
            const judul = document.getElementById('editJudul').value;
            const penulis = document.getElementById('editPenulis').value;
            const tahun = parseInt(document.getElementById('editTahun').value);
            const isbn = document.getElementById('editIsbn').value.replace(/[^0-9]/g, ''); // Hapus non-digit
            const halaman = parseInt(document.getElementById('editHalaman').value);
            const stok = parseInt(document.getElementById('editStok').value);

            if (!judul || !penulis || !isbn || !halaman || typeof stok !== 'number') {
                alert('Semua kolom bertanda * harus diisi.');
                e.preventDefault();
                return;
            }

            if (!/^\d{10}(\d{3})?$/.test(isbn)) {
                alert('ISBN harus berupa 10 atau 13 digit angka.');
                e.preventDefault();
                return;
            }

            const currentYear = new Date().getFullYear();
            if (tahun < 1900 || tahun > currentYear) {
                alert(`Tahun harus antara 1900 dan ${currentYear}.`);
                e.preventDefault();
                return;
            }
            if (halaman < 1) {
                alert('Jumlah halaman minimal 1.');
                e.preventDefault();
                return;
            }
            if (stok < 0) {
                alert('Stok tidak boleh negatif.');
                e.preventDefault();
                return;
            }
        });

        window.onclick = function (event) {
            const addModal = document.getElementById('addModal');
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            if (event.target === addModal) closeAddModal();
            if (event.target === viewModal) closeViewModal();
            if (event.target === editModal) closeEditModal();
            if (event.target === deleteModal) closeDeleteModal();
            // Tutup sidebar jika overlay diklik (hanya di mobile)
            if (event.target === sidebarOverlay) toggleSidebar(); 
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

        const errorMessage = document.querySelector('.bg-red-100');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.opacity = '0';
                setTimeout(() => {
                    errorMessage.remove();
                }, 300);
            }, 5000);
        }

        function closeAlert(element) {
            element.style.opacity = '0';
            setTimeout(() => {
                element.remove();
            }, 300);
        }
        
        document.querySelectorAll('[title]').forEach(element => {
            element.addEventListener('mouseenter', function () {
                this.setAttribute('data-tooltip', this.getAttribute('title'));
                this.removeAttribute('title');
            });
            element.addEventListener('mouseleave', function () {
                this.setAttribute('title', this.getAttribute('data-tooltip'));
                this.removeAttribute('data-tooltip');
            });
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('sidebar-hidden');
            sidebar.classList.toggle('sidebar-visible');
            sidebarOverlay.classList.toggle('hidden');
        }

        let formChanged = false;
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('input', () => {
                formChanged = true;
            });

            form.addEventListener('submit', () => {
                formChanged = false;
            });
        });

        window.addEventListener('beforeunload', function (e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const searchQuery = urlParams.get('search');
            if (searchQuery) {
                document.querySelector('input[name="search"]').value = searchQuery;
            }
        };

    </script>
</body>
</html>