<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./src/output.css" rel="stylesheet">
    <title>Login Admin Page</title>
</head>
<body class="flex justify-center items-center min-h-screen m-6 bg-[#948979]">
    <div class="bg-[#DFD0B8] p-8 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Login Admin</h2>
    
        <form action="#" method="POST" class="space-y-5">
          <div>
            <input type="text" id="username" name="username" placeholder="Username"
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"/>
          </div>

          <div>
            <input type="password" id="password" name="password" placeholder="Password"
              class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"/>
          </div>

          <div class="mt-1">
              <div class="flex items-center justify-between space-x-2">
                <div>
                  <input type="checkbox" id="remember" />
                  <label for="remember" class="text-sm text-gray-600">Remember me</label>
                </div>
    
                  <p class=" text-sm text-blue-600">
                    Forgot password?
                  </p>
              </div>

              <a href="dashboardAdmin.php" class="flex justify-center items-center w-full bg-[#393E46] text-white py-2 px-6 rounded-md hover:bg-[#2f3238] transition-colors mt-4">
                Login
              </a>

              <div class="flex justify-center items-center mt-4 border-t pt-4">
                  <p class="text-center text-sm text-gray-700">
                      Login Anggota ?
                      <a href="loginAnggota.php" class="text-blue-500 font-medium hover:underline ml-1">Klik disini</a>
                  </p>
              </div>

              <p class="text-center text-sm text-gray-700 mt-3">
                Belum punya akun ? 
                <a href="register.html" class="text-blue-500 font-medium hover:underline ml-1">Register</a>
              </p>
          </div>
        </form>
    </div>
</body>
</html>