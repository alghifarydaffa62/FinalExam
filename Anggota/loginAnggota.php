<?php
session_start(); // Start the session at the very beginning

// Database configuration
$servername = "localhost";
$username = "root"; // Sesuaikan dengan username database Anda
$password = "";     // Sesuaikan dengan password database Anda
$dbname = "perpustakaan"; // Sesuaikan dengan nama database Anda

$message = "";
$messageType = "";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($email) || empty($password)) {
        $message = "Email dan password harus diisi!";
        $messageType = "error";
    } else {
        // Prepare a select statement to fetch user data
        $stmt = $conn->prepare("SELECT NRP, Nama, Email, Pwd FROM anggota WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['Pwd'])) {
                // Login successful, set session variables
                $_SESSION['loggedin'] = true;
                $_SESSION['nrp'] = $user['NRP'];
                $_SESSION['namaLengkap'] = $user['Nama'];
                $_SESSION['email'] = $user['Email'];

                $message = "Login berhasil! Selamat datang, " . htmlspecialchars($user['Nama']) . ".";
                $messageType = "success";
                // Redirect to dashboard or home page after successful login
                header("Location: dashboard.php"); // Change to your desired redirection page
                exit();
            } else {
                $message = "Password salah!";
                $messageType = "error";
            }
        } else {
            $message = "Email belum terdaftar!";
            $messageType = "error";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../src/output.css" rel="stylesheet">
    <title>Login Anggota</title>
</head>
<body class="flex justify-center items-center min-h-screen m-6 bg-[#948979]">
    <div class="w-full max-w-sm">
      <a href="../home.php" class="bg-[#393E46] text-white py-2 px-6 rounded-md inline-block">
        Kembali ke home
      </a>

      <div class="bg-[#DFD0B8] p-8 rounded-lg shadow-md w-full max-w-sm mt-6">
          <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Sign In Member - SiPerpus</h2>
      
          <?php if (!empty($message)): ?>
              <div class="mb-4 p-3 rounded-md <?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                  <?php echo htmlspecialchars($message); ?>
              </div>
          <?php endif; ?>

          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-5">
              <div class="flex items-center border rounded-md px-3 py-2 w-full gap-2">
                
                <img src="../images/Group 1.png" alt="User Icon" class="w-5 h-5" />
                <input 
                  type="email" 
                  id="email" 
                  name="email" 
                  placeholder="Email"
                  value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                  class="w-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none border-none bg-transparent"
                  required />
              </div>

              <div class="flex items-center border rounded-md px-3 py-2 w-full gap-2">
                <img src="../images/Group 3 (1).png" alt="Lock Icon" class="w-5 h-5" />
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Password"
                    class="w-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none border-none bg-transparent"
                    required />
              </div>

              <div class="flex items-center justify-between mt-1">
                  <div class="flex items-center space-x-2">
                    <input type="checkbox" id="remember" name="remember" />
                    <label for="remember" class="text-sm text-gray-600">Remember me</label>
                  </div>

                  <p class="text-center text-sm text-gray-600">
                  Forgot password?
                  </p>
              </div>

              <button 
                  type="submit" 
                  class="flex justify-center items-center w-full bg-[#393E46] text-white py-2 px-6 rounded-md hover:bg-[#2f3238] transition-colors font-medium text-sm">
                  Login
              </button>

              <div class="flex flex-col gap-2 pt-4 border-t">
                <div class="flex justify-center items-center">
                  <p class="text-center text-sm text-gray-700">Login admin ?</p>
                  <a href="../Admin/loginAdmin.php" class="text-blue-500 text-sm hover:underline ml-1">Klik disini</a>
                </div>
                
                <div class="flex justify-center items-center">
                    <p class="text-center text-sm text-gray-700">Belum punya akun ?</p>
                    <a href="registerAnggota.php" class="text-blue-500 text-sm hover:underline ml-1">Register</a>
                </div>
              </div>
          </form>
        </div>
    </div>
</body>
</html>