<?php
session_start();

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "graduation_store");

$con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$errors = [];


if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

$temporary_block_duration = 30;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["mid"]) && isset($_POST["ps"])) {
        $managerid = $_POST["mid"];
        $mgnpassword = $_POST["ps"];

        if (isset($_SESSION['block_time']) && time() - $_SESSION['block_time'] < $temporary_block_duration) {
            echo "<script>alert('You are temporarily blocked. Please try again later.');</script>";
        } else {
            $sql = "SELECT * FROM manager WHERE ManagerID = '$managerid' AND mgnpassword = '$mgnpassword'";
            $result = mysqli_query($con, $sql);

            if (mysqli_num_rows($result) == 1) {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['ManagerID'] = $managerid;
                header("Location: admindetails.php");
                exit();
            } else {
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= 3) {
                    $_SESSION['block_time'] = time();
                    echo "<script>alert('You have failed login attempts. You are temporarily blocked for $temporary_block_duration seconds.');</script>";
                } else {
                    echo "<script>alert('Invalid Manager ID or password');</script>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manager Login</title>
  
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      background-color: #080710;
      font-family: 'Poppins', sans-serif;
    }
    .background {
      width: 430px;
      height: 520px;
      position: absolute;
      transform: translate(-50%, -50%);
      left: 50%;
      top: 50%;
    }
    .background .shape {
      height: 200px;
      width: 200px;
      position: absolute;
      border-radius: 50%;
    }
    .shape:first-child {
      background: linear-gradient(#1845ad, #23a2f6);
      left: -80px;
      top: -80px;
    }
    .shape:last-child {
      background: linear-gradient(to right, #ff512f, #f09819);
      right: -30px;
      bottom: -80px;
    }
    form {
      height: 520px;
      width: 400px;
      background-color: rgba(255,255,255,0.13);
      position: absolute;
      transform: translate(-50%,-50%);
      top: 50%;
      left: 50%;
      border-radius: 10px;
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255,255,255,0.1);
      box-shadow: 0 0 40px rgba(8,7,16,0.6);
      padding: 50px 35px;
    }
    form * {
      color: #fff;
      letter-spacing: 0.5px;
      outline: none;
      border: none;
    }
    form h1 {
      font-size: 32px;
      font-weight: 500;
      text-align: center;
      margin-bottom: 30px;
    }
    label {
      font-size: 16px;
      font-weight: 500;
      margin-top: 20px;
      display: block;
    }
    input {
      display: block;
      width: 100%;
      height: 50px;
      background-color: rgba(255,255,255,0.07);
      border-radius: 3px;
      padding: 0 10px;
      margin-top: 8px;
      font-size: 14px;
      font-weight: 300;
    }
    ::placeholder {
      color: #e5e5e5;
    }
    .btn-group {
      margin-top: 40px;
      display: flex;
      justify-content: space-between;
    }
    button {
      width: 48%;
      background-color: #ffffff;
      color: #080710;
      padding: 15px 0;
      font-size: 18px;
      font-weight: 600;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s;
    }
    button:hover {
      background-color: #ddd;
    }
    .links {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
    }
    .links a {
      color: #fff;
      text-decoration: none;
      margin: 0 10px;
    }
    .links a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="background">
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <form action="" method="POST">
    <h1>Manager Login</h1>

    <label for="mid">Manager ID:</label>
    <input type="text" name="mid" id="mid" maxlength="20" placeholder="Enter your Manager ID" required>

    <label for="ps">Password:</label>
    <input type="password" name="ps" id="ps" maxlength="8" placeholder="Enter your password" required>

    <div class="btn-group">
      <button type="submit" name="l">Login</button>
      <button type="reset" name="r">Reset</button>
    </div>

    <div class="links">
      <a href="adminforget.php">Forget Password</a> |
      <a href="login.php">Customer Login</a>
    </div>
  </form>
</body>
</html>
