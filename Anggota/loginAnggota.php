<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../src/output.css" rel="stylesheet">
    <title>Login Anggota</title>
</head>
<body class="flex justify-center items-center min-h-screen m-6 bg-[#948979]">
    <div class="bg-[#DFD0B8] p-8 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Sign In Member - SiPerpus</h2>
    
        <form action="#" method="POST" class="space-y-5">
            <div class="flex items-center border rounded-md px-3 py-2 w-full gap-2">
              <img src="../images/Group 1.png" alt="User Icon" class="w-5 h-5" />
              <input 
                type="email" 
                id="email" 
                name="email" 
                placeholder="Email"
                class="w-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none border-none bg-transparent" />
            </div>

            <div class="flex items-center border rounded-md px-3 py-2 w-full gap-2">
              <img src="../images/Group 3 (1).png" alt="User Icon" />
              <input 
                  type="password" 
                  id="password" 
                  name="password" 
                  placeholder="Password"
                  class="w-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none" />
            </div>

            <div class="flex items-center space-x-23 mt-1">
                <div class="flex items-center space-x-2">
                  <input type="checkbox" id="remember" />
                  <label for="remember" class="text-sm text-gray-600">Remember me</label>
                </div>

                <p class="text-center text-sm text-gray-600">
                Forgot password?
                </p>
            </div>

            <a href="dashboard.php" class="flex justify-center items-center w-full bg-[#393E46] text-white py-2 px-6 rounded-md hover:bg-[#2f3238] transition-colors">
            Login
            </a>

            <div class="flex flex-col gap-2 pt-4 border-t">
              <div class="flex justify-center items-center">
                <p class="text-center text-sm text-gray-700">Login admin ?</p>
                <a href="../Admin/loginAdmin.php" class="text-blue-500 text-sm hover:underline ml-1">Klik disini</a>
              </div>
              
              <div class="flex justify-center items-center">
                  <p class="text-center text-sm text-gray-700">Belum punya akun ?</p>
                  <a href="register.html" class="text-blue-500 text-sm hover:underline ml-1">Register</a>
              </div>
            </div>
        </form>
      </div>
</body>
</html>
