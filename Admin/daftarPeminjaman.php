<?php
session_start();
include_once __DIR__ . '/../konek.php';

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

$books = []; 
$error_message = ''; 
$success_message = ''; 

$selected_status = $_GET['status'] ?? 'all';

$sql = "SELECT
    p.Id_Peminjaman,
    p.Tanggal_Pinjam,
    p.Batas_waktu AS tanggal_kembali_seharusnya,
    p.Tanggal_kembali AS tanggal_dikembalikan_aktual,
    p.status_peminjaman,
    b.Judul,
    b.ISBN,
    b.Penulis,
    a.Nama,
    CASE
        WHEN p.Batas_waktu < CURDATE() AND p.status_peminjaman = 'dipinjam' THEN 'Terlambat'
        WHEN p.status_peminjaman = 'dipinjam' THEN 'Dipinjam'
        ELSE 'Dipinjam'
    END AS status_tampilan
FROM peminjaman p
JOIN buku b ON p.ID_Buku = b.ID
JOIN anggota a ON p.NRP = a.NRP
WHERE p.status_peminjaman = 'dipinjam'"; 

$additional_conditions = [];

if ($selected_status == 'dipinjam') {
    $additional_conditions[] = "p.Batas_waktu >= CURDATE()";
} elseif ($selected_status == 'terlambat') {
    $additional_conditions[] = "p.Batas_waktu < CURDATE()";
}

if (!empty($additional_conditions)) {
    $sql .= " AND " . implode(" AND ", $additional_conditions);
}

