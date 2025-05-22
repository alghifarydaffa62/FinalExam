<?php
session_start();

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: loginAdmin.php");
//     exit;
// }

$admin = [
    'name' => $_SESSION['admin_name'] ?? 'Admin',
    'id' => $_SESSION['admin_id'] ?? '1'
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'edit_status') {
        // Update payment status
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
    [
        'id' => '1',
        'nama_anggota' => 'Ahmad Wijaya',
        'nim' => '12345678',
        'judul_buku' => 'Harry Potter dan Batu Bertuah',
        'tanggal_pinjam' => '2024-01-15',
        'tanggal_kembali' => '2024-01-29',
        'tanggal_dikembalikan' => '2024-02-05',
        'hari_terlambat' => 7,
        'denda' => 35000,
        'status_denda' => 'Belum Bayar'
    ],
    [
        'id' => '2',
        'nama_anggota' => 'Siti Nurhaliza',
        'nim' => '87654321',
        'judul_buku' => 'Laskar Pelangi',
        'tanggal_pinjam' => '2024-01-10',
        'tanggal_kembali' => '2024-01-24',
        'tanggal_dikembalikan' => '2024-01-28',
        'hari_terlambat' => 4,
        'denda' => 20000,
        'status_denda' => 'Sudah Bayar'
    ],
    [
        'id' => '3',
        'nama_anggota' => 'Budi Santoso',
        'nim' => '11223344',
        'judul_buku' => 'Filosofi Teras',
        'tanggal_pinjam' => '2024-01-20',
        'tanggal_kembali' => '2024-02-03',
        'tanggal_dikembalikan' => '2024-02-12',
        'hari_terlambat' => 9,
        'denda' => 45000,
        'status_denda' => 'Belum Bayar'
    ],
    [
        'id' => '4',
        'nama_anggota' => 'Maria Gonzalez',
        'nim' => '55667788',
        'judul_buku' => 'Bumi Manusia',
        'tanggal_pinjam' => '2024-01-25',
        'tanggal_kembali' => '2024-02-08',
        'tanggal_dikembalikan' => '2024-02-11',
        'hari_terlambat' => 3,
        'denda' => 15000,
        'status_denda' => 'Sudah Bayar'
    ],
    [
        'id' => '5',
        'nama_anggota' => 'Rizky Pratama',
        'nim' => '99887766',
        'judul_buku' => 'Rich Dad Poor Dad',
        'tanggal_pinjam' => '2024-02-01',
        'tanggal_kembali' => '2024-02-15',
        'tanggal_dikembalikan' => '2024-02-22',
        'hari_terlambat' => 7,
        'denda' => 35000,
        'status_denda' => 'Belum Bayar'
    ],
    [
        'id' => '6',
        'nama_anggota' => 'Linda Sari',
        'nim' => '44332211',
        'judul_buku' => 'Atomic Habits',
        'tanggal_pinjam' => '2024-02-05',
        'tanggal_kembali' => '2024-02-19',
        'tanggal_dikembalikan' => '2024-02-25',
        'hari_terlambat' => 6,
        'denda' => 30000,
        'status_denda' => 'Belum Bayar'
    ]
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
<body class="bg-blue-100">
    <div class="flex h-screen">
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
                <a href="dashboardAdmin.php" class="flex items-center px-4 py-3 hover:bg-blue-700 text-black">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="kelolaBuku.php" class="flex items-center px-4 py-3 hover:bg-blue-700 text-black">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Buku</span>
                </a>
                <a href="kelolaKeterlambatan.php" class="flex items-center px-4 py-3 bg-blue-700 text-white">
                    <i class="fas fa-clock w-6"></i>
                    <span class="ml-2">Keterlambatan</span>
                </a>
                <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 hover:bg-blue-700 text-black">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-2">Anggota</span>
                </a>
                    <a href="loginAdmin.php" class="flex items-center px-3 py-3 hover:bg-blue-700 text-black mt-60">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-2">Logout</span>
                 </a>
                </a>
            </nav>
        </div>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm z-10">
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

            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
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
                        <div class="flex items-center mt-2 text-sm">
                            <span class="text-red-500"><i class="fas fa-arrow-up mr-1"></i>8%</span>
                            <span class="text-gray-500 ml-2">dari bulan lalu</span>
                        </div>
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

    <!-- Edit Status Modal -->
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
                            <!-- Details will be filled by JavaScript -->
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
        // Edit Status
        function editStatus(late) {
            const modal = document.getElementById('editStatusModal');
            const details = document.getElementById('editStatusDetails');
            
            // Fill form with current data
            document.getElementById('editStatusId').value = late.id;
            document.getElementById('editStatusDenda').value = late.status_denda;
            
            // Fill details
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

        // Close modal when clicking outside
        window.onclick = function(event) {
            const editStatusModal = document.getElementById('editStatusModal');
            
            if (event.target === editStatusModal) {
                closeEditStatusModal();
            }
        };
    </script>
</body>
</html>