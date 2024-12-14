<?php
// Connect to the database (replace with your database credentials)
$servername = "localhost";
$username = "id21931865_rishi";
$password = "Rishi@2006";
$dbname = "id21931865_harpcloud";

$conn = new mysqli($servername, $username, $password, $dbname);

// Get user email
session_start();
$userEmail = $_SESSION['user_email'];

// Check if the 'file' parameter is set in the URL
if(isset($_GET['file'])) {
    $fileName = $_GET['file'];

    // Retrieve encrypted file content from the database
    $sql = "SELECT file_data, encryption_key, iv FROM user_files WHERE user_email='$userEmail' AND file_name='$fileName'";
    $result = $conn->query($sql);

    if($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $encryptedContent = $row['file_data'];
        $key = $row['encryption_key'];
        $iv = $row['iv'];

        // Decrypt the file content
        $decryptedContent = openssl_decrypt($encryptedContent, 'aes-256-cbc', $key, 0, $iv);

        // Set appropriate headers for file download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        // Output the decrypted content to the browser
        echo $decryptedContent;
        exit();
    } else {
        echo "File not found or you do not have permission to access it.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
