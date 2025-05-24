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
    return $originalExtension . ':' . $encoded;
}

// Function to decrypt data
function decrypt($data, $key) {
    list($originalExtension, $encoded) = explode(':', $data, 2);
    $cipher = "aes-256-cbc";
    $decoded = base64_decode($encoded);
    $iv = substr($decoded, 0, openssl_cipher_iv_length($cipher));
    $decrypted = openssl_decrypt(substr($decoded, openssl_cipher_iv_length($cipher)), $cipher, $key, 0, $iv);
    return [$originalExtension, $decrypted];
}

// Handle Encryption
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'], $_POST['encrypt_key'])) {
    $file = $_FILES['file']['tmp_name'];
    $key = $_POST['encrypt_key'];
    $content = file_get_contents($file);
    $originalExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $encrypted_data = encrypt($content, $key, $originalExtension);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="encrypted_file.enc"');
    echo $encrypted_data;
    exit;
}

// Handle Decryption
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['encrypted_file'], $_POST['decrypt_key'])) {
    $file = $_FILES['encrypted_file']['tmp_name'];
    $key = $_POST['decrypt_key'];
    $content = file_get_contents($file);

    if (strpos($content, ':') === false) {
        echo "<script>alert('Error: Please upload a valid encrypted file.'); window.location.href='';</script>";
        exit;
    }

    list($originalExtension, $decrypted_data) = decrypt($content, $key);

    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=\"decrypted_file.$originalExtension\"");
    echo $decrypted_data;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt / Decrypt File</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #141E30, #243B55);
            color: #fff;
            margin: 0;
            padding: 20px;
            height: 100vh;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
        }

        .container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 40px;
            margin-top: 40px;
        }

        .box {
            background-color: #1f2a35;
            border: 1px solid #0074D9;
            border-radius: 8px;
            padding: 25px;
            width: 300px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input[type="file"],
        input[type="text"],
        button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="file"] {
            background-color: #0074D9;
            color: white;
            cursor: pointer;
        }

        input[type="text"] {
            background-color: #f9f9f9;
            color: #000;
        }

        button {
            background-color: #39CCCC;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0074D9;
        }

        .navigate-btn {
            position: absolute;
            top: 20px;
            right: 160px;
            padding: 10px 20px;
            background-color: #FF851B;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }

        .logout-form input[type="submit"] {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #FF4136;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
        }

        .logout-form input[type="submit"]:hover {
            background-color: #FF851B;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

    <h1>File Encryption & Decryption</h1>

    <div class="container">
        <div class="box">
            <h2>Encrypt File</h2>
            <form method="post" enctype="multipart/form-data">
                <label for="file">Select file to encrypt:</label>
                <input type="file" name="file" required>
                <label for="encrypt_key">Encryption key:</label>
                <input type="text" name="encrypt_key" required>
                <button type="submit">Encrypt & Download</button>
            </form>
        </div>

        <div class="box">
            <h2>Decrypt File</h2>
            <form method="post" enctype="multipart/form-data">
                <label for="encrypted_file">Select encrypted file:</label>
                <input type="file" name="encrypted_file" required>
                <label for="decrypt_key">Decryption key:</label>
                <input type="text" name="decrypt_key" required>
                <button type="submit">Decrypt & Download</button>
            </form>
        </div>
    </div>

    <button class="navigate-btn" onclick="window.location.href='jnc.php'">My Files</button>

    <form class="logout-form" method="post" action="logout.php">
        <input type="submit" value="Logout">
    </form>

</body>
</html>
