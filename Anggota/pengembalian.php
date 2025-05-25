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

$user_nrp = null;
$user = [
    'name' => $_SESSION['member_name'] ?? 'Anggota',
    'id' => $_SESSION['member_id'] ?? '1'
];

// Query untuk mendapatkan NRP berdasarkan nama user
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

// Jika tidak bisa mendapatkan NRP, redirect ke login
if (!$user_nrp) {
    session_destroy();
    header("Location: loginAnggota.php");
    exit;
}

$message = '';
$message_type = '';

if (isset($_POST['kembalikan_buku']) && isset($_POST['id_peminjaman'])) {
    $id_peminjaman = $_POST['id_peminjaman'];
    $id_buku = $_POST['id_buku'];
    
    try {

        $conn->begin_transaction();
        
        $tanggal_kembali = date('Y-m-d');
        $update_peminjaman = $conn->prepare("UPDATE peminjaman SET Tanggal_kembali = ?, status_peminjaman = 'Dikembalikan' WHERE Id_Peminjaman = ? AND NRP = ? AND Tanggal_kembali IS NULL");
        $update_peminjaman->bind_param("sss", $tanggal_kembali, $id_peminjaman, $user_nrp);
        
        if ($update_peminjaman->execute() && $update_peminjaman->affected_rows > 0) {
            $update_stok = $conn->prepare("UPDATE buku SET Stok = Stok + 1 WHERE ID = ?");
            $update_stok->bind_param("s", $id_buku);
            $update_stok->execute();
            
            $conn->commit();
        
            $_SESSION['return_success'] = "Buku berhasil dikembalikan!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $conn->rollback();
            $message = "Gagal mengembalikan buku. Buku mungkin sudah dikembalikan sebelumnya.";
            $message_type = 'error';
        }
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Terjadi kesalahan: " . $e->getMessage();
        $message_type = 'error';
    }
}

if (isset($_SESSION['return_success'])) {
    $message = $_SESSION['return_success'];
    $message_type = 'success';
    unset($_SESSION['return_success']);
}

