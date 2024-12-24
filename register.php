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

$message = ""; // Initialize message variable

// Registration
if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the email already exists
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows > 0) {
        $message = "<span class='error'>Email already exists! Try another email.</span>";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $insert_query = "INSERT INTO users (email, password) VALUES ('$email', '$hashed_password')";
        if ($conn->query($insert_query) === TRUE) {
            $message = "<span class='success'>Registration Successful!</span>";
        } else {
            $message = "<span class='error'>Error: " . $conn->error . "</span>";
        }
    }
}

$conn->close();
?>

<html>
<head>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap');

        html, body {
            height: 100%;
            margin: 0;
            background: linear-gradient(135deg, #141E30, #243B55);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', sans-serif;
            overflow: hidden;
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
            text-align: center;
            font-size: 1em;
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
        }

        .error {
            color: #FF4B4B;
            background: rgba(255, 75, 75, 0.2);
        }

        .success {
            color: #4CAF50;
            background: rgba(76, 175, 80, 0.2);
        }

        form {
            display: grid;
            gap: 15px;
            color: #FFFFFF;
            width: 262px;
            height: 398px;
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
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <h1>Register</h1>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" name="register">Register</button>
            <p><a href="index.php">Back to Login</a></p>
        </form>
    </div>
</body>
</html>
