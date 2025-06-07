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
        'dipinjam' => 0, // Ini adalah jumlah buku yang sedang dipinjam (totalPeminjaman)
        'tersedia' => 0,
        'dikembalikan' => 0 // Ini adalah total riwayat pengembalian (totalPengembalian)
    ];

    $result = $conn->query("SELECT COUNT(*) as total FROM buku");
    if ($result) {
        $stats['total_buku'] = $result->fetch_assoc()['total'];
    }

    // Perbaikan: totalPengembalian mengacu pada berapa kali buku dikembalikan, bukan jumlah buku yang sedang dikembalikan
    $dikembalikan_query = $conn->query("SELECT COUNT(*) as totalPengembalian FROM peminjaman WHERE status_peminjaman = 'dikembalikan'");
    if ($dikembalikan_query) {
        $stats['dikembalikan'] = $dikembalikan_query->fetch_assoc()['totalPengembalian'];
    }

    $result = $conn->query("SELECT SUM(Stok) as total_tersedia FROM buku");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['tersedia'] = $row['total_tersedia'] ?? 0;
    }

    // Ambil total buku yang sedang dipinjam (status 'dipinjam')
    // Untuk admin, ini harus total semua peminjaman yang sedang dipinjam, tanpa filter NRP
    $peminjaman_dipinjam_query = $conn->query("SELECT COUNT(*) as total_dipinjam FROM peminjaman WHERE status_peminjaman = 'dipinjam'");
    if ($peminjaman_dipinjam_query) {
        $stats['dipinjam'] = $peminjaman_dipinjam_query->fetch_assoc()['total_dipinjam'];
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

    if ($file["size"] > 5000000) { // 5MB limit
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
                // Hapus cover lama jika ada dan bukan cover default/kosong
                if ($cover && !empty($cover) && file_exists("../uploads/covers/" . $cover)) {
                    unlink("../uploads/covers/" . $cover); // Perbaiki path
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

        // Ambil nama file cover sebelum menghapus record buku
        $stmt_get_cover = $conn->prepare("SELECT Cover FROM buku WHERE ID = ?");
        $stmt_get_cover->bind_param("s", $delete_id);
        $stmt_get_cover->execute();
        $result_cover = $stmt_get_cover->get_result();
        $cover_to_delete = null;
        if ($row = $result_cover->fetch_assoc()) {
            $cover_to_delete = $row['Cover'];
        }
        $stmt_get_cover->close();

        $stmt = $conn->prepare("DELETE FROM buku WHERE ID = ?");
        $stmt->bind_param("s", $delete_id);

        if ($stmt->execute()) {
            // Hapus file cover fisik jika ada
            if ($cover_to_delete && !empty($cover_to_delete) && file_exists("../uploads/covers/" . $cover_to_delete)) {
                unlink("../uploads/covers/" . $cover_to_delete);
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

// Hitung total buku untuk paginasi (dengan search query jika ada)
$total_buku_query = "SELECT COUNT(*) as total FROM buku " . $where_clause;
$stmt_total = $conn->prepare($total_buku_query);
if (!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_buku_filtered = $result_total->fetch_assoc()['total'];
$stmt_total->close();

$total_halaman = ceil($total_buku_filtered / $buku_per_halaman);


$sql = "SELECT * FROM buku $where_clause ORDER BY Judul ASC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    // Gabungkan tipe data: tipe dari pencarian + 'ii' untuk limit dan offset
    $full_types = $types . "ii";
    
    // Gabungkan semua parameter ke dalam satu array
    $all_params = array_merge($params, [$buku_per_halaman, $offset]);
    
    // Panggil bind_param dengan membongkar array yang sudah digabung
    // Ini adalah PERBAIKAN untuk error "positional argument after argument unpacking"
    $stmt->bind_param($full_types, ...$all_params); 
} else {
    // Jika tidak ada parameter pencarian, langsung bind parameter untuk limit dan offset
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
        /* Optional: Add some basic transition for sidebar for smoother animation */
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }
        #sidebarOverlay {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>

<body class="bg-[#FFFAEC]">
    <div class="md:flex h-screen">
        <div id="sidebar"
            class="fixed inset-y-0 left-0 z-40 w-64 bg-[#DFD0B8] shadow-md transform -translate-x-full md:relative md:translate-x-0">
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
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Daftar Peminjaman</span>
                </a>
                <a href="?logout=1" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black mt-auto">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-2">Logout</span>
                </a>
            </nav>
        </div>

        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden" style="opacity: 0;"></div>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-[#DFD0B8] shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <button id="menuButton" class="md:hidden text-gray-800 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="font-bold text-lg text-gray-800 ml-4 md:ml-0">Kelola Buku</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <form action="kelolaBuku.php" method="GET">
                                <input type="text" name="search"
                                    class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-32 sm:w-48 md:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                            <div class="text-sm hidden sm:block"> <div class="font-medium"><?php echo htmlspecialchars($admin['name']); ?></div>
                                <div class="text-gray-500 text-xs">Admin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <main class="flex-1 overflow-y-auto p-4 md:p-6"> <?php if (isset($_SESSION['success_message'])): ?>
                    <div
                        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex justify-between items-center">
                        <span><?php echo $_SESSION['success_message']; ?></span>
                        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div
                        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex justify-between items-center">
                        <span><?php echo $_SESSION['error_message']; ?></span>
                        <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 sm:mb-0">Daftar Buku Perpustakaan</h2>
                    <button onclick="addBook()" class="bg-[#393E46] hover:bg-[#4a4f57] text-white px-4 py-2 rounded-lg flex items-center transition duration-200 w-full sm:w-auto justify-center">
                        <i class="fas fa-plus mr-2"></i> Tambah Buku
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8"> <div
                        class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-blue-500">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Buku</h3>
                        <p class="text-2xl sm:text-3xl font-bold text-blue-600"><?php echo $book_stats['total_buku']; ?></p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Buku dalam koleksi</p>
                    </div>
                    <div
                        class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-orange-500">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Buku Dipinjam</h3>
                        <p class="text-2xl sm:text-3xl font-bold text-orange-600"><?php echo $book_stats['dipinjam']; ?></p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Buku sedang dipinjam</p>
                    </div>
                    <div
                        class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-green-500">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Buku Tersedia</h3>
                        <p class="text-2xl sm:text-3xl font-bold text-green-600"><?php echo $book_stats['tersedia']; ?></p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Buku belum dipinjam</p>
                    </div>
                    <div
                        class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition duration-200 border-l-4 border-blue-900">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Buku Dikembalikan</h3>
                        <p class="text-2xl sm:text-3xl font-bold text-blue-900"><?php echo $book_stats['dikembalikan']; ?></p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Total pengembalian buku</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-b">
                        <div class="text-sm text-gray-500 mb-2 sm:mb-0">
                            Menampilkan <span class="font-medium"><?php echo ($offset + 1) . ' - ' . min($offset + count($books), $total_buku_filtered); ?></span> dari <span
                                class="font-medium"><?php echo $total_buku_filtered; ?></span> buku
                        </div>
                        <select
                            class="bg-gray-100 rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-auto">
                            <option value="8" <?php echo ($buku_per_halaman == 8) ? 'selected' : ''; ?>>8 per halaman</option>
                            <option value="10" <?php echo ($buku_per_halaman == 10) ? 'selected' : ''; ?>>10 per halaman</option>
                            <option value="15" <?php echo ($buku_per_halaman == 15) ? 'selected' : ''; ?>>15 per halaman</option>
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
                                        <td class="px-6 py-4 font-medium text-gray-900 truncate max-w-xs sm:max-w-none">
                                            <?php echo htmlspecialchars($book['judul']); ?></td>
                                        <td class="px-6 py-4 truncate max-w-[100px] sm:max-w-none"><?php echo htmlspecialchars($book['penulis']); ?></td>
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

                    <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-t">
                        <div class="text-sm text-gray-500 mb-2 sm:mb-0">
                            Menampilkan <span class="font-medium"><?php echo ($offset + 1) . ' - ' . min($offset + count($books), $total_buku_filtered); ?></span> dari <span class="font-medium"><?php echo $total_buku_filtered; ?></span> buku
                        </div>
                        <div class="flex items-center space-x-2">
                            <?php if ($halaman_saat_ini > 1): ?>
                                <a href="?page=<?php echo $halaman_saat_ini - 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </a>
                            <?php else: ?>
                                <button class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200 disabled:opacity-50" disabled>
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
                                <button class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200 disabled:opacity-50" disabled>
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden p-4">
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

    <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden p-4">
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

    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden p-4">
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

    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden p-4">
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
            
            // Perbaiki path cover
            const coverPath = book.cover ? `../uploads/covers/${book.cover}` : 'https://via.placeholder.com/100x120?text=No+Cover';
            
            details.innerHTML = `
                ${book.cover ? `<div class="mb-4 text-center"><img src="${coverPath}" alt="Cover Buku" class="mx-auto rounded-lg shadow-md max-h-48"></div>` : ''}
                <div class="flex flex-wrap">
                    <strong class="w-24 font-semibold">ID:</strong>
                    <span class="flex-1">${book.id}</span>
                </div>
                <div class="flex flex-wrap">
                    <strong class="w-24 font-semibold">Judul:</strong>
                    <span class="flex-1">${book.judul}</span>
                </div>
                <div class="flex flex-wrap">
                    <strong class="w-24 font-semibold">Penulis:</strong>
                    <span class="flex-1">${book.penulis}</span>
                </div>
                <div class="flex flex-wrap">
                    <strong class="w-24 font-semibold">Tahun:</strong>
                    <span class="flex-1">${book.tahun}</span>
                </div>
                <div class="flex flex-wrap">
                    <strong class="w-24 font-semibold">ISBN:</strong>
                    <span class="flex-1">${book.isbn}</span>
                </div>
                <div class="flex flex-wrap">
                    <strong class="w-24 font-semibold">Halaman:</strong>
                    <span class="flex-1">${book.halaman}</span>
                </div>
                <div class="flex flex-wrap">
                    <strong class="w-24 font-semibold">Stok:</strong>
                    <span class="flex-1">${book.stok}</span>
                </div>
                <div class="flex flex-wrap items-center">
                    <strong class="w-24 font-semibold">Status:</strong>
                    <span class="px-2 py-1 rounded-full text-xs ${book.status === 'Tersedia' ? 'bg-green-100 text-green-800' : 'bg-red-500 text-white'}">
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
                        <div class="flex items-center space-x-2 mb-2">
                            <img src="../uploads/covers/${book.cover}" alt="Current Cover" class="w-16 h-20 object-cover rounded shadow-sm">
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
            const isbn = document.getElementById('addIsbn').value;
            const tahun = document.getElementById('addTahun').value;
            const stok = document.getElementById('addStok').value;

            if (isbn && !/^\d{10}(\d{3})?$/.test(isbn.replace(/[^0-9]/g, ''))) {
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

        document.getElementById('editForm')?.addEventListener('submit', function (e) {
            const isbn = document.getElementById('editIsbn').value;
            const tahun = document.getElementById('editTahun').value;
            const stok = document.getElementById('editStok').value;

            if (isbn && !/^\d{10}(\d{3})?$/.test(isbn.replace(/[^0-9]/g, ''))) {
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

        window.onclick = function (event) {
            const addModal = document.getElementById('addModal');
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');

            if (event.target === addModal) closeAddModal();
            if (event.target === viewModal) closeViewModal();
            if (event.target === editModal) closeEditModal();
            if (event.target === deleteModal) closeDeleteModal();
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
      
        
function generateBookId() {
            // Contoh sederhana: kombinasi timestamp dan angka acak
            return 'BK-' + Date.now().toString().substring(3, 3) + Math.floor(Math.random() * 1000);
        }
        document.getElementById('id_buku').addEventListener('focus', function() {
            if (!this.value) {
                this.value = generateBookId();
            }
        });

        // Event listener untuk tombol hamburger menu
        const sidebar = document.getElementById('sidebar');
        const menuButton = document.getElementById('menuButton');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (menuButton && sidebar && sidebarOverlay) {
            menuButton.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
                // Mengubah opasitas overlay untuk efek transisi
                setTimeout(() => {
                    sidebarOverlay.style.opacity = sidebar.classList.contains('-translate-x-full') ? '0' : '1';
                }, 10);
            });

            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.style.opacity = '0'; // Set opasitas ke 0 saat overlay di-hide
                setTimeout(() => {
                    sidebarOverlay.classList.add('hidden');
                }, 300); // Tunggu transisi selesai sebelum hidden
            });

            // Sembunyikan sidebar di layar mobile saat resize ke desktop
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) { // md breakpoint in Tailwind
                    sidebar.classList.remove('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                    sidebarOverlay.style.opacity = '0';
                }
            });
        }
        
        // Fitur keyboard shortcuts dan form status yang sudah ada
        let searchTimeout;
        const searchInput = document.querySelector('input[name="search"]');

        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                addBook();
            }

            if (e.key === 'Escape') {
                closeAddModal();
                closeViewModal();
                closeEditModal();
                closeDeleteModal();
            }
        });

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

        document.querySelectorAll('[title]').forEach(element => {
            element.addEventListener('mouseenter', function () {
                this.setAttribute('data-tooltip', this.getAttribute('title'));
                this.removeAttribute('title');
            });
            // Tidak perlu mouseleave karena title akan muncul lagi setelah dilepas
        });
    </script>
</body>
</html>