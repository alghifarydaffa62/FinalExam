<?php
session_start();
include '../konek.php'; // Panggil file koneksi MySQLi lo

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

$books = []; // Inisialisasi array books
$error_message = ''; // Inisialisasi error message
$success_message = ''; // Inisialisasi success message

// Filter status - hanya dipinjam dan terlambat
$selected_status = $_GET['status'] ?? 'all';

// Query untuk ambil data peminjaman dari database
// Hanya ambil data dengan status_peminjaman = 'dipinjam'
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
WHERE p.status_peminjaman = 'dipinjam'"; // Hanya ambil yang statusnya 'dipinjam'

// INISIASI VARIABEL UNTUK KONDISI WHERE TAMBAHAN
$additional_conditions = [];

// Filter berdasarkan status (hanya untuk dipinjam dan terlambat)
if ($selected_status == 'dipinjam') {
    $additional_conditions[] = "p.Batas_waktu >= CURDATE()";
} elseif ($selected_status == 'terlambat') {
    $additional_conditions[] = "p.Batas_waktu < CURDATE()";
}
// Tidak ada kondisi untuk 'dikembalikan' karena sudah difilter di WHERE utama

// Tambahkan kondisi tambahan jika ada
if (!empty($additional_conditions)) {
    $sql .= " AND " . implode(" AND ", $additional_conditions);
}

// Tambahkan ORDER BY di akhir query
$sql .= " ORDER BY p.Tanggal_Pinjam DESC";

try {
    // Gunakan mysqli_prepare untuk eksekusi query
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result(); // Ambil hasil
    
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row; // Masukkan semua baris ke array $books
    }
    $stmt->close(); // Tutup statement
} catch (Exception $e) { // Tangkap Exception umum
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
                <a href="dashboardAdmin.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="kelolaBuku.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
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

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-[#DFD0B8] shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="font-bold text-lg">Peminjaman Buku</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64" placeholder="Cari buku...">
                            <button class="absolute right-2 top-2 text-gray-500">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                            <div class="text-sm">
                                <div class="font-medium"><?php echo htmlspecialchars($admin['name']); ?></div>
                                <div class="text-gray-500 text-xs">Admin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 ">
                <div class="flex justify-between items-center mb-6">
                    <div class="text-sm">
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

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-medium">Daftar Peminjaman Aktif</h2>
                </div>

                <div class="bg-white rounded-lg shadow-sm mb-6">

                    <div class="p-4 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <select class="border border-gray-300 rounded-md px-3 py-1 text-sm" onchange="filterByStatus(this.value)">
                                <option value="all" <?php echo ($selected_status == 'all') ? 'selected' : ''; ?>>Semua Peminjaman Aktif</option>
                                <option value="dipinjam" <?php echo ($selected_status == 'dipinjam') ? 'selected' : ''; ?>>Normal</option>
                                <option value="terlambat" <?php echo ($selected_status == 'terlambat') ? 'selected' : ''; ?>>Terlambat</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                                    <th class="px-6 py-3 text-left">Nama Anggota</th>
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
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada peminjaman aktif</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['Nama'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($book['Judul'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['ISBN'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($book['Penulis'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars(date('d/m/Y', strtotime($book['Tanggal_Pinjam'] ?? 'now'))); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars(date('d/m/Y', strtotime($book['tanggal_kembali_seharusnya'] ?? 'now'))); ?></td>
                                        <td class="px-6 py-4">
                                            <?php
                                            // Menggunakan alias status_tampilan dari query SQL
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
    </div>

    <script>
        function openPinjamModal() {
            document.getElementById('pinjamModal').classList.remove('hidden');
        }

        function closePinjamModal() {
            document.getElementById('pinjamModal').classList.add('hidden');
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

            const searchInput = document.querySelector('header input[type="text"]');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const tableBody = document.querySelector('tbody');
                const rows = tableBody.querySelectorAll('tr');

                rows.forEach(row => {
                    // Cari di kolom Judul (indeks 1) dan Nama Anggota (indeks 0)
                    const namaAnggota = row.cells[0]?.textContent.toLowerCase() || '';
                    const judulBuku = row.cells[1]?.textContent.toLowerCase() || '';

                    if (namaAnggota.includes(searchTerm) || judulBuku.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>