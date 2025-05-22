<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./src/output.css">
</head>
<body class="bg-violet-200">
    <nav class="flex justify-around items-center py-4 bg-white shadow-sm">
        <div class="flex items-center gap-2">
            <img src="./images/Logo.png" alt="" class="w-11 h-11">
            <div>
                <h1 class="text-2xl font-bold">SiPerpus</h1>
                <p class="text-sm">Sistem Perpustakaan Digital</p>
            </div>
        </div>

        <ul class="flex list-style-none gap-6 text-lg">
            <li>
                <a href="home.php">Home</a>
            </li>
            <li>
                <a href="choose.php" class="px-6 py-2 bg-violet-300 rounded-md text-violet-700 font-semibold">Login</a>
            </li>
            <li>
                <a href="" class="px-6 py-2 bg-violet-600 rounded-md text-white font-semibold">Register</a>
            </li>
        </ul>
    </nav>

    <section class="h-[80vh] flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-6xl font-bold">
            Kelola Perpustakaan Anda<br />
            dengan Mudah dan Efisien
            </h1>
            <p class="mt-5 text-xl">
            SiPerpus adalah sistem perpustakaan digital terpadu yang memudahkan<br />
            pengoleksian buku, peminjaman dan anggota perpustakan anda
            </p>
            <div class="mt-8">
            <a
                href="./Anggota/loginAnggota.php"
                class="bg-sky-800 p-4 rounded-lg text-white font-semibold"
                >Daftar anggota</a
            >
            </div>
        </div>
    </section>
    
</body>
</html>