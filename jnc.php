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
                echo "Error: You can only upload up to 5 files. Delete a file to upload a new one.";
                 } else { 
                $fileName = basename($_FILES["fileToUpload"]["name"]); 
                $tempFile = $_FILES["fileToUpload"]["tmp_name"]; 
                $fileSize = $_FILES["fileToUpload"]["size"]; 
 
                // Check if the file size is within the limit 
                if ($fileSize > $maxFileSize) { 
                    echo "Error: File size exceeds the limit ({$maxFileSize} bytes)."; 
                } else { 
                    // Read the file content 
                    $fileContent = file_get_contents($tempFile); 
 
                    // Encrypt the file content 
                    $key = random_bytes(32); // 256-bit key for AES-256 encryption 
                    $iv = random_bytes(16);  // 128-bit IV for AES-256 encryption 
                    $encryptedContent = openssl_encrypt($fileContent, 'aes-256-cbc', $key, 0, $iv); 
 
                    // Insert encrypted file into the database 
                    $insertSql = "INSERT INTO user_files (user_email, file_name, file_data, encryption_key, iv) 
VALUES (?, ?, ?, ?, ?)"; 
                    $stmt = $conn->prepare($insertSql); 
 
                    if ($stmt) { 
                        $stmt->bind_param("sssss", $userEmail, $fileName, $encryptedContent, $key, $iv); 
 
                        // Attempt to execute the query with retries 
                        $maxRetries = 3; 
                        $retryCount = 0; 
                        do { 
                            $executeResult = $stmt->execute(); 
                            if (!$executeResult && $conn->errno == 2006) { 
                                // MySQL server gone away, reconnect and retry 
                                $conn->close(); 
                                $conn = new mysqli($servername, $username, $password, $dbname); 
                                $stmt = $conn->prepare($insertSql); 
                                $stmt->bind_param("sssss", $userEmail, $fileName, $encryptedContent, $key, $iv); 
                            } else { 
                                // Break the loop on success or non-gone away error 
                                break; 
                            } 
                            $retryCount++; 
                        } while ($retryCount < $maxRetries); 
 
                        if (!$executeResult) { 
echo "Error uploading file: " . $stmt->error; 
                        } 
 
                        $stmt->close(); 
                    } else { 
                        echo "Error preparing statement: " . $conn->error; 
                    } 
                } 
            } 
        } else { 
            echo "Error checking file count: " . $conn->error; 
        } 
    } else { 
        echo "Error uploading file: " . $_FILES["fileToUpload"]["error"]; 
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
            echo "<div style='position: fixed; bottom: 20px; left: 20px; background-color: #28a745; color: #fff; 
padding: 10px 20px; border-radius: 5px;'>" . "File '" . $deleteFileName . "' deleted successfully." . "</div>"; 
        } else { 
            echo "<div style='position: fixed; bottom: 20px; left: 20px; background-color: #28a745; color: #fff; 
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
    echo "<div class='upload-container'>"; 
    echo "<h2>Uploaded Files</h2>"; 
    if ($filesResult->num_rows > 0) { 
        echo "<ul>"; 
        while ($fileRow = $filesResult->fetch_assoc()) { 
            $fileName = $fileRow['file_name']; 
            $fileId = $fileRow['file_id']; 
            echo "<li>"; 
            echo "<a href='download.php?file=$fileName'>$fileName</a>"; 
            echo "<form method='post' action='upload.php' style='display:inline;'>"; 
            echo "<input type='hidden' name='deleteFile' value='" . htmlspecialchars(json_encode(['fileName' => 
$fileName, 'fileId' => $fileId])) . "'>"; 
            echo "<input type='submit' value='Delete'>"; 
            echo "</form>"; 
            echo "</li>"; 
        } 
        echo "</ul>"; 
    } else { 
        echo "<p>No files uploaded yet.</p>"; 
    } 
    echo "</div>"; 
} else { 
    echo "<div style='position: fixed; bottom: 20px; left: 20px; background-color: #FF003F; color: #fff; 
padding: 10px 20px; border-radius: 5px;'>" ."Error fetching files: " . $conn->error; 
} 
 
