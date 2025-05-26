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

function getBookStats($conn, $user_nrp) {
    $stats = [
        'total_buku' => 0,
        'totalPeminjaman' => 0
    ];

    $result = $conn->query("SELECT COUNT(*) as total FROM buku");
    if ($result) {
        $stats['total_buku'] = $result->fetch_assoc()['total'];
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as totalPeminjaman FROM peminjaman WHERE NRP = ? AND status_peminjaman = 'dipinjam'");
        $stmt->bind_param("s", $user_nrp);
        $stmt->execute();
        $peminjaman_result = $stmt->get_result();
        
        if ($peminjaman_result && $peminjaman_result->num_rows > 0) {
            $peminjaman_data = $peminjaman_result->fetch_assoc();
            $stats['totalPeminjaman'] = $peminjaman_data['totalPeminjaman'];
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error getting total peminjaman: " . $e->getMessage());
        $stats['totalPeminjaman'] = 0;
    }

    return $stats;
}

$book_stats = getBookStats($conn, $user_nrp);

$borrowing_history = [];
try {
    $stmt = $conn->prepare("
        SELECT b.Judul as title, 
               p.Tanggal_Pinjam as borrow_date, 
               p.Tanggal_Kembali as due_date,
               CASE 
                   WHEN p.status_peminjaman = 'dipinjam' THEN 'Dipinjam'
                   WHEN p.status_peminjaman = 'dikembalikan' THEN 'Dikembalikan'
                   ELSE 'Dipinjam'
               END as status,
               p.ID_Peminjaman as id
        FROM peminjaman p 
        JOIN buku b ON p.ID_Buku = b.ID_Buku 
        WHERE p.NRP = ? 
        ORDER BY p.Tanggal_Pinjam DESC 
        LIMIT 10
    ");
    $stmt->bind_param("s", $user_nrp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $borrow_date = date('d/m/Y', strtotime($row['borrow_date']));
        $due_date = date('d/m/Y', strtotime($row['due_date']));
        
        $borrowing_history[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'borrow_date' => $borrow_date,
            'due_date' => $due_date,
            'status' => $row['status']
        ];
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error getting borrowing history: " . $e->getMessage());

    $borrowing_history = [
        [
            'id' => 0,
            'title' => 'Harry Potter dan Batu Bertuah',
            'borrow_date' => '15/05/2025',
            'due_date' => '22/05/2025',
            'status' => 'Dipinjam'
        ],
        [
            'id' => 1,
            'title' => 'Laskar Pelangi',
            'borrow_date' => '10/05/2025',
            'due_date' => '17/05/2025',
            'status' => 'Dikembalikan'
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - SiPerpus</title>
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
                <a href="dashboard.php" class="flex items-center px-4 py-3 bg-[#948979] text-white">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="peminjaman.php" class="flex items-center px-4 py-3 hover:bg-[#948979] text-black">
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
                    <div class="font-bold text-lg">Dashboard Anggota</div>
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
            </header>

            <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAEC]">
                <h2 class="text-lg font-medium mb-6">Selamat datang, <?php echo htmlspecialchars($user['name']); ?>!</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col items-center justify-center hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-4">Pinjam</h3>
                        <a href="peminjaman.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-[#948979]">Pinjam Buku</a>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col items-center justify-center hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-4">Kembali</h3>
                        <a href="pengembalian.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-[#948979]">Kembalikan Buku</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Peminjaman</h3>
                        <p class="text-3xl font-bold text-[#948979]"><?php echo $book_stats['totalPeminjaman']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku yang sedang dipinjam</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-medium mb-2">Total Buku</h3>
                        <p class="text-3xl font-bold text-[#948979]"><?php echo $book_stats['total_buku']; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Buku yang tersedia di perpustakaan</p>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard Anggota loaded');
            console.log('User NRP: <?php echo $user_nrp; ?>');
            console.log('Total Peminjaman: <?php echo $book_stats['totalPeminjaman']; ?>');
        });
    </script>
</body>
</html>