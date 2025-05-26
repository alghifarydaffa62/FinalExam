<?php

include '../konek.php';

$message = "";
$messageType = "";
$formData = array(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nrp = trim($_POST['nrp']);
    $nama = trim($_POST['namaLengkap']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $jurusan = trim($_POST['jurusan']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $gender = trim($_POST['gender']);

    // Process gender - convert to uppercase
    $gender = strtoupper($gender);
    
    // Process phone number - ensure it starts with +62
    if (!empty($phoneNumber)) {
        // Remove all non-numeric characters except +
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // If starts with 0, replace with +62
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '+62' . substr($cleanPhone, 1);
        }
        // If doesn't start with +62, add it (for cases like 85546...)
        elseif (substr($cleanPhone, 0, 3) !== '+62') {
            $cleanPhone = '+62' . $cleanPhone;
        }
        
        $phoneNumber = $cleanPhone;
    }

    $formData = array(
        'nrp' => $nrp,
        'namaLengkap' => $nama,
        'email' => $email,
        'jurusan' => $jurusan,
        'phoneNumber' => $phoneNumber,
        'gender' => $gender
    );

    if (empty($nrp) || empty($nama) || empty($email) || empty($password) || empty($jurusan) || empty($phoneNumber) || empty($gender)) {
        $message = "Semua field harus diisi!";
        $messageType = "error";
    } elseif (!in_array($gender, ['L', 'P'])) {
        $message = "Jenis kelamin harus L (Laki-laki) atau P (Perempuan)!";
        $messageType = "error";
    } else {
        // Validate phone number length (digits after +62)
        $phoneDigitsOnly = preg_replace('/[^0-9]/', '', $phoneNumber);
        $phoneAfter62 = substr($phoneDigitsOnly, 2); // Remove "62" from beginning
        
        if (strlen($phoneAfter62) > 12) {
            $message = "Nomor telepon setelah kode negara tidak boleh lebih dari 12 digit!";
            $messageType = "error";
        } else {
        $checkStmt = $conn->prepare("SELECT NRP FROM anggota WHERE NRP = ?");
        $checkStmt->bind_param("s", $nrp);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = "NRP sudah terdaftar! Silakan gunakan NRP yang berbeda.";
            $messageType = "error";
        } else {
            $checkEmailStmt = $conn->prepare("SELECT Email FROM anggota WHERE Email = ?");
            $checkEmailStmt->bind_param("s", $email);
            $checkEmailStmt->execute();
            $emailResult = $checkEmailStmt->get_result();
            
            if ($emailResult->num_rows > 0) {
                $message = "Email sudah terdaftar! Silakan gunakan email yang berbeda.";
                $messageType = "error";
            } else {
                // Fix: Remove extra parameter - only 7 parameters needed, not 8
                $stmt = $conn->prepare("INSERT INTO anggota (NRP, Nama, Email, Pwd, Jurusan, No_Telp, Jenis_kelamin) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $nrp, $nama, $email, $password, $jurusan, $phoneNumber, $gender);
                
                if ($stmt->execute()) {
                    $message = "Pendaftaran berhasil! Selamat datang di SiPerpus.";
                    $messageType = "success";
                    $formData = array();
                } else {
                    $message = "Error: " . $stmt->error;
                    $messageType = "error";
                }            
                $stmt->close();
            }
            $checkEmailStmt->close();
        }
        $checkStmt->close();
    }
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
    <title>Register Anggota Perpustakaan</title>
</head>
<body class="flex justify-center items-center min-h-screen p-6 bg-[#948979]">
    <div class="w-full max-w-sm">
        <a href="../home.php" class="bg-[#393E46] text-white py-2 px-6 rounded-md inline-block">
            Kembali ke home
        </a>

        <div class="bg-[#DFD0B8] p-6 rounded-lg shadow-lg w-full max-w-sm mt-6">
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-700 mb-1">Daftar Anggota - SiPerpus</h2>
            </div>

            <?php if (!empty($message)): ?>
                <div class="mb-4 p-6 rounded-md <?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="namaLengkap" 
                        name="namaLengkap" 
                        placeholder="Daffa Al Ghifary"
                        value="<?php echo isset($formData['namaLengkap']) ? htmlspecialchars($formData['namaLengkap']) : ''; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-[#948979] focus:border-transparent bg-white"
                        required />
                </div>

                <div>
                    <label class="block text-xs text-gray-600 mb-1">Jurusan</label>
                    <div class="relative">
                        <button onclick="toggleDropdown()" type="button" id="dropdownButton" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#948979] focus:border-transparent text-left">
                            <span id="selectedOption"><?php echo isset($formData['jurusan']) && !empty($formData['jurusan']) ? htmlspecialchars($formData['jurusan']) : 'Pilih Jurusan'; ?></span>
                        </button>
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                            <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>

                        <div id="dropdownMenu" class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg hidden">
                            <div class="py-1 max-h-48 overflow-y-auto">
                                <a href="#" onclick="selectOption('Teknik Informatika')" class="block px-3 py-2 text-sm text-gray-700 hover:bg-[#DFD0B8] hover:text-gray-900">Teknik Informatika</a>
                                <a href="#" onclick="selectOption('Sains Data')" class="block px-3 py-2 text-sm text-gray-700 hover:bg-[#DFD0B8] hover:text-gray-900">Sains Data</a>
                                <a href="#" onclick="selectOption('Teknik Komputer')" class="block px-3 py-2 text-sm text-gray-700 hover:bg-[#DFD0B8] hover:text-gray-900">Teknik Komputer</a>
                                <a href="#" onclick="selectOption('Teknik Elektro')" class="block px-3 py-2 text-sm text-gray-700 hover:bg-[#DFD0B8] hover:text-gray-900">Teknik Elektro</a>
                                <a href="#" onclick="selectOption('Akuntansi')" class="block px-3 py-2 text-sm text-gray-700 hover:bg-[#DFD0B8] hover:text-gray-900">Akuntansi</a>
                                <a href="#" onclick="selectOption('Manajemen')" class="block px-3 py-2 text-sm text-gray-700 hover:bg-[#DFD0B8] hover:text-gray-900">Manajemen</a>
                            </div>
                        </div>
                        <input type="hidden" id="jurusan" name="jurusan" value="<?php echo isset($formData['jurusan']) ? htmlspecialchars($formData['jurusan']) : ''; ?>" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-600 mb-1">NRP</label>
                    <input 
                        type="text" 
                        id="nrp" 
                        name="nrp" 
                        placeholder="3124....."
                        pattern="[0-9]{10}"
                        maxlength="10"
                        value="<?php echo isset($formData['nrp']) ? htmlspecialchars($formData['nrp']) : ''; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-[#948979] focus:border-transparent bg-white"
                        required />
                </div>

                <div>
                    <label class="block text-xs text-gray-600 mb-1">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="daffa@student.edu"
                        value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-[#948979] focus:border-transparent bg-white"
                        required />
                </div>

                <div>
                    <label class="block text-xs text-gray-600 mb-1">Password</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Minimal 8 karakter"
                            minlength="8"
                            class="w-full px-3 py-2 pr-8 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-[#948979] focus:border-transparent bg-white"
                            required />
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-2 flex items-center">
                            <svg id="eyeIcon" class="h-3 w-3 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-600 mb-1">Nomor Telepon</label>
                    <input 
                        type="tel" 
                        id="phoneNumber" 
                        name="phoneNumber" 
                        placeholder="085123456789 atau +6285123456789"
                        oninput="validatePhoneInput(this)"
                        value="<?php echo isset($formData['phoneNumber']) ? htmlspecialchars($formData['phoneNumber']) : ''; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-[#948979] focus:border-transparent bg-white"
                        required />
                </div>

                <div>
                    <label class="block text-xs text-gray-600 mb-1">Jenis Kelamin</label>
                    <div class="relative">
                        <select 
                            id="gender" 
                            name="gender" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-[#948979] focus:border-transparent bg-white"
                            required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?php echo (isset($formData['gender']) && $formData['gender'] === 'L') ? 'selected' : ''; ?>>L - Laki-laki</option>
                            <option value="P" <?php echo (isset($formData['gender']) && $formData['gender'] === 'P') ? 'selected' : ''; ?>>P - Perempuan</option>
                        </select>
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-[#393E46] text-white py-2 px-4 rounded-md hover:bg-[#2f3238] focus:outline-none focus:ring-1 focus:ring-[#393E46] font-medium text-sm">
                    Daftar Sekarang
                </button>

                <div class="text-center mt-4">
                    <p class="text-md text-gray-600">
                        Sudah punya akun? 
                        <a href="../choose.php" class="text-[#948979] hover:underline font-medium">Masuk di sini</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jurusanValue = document.getElementById('jurusan').value;
            if (jurusanValue) {
                document.getElementById('selectedOption').textContent = jurusanValue;
            }
        });

        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('hidden');
        }

        function selectOption(option) {
            document.getElementById('selectedOption').textContent = option;
            document.getElementById('jurusan').value = option;
            document.getElementById('dropdownMenu').classList.add('hidden');
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }

        function validatePhoneInput(input) {
            // Remove non-numeric characters except +
            let value = input.value.replace(/[^0-9+]/g, '');
            
            // Handle different input formats
            if (value.startsWith('0')) {
                // Convert 0xxx to +62xxx
                let digitsAfterZero = value.substring(1);
                if (digitsAfterZero.length > 12) {
                    digitsAfterZero = digitsAfterZero.substring(0, 12);
                }
                value = '+62' + digitsAfterZero;
            } else if (value.startsWith('+62')) {
                // Already has +62, limit digits after +62
                let digitsAfter62 = value.substring(3);
                if (digitsAfter62.length > 12) {
                    digitsAfter62 = digitsAfter62.substring(0, 12);
                }
                value = '+62' + digitsAfter62;
            } else if (value.match(/^[0-9]/)) {
                // Pure numbers without 0 prefix (like 85123...)
                if (value.length > 12) {
                    value = value.substring(0, 12);
                }
                value = '+62' + value;
            }
            
            input.value = value;
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdownMenu');
            const button = document.getElementById('dropdownButton');
            
            if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>