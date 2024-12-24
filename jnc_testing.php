<?php
ob_start();

$servername = "localhost";
$username = "nucfrkvh_CloudEncryp";
$password = "tjyrXKCafhPA8pEc4bsL";
$dbname = "nucfrkvh_CloudEncryp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to encrypt data
function encrypt($data, $key, $originalExtension) {
    $cipher = "aes-256-cbc";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
    $encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);
    $encoded = base64_encode($iv . $encrypted);

    // Save the original file extension in the encrypted data
    return $originalExtension . ':' . $encoded;
}

// Function to decrypt data
function decrypt($data, $key) {
    // Extract the original file extension
    list($originalExtension, $encoded) = explode(':', $data, 2);
    
    $cipher = "aes-256-cbc";
    $decoded = base64_decode($encoded);
    $iv = substr($decoded, 0, openssl_cipher_iv_length($cipher));
    $decrypted = openssl_decrypt(substr($decoded, openssl_cipher_iv_length($cipher)), $cipher, $key, 0, $iv);

    return [$originalExtension, $decrypted];
}

$encryption_result = '';
$decryption_result = '';

// Check if the form is submitted for encryption
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file']) && isset($_POST['encrypt_key'])) {
    // Process file upload
    $file = $_FILES['file']['tmp_name'];
    $key = $_POST['encrypt_key'];

    $content = file_get_contents($file);
    $originalExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $encrypted_data = encrypt($content, $key, $originalExtension);

    // Save the encrypted data to a file using binary mode
    $filename = "encrypted_file.enc";
    file_put_contents($filename, $encrypted_data, LOCK_EX | FILE_BINARY);

    $encryption_result = "File successfully encrypted";
}

