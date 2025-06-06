<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./src/output.css" rel="stylesheet">
    <title>Choose Role</title>
</head>

<body class="flex justify-center items-center h-[80vh] m-6 bg-[#948979]">
    <div class="w-full max-w-sm">
         <a href="home.php" class="bg-[#393E46] text-white py-2 px-6 rounded-md inline-block">
            Kembali ke home
        </a>
        <div class="bg-white p-8 rounded-lg shadow-md mt-4">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Sign In</h2>

            <form action="#" method="POST" class="space-y-5">
                <div class="bg-[#948979] hover:bg-[#81786a] flex items-center border rounded-md px-3 py-2 w-full gap-2">
                    <img src="./images/people2.png" alt="admin icon" class="w-5 h-5"/>
                    <a href="./Anggota/loginAnggota.php"
                        class="w-full text-sm  text-white placeholder-gray-400 focus:outline-none border-none bg-transparent">
                        Sign In Anggota</a>
                </div>
                <div class="bg-[#948979] hover:bg-[#81786a] flex items-center border rounded-md px-3 py-2 w-full gap-2">
                    <img src="./images/admin2.png" alt="admin icon" class="w-5 h-5"/>
                    <a href="./Admin/loginAdmin.php"
                        class="w-full text-sm text-white placeholder-gray-400 focus:outline-none border-none bg-transparent">Sign In Admin</a>
                </div>
            </form>
        </div>
    </div>
   
</body>

</html>