<?php
$servername = "localhost";
$username = "id21931865_rishi";
$password = "Rishi@2006";
$dbname = "id21931865_harpcloud";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start a PHP session
session_start();

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
            header("Location: upload.php");
            exit(); // Ensure that no further code is executed after the redirect
        } else {
            echo "Invalid password";
        }
    } else {
        echo "User not found";
    }
}

$conn->close();
?>

<html>
<body>
   <div class="colored-section"></div>
    <div class="login-container">
       <form action="index.php" method="post">
         <h1>HARP</h1> 
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>
    <button type="submit" name="login">Login</button>
         <p>Don't have an account? <a href="register.php">         
               Register Now</a></p>
</form>
 
   </div>
</body>
</html>
<style>
html {
  height: 100%;
  background: #042035;
  display: flex;
  display: grid;
  place-items: center;
}

body {
  margin: 0px;
  padding: 0px;
}

.login-container {
  text-align: center;
  background: #000000;
  margin: 230px;
  margin-top:240px;
  z-index: 2; 
  display flex;
  justify-content: space-around;
  margin-left: 230px;
}



form {
  width: 550px;
  height: 420px;
  display: grid;
  justify-items: center;
  align-content: center;
  grid-template: 'auto';
  background: linear-gradient(77deg, cyan, cyan, transparent 20%) 0 bottom/ 20% 50% no-repeat,
    linear-gradient(-77deg, cyan, cyan, transparent 20%) right bottom/ 20% 50% no-repeat,
    rgba(0, 0, 0, 0);
  transition: 1s;
  border-radius: 10px;
  box-shadow: 0px 3px 15px cyan, 0px 3px 5px cyan;
}

form:hover {
  background: linear-gradient(77deg, cyan, #6649df, transparent 70%) 0 bottom/ 100% 100% no-repeat,
    linear-gradient(-77deg, cyan, #6649df, transparent 70%) right bottom/ 100% 100% no-repeat,
    rgba(0, 0, 0, 0);
}

h1 {
  margin-top: 5px;
  font-size: 40px;
  color: #000000;
  font-family: 'regular';
}

label {
  display: inline-block;
  font-size: 22px;
  margin: 11px;
  margin-right: 20px;
  text-align: left;
  color: #000000;
  font-family: 'regular';
}

input {
  width: 50%;
  padding: 6px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
}

button {
  display: inline-block;
  font-size: 18px;
  padding: 8px 10px;
  background: #FFFFFF;
  border: none;
  outline: none;
  margin-top: 25px;
  margin-bottom: 15px;
  text-shadow: 0px 1px 3px white;
  color: #0f1923;
  border-radius: 3px/4px;
  font-family: 'regular';
  cursor: pointer;
}

button:hover {
  background-color: #8A2BE2;
}

p {
  margin-top: 10px;
  margin-bottom: -1px;
  color: #000000;
  font-size: 15px;
  font-family: 'regular', sans-serif;
}

a {
  color: #000000;
  text-decoration: underline;
  font-size: 20px;
  font-family: 'regular';
}

a:hover {
  color: #000000;
  font-family: 'regular';

}



</style>