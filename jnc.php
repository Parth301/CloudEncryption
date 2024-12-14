<?php
ob_start();

$servername = "localhost";
$username = "id21931865_rishi";
$password = "Rishi@2006";
$dbname = "id21931865_harpcloud";

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
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
            overflow: auto;
            background: linear-gradient(315deg, rgba(0,0,94,1) 3%, rgba(0,132,206,1) 38%, rgba(0,238,226,1) 68%, rgba(0,25,25,1) 98%);
             animation: gradient 15s ease infinite;
             background-size: 400% 400%;
             background-attachment: fixed;
        }

        .encryption-container{
            width: 500px;
            height: 490px;
            padding: 25px;
            margin-left: 500px;
            margin-top: 35px;
            background-color: #353935;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 100px;
        }
        .decryption-container {
            width: 500px;
            height: 490px;
            margin-top: 60px;
            padding: 25px;
            background-color: #353935;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 100px;
        }
 #photo-overlay {
             position: absolute;
    top: 507px;
    left: 70%;
    width: 21%;
    height: 26%;
    z-index: 1;
    opacity: 1;
        }
h3{ 
    top: 306px;
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
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #fff;
        }

        input {
            width: 100%;
        
           color: white;

            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
            margin-bottom: 20px;
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
        }

        .download-button:hover {
            background-color: #3569d0;
        }
        
        .navigate-btn {
            background-color: #28a745;
            width: 100px;
            margin-top: -630px;
            margin-left: 150px;
            margin-right: 40px;
            height: 60px;
            color: #fff;
            white-space: nowrap;
            border: none;
            padding: 8px; 
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem; 
        }

        .navigate-btn:hover {
            background-color: #218838;
        }

        .logout-form {
            margin-top: -610px;
            margin-left: auto;
            margin-right: 150px; 
        }

        .logout-form input[type="submit"] {
            width: auto;
            background-color: #dc3545;
            border: none;
            border-radius: 3px;
            color: #fff;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .logout-form input[type="submit"]:hover {
            background-color: #c82333;
        }
        
        /* Media Queries */
        @media only screen and (min-width: 768px) {
            .encryption-container,
            .decryption-container {
                width: 45%; 
                margin: 20px 10px;
            }
        }

        @media only screen and (min-width: 1200px) {
            .encryption-container,
            .decryption-container {
                width: 30%;
                margin: 20px 15px;
            }
        }
    
        @keyframes gradient {
    0% {
        background-position: 0% 0%;
    }
    50% {
        background-position: 100% 100%;
    }
    100% {
        background-position: 0% 0%;
    }
}

infinite;
    transform: translate3d(0, 0, 0);
    opacity: 0.8;
    bottom: 0;
    left: 0;
    z-index: -1;
}

#file
{
    color: white;
}
@import "compass/css3";

* { box-sizing: border-box; }
html, body { 
  margin: 0; padding: 0; 
  position: relative; 
  width: 100%;
  height: 100%;
  background-color: #fff;
}
.page {
    height: 100%;
    -moz-box-align: center;
    -webkit-box-pack: center;
    position: absolute;
    top: 378px;
    /* -webkit-justify-content: center; */
    justify-content: center;
    -moz-box-pack: center;
    -ms-flex-pack: center;
    right: 275px;
}

.folder {
  background-color: sandybrown;
  position: relative;
  width: 104px;
  height: 72px;
  display: block;
    top: -59px;
  border-top-right-radius: 8px;
  border-bottom-right-radius: 8px;
  border-bottom-left-radius: 8px;
}
  .folder-tab {
    position: absolute;
    height: 10px;
    left: 0;
    bottom: 100%;
    display: block;
    width: 40%;
    border-top-left-radius: 8px;
    background-color: inherit;

    &:after {
      content: '';
      position: absolute;
      display: block;
      top: 0;
      left: calc(100% - 10px);
      border-bottom: 10px solid #2196f3;
      border-left: 10px solid transparent;
      border-right: 10px solid transparent;
    }
  }

  .folder-icn {
    padding-top: 12px;
    width: 100%;
    height: 100%;
    display: block;
  }
  .downloading {
    width: 30px;
    height: 32px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
  }
    .custom-arrow {
      width: 14px;
      height: 14px;
      position: absolute;
      top: 0;
      left: 50%;
      margin-left: -7px;
      background-color: #fff;
      
      -webkit-animation-name: downloading;
      -webkit-animation-duration: 1.5s;
      -webkit-animation-iteration-count: infinite;
      animation-name: downloading;
      animation-duration: 1.5s;
      animation-iteration-count: infinite;
      
      &:after {
        content: ''; position: absolute; display: block;
        top: 100%;
        left: -9px;
        border-top: 15px solid #fff;
        border-left: 16px solid transparent;
        border-right: 16px solid transparent;
      }
    }
  .bar {
    width: 30px;
    height: 4px;
    background-color: #fff;
    margin: 0 auto;
  }

@-webkit-keyframes downloading {
  0% {
    top: 0;
    opacity: 1;
  }
  50% {
    top: 110%;
    opacity: 0;
  }
  52% {
    top: -110%;
    opacity: 0;
  } 
  100% {
    top: 0;
    opacity: 1;
  }
}
@keyframes downloading {
  0% {
    top: 0;
    opacity: 1;
  }
  50% {
    top: 110%;
    opacity: 0;
  }
  52% {
    top: -110%;
    opacity: 0;
  } 
  100% {
    top: 0;
    opacity: 1;
  }
}


     
    </style>
</head>

<body>


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

    <button class="navigate-btn" onclick="window.location.href='upload.php'"> My Files </button>

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
 <h3>Secure files </h3>
    <img id="photo-overlay" src="logo.png" alt="Photo Overlay">

<div class="page">
  
  <div class="folder">
    <span class="folder-tab"></span>
    <div class="folder-icn">
      <div class="downloading">
        <span class="custom-arrow"></span>
      </div>
      <div class="bar"></div>
    </div>
  </div>


</body>

</html>