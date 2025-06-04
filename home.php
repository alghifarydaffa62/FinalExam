<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPerpus</title>
    <link rel="stylesheet" href="./src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#DFD0B8]">
    <nav class="bg-[#948979] shadow-sm relative">
        <div class="flex justify-around items-center py-4 px-4">
            <div class="flex items-center gap-2">
                <div class="bg-[#393E46] p-2 rounded">
                    <span class="font-bold text-white">SP</span>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold">SiPerpus</h1>
                    <p class="text-xs sm:text-sm">Sistem Perpustakaan Digital</p>
                </div>
            </div>

            <ul class="hidden sm:flex gap-6 text-lg">
                <li>
                    <a href="home.php" class="text-white font-semibold hover:text-gray-200 transition-colors">Home</a>
                </li>
                <li>
                    <a href="choose.php" class="px-6 py-2 text-white font-semibold hover:text-gray-200 transition-colors">Login</a>
                </li>
                <li>
                    <a href="./Anggota/registerAnggota.php" class="px-6 py-2 bg-[#393E46] rounded-md text-white font-semibold hover:bg-[#2a2f35] transition-colors">Register</a>
                </li>
            </ul>

            <button id="hamburger" class="sm:hidden text-white text-xl p-2 hover:text-gray-200 transition-colors">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div id="mobileMenu" class="sm:hidden hidden absolute top-full left-0 right-0 bg-[#948979] border-t border-[#7a7365] shadow-lg z-50">
            <ul class="flex flex-col py-4 text-center">
                <li>
                    <a href="home.php" class="block mx-6 px-6 py-3 text-white font-semibold hover:bg-[#7a7365] transition-colors">Home</a>
                </li>
                <li>
                    <a href="choose.php" class="block mx-6 my-2 px-4 py-2 text-white font-semibold hover:bg-[#7a7365] transition-colors text-center">Login</a>
                </li>
                <li>
                    <a href="./Anggota/registerAnggota.php" class="block mx-6 my-2 px-4 py-2 bg-[#393E46] rounded-md text-white font-semibold hover:bg-[#2a2f35] transition-colors text-center">Register</a>
                </li>
            </ul>
        </div>
    </nav>

    <section class="min-h-[70vh] sm:h-[80vh] flex items-center justify-center px-4 py-8 sm:py-0">
        <div class="text-center max-w-4xl mx-auto">
            <h1 class="text-[1.7rem] sm:text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
                Kelola Perpustakaan Anda<br />
                dengan Mudah dan Efisien
            </h1>
            <p class="mt-4 sm:mt-5 text-base sm:text-lg md:text-xl px-4 sm:px-0">
                SiPerpus adalah sistem perpustakaan digital terpadu yang memudahkan<br class="hidden sm:block" />
                pengoleksian buku, peminjaman dan anggota perpustakan anda
            </p>
            <div class="mt-6 sm:mt-8">
                <a
                    href="./Anggota/registerAnggota.php"
                    class="inline-block bg-[#393E46] px-6 sm:px-8 py-3 sm:py-4 rounded-lg text-white font-semibold hover:bg-[#2a2f35] transition-colors text-sm sm:text-base"
                >Daftar anggota</a>
            </div>
        </div>
    </section>
    
    
    <script>
        const hamburger = document.getElementById('hamburger');
        const mobileMenu = document.getElementById('mobileMenu');
        const hamburgerIcon = hamburger.querySelector('i');

        hamburger.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');

            if (mobileMenu.classList.contains('hidden')) {
                hamburgerIcon.className = 'fas fa-bars';
            } else {
                hamburgerIcon.className = 'fas fa-times';
            }
        });

        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                    hamburgerIcon.className = 'fas fa-bars';
                }
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 640) { 
                mobileMenu.classList.add('hidden');
                hamburgerIcon.className = 'fas fa-bars';
            }
        });
    </script>
</body>
</html>