$sql .= " ORDER BY p.Tanggal_Pinjam DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result(); 
    
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row; 
    }
    $stmt->close(); 
} catch (Exception $e) { 
    $books = [];
    $error_message = "Error mengambil data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Daftar Peminjaman - SiPerpus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#FFFAEC]">
    <div class="flex h-screen">
        <!-- Sidebar - Hidden on mobile, visible on desktop -->
        <div class="hidden lg:block w-64 bg-[#DFD0B8] flex-shrink-0">
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
                <a href="dashboardAdmin.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="kelolaBuku.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Buku</span>
                </a>
                <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-2">Anggota</span>
                </a>
                <a href="daftarPeminjaman.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Daftar Peminjaman</span>
                </a>
                <a href="?logout=1" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black mt-auto">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-2">Logout</span>
                </a>
            </nav>
        </div>

        <!-- Mobile Sidebar Overlay -->
        <div id="mobileSidebar" class="fixed inset-0 z-50 lg:hidden hidden">
            <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeMobileSidebar()"></div>
            <div class="fixed left-0 top-0 h-full w-64 bg-[#DFD0B8] z-50">
                <div class="bg-[#DFD0B8] p-4 flex items-center justify-between text-black border-b border-[#FFFAEC]">
                    <div class="flex items-center space-x-3">
                        <div class="bg-[#393E46] p-2 rounded">
                            <span class="font-bold text-white">SP</span>
                        </div>
                        <div class="text-sm leading-tight">
                            <div class="font-bold">SiPerpus</div>
                            <div class="text-xs">Sistem Perpustakaan Digital</div>
                        </div>
                    </div>
                    <button onclick="closeMobileSidebar()" class="text-black">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <nav class="mt-4">
                    <a href="dashboardAdmin.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                        <i class="fas fa-chart-bar w-6"></i>
                        <span class="ml-2">Dashboard</span>
                    </a>
                    <a href="kelolaBuku.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                        <i class="fas fa-book w-6"></i>
                        <span class="ml-2">Buku</span>
                    </a>
                    <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                        <i class="fas fa-users w-6"></i>
                        <span class="ml-2">Anggota</span>
                    </a>
                    <a href="daftarPeminjaman.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
                        <i class="fas fa-book w-6"></i>
                        <span class="ml-2">Daftar Peminjaman</span>
                    </a>
                    <a href="?logout=1" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black mt-auto">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        <span class="ml-2">Logout</span>
                    </a>
                </nav>
            </div>
        </div>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-[#DFD0B8] shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <!-- Mobile menu button and title -->
                    <div class="flex items-center space-x-4">
                        <button onclick="openMobileSidebar()" class="lg:hidden text-black">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="font-bold text-lg">Peminjaman Buku</div>
                    </div>
                    
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <!-- Search - hidden on mobile, visible on desktop -->
                        <div class="relative hidden md:block">
                            <input type="text" class="bg-gray-100 rounded-lg px-3 py-2 pr-8 w-48 md:w-64" placeholder="Cari buku...">
                            <button class="absolute right-2 top-2 text-gray-500">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <!-- User info - hidden on small screens -->
                        <div class="flex sm:flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                            <div class="text-sm md:block">
                                <div class="font-medium"><?php echo htmlspecialchars($admin['name']); ?></div>
                                <div class="text-gray-500 text-xs">Admin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-3 sm:p-4 lg:p-6">
                <!-- Breadcrumb - responsive text size -->
                <div class="flex justify-between items-center mb-4 lg:mb-6">
                    <div class="text-xs sm:text-sm">
                        <a href="dashboardAdmin.php" class="text-[#948979] hover:text-[#948979]">Dashboard</a> /
                        <span class="text-gray-600">Peminjaman Aktif</span>
                    </div>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>

                <!-- Mobile Search Bar - only visible on mobile -->
                <div class="md:hidden mb-4">
                    <div class="relative">
                        <input type="text" class="bg-gray-100 rounded-lg px-3 py-2 pr-8 w-full" placeholder="Cari buku...">
                        <button class="absolute right-2 top-2 text-gray-500">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Page title - responsive -->
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 lg:mb-6 gap-2">
                    <h2 class="text-lg sm:text-xl font-medium">Daftar Peminjaman Aktif</h2>
                </div>


                <div class="bg-white rounded-lg shadow-sm mb-4 lg:mb-6">
                    <!-- Filter section - responsive -->
                    <div class="p-3 sm:p-4 flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center justify-between gap-2 border-b border-gray-200">
                        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                            <select class="border border-gray-300 rounded-md px-3 py-1 text-sm w-full sm:w-auto" onchange="filterByStatus(this.value)">
                                <option value="all" <?php echo ($selected_status == 'all') ? 'selected' : ''; ?>>Semua Peminjaman Aktif</option>
                                <option value="dipinjam" <?php echo ($selected_status == 'dipinjam') ? 'selected' : ''; ?>>Normal</option>
                                <option value="terlambat" <?php echo ($selected_status == 'terlambat') ? 'selected' : ''; ?>>Terlambat</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table container with horizontal scroll -->
                    <div class="overflow-x-auto">
                        <!-- Desktop Table -->
                        <table class="min-w-full hidden lg:table">
                            <thead>
                                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                                    <th class="px-6 py-3 text-left">ID Peminjaman</th>
                                    <th class="px-6 py-3 text-left">Nama Peminjam</th>
                                    <th class="px-6 py-3 text-left">Judul</th>
                                    <th class="px-6 py-3 text-left">ISBN</th>
                                    <th class="px-6 py-3 text-left">Penulis</th>
                                    <th class="px-6 py-3 text-left">Tanggal Pinjam</th>
                                    <th class="px-6 py-3 text-left">Tanggal Kembali</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($books)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Tidak ada peminjaman aktif</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['Id_Peminjaman'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['Nama'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($book['Judul'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['ISBN'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['Penulis'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars(date('d/m/Y', strtotime($book['Tanggal_Pinjam'] ?? 'now'))); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars(date('d/m/Y', strtotime($book['tanggal_kembali_seharusnya'] ?? 'now'))); ?></td>
                                        <td class="px-6 py-4">
                                            <?php
                                            $display_status = $book['status_tampilan'] ?? 'N/A';
                                            if ($display_status == 'Dipinjam'): ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Dipinjam</span>
                                            <?php elseif ($display_status == 'Terlambat'): ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Terlambat</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800"><?php echo $display_status; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Mobile/Tablet Cards -->
                        <div class="lg:hidden">
                            <?php if (empty($books)): ?>
                                <div class="p-6 text-center text-gray-500">Tidak ada peminjaman aktif</div>
                            <?php else: ?>
                                <?php foreach ($books as $book): ?>
                                <div class="border-b border-gray-200 p-4">
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-start">
                                            <div class="font-medium text-sm">
                                                <?php echo htmlspecialchars($book['Judul'] ?? 'N/A'); ?>
                                            </div>
                                            <div class="ml-2">
                                                <?php
                                                $display_status = $book['status_tampilan'] ?? 'N/A';
                                                if ($display_status == 'Dipinjam'): ?>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Dipinjam</span>
                                                <?php elseif ($display_status == 'Terlambat'): ?>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Terlambat</span>
                                                <?php else: ?>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800"><?php echo $display_status; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <div><strong>ID:</strong> <?php echo htmlspecialchars($book['Id_Peminjaman'] ?? 'N/A'); ?></div>
                                            <div><strong>Peminjam:</strong> <?php echo htmlspecialchars($book['Nama'] ?? 'N/A'); ?></div>
                                            <div><strong>ISBN:</strong> <?php echo htmlspecialchars($book['ISBN'] ?? 'N/A'); ?></div>
                                            <div><strong>Penulis:</strong> <?php echo htmlspecialchars($book['Penulis'] ?? 'N/A'); ?></div>
                                            <div class="flex justify-between mt-1">
                                                <span><strong>Pinjam:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($book['Tanggal_Pinjam'] ?? 'now'))); ?></span>
                                                <span><strong>Kembali:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($book['tanggal_kembali_seharusnya'] ?? 'now'))); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function openPinjamModal() {
            document.getElementById('pinjamModal').classList.remove('hidden');
        }

        function closePinjamModal() {
            document.getElementById('pinjamModal').classList.add('hidden');
        }

        function openMobileSidebar() {
            document.getElementById('mobileSidebar').classList.remove('hidden');
        }

        function closeMobileSidebar() {
            document.getElementById('mobileSidebar').classList.add('hidden');
        }

        function filterByStatus(status) {
            const url = new URL(window.location);
            if (status === 'all') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', status);
            }
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('Daftar Peminjaman Admin page loaded');

            const searchInputs = document.querySelectorAll('input[type="text"]');
            searchInputs.forEach(searchInput => {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    // Search in desktop table
                    const tableBody = document.querySelector('table tbody');
                    if (tableBody) {
                        const rows = tableBody.querySelectorAll('tr');
                        rows.forEach(row => {
                            if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) {
                                return;
                            }
                            
                            const IDBuku = row.cells[0]?.textContent.toLowerCase() || ''; 
                            const namaPeminjam = row.cells[1]?.textContent.toLowerCase() || ''; 
                            const judulBuku = row.cells[2]?.textContent.toLowerCase() || '';   
                            const isbn = row.cells[3]?.textContent.toLowerCase() || '';         
                            const penulis = row.cells[4]?.textContent.toLowerCase() || '';      

                            if (IDBuku.includes(searchTerm) ||
                                namaPeminjam.includes(searchTerm) || 
                                judulBuku.includes(searchTerm) || 
                                isbn.includes(searchTerm) || 
                                penulis.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }

                    // Search in mobile cards
                    const mobileCards = document.querySelectorAll('.lg\\:hidden > div');
                    mobileCards.forEach(card => {
                        if (card.classList.contains('text-center')) return; // Skip "no data" message
                        
                        const cardText = card.textContent.toLowerCase();
                        if (cardText.includes(searchTerm)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Sync search inputs
                    searchInputs.forEach(input => {
                        if (input !== this) {
                            input.value = this.value;
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>