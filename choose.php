<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./src/output.css" rel="stylesheet">
    <title>Choose Role</title>
</head>

<body class="flex justify-center items-center min-h-screen m-6 bg-[#948979]">
    <div class="bg-[#DFD0B8] p-8 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Sign In </h2>

        <form action="#" method="POST" class="space-y-5">
            <div class="bg-[#948979] hover:bg-[#81786a] flex items-center border rounded-md px-3 py-2 w-full gap-2">
                <img src="./images/peoples.png" alt="admin icon" class="w-5 h-5"/>
                <a href="./Anggota/loginAnggota.php"
                    class="w-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none border-none bg-transparent">
                    Sign In Anggota</a>
            </div>
            <div class="bg-[#948979] hover:bg-[#81786a] flex items-center border rounded-md px-3 py-2 w-full gap-2">
                <img src="./images/admin.png" alt="admin icon" class="w-5 h-5"/>
                <a href="./Admin/loginAdmin.php"
                    class="w-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none border-none bg-transparent">Sign In Admin</a>
            </div>
        </form>
    </div>
</body>

</html>