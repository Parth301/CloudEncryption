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

$message = ""; // Initialize message

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["email"];
    $password = $_POST["password"];

    // Retrieve user data from the database
    $sql = "SELECT * FROM users WHERE email = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["password"])) {
            // Store user email in the session
            $_SESSION['user_email'] = $username;

            // Redirect to upload.php on successful login
            header("Location: jnc.php");
            exit(); // Ensure that no further code is executed after the redirect
        } else {
            $message = "Invalid password";
        }
    } else {
        $message = "User not found";
    }
}

$conn->close();
?>

<html>
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap');

        html {
            height: 100%;
            background: linear-gradient(135deg, #141E30, #243B55);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', sans-serif;
            overflow: hidden;
        }

        body {
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: rgba(0, 0, 0, 0.8);
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
            transition: transform 0.5s ease, background 0.5s ease;
            max-width: 400px;
            width: 100%;
        }

        .login-container:hover {
            transform: translateY(-10px);
            background: rgba(0, 0, 0, 0.9);
        }

        .message {
            color: #FF4B4B;
            font-size: 1em;
            text-align: center;
            margin-top: 10px;
            padding: 5px 10px;
            background: rgba(255, 75, 75, 0.2);
            border-radius: 5px;
            display: none; /* Hidden by default */
        }

        .message.visible {
            display: block;
        }

        form {
            display: grid;
            gap: 15px;
            color: #FFFFFF;
        }

        h1 {
            font-size: 2.5em;
            text-align: center;
            color: cyan;
            margin-bottom: 20px;
        }

        label {
            font-size: 1.1em;
        }

        input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #243B55;
            color: #FFFFFF;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            background: #364C62;
            box-shadow: 0 0 10px cyan;
        }

        button {
            background: linear-gradient(135deg, #06B6D4, #00D4FF);
            color: #000000;
            border: none;
            padding: 12px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.2em;
            transition: 0.3s;
        }

        button:hover {
            background: linear-gradient(135deg, #00D4FF, #06B6D4);
            transform: scale(1.05);
            box-shadow: 0px 5px 15px rgba(0, 212, 255, 0.5);
        }

        p {
            text-align: center;
        }

        a {
            color: #00D4FF;
            text-decoration: none;
            transition: 0.3s;
        }

        a:hover {
            color: cyan;
            text-decoration: underline;
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
        @media only screen and (max-width: 1024px){
        html, body {
            overflow: hidden; /* Prevent scrolling */
            -ms-overflow-style: none;
            scrollbar-width: none;
            max-height: 90%;
        }
        body::-webkit-scrollbar {
             display: none;
            }
        .login-container {
            background: rgba(0, 0, 0, 0.8);
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
            transition: transform 0.5s ease, background 0.5s ease;
            width: 350px;
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
    <div class="login-container">
        <!-- Display message if exists -->
        <?php if (!empty($message)): ?>
            <div class="message visible"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="index.php" method="post">
            <h1>Cencryp</h1>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" name="login">Login</button>
            <p>Don't have an account? <a href="register.php">Register Now</a></p>
        </form>
    </div>
</body>
</html>