$borrowed_books = [];
try {
    $query = "SELECT p.Id_Peminjaman, p.ID_Buku, p.Judul, p.Tanggal_Pinjam, p.Batas_waktu, 
                      b.Penulis, b.Jumlah_halaman, b.ISBN
              FROM peminjaman p 
              LEFT JOIN buku b ON p.ID_Buku = b.ID 
              WHERE p.NRP = ? AND (p.Tanggal_kembali IS NULL OR p.status_peminjaman != 'Dikembalikan')
              ORDER BY p.Tanggal_Pinjam DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_nrp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $status = (date('Y-m-d') > $row['Batas_waktu']) ? 'Terlambat' : 'Normal';
        
        $borrowed_books[] = [
            'id_peminjaman' => $row['Id_Peminjaman'],
            'id_buku' => $row['ID_Buku'],
            'judul' => $row['Judul'],
            'isbn' => $row['ISBN'] ?? 'N/A',
            'jumlah_halaman' => $row['Jumlah_halaman'] ?? 0,
            'penulis' => $row['Penulis'] ?? 'N/A',
            'jumlah_halaman' => $row['Jumlah_halaman'] ?? 0,
            'isbn' => $row['ISBN'] ?? 'N/A',
            'tanggal_pinjam' => $row['Tanggal_Pinjam'],
            'batas_waktu' => $row['Batas_waktu'],
            'status' => $status
        ];
    }
} catch (Exception $e) {
    $message = "Error loading data: " . $e->getMessage();
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Buku - SiPerpus</title>
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
                <a href="pengembalian.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
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
                    <div class="font-bold text-lg">Pengembalian Buku</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" id="searchInput" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64" placeholder="Cari buku...">
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
                        <span class="text-gray-600">Pengembalian</span>
                    </div>
                </div>

                <h2 class="text-xl font-medium mb-6">Pengembalian Buku</h2>

                <?php if ($message): ?>
                <div class="mb-6 px-4 py-3 rounded-lg flex items-center <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium">Buku Yang Sedang Dipinjam</h3>
                            <div class="text-sm text-gray-500">
                                Total: <?php echo count($borrowed_books); ?> buku
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($borrowed_books)): ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-500 mb-2">Tidak Ada Buku Yang Dipinjam</h3>
                        <p class="text-gray-400 mb-4">Anda belum meminjam buku atau semua buku sudah dikembalikan.</p>
                        <a href="peminjaman.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>
                            Pinjam Buku
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Peminjaman</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Buku</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batas Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="bookTableBody">
                                <?php foreach ($borrowed_books as $book): ?>
                                <tr class="hover:bg-gray-50 book-row">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm"><?php echo htmlspecialchars($book['id_peminjaman']); ?></td>
                                    <td class="px-6 py-4 book-title">
                                        <div class="font-medium"><?php echo htmlspecialchars($book['judul']); ?></div>
                                        <div class="text-sm text-gray-500">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium"><?php echo htmlspecialchars($book['id_buku']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d/m/Y', strtotime($book['tanggal_pinjam'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo date('d/m/Y', strtotime($book['batas_waktu'])); ?>
                                        <?php if ($book['status'] == 'Terlambat'): ?>
                                            <div class="text-xs text-red-600 font-medium">
                                                <?php 
                                                $days_late = (strtotime(date('Y-m-d')) - strtotime($book['batas_waktu'])) / (60 * 60 * 24);
                                                echo "Terlambat " . ceil($days_late) . " hari";
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($book['status'] == 'Terlambat'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Terlambat</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Dalam Peminjaman</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <button onclick="showBookDetail('<?php echo htmlspecialchars($book['id_peminjaman']); ?>', '<?php echo htmlspecialchars($book['judul']); ?>', '<?php echo htmlspecialchars($book['id_buku']); ?>', '<?php echo htmlspecialchars($book['isbn']); ?>', '<?php echo htmlspecialchars($book['penulis']); ?>', '<?php echo htmlspecialchars($book['jumlah_halaman']); ?>', '<?php echo date('d/m/Y', strtotime($book['tanggal_pinjam'])); ?>', '<?php echo date('d/m/Y', strtotime($book['batas_waktu'])); ?>', '<?php echo htmlspecialchars($book['status']); ?>')" 
                                                    class="bg-blue-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-blue-700 transition-colors duration-200">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            <form method="post" action="" class="inline" onsubmit="return confirmReturn('<?php echo htmlspecialchars($book['judul']); ?>')">
                                                <input type="hidden" name="id_peminjaman" value="<?php echo htmlspecialchars($book['id_peminjaman']); ?>">
                                                <input type="hidden" name="id_buku" value="<?php echo htmlspecialchars($book['id_buku']); ?>">
                                                <button type="submit" name="kembalikan_buku" class="bg-green-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-green-700 transition-colors duration-200">
                                                    <i class="fas fa-undo mr-1"></i>Kembalikan
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg w-full max-w-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Detail Peminjaman Buku</h3>
                        <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div id="detailContent" class="space-y-4"></div>  
                    <div class="flex justify-end mt-6">
                        <button onclick="closeDetailModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.book-row');
            
            rows.forEach(row => {
                const title = row.querySelector('.book-title').textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        function showBookDetail(idPeminjaman, title, idBuku, isbn, penulis, jumlahHalaman, tanggalPinjam, batasWaktu, status) {
            const detailContent = document.getElementById('detailContent');
            detailContent.innerHTML = `
                <div class="grid grid-cols-1 gap-4">
                    <div class="border-b pb-2">
                        <label class="block text-sm font-medium text-gray-700">ID Peminjaman</label>
                        <p class="text-sm text-gray-900 font-mono">${idPeminjaman}</p>
                    </div>
                    <div class="border-b pb-2">
                        <label class="block text-sm font-medium text-gray-700">Judul Buku</label>
                        <p class="text-sm text-gray-900 font-medium">${title}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ID Buku</label>
                            <p class="text-sm text-gray-900">${idBuku}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ISBN</label>
                            <p class="text-sm text-gray-900">${isbn}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Penulis</label>
                            <p class="text-sm text-gray-900">${penulis}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jumlah Halaman</label>
                            <p class="text-sm text-gray-900">${jumlahHalaman} halaman</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Pinjam</label>
                            <p class="text-sm text-gray-900">${tanggalPinjam}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Batas Waktu</label>
                            <p class="text-sm text-gray-900">${batasWaktu}</p>
                        </div>
                    </div>
                    <div class="border-t pt-2">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <p class="text-sm mt-1">
                            <span class="px-2 py-1 text-xs rounded-full ${
                                status === 'Terlambat' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'
                            }">${status}</span>
                        </p>
                    </div>
                </div>
            `;
            document.getElementById('detailModal').classList.remove('hidden');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        function confirmReturn(judulBuku) {
            return confirm('Apakah Anda yakin ingin mengembalikan buku "' + judulBuku + '"?');
        }

        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDetailModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.querySelector('.bg-green-100');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.transition = 'opacity 0.5s';
                    successMessage.style.opacity = '0';
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>