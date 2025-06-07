<?php
session_start();
include '../konek.php';

// Cek sesi login admin
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

$peminjaman_data = []; // Ubah nama variabel dari $books menjadi $peminjaman_data agar lebih jelas
$error_message = '';
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']); // Hapus pesan setelah ditampilkan

$selected_status = $_GET['status'] ?? 'all';
$search_query = $_GET['search'] ?? '';

// Query dasar untuk semua peminjaman (aktif dan dikembalikan)
// Kita akan memfilter di PHP atau di tampilan jika perlu, tapi data dasar semua diambil
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
        ELSE 'Dikembalikan'
    END AS status_tampilan
FROM peminjaman p
JOIN buku b ON p.ID_Buku = b.ID
JOIN anggota a ON p.NRP = a.NRP";

$where_clauses = [];
$params = [];
$types = "";

// Filter berdasarkan status
// Jika 'all' maka tidak ada filter status_peminjaman di DB, tapi kita akan tampilkan hanya yang dipinjam atau terlambat
if ($selected_status == 'dipinjam' || $selected_status == 'terlambat') {
    $where_clauses[] = "p.status_peminjaman = 'dipinjam'";
} 
// Jika status_peminjaman diubah di database menjadi 'dikembalikan', baris tersebut tidak akan muncul di halaman ini.

