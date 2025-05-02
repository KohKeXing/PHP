<?php

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "graduation_store");

$con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$errors = [];


function checkPassword($mgnpassword){
    if(empty($mgnpassword)){
        return "Please enter your <b>Password</b>.";
    } elseif(strlen($mgnpassword) < 8){
        return "Your <b>Password</b> must be at least 8 characters long.";
    }
    return "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['nps']) && isset($_POST['cps'])) {
        $newPassword = $_POST['nps'];
        $confirmPassword = $_POST['cps'];

        $passwordError = checkPassword($newPassword);
        if (!empty($passwordError)) {
            echo "<script>alert('$passwordError');</script>";
        } else {
            if ($newPassword !== $confirmPassword) {
                echo "<script>alert('Passwords do not match. Please make sure your passwords match.');</script>";
            } else {
                // Use mgnemail from session instead of ManagerID
                $mgnemail = $_SESSION['mgnemail'];
                $sql = "UPDATE manager SET mgnpassword = ? WHERE mgnemail = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("ss", $newPassword, $mgnemail);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    header("Location: admin_login.php");
                    exit();
                } else {
                    echo "<script>alert('Failed to update password.');</script>";
                }

                $stmt->close();
                $con->close();
            }
        }
    } else {
        echo "<script>alert('Please fill in both fields.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>New Password</title>
  <link rel="stylesheet" href="adminforget.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Asap&display=swap" rel="stylesheet">
</head>
<body>

<div class="login">
  <form action="" method="post">
    <h1 style="text-align:center; color: #a759f5;">Golden Gown</h1>
    <table class="form-table">
      <tr>
        <td><label for="nps">New Password:</label></td>
        <td><input type="password" name="nps" id="nps" maxlength="12" required></td>
      </tr>
      <tr>
        <td><label for="cps">Confirm Password:</label></td>
        <td><input type="password" name="cps" id="cps" maxlength="12" required></td>
      </tr>
      <tr>
        <td colspan="2">
          <button type="submit" name="nsb" id="nsb">Submit</button>
        </td>
      </tr>
    </table>
  </form>
</div>

</body>
</html>