$conn->close(); 
?> 
 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>File Upload</title> 
    <style> 
 
        body {
 display: flex; 
    justify-content: space-around; 
    align-items: center; 
    height: 100vh; 
    margin: auto; 
    font-family: -apple-system, BlinkMacSystemFont, sans-serif; 
    overflow: auto; 
    background: linear-gradient(315deg, rgba(0, 0, 94, 1) 3%, rgba(0, 132, 206, 1) 38%, rgba(0, 238, 226, 1) 
68%, rgba(0, 25, 25, 1) 98%); 
    animation: gradient 15s ease infinite; 
    background-size: 400% 400%; 
    background-attachment: fixed; 
        } 
 
 
        .upload-container { 
            width: 59%; 
            margin: 35px; 
            padding: 20px; 
            background-color: #353935; 
            height:600px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
        } 
        .navigate-btn, 
        .logout-form { 
            margin: 20px; 
        } 
        .navigate-btn { 
             
            padding: 8px; 
            width: auto; 
            margin-top: -606px; 
            margin-left: 121px; 
            margin-right: 183px; 
            height: 60px; 
            justify-content: center;  
            align-items: center; 
            background-color: #28a745; 
            color: #fff; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer;
    font-size: 1rem; 
            transition: background-color 0.3s; 
            white-space: nowrap;  
        } 
 
        .navigate-btn:hover { 
            background-color: #218838; 
        } 
 
        .logout-form input[type="submit"] { 
            width: auto;      
            top: 4.6%;                  
            position: absolute; 
            right: 11.09%;  
            transform: translateY(0);  
            background-color: #dc3545; 
            border: none; 
            border-radius: 3px; 
            color: #fff; 
            font-size: 0.9rem;            //logout 
            cursor: pointer; 
            transition: background-color 0.3s; 
        } 
 
        .logout-form input[type="submit"]:hover { 
            background-color: #c82333; 
        } 
 
        #photo-overlay { 
             position: absolute; 
    top: 507px; 
    left: 74%; 
    width: 15%; 
    height: 26%; 
    z-index: 1; 
    opacity: 1; 
        } 
 
  h2 { 
            text-align: center; 
            font-size: 2rem; 
            margin-bottom: 10px; 
            color: white; 
        }
h3{  
    top: 359px; 
    right: 106px; 
    position: absolute; 
    font-size: 32px; 
  } 
   
} 
 
        ul { 
            list-style: none; 
            margin-top: 25px; 
            padding: 0; 
        } 
 
        li { 
            margin-bottom: 12px; 
        } 
 
        a { 
          text-decoration: none; 
          color: #007bff; 
          font-weight: bold; 
          margin-right: -19px; 
 
        } 
 
 
           form { 
    margin-top: 20px; 
    margin-left: -10px; 
    margin-right: -12px; 
} 
 
        label { 
            display: block; 
            font-size: 0.9rem; 
            margin-bottom: 5px; 
            margin-top: 35px; 
            color: #333; 
        } 
 
        input[type="file"] { 
            width: 100%; 
      } 
        } 
 
        @media only screen and (min-width: 1200px) { 
            .upload-container { 
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
    margin-top: 55px; 
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
    <div class="upload-container"> 
        <form action="upload.php" method="post" enctype="multipart/form-data">
 <label for="fileToUpload">Choose a file:</label> 
            <input type="file" name="fileToUpload" required> 
            <br> 
            <input type="submit" value="Upload File"> 
        </form> 
    </div> 
 
    <button class="navigate-btn" onclick="window.location.href='jnc_testing.php'"> Secure Files</button> 
 
    <form class="logout-form" action="logout.php" method="post"> 
        <input type="submit" value="Logout"> 
    </form> 
  <h3>Store files </h3> 
    
 
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
</div> 
</body> 
</html>
