<?php
session_start();

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

$member_stats = [
    'total_anggota' => 89
];

$members = [
    
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'edit') {
            $id = $_POST['id'];
            foreach ($members as &$member) {
                if ($member['id'] === $id) {
                    $member['nrp'] = $_POST['nrp'];
                    $member['nama'] = $_POST['nama'];
                    $member['email'] = $_POST['email'];
                    $member['jurusan'] = $_POST['jurusan'];
                    $member['no_telp'] = $_POST['no_telp'];
                    break;
                }
            }
            $success_message = "Data anggota berhasil diperbarui!";
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            $members = array_filter($members, function($member) use ($id) {
                return $member['id'] !== $id;
            });
            $member_stats['total_anggota']--;
            $success_message = "Anggota berhasil dihapus!";
        }
    }
}

$search_query = $_GET['search'] ?? '';
if (!empty($search_query)) {
    $members = array_filter($members, function($member) use ($search_query) {
        return stripos($member['nama'], $search_query) !== false || 
               stripos($member['nrp'], $search_query) !== false ||
               stripos($member['email'], $search_query) !== false ||
               stripos($member['jurusan'], $search_query) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota - SiPerpus</title>
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
                <a href="kelolaAnggota.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
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

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-[#DFD0B8] shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="font-bold text-lg">Kelola Anggota</div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <form action="kelolaAnggota.php" method="GET">
                                <input type="text" name="search" class="bg-gray-100 rounded-lg px-4 py-2 pr-8 w-64" 
                                    placeholder="Cari anggota..." value="<?php echo htmlspecialchars($search_query); ?>">
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
                    <h2 class="text-lg font-medium">Daftar Anggota Perpustakaan</h2>
                </div>

                <div class="mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition max-w-sm">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Anggota</h3>
                        <p class="text-3xl font-bold text-[#393E46]"><?php echo $member_stats['total_anggota']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Anggota terdaftar</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-end mb-6">
                        <select class="bg-white text-gray-700 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        <option class="bg-white text-gray-700 hover:bg-[#393E46]" value="10">10 per halaman</option>
                        <option class="bg-white text-gray-700" value="25">25 per halaman</option>
                        <option class="bg-white text-gray-700" value="50">50 per halaman</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-lg">ID</th>
                                    <th class="px-4 py-3">NRP</th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Jurusan</th>
                                    <th class="px-4 py-3">No. Telp</th>
                                    <th class="px-4 py-3 rounded-tr-lg">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $index => $member): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($member['id']); ?></td>
                                        <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($member['nrp']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($member['nama']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($member['email']); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="bg-blue-100 text-[#393E46] text-xs px-2 py-1 rounded-full">
                                                <?php echo htmlspecialchars($member['jurusan']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($member['no_telp']); ?></td>
                                        <td class="px-4 py-3">
                                            <div class="flex space-x-2">
                                                <button onclick="viewMember(<?php echo htmlspecialchars(json_encode($member)); ?>)" class="text-blue-600 hover:text-blue-900" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="editMember(<?php echo htmlspecialchars(json_encode($member)); ?>)" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="confirmDelete(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['nama']); ?>')" class="text-red-600 hover:text-red-900" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <div class="text-sm text-gray-500">
                            Menampilkan 1 - 6 dari 89 anggota
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                            <a href="#" class="bg-[#393E46] text-white px-3 py-1 rounded-md">1</a>
                            <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">2</a>
                            <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">3</a>
                            <span class="text-gray-500">...</span>
                            <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">15</a>
                            <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md hover:bg-gray-200">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4 w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Detail Anggota</h3>
                <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="detailContent">

            </div>
            <div class="flex justify-end mt-6">
                <button onclick="closeDetailModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg">Tutup</button>
            </div>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4 w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Edit Anggota</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NRP</label>
                        <input type="text" name="nrp" id="editNrp" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                        <input type="text" name="nama" id="editNama" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="editEmail" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan</label>
                        <select name="jurusan" id="editJurusan" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Teknik Informatika">Teknik Informatika</option>
                            <option value="Sastra Indonesia">Sastra Indonesia</option>
                            <option value="Manajemen">Manajemen</option>
                            <option value="Psikologi">Psikologi</option>
                            <option value="Ekonomi">Ekonomi</option>
                            <option value="Hukum">Hukum</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                        <input type="text" name="no_telp" id="editNoTelp" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#393E46] text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-auto">
            <h3 class="text-lg font-medium mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus anggota <span id="deleteMemberName" class="font-semibold"></span>? Tindakan ini tidak dapat dibatalkan.</p>
            <form id="deleteForm" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function viewMember(member) {
            const modal = document.getElementById('detailModal');
            const content = document.getElementById('detailContent');
            
            content.innerHTML = `
                <div class="space-y-3">
                    <div>
                        <span class="font-medium text-gray-700">ID:</span>
                        <span class="ml-2">${member.id}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">NRP:</span>
                        <span class="ml-2">${member.nrp}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Nama:</span>
                        <span class="ml-2">${member.nama}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Email:</span>
                        <span class="ml-2">${member.email}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Jurusan:</span>
                        <span class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">${member.jurusan}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">No. Telepon:</span>
                        <span class="ml-2">${member.no_telp}</span>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
        }
        
        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            modal.classList.add('hidden');
        }

        function editMember(member) {
            const modal = document.getElementById('editModal');

            document.getElementById('editId').value = member.id;
            document.getElementById('editNrp').value = member.nrp;
            document.getElementById('editNama').value = member.nama;
            document.getElementById('editEmail').value = member.email;
            document.getElementById('editJurusan').value = member.jurusan;
            document.getElementById('editNoTelp').value = member.no_telp;
            
            modal.classList.remove('hidden');
        }
        
        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.add('hidden');
        }

        function confirmDelete(id, name) {
            const modal = document.getElementById('deleteModal');
            
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteMemberName').textContent = name;
            
            modal.classList.remove('hidden');
        }
        
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
        }

        window.onclick = function(event) {
            const detailModal = document.getElementById('detailModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === detailModal) {
                closeDetailModal();
            } else if (event.target === editModal) {
                closeEditModal();
            } else if (event.target === deleteModal) {
                closeDeleteModal();
            }
        };
    </script>
</body>
</html>