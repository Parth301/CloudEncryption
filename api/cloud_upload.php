<?php 
$servername = "localhost";
$username = "nucfrkvh_CloudEncryp";
$password = "tjyrXKCafhPA8pEc4bsL";
$dbname = "nucfrkvh_CloudEncryp";
 
$conn = new mysqli($servername, $username, $password, $dbname); 
 
// Check connection 
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
} 
 
// Start a PHP session 
session_start(); 
 
// Get user email 
if (isset($_SESSION['user_email'])) { 
    $userEmail = $_SESSION['user_email']; 
} else { 
    die("Error: User email not found in the session."); 
} 
 
// Set a file size limit (in bytes) 
$maxFileSize = 50 * 1024 * 1024; // 5 MB 
 
// Handle file upload 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {
    // Check if a file was selected
    if ($_FILES["fileToUpload"]["error"] == UPLOAD_ERR_OK) {
        // Check the number of uploaded files
        $filesSql = "SELECT COUNT(*) AS file_count FROM user_files WHERE user_email='$userEmail'";
        $filesResult = $conn->query($filesSql);

        if ($filesResult) {
            $fileCountRow = $filesResult->fetch_assoc();
            $fileCount = $fileCountRow['file_count'];

            if ($fileCount >= 5) {
                echo "<div class='message-visible'>Error: You can only upload up to 5 files. Delete a file to upload a new one.</div>";
            } else {
                $fileName = basename($_FILES["fileToUpload"]["name"]);
                $tempFile = $_FILES["fileToUpload"]["tmp_name"];
                $fileSize = $_FILES["fileToUpload"]["size"];

                // Extract file extension
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

                // Check if the file size is within the limit
                if ($fileSize > $maxFileSize) {
                    echo "<div class='message-visible'>Error: File size exceeds the limit ({$maxFileSize} bytes).</div>";
                } else {
                    // Read the file content
                    $fileContent = file_get_contents($tempFile);

                    // Encrypt the file content
                    $key = random_bytes(32); // 256-bit key for AES-256 encryption
                    $iv = random_bytes(16);  // 128-bit IV for AES-256 encryption
                    $encryptedContent = openssl_encrypt($fileContent, 'aes-256-cbc', $key, 0, $iv);

                    // Combine encrypted content with file extension, separated by a semicolon
                    $fileDataWithExtension = $encryptedContent . ";" . $fileExtension;

                    // Insert encrypted file and extension into the database
                    $insertSql = "INSERT INTO user_files (user_email, file_name, file_data, encryption_key, iv) 
                    VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insertSql);

                    if ($stmt) {
                        $stmt->bind_param("sssss", $userEmail, $fileName, $fileDataWithExtension, $key, $iv);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        } else {
            echo "<div class='message-visible'>Error checking file count: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='message-visible'>Error uploading file: " . $_FILES["fileToUpload"]["error"] . "</div>";
    }
}
 
 // Handle file deletion 
if (isset($_POST['deleteFile'])) { 
    $deleteFileData = json_decode($_POST['deleteFile'], true); 
    $deleteFileName = $deleteFileData['fileName']; 
    $deleteFileId = $deleteFileData['fileId']; 
 
    $deleteSql = "DELETE FROM user_files WHERE user_email=? AND file_name=? AND file_id=?"; 
    $stmt = $conn->prepare($deleteSql); 
 
    if ($stmt) { 
        $stmt->bind_param("sss", $userEmail, $deleteFileName, $deleteFileId); 
 
        $deleteResult = $stmt->execute(); 
 
        if ($deleteResult) { 
            echo "<div class='message-del' style='position: fixed; bottom: 20px; left: 32px; background-color: #28a745; color: #fff; 
padding: 10px 20px; border-radius: 5px;'>" . "File '" . $deleteFileName . "' deleted successfully." . "</div>"; 
        } else { 
            echo "<div class='message-err' style='position: fixed; bottom: 20px; left: 20px; background-color: #28a745; color: #fff; 
padding: 10px 20px; border-radius: 5px;'>" . "Error deleting file: " . $stmt->error; 
        } 
 
        $stmt->close(); 
    } else { 
        echo "Error preparing delete statement: " . $conn->error; 
    } 
}

// Display user's uploaded files 
$filesSql = "SELECT file_name, file_id FROM user_files WHERE user_email='$userEmail'"; 
$filesResult = $conn->query($filesSql); 
 
if ($filesResult) { 
    echo "<div class='file-container'>"; 
    echo "<h2>Uploaded Files</h2>"; 
    if ($filesResult->num_rows > 0) { 
        echo "<ul>"; 
        while ($fileRow = $filesResult->fetch_assoc()) { 
            $fileName = $fileRow['file_name']; 
            $fileId = $fileRow['file_id']; 
            echo "<li>"; 
            echo "<a href='download.php?file=$fileName'>$fileName</a>"; 
            echo "<form method='post' action='jnc.php' style='display:inline;'>"; 
            echo "<input type='hidden' name='deleteFile' value='" . htmlspecialchars(json_encode(['fileName' => $fileName, 'fileId' => $fileId])) . "'>"; 
            echo "<input type='submit' value='Delete' class='delete-btn'>"; 
            echo "</form>"; 
            echo "</li>"; 
        } 
        echo "</ul>"; 
    } else { 
        echo "<p>No files uploaded yet.</p>"; 
    } 
    echo "</div>"; 
} else { 
    echo "<div class='message-visible'>Error fetching files: " . $conn->error . "</div>"; 
} 
 
$conn->close(); 
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>File Upload</title>
    <style>
        body {
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 100vh;
            margin: auto;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #141E30, #243B55);
            overflow: auto;
            color: white;
        }

        .upload-container {
            width: 29%;
            margin: 20px 50px;
            padding: 25px;
            background-color: #1f2a35;
            border: 1px solid #0074D9;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .file-container {
            width: 29%;
            margin: 20px 50px;
            padding: 25px;
            background-color: #1f2a35;
            border: 1px solid #0074D9;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .message-visible{
           position: absolute;
           top: 30px;
           font-size: 25px;
           color: #01FF70;
        }

        h2 {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #FFD700;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            font-size: 1rem;
            margin-bottom: 10px;
            color: #BBE1FA;
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

        .navigate-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #FF851B;
            position: absolute;
            top: 75px;
            right: 0px;
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

        li {
            margin-bottom: 10px;
        }

        a {
            text-decoration: none;
            color: #01FF70;
            font-weight: bold;
            margin-right: 10px
        }

        a:hover {
            text-decoration: underline;
        }

        .folder-icon {
            margin: 50px auto;
            width: 120px;
            height: 80px;
            background-color: #FFDC00;
            border-radius: 8px;
            position: relative;
        }

        .folder-tab {
            width: 40%;
            height: 20%;
            background-color: #FFD700;
            position: absolute;
            top: -20%;
            left: 10%;
            border-radius: 5px 5px 0 0;
        }

        .animated-arrow {
            width: 10px;
            height: 10px;
            margin: 20px auto;
            background-color: white;
            position: relative;
            animation: bounce 2s infinite;
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

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
        }
        @media only screen and (max-width: 1024px){
        
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
        .navigate-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #FF851B;
            position: absolute;
            top: 75px;
            right: 0px;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1rem;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }
        .upload-container {
            position : absolute;
            top : 140px; 
            width: 81%;
            margin: 20px 50px;
            padding: 25px;
            background-color: #1f2a35;
            border: 1px solid #0074D9;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .file-container {
            width: 81%;
            position: absolute;
            top: 470px;
            margin: 20px 50px;
            padding: 25px;
            background-color: #1f2a35;
            border: 1px solid #0074D9;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .message-visible{
        position: absolute;
        font-size: 21px;
        top: 18px;
        left: 18px;
        max-width: 71%;
        }
        .message-del , .message-err{
        position: absolute;
        top: 440px;
        font-size: 16px;
        width: 71%;
        height: 40px;
        }

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
    <div class="upload-container">
        <h2>Upload Your Secure Files</h2>
        <form action="jnc.php" method="post" enctype="multipart/form-data">
            <label for="fileToUpload">Choose a file:</label>
            <input type="file" name="fileToUpload" id="fileToUpload" required>
            <input type="submit" value="Upload File">
        </form>
    </div>
    <a class="navigate-btn" href="jnc_testing.php">Secure Files</a>
    <form class="logout-form" action="logout.php" method="post">
        <input type="submit" value="Logout">
    </form>
</body>
</html>
