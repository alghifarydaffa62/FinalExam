<?php
session_start();
include '../konek.php'; 

$error_message = '';
$success_message = '';

if (isset($_SESSION['member_logged_in']) && $_SESSION['member_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($email) || empty($password)) {
        $error_message = "Email dan password tidak boleh kosong!";
    } else {
        try {
            $stmt = $conn->prepare("SELECT NRP, Nama, Email, Pwd FROM anggota WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $member = $result->fetch_assoc();
                
                if ($password === $member['Pwd']) {
                    $_SESSION['member_logged_in'] = true;
                    $_SESSION['member_id'] = $member['NRP'];
                    $_SESSION['member_name'] = $member['Nama'];
                    $_SESSION['member_email'] = $member['Email'];

                    if ($remember) {
                        $cookie_value = base64_encode($member['NRP'] . ':' . $member['Email']);
                        setcookie('member_remember', $cookie_value, time() + (30 * 24 * 60 * 60), '/'); 
                    }
                    
                    $success_message = "Login berhasil! Mengalihkan ke dashboard...";
                    
                    header("refresh:2;url=dashboard.php");
                } else {
                    $error_message = "Password salah!";
                }
            } else {
                $error_message = "Email tidak ditemukan!";
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

if (!isset($_SESSION['member_logged_in']) && isset($_COOKIE['member_remember'])) {
    $cookie_data = base64_decode($_COOKIE['member_remember']);
    $cookie_parts = explode(':', $cookie_data);
    
    if (count($cookie_parts) === 2) {
        $nrp = $cookie_parts[0];
        $email = $cookie_parts[1];
        
        try {
            $stmt = $conn->prepare("SELECT NRP, Nama, Email FROM anggota WHERE NRP = ? AND Email = ?");
            $stmt->bind_param("ss", $nrp, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $member = $result->fetch_assoc();
                $_SESSION['member_logged_in'] = true;
                $_SESSION['member_id'] = $member['NRP'];
                $_SESSION['member_name'] = $member['Nama'];
                $_SESSION['member_email'] = $member['Email'];
                
                header("Location: dashboardAnggota.php");
                exit();
            }
        } catch (Exception $e) {
            setcookie('member_remember', '', time() - 3600, '/');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../src/output.css" rel="stylesheet">
    <title>Login Admin Page</title>
    <style>
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body class="flex justify-center items-center min-h-screen bg-[#948979]">
    <div class="w-full max-w-sm">
        <a href="../home.php" class="bg-[#393E46] text-white py-2 px-6 rounded-md inline-block">
            Kembali ke home
        </a>

        <div class="bg-[#DFD0B8] p-8 rounded-lg shadow-md mt-6">
            <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Sign In Member - SiPerpus</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-5">
                <div>
                    <input type="email" id="email" name="email" placeholder="Email" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" />
                </div>

                <div>
                    <input type="password" id="password" name="password" placeholder="Password" required
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" />
                </div>

                <div class="mt-1">
                    <div class="flex items-center justify-between space-x-2">
                        <div>
                            <input type="checkbox" id="remember" name="remember" />
                            <label for="remember" class="text-sm text-gray-600">Remember me</label>
                        </div>

                        <a href="forgotPassword.php" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                    </div>

                    <button type="submit"
                        class="flex justify-center items-center w-full bg-[#393E46] text-white py-2 px-6 rounded-md hover:bg-[#2f3238] transition-colors mt-4">
                        Login
                    </button>

                    <div class="flex justify-center items-center mt-4 border-t pt-4">
                        <p class="text-center text-sm text-gray-700">
                            Login admin ?
                            <a href="../Admin/loginAdmin.php" class="text-blue-500 font-medium hover:underline ml-1">Klik disini</a>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        <?php if (!empty($success_message)): ?>
        setTimeout(function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>