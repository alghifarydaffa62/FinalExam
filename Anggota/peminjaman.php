<?php
session_start();
include '../konek.php';

if (isset($_GET['logout'])) {
    session_destroy();
    
    if (isset($_COOKIE['member_remember'])) {
        setcookie('member_remember', '', time() - 3600, '/');
    }

    header("Location: loginAnggota.php");
    exit;
}

$user_nrp = null;
$user = [
    'name' => $_SESSION['member_name'] ?? 'Anggota',
    'id' => $_SESSION['member_id'] ?? '1'
];

if (!empty($user['name'])) {
    try {
        $get_nrp = $conn->prepare("SELECT NRP FROM anggota WHERE Nama = ?");
        $get_nrp->bind_param("s", $user['name']);
        $get_nrp->execute();
        $nrp_result = $get_nrp->get_result();
        
        if ($nrp_result->num_rows > 0) {
            $user_data = $nrp_result->fetch_assoc();
            $user_nrp = $user_data['NRP'];
        }
    } catch (Exception $e) {
        error_log("Error getting user NRP: " . $e->getMessage());
    }
}

if (!$user_nrp) {
    session_destroy();
    header("Location: loginAnggota.php");
    exit;
}

$books = [];
$error_message = '';
$success_message = '';


if (isset($_POST['pinjam_buku'])) {
    $nrp_input = $user_nrp; 
    $judul_buku = trim($_POST['judul_buku'] ?? '');
    
    if (!empty($judul_buku)) {
        try {
            $check_book = $conn->prepare("SELECT * FROM buku WHERE Judul LIKE ? AND Stok > 0");
            $judul_search = "%{$judul_buku}%";
            $check_book->bind_param("s", $judul_search);
            $check_book->execute();
            $book_result = $check_book->get_result();
            
            if ($book_result->num_rows > 0) {
                $book_data = $book_result->fetch_assoc();

                $check_existing = $conn->prepare("SELECT * FROM peminjaman WHERE NRP = ? AND ID_Buku = ? AND Tanggal_kembali IS NULL");
                $check_existing->bind_param("ss", $nrp_input, $book_data['ID']);
                $check_existing->execute();
                $existing_result = $check_existing->get_result();
                
                if ($existing_result->num_rows > 0) {
                    $error_message = "Anda sudah meminjam buku '{$book_data['Judul']}' dan belum mengembalikannya.";
                } else {
                    $get_last_id = $conn->query("SELECT Id_Peminjaman FROM peminjaman ORDER BY Id_Peminjaman DESC LIMIT 1");
                    $last_id_result = $get_last_id->fetch_assoc();
                    
                    if ($last_id_result && $last_id_result['Id_Peminjaman']) {
                        $last_number = intval(substr($last_id_result['Id_Peminjaman'], 2));
                        $new_number = $last_number + 1;
                    } else {
                        $new_number = 1;
                    }
                    $id_peminjaman = '#P' . str_pad($new_number, 3, '0', STR_PAD_LEFT);

                    $tanggal_pinjam = date('Y-m-d');
                    $batas_waktu = date('Y-m-d', strtotime('+7 days'));
                    $status_peminjaman = 'Dipinjam';

                    $insert_peminjaman = $conn->prepare("INSERT INTO peminjaman (Id_Peminjaman, NRP, ID_Buku, Judul, Tanggal_Pinjam, Batas_waktu, status_peminjaman) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $insert_peminjaman->bind_param("sssssss", $id_peminjaman, $nrp_input, $book_data['ID'], $book_data['Judul'], $tanggal_pinjam, $batas_waktu, $status_peminjaman);
                    
                    if ($insert_peminjaman->execute()) {
                        $update_stok = $conn->prepare("UPDATE buku SET Stok = Stok - 1 WHERE ID = ?");
                        $update_stok->bind_param("s", $book_data['ID']);
                        $update_stok->execute();
                        
                        $_SESSION['success_message'] = "Buku '{$book_data['Judul']}' berhasil dipinjam! ID Peminjaman: {$id_peminjaman}";
                        header("Location: peminjaman.php");
                        exit;
                    } else {
                        $error_message = "Gagal menyimpan data peminjaman. Silakan coba lagi.";
                    }
                }
            } else {
                $error_message = "Buku dengan judul '{$judul_buku}' tidak ditemukan atau stok habis.";
            }
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $error_message = "Mohon masukkan judul buku yang akan dipinjam.";
    }
}

try {
    $where_clause = "WHERE p.NRP = ? AND p.Tanggal_kembali IS NULL";
    $params = [$user_nrp];
    $param_types = "s";
    
    $query = "SELECT p.Id_Peminjaman, p.NRP, p.ID_Buku, p.Judul, p.Tanggal_Pinjam, p.Batas_waktu, p.Tanggal_kembali, p.status_peminjaman,
                     b.Penulis, b.ISBN, b.Stok,
                     a.Nama as nama_anggota
              FROM peminjaman p 
              LEFT JOIN buku b ON p.ID_Buku = b.ID 
              LEFT JOIN anggota a ON p.NRP = a.NRP
              {$where_clause} 
              ORDER BY p.Tanggal_Pinjam DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (date('Y-m-d') > $row['Batas_waktu']) {
            $status = 'Terlambat';
        } else {
            $status = 'Dipinjam';
        }
        
        $books[] = [
            'id' => $row['Id_Peminjaman'],
            'title' => $row['Judul'],
            'isbn' => $row['ISBN'] ?? 'N/A',
            'author' => $row['Penulis'] ?? 'N/A',
            'tanggal_pinjam' => $row['Tanggal_Pinjam'],
            'tanggal_kembali_batas' => $row['Batas_waktu'],
            'tanggal_kembali_actual' => $row['Tanggal_kembali'],
            'status' => $status,
            'idBuku' => $row['ID_Buku']
        ];
    }
} catch (Exception $e) {
    $error_message = "Error loading data: " . $e->getMessage();
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Buku Saya - SiPerpus</title>
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
                <a href="peminjaman.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
                    <i class="fas fa-book-open w-6"></i>
                    <span class="ml-2">Peminjaman</span>
                </a>
                <a href="pengembalian.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
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
                    <div class="font-bold text-lg">Peminjaman Buku Saya</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" id="searchInput" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64">
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
                                <div class="text-gray-500 text-xs">NRP: <?php echo htmlspecialchars($user_nrp); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="text-sm">
                        <a href="dashboard.php" class="text-[#948979] hover:text-[#948979]">Dashboard</a> / 
                        <span class="text-gray-600">Peminjaman</span>
                    </div>
                </div>

                <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-medium">Buku yang Sedang Saya Pinjam</h2>
                    <button onclick="openPinjamModal()" class="bg-blue-600 text-white px-4 py-2 rounded-md flex items-center text-sm hover:bg-[#948979]">
                        <i class="fas fa-plus mr-2 text-white"></i> Pinjam Buku Baru
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium">Daftar Buku yang Sedang Dipinjam</h3>
                    </div> 

                    <div class="p-4 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <div class="text-sm text-gray-600">
                                Menampilkan buku yang sedang dipinjam (belum dikembalikan)
                            </div>
                        </div>
                        <div class="text-sm text-gray-600">
                            Total: <?php echo count($books); ?> buku
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                                    <th class="px-6 py-3 text-left">ID Peminjaman</th>
                                    <th class="px-6 py-3 text-left">Judul</th>
                                    <th class="px-6 py-3 text-left">ID Buku</th>
                                    <th class="px-6 py-3 text-left">ISBN</th>
                                    <th class="px-6 py-3 text-left">Penulis</th>
                                    <th class="px-6 py-3 text-left">Tanggal Pinjam</th>
                                    <th class="px-6 py-3 text-left">Batas Waktu</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="bookTableBody">
                                <?php if (empty($books)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-book-open text-4xl mb-2"></i>
                                        <div>Anda tidak memiliki buku yang sedang dipinjam</div>
                                        <button onclick="openPinjamModal()" class="mt-4 text-blue-600 hover:text-blue-800 underline">
                                            Pinjam buku sekarang
                                        </button>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($books as $book): ?>
                                    <tr class="book-row">
                                        <td class="px-6 py-4 font-mono text-sm"><?php echo htmlspecialchars($book['id']); ?></td>
                                        <td class="px-6 py-4 font-medium book-title"><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['idBuku']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['isbn']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td class="px-6 py-4"><?php echo date('d/m/Y', strtotime($book['tanggal_pinjam'])); ?></td>
                                        <td class="px-6 py-4"><?php echo date('d/m/Y', strtotime($book['tanggal_kembali_batas'])); ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($book['status'] == 'Dipinjam'): ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Dipinjam</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Terlambat</span>
                                            <?php endif; ?>
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
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Judul Buku</label>
                            <input type="text" name="judul_buku" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan judul buku yang ingin dipinjam" required>
                            <p class="text-xs text-gray-500 mt-1">Sistem akan mencari buku berdasarkan judul</p>
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
  
    <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg w-full max-w-md">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Detail Peminjaman</h3>
                        <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div id="detailContent" class="space-y-3"></div>
                    <div class="flex justify-end mt-6">
                        <button onclick="closeDetailModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Tutup</button>
                    </div>
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

        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.book-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const loanId = cells[0]?.textContent.toLowerCase() || ''; 
                const title = cells[1]?.textContent.toLowerCase() || ''; 
                const bookId = cells[2]?.textContent.toLowerCase() || ''; 
                const isbn = cells[3]?.textContent.toLowerCase() || ''; 
                const author = cells[4]?.textContent.toLowerCase() || ''; 

                const isMatch = searchTerm === '' ||
                            loanId.includes(searchTerm) || 
                            title.includes(searchTerm) || 
                            bookId.includes(searchTerm) || 
                            isbn.includes(searchTerm) || 
                            author.includes(searchTerm);

                if (isMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            updateSearchCounter(searchTerm, visibleCount);
        });

        function updateSearchCounter(searchTerm, visibleCount) {
            const totalElement = document.querySelector('.text-sm.text-gray-600:last-child');
            
            if (searchTerm.trim() === '') {
                totalElement.textContent = `Total: ${visibleCount} buku`;
            } else {
                totalElement.textContent = `Ditemukan: ${visibleCount} buku dari pencarian "${searchTerm}"`;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Peminjaman page loaded');

            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.placeholder = 'Cari Peminjaman...';
            }

            document.getElementById('pinjamModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closePinjamModal();
                }
            });
        });
    </script>
</body>
</html>