// Check if the form is submitted for decryption
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['encrypted_file']) && isset($_POST['decrypt_key'])) {
    // Process file upload
    $file = $_FILES['encrypted_file']['tmp_name'];
    $key = $_POST['decrypt_key'];

    $content = file_get_contents($file);
    
    // Check if the file is actually encrypted
    if (strpos($content, ':') === false) {
        // Display error toast if the file is not encrypted
        $decryption_result = "Error: Please select a valid encrypted file.";
    } else {
        list($originalExtension, $decrypted_data) = decrypt($content, $key);

        // Save the decrypted data to a file with the original file extension
        $decrypted_filename = "decrypted_file.$originalExtension";
        file_put_contents($decrypted_filename, $decrypted_data, LOCK_EX | FILE_BINARY);

        $decryption_result = "File successfully decrypted";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body {
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 100vh;
            margin: auto;
            font-family: Arial, sans-serif;
            overflow: auto;
            background: linear-gradient(135deg, #141E30, #243B55);
        }
        
        .encryption-container{
            width: 23%;
            height: 410px;
            padding: 25px;
            margin: 20px 50px;
            background-color: #1f2a35;
            border: 1px solid #0074D9;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .decryption-container {
            width: 23%;
            height: 410px;
            padding: 25px;
            margin: 20px 50px;
            background-color: #1f2a35;
            border: 1px solid #0074D9;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
h3{ 
    top: 388px;
    right: 83px;
    position: absolute;
    font-size: 32px;
  }

        h1 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 10px;
            margin-top:45px;
            color: #fff;
        }

        form {
            margin-top: 50px;
        }

        label {
            display: block;
            font-size: 18px;
            margin-bottom: 5px;
            color: #fff;
        }
        input[type="file"],
        input[type="submit"] {
            padding: 8px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
        }

        input[type="file"] {
            background-color: #0288D1;
            color: white;
            cursor: pointer;
        }

        input[type="submit"] {
            background-color: #39CCCC;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0074D9;
        }

        input[type="text"] {
  width: 100%;
  padding: 12px 16px;
  margin: 8px 0;
  box-sizing: border-box;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
  background-color: #f9f9f9;
  transition: border-color 0.3s, box-shadow 0.3s;
}

input[type="text"]:focus {
  border-color: #4a90e2;
  box-shadow: 0 0 8px rgba(74, 144, 226, 0.4);
  outline: none;
}

input[type="text"]::placeholder {
  color: #aaa;
  font-style: italic;
}


        button,
        .download-button {
            width: 100%;
            padding: 8px;
            border: none;
            border-radius: 3px;
            font-size: 0.9rem;
            cursor: pointer;
            margin-top:5px;
        }

        button {
            background-color: #4a86e8;
            color: #fff;
        }

        button:hover {
            background-color: #3569d0;
        }

        a {
            display: block;
            text-align: center;
            font-size: 0.8rem;
            color: white;
            text-decoration: none;
            margin-top: 10px;
        }

        a:hover {
            text-decoration: underline;
        }

        .download-button {
            background-color: #4a86e8;
            width: 95.5%
            color: white;
            margin-top: 10px;
            text-align: center;
            text-decoration: none;
            display: block;
            pointer: absolute;
            z-index: 5;
        }

        .download-button:hover {
            background-color: #3569d0;
        }
        
        .navigate-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #FF851B;
            position: absolute;
            width: 144px;
            right: 10px;
            top: 65px;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1rem;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .navigate-btn:hover {
            background-color: #FF4136;
            transform: scale(1.05);
        }

        .logout-form input[type="submit"] {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 10px 15px;
            background-color: #FF4136;
            color: white;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-form input[type="submit"]:hover {
            background-color: #FF851B;
        }
        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .background-animation span {
            position: absolute;
            display: block;
            width: 20px;
            height: 20px;
            background: rgba(0, 212, 255, 0.5);
            animation: animate 25s linear infinite;
            bottom: -150px;
        }

        @keyframes animate {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-1200px) rotate(720deg);
                opacity: 0;
            }
        }

        .background-animation span:nth-child(odd) {
            animation-duration: 20s;
            animation-delay: -5s;
        }

        .background-animation span:nth-child(even) {
            animation-duration: 30s;
            animation-delay: -10s;
        }
        
    </style>
</head>

<body>
<div class="background-animation">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>


    <div class="encryption-container">
        <h1>File Encryption</h1>
        <form method="post" enctype="multipart/form-data">
            <label for="file">Choose a file for encryption:</label>
            <input type="file" name="file" id="file" required>
            <label for="encrypt_key">Enter encryption key:</label>
            <input type="text" name="encrypt_key" id="encrypt_key" required>
            <button type="submit" onclick="showToast('File successfully encrypted')">Encrypt File</button>
            <?php
            if (!empty($encryption_result)) {
                echo "<a class='download-button' href='$filename' download>Download Encrypted File</a>";
            }
            ?>
        </form>
    </div>

    <div class="decryption-container">
        <h1>File Decryption</h1>
        <form method="post" enctype="multipart/form-data">
            <label for="encrypted_file">Choose an encrypted file for decryption:</label>
            <input type="file" name="encrypted_file" id="encrypted_file" required>
            <label for="decrypt_key">Enter decryption key:</label>
            <input type="text" name="decrypt_key" id="decrypt_key" required>
            <button type="submit" onclick="showToast('File successfully decrypted')">Decrypt File</button>
            <?php
            if (!empty($decryption_result)) {
                echo "<h2></h2>";
                echo "<a class='download-button' href='$decrypted_filename' download>Download Decrypted File</a>";
            }
            ?>
        </form>
    </div>

    <button class="navigate-btn" onclick="window.location.href='jnc.php'"> My Files </button>

    <form class="logout-form" action="logout.php" method="post">
        <input type="submit" value="Logout">
    </form>

    <!-- Toast Message Style -->
    <style>
        .toast-message {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            opacity: 0.9;
            z-index: 9999;
        }
    </style>

    <script>
        function showToast(message) {
            const toast = document.createElement('div');
            toast.classList.add('toast-message');
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 7000);
        }
    </script>

</body>

</html>