// Filter berdasarkan pencarian
if (!empty($search_query)) {
    $where_clauses[] = "(b.Judul LIKE ? OR b.ISBN LIKE ? OR b.Penulis LIKE ? OR a.Nama LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= "ssss";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY p.Tanggal_Pinjam DESC";

try {
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $peminjaman_data = [];
    while ($row = $result->fetch_assoc()) {
        // Logika tambahan untuk memfilter 'dipinjam' atau 'terlambat' setelah diambil dari DB
        // Karena query awal hanya mengambil 'dipinjam', maka filter ini akan efektif untuk 'dipinjam' dan 'terlambat'
        if ($selected_status == 'all') {
            $peminjaman_data[] = $row;
        } elseif ($selected_status == 'dipinjam' && $row['status_tampilan'] == 'Dipinjam') {
            $peminjaman_data[] = $row;
        } elseif ($selected_status == 'terlambat' && $row['status_tampilan'] == 'Terlambat') {
            $peminjaman_data[] = $row;
        }
    }
    $stmt->close();
} catch (Exception $e) {
    $peminjaman_data = [];
    $error_message = "Error mengambil data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peminjaman - SiPerpus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom CSS for sidebar transition and overlay */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }
        .sidebar-overlay {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-[#FFFAEC] flex h-screen">

    <div id="sidebar"
        class="fixed inset-y-0 left-0 z-40 w-64 bg-[#DFD0B8] shadow-md transform -translate-x-full 
               md:relative md:translate-x-0 sidebar-transition">
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
            <a href="kelolaBuku.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                <i class="fas fa-book w-6"></i>
                <span class="ml-2">Buku</span>
            </a>
            <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                <i class="fas fa-users w-6"></i>
                <span class="ml-2">Anggota</span>
            </a>
            <a href="daftarPeminjaman.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
                <i class="fas fa-book-reader w-6"></i>
                <span class="ml-2">Daftar Peminjaman</span>
            </a>
            <a href="?logout=1" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black mt-auto">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span class="ml-2">Logout</span>
            </a>
        </nav>
    </div>

    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden sidebar-overlay" style="opacity: 0;"></div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-[#DFD0B8] shadow-sm">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center">
                    <button id="menuButton" class="md:hidden text-gray-800 focus:outline-none mr-4">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="font-bold text-base text-gray-800">Daftar Peminjaman</div>
                </div>
          
                <div class="flex items-center space-x-4">
                           <div class="relative">
                            <button class="text-gray-500">
                                <i class="fas fa-bell"></i>
                            </button>
                        </div>
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

        <main class="flex-1 overflow-y-auto p-4 md:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <div class="text-sm mb-2 sm:mb-0">
                    <a href="dashboardAdmin.php" class="text-[#948979] hover:underline">Dashboard</a> /
                    <span class="text-gray-600">Daftar Peminjaman</span>
                </div>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex justify-between items-center transition-opacity duration-300">
                <span><?php echo $success_message; ?></span>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex justify-between items-center transition-opacity duration-300">
                <span><?php echo $error_message; ?></span>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-medium text-gray-800">Daftar Peminjaman</h2>
            </div>
            
            <div class="relative mb-4">
                <form action="daftarPeminjaman.php" method="GET">
                    <input type="text" name="search"
                        class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Cari buku/peminjam..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="absolute right-2 top-2 text-gray-500 hover:text-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if ($selected_status != 'all'): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($selected_status); ?>">
                    <?php endif; ?>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="p-4 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200">
                    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                        <select class="border border-gray-300 rounded-md px-3 py-1 text-sm w-full sm:w-auto" onchange="filterByStatus(this.value)">
                            <option value="all" <?php echo ($selected_status == 'all') ? 'selected' : ''; ?>>Semua Peminjaman Aktif</option>
                            <option value="dipinjam" <?php echo ($selected_status == 'dipinjam') ? 'selected' : ''; ?>>Normal (Belum Terlambat)</option>
                            <option value="terlambat" <?php echo ($selected_status == 'terlambat') ? 'selected' : ''; ?>>Terlambat</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <th class="px-6 py-3 text-left">ID Peminjaman</th>
                                <th class="px-6 py-3 text-left">Nama Peminjam</th>
                                <th class="px-6 py-3 text-left">Judul Buku</th>
                                <th class="px-6 py-3 text-left">ISBN</th>
                                <th class="px-6 py-3 text-left">Penulis</th>
                                <th class="px-6 py-3 text-left">Tanggal Pinjam</th>
                                <th class="px-6 py-3 text-left">Batas Kembali</th>
                                <th class="px-6 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($peminjaman_data)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Tidak ada peminjaman aktif ditemukan</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($peminjaman_data as $peminjaman): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($peminjaman['Id_Peminjaman'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900 truncate max-w-[150px] sm:max-w-[200px]"><?php echo htmlspecialchars($peminjaman['Nama'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 truncate max-w-[150px] sm:max-w-[200px]"><?php echo htmlspecialchars($peminjaman['Judul'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($peminjaman['ISBN'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-[120px] sm:max-w-[150px]"><?php echo htmlspecialchars($peminjaman['Penulis'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars(date('d/m/Y', strtotime($peminjaman['Tanggal_Pinjam'] ?? 'now'))); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars(date('d/m/Y', strtotime($peminjaman['tanggal_kembali_seharusnya'] ?? 'now'))); ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php
                                        $display_status = $peminjaman['status_tampilan'] ?? 'N/A';
                                        $status_class = '';
                                        if ($display_status == 'Dipinjam') {
                                            $status_class = 'bg-blue-100 text-blue-800';
                                        } elseif ($display_status == 'Terlambat') {
                                            $status_class = 'bg-red-100 text-red-800';
                                        } else {
                                            $status_class = 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                        <span class="px-2 py-1 text-xs rounded-full <?php echo $status_class; ?>"><?php echo $display_status; ?></span>
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

    <div id="pinjamModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-lg w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Pinjam Buku Baru (Admin)</h3>
                    <button onclick="closePinjamModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Buku</label>
                        <input type="text" name="judul_buku" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan judul buku" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                        <input type="text" name="isbn_buku" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan ISBN buku" required>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Penulis</label>
                        <input type="text" name="penulis_buku" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan nama penulis">
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closePinjamModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Batal</button>
                        <button type="submit" name="pinjam_buku" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Pinjam Buku</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk membuka/menutup modal (jika modal Pinjam Buku digunakan)
        function openPinjamModal() {
            document.getElementById('pinjamModal').classList.remove('hidden');
        }

        function closePinjamModal() {
            document.getElementById('pinjamModal').classList.add('hidden');
        }

        // Fungsi untuk filter berdasarkan status
        function filterByStatus(status) {
            const url = new URL(window.location);
            // Pertahankan query pencarian yang sudah ada
            const currentSearch = url.searchParams.get('search');

            url.searchParams.delete('status'); // Hapus status lama
            if (status !== 'all') {
                url.searchParams.set('status', status);
            }
            // Pastikan parameter pencarian tetap ada
            if (currentSearch) {
                url.searchParams.set('search', currentSearch);
            }
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('Daftar Peminjaman Admin page loaded');

            // --- JavaScript untuk Sidebar Responsif ---
            const sidebar = document.getElementById('sidebar');
            const menuButton = document.getElementById('menuButton');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            if (menuButton && sidebar && sidebarOverlay) {
                menuButton.addEventListener('click', () => {
                    sidebar.classList.toggle('-translate-x-full');
                    sidebarOverlay.classList.toggle('hidden');
                    // Set opacity for smooth transition
                    setTimeout(() => {
                        sidebarOverlay.style.opacity = sidebar.classList.contains('-translate-x-full') ? '0' : '1';
                    }, 10);
                });

                sidebarOverlay.addEventListener('click', () => {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.style.opacity = '0';
                    setTimeout(() => {
                        sidebarOverlay.classList.add('hidden');
                    }, 300);
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 768) { // md breakpoint in Tailwind
                        sidebar.classList.remove('-translate-x-full');
                        sidebarOverlay.classList.add('hidden');
                        sidebarOverlay.style.opacity = '0';
                    }
                });
            }
            // --- Akhir JavaScript untuk Sidebar Responsif ---

            // --- JavaScript untuk Client-side Search (Optional jika sudah ada server-side) ---
            // Karena sekarang sudah ada server-side search (form GET),
            // bagian ini mungkin tidak sepenuhnya dibutuhkan untuk fungsionalitas pencarian,
            // tapi bisa tetap dipertahankan jika ada kebutuhan untuk live filtering tanpa reload halaman.
            // Jika ingin sepenuhnya server-side, bagian ini bisa dihapus.
            const searchInputMain = document.querySelector('main input[name="search"]'); 

            const liveSearchFunctionality = (inputElement) => {
                if (inputElement) {
                    inputElement.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const tableBody = document.querySelector('tbody');
                        const rows = tableBody.querySelectorAll('tr');
                        let foundRows = 0;

                        rows.forEach(row => {
                            // Skip the "Tidak ada peminjaman" row if it exists
                            if (row.cells.length === 1 && row.cells[0].getAttribute('colspan') === '8') {
                                row.style.display = 'none'; 
                                return;
                            }

                            const IDPeminjaman = row.cells[0]?.textContent.toLowerCase() || '';
                            const namaPeminjam = row.cells[1]?.textContent.toLowerCase() || '';
                            const judulBuku = row.cells[2]?.textContent.toLowerCase() || '';
                            const isbn = row.cells[3]?.textContent.toLowerCase() || '';
                            const penulis = row.cells[4]?.textContent.toLowerCase() || '';

                            // Only show rows that match the current selected status
                            const statusCell = row.cells[7]?.textContent.trim(); // Get the status text
                            const isStatusMatched = 
                                (document.querySelector('select[onchange="filterByStatus(this.value)"]').value === 'all') ||
                                (document.querySelector('select[onchange="filterByStatus(this.value)"]').value === 'dipinjam' && statusCell === 'Dipinjam') ||
                                (document.querySelector('select[onchange="filterByStatus(this.value)"]').value === 'terlambat' && statusCell === 'Terlambat');

                            if (isStatusMatched && 
                                (IDPeminjaman.includes(searchTerm) ||
                                namaPeminjam.includes(searchTerm) ||
                                judulBuku.includes(searchTerm) ||
                                isbn.includes(searchTerm) ||
                                penulis.includes(searchTerm))) {
                                row.style.display = '';
                                foundRows++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        const noDataRow = tableBody.querySelector('tr[colspan="8"]');
                        if (foundRows === 0) {
                            if (!noDataRow) {
                                const newRow = tableBody.insertRow();
                                const cell = newRow.insertCell();
                                cell.colSpan = 8;
                                cell.className = 'px-6 py-4 text-center text-gray-500';
                                cell.textContent = 'Tidak ada peminjaman aktif ditemukan';
                            } else {
                                noDataRow.style.display = '';
                            }
                        } else if (noDataRow) {
                            noDataRow.style.display = 'none';
                        }
                    });
                }
            };
            liveSearchFunctionality(searchInputMain);
            // --- Akhir JavaScript untuk Client-side Search ---

            // Auto-hide success/error messages
            const successMessageDiv = document.querySelector('.bg-green-100');
            const errorMessageDiv = document.querySelector('.bg-red-100');
            if (successMessageDiv) {
                setTimeout(() => { successMessageDiv.remove(); }, 5000);
            }
            if (errorMessageDiv) {
                setTimeout(() => { errorMessageDiv.remove(); }, 5000);
            }

            // Set search input value on page load if search query exists
            window.onload = function() {
                const urlParams = new URLSearchParams(window.location.search);
                const searchQuery = urlParams.get('search');
                if (searchQuery) {
                    if (searchInputMain) searchInputMain.value = searchQuery;
                }
            };
        });
    </script>
</body>
</html>