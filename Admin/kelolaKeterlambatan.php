<?php
session_start();

$admin = [
    'name' => $_SESSION['admin_name'] ?? 'Admin',
    'id' => $_SESSION['admin_id'] ?? '1'
];

if (isset($_GET['logout'])) {
    session_destroy();
    
    if (isset($_COOKIE['admin_remember'])) {
        setcookie('admin_remember', '', time() - 3600, '/');
    }

    header("Location: loginAdmin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'edit_status') {
        $id = $_POST['id'];
        foreach ($late_returns as &$late) {
            if ($late['id'] === $id) {
                $late['status_denda'] = $_POST['status_denda'];
                break;
            }
        }
        $success_message = "Status pembayaran berhasil diperbarui!";
    }
}

$late_stats = [
    'total_terlambat' => 12,
    'belum_bayar' => 8
];

$late_returns = [
    
];

$search_query = $_GET['search'] ?? '';
if (!empty($search_query)) {
    $late_returns = array_filter($late_returns, function($late) use ($search_query) {
        return stripos($late['nama_anggota'], $search_query) !== false || 
               stripos($late['nim'], $search_query) !== false ||
               stripos($late['judul_buku'], $search_query) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Keterlambatan - SiPerpus</title>
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
                <a href="kelolaBuku.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Buku</span>
                </a>
                <a href="kelolaKeterlambatan.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
                    <i class="fas fa-clock w-6"></i>
                    <span class="ml-2">Keterlambatan</span>
                </a>
                <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-2">Anggota</span>
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
                    <div class="font-bold text-lg">Kelola Keterlambatan</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <form action="kelolaKeterlambatan.php" method="GET">
                                <input type="text" name="search" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64" 
                                    placeholder="Cari anggota atau buku..." value="<?php echo htmlspecialchars($search_query); ?>">
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
                <?php if (isset($success_message)): ?>
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mb-6">
                    <h2 class="text-lg font-medium">Data Keterlambatan Pengembalian</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Terlambat</h3>
                        <p class="text-3xl font-bold text-red-600"><?php echo $late_stats['total_terlambat']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Keterlambatan</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Belum Bayar Denda</h3>
                        <p class="text-3xl font-bold text-purple-600"><?php echo $late_stats['belum_bayar']; ?></p>
                        <div class="flex items-center mt-2 text-sm">
                            <span class="text-gray-500">Perlu tindak lanjut</span>
                        </div>
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
                                    <th class="px-4 py-3">Nama Anggota</th>
                                    <th class="px-4 py-3">NIM</th>
                                    <th class="px-4 py-3">Judul Buku</th>
                                    <th class="px-4 py-3">Tanggal Pinjam</th>
                                    <th class="px-4 py-3">Tanggal Kembali</th>
                                    <th class="px-4 py-3">Tanggal Dikembalikan</th>
                                    <th class="px-4 py-3">Hari Terlambat</th>
                                    <th class="px-4 py-3">Denda</th>
                                    <th class="px-4 py-3">Status Denda</th>
                                    <th class="px-4 py-3 rounded-tr-lg">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($late_returns as $index => $late): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($late['id']); ?></td>
                                        <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($late['nama_anggota']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($late['nim']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($late['judul_buku']); ?></td>
                                        <td class="px-4 py-3"><?php echo date('d/m/Y', strtotime($late['tanggal_pinjam'])); ?></td>
                                        <td class="px-4 py-3"><?php echo date('d/m/Y', strtotime($late['tanggal_kembali'])); ?></td>
                                        <td class="px-4 py-3"><?php echo date('d/m/Y', strtotime($late['tanggal_dikembalikan'])); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">
                                                <?php echo $late['hari_terlambat']; ?> hari
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-medium">Rp <?php echo number_format($late['denda'], 0, ',', '.'); ?></td>
                                        <td class="px-4 py-3">
                                            <?php if ($late['status_denda'] === 'Sudah Bayar'): ?>
                                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                    Sudah Bayar
                                                </span>
                                            <?php else: ?>
                                                <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">
                                                    Belum Bayar
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <button onclick="editStatus(<?php echo htmlspecialchars(json_encode($late)); ?>)" class="text-yellow-600 hover:text-yellow-900" title="Edit Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <div class="text-sm text-gray-500">
                            Menampilkan 1 - 6 dari 12 data keterlambatan
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                            <a href="#" class="bg-blue-600 text-white px-3 py-1 rounded-md">1</a>
                            <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">2</a>
                            <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="editStatusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4 w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Edit Status Pembayaran</h3>
                <button onclick="closeEditStatusModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editStatusForm" method="POST">
                <input type="hidden" name="action" value="edit_status">
                <input type="hidden" name="id" id="editStatusId">
                
                <div class="space-y-4">
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-2">Detail Keterlambatan:</span>
                        <div id="editStatusDetails" class="bg-gray-50 p-3 rounded text-sm">

                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                        <select name="status_denda" id="editStatusDenda" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Belum Bayar">Belum Bayar</option>
                            <option value="Sudah Bayar">Sudah Bayar</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditStatusModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editStatus(late) {
            const modal = document.getElementById('editStatusModal');
            const details = document.getElementById('editStatusDetails');

            document.getElementById('editStatusId').value = late.id;
            document.getElementById('editStatusDenda').value = late.status_denda;

            details.innerHTML = `
                <div class="space-y-2">
                    <div><strong>Nama:</strong> ${late.nama_anggota}</div>
                    <div><strong>NIM:</strong> ${late.nim}</div>
                    <div><strong>Buku:</strong> ${late.judul_buku}</div>
                    <div><strong>Hari Terlambat:</strong> ${late.hari_terlambat} hari</div>
                    <div><strong>Denda:</strong> Rp ${parseInt(late.denda).toLocaleString('id-ID')}</div>
                </div>
            `;
            
            modal.classList.remove('hidden');
        }
        
        function closeEditStatusModal() {
            const modal = document.getElementById('editStatusModal');
            modal.classList.add('hidden');
        }

        window.onclick = function(event) {
            const editStatusModal = document.getElementById('editStatusModal');
            
            if (event.target === editStatusModal) {
                closeEditStatusModal();
            }
        };
    </script>
</body>
</html>