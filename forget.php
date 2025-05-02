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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = $_POST['email'];

        $sql = "SELECT * FROM customer WHERE email = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['email'] = $email;
            header("Location: newps.php"); 
            exit();
        } else {
            echo "<script>alert('Invalid email. Please try again.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please fill in the email field.');</script>";
    }

    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forget Password</title>
    <link rel="stylesheet" href="adminforget.css">
    <link href="https://fonts.googleapis.com/css2?family=Asap&display=swap" rel="stylesheet">
</head>
<body>

    <form class="login" method="post">
        <h1 style="text-align:center; color:#444;">Golden Gown</h1>
        <table class="form-table">
            <tr>
                <td colspan="2">Enter your Email:</td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="text" name="email" placeholder="Enter your Email" required>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type="submit" name="sb">Next</button>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="right-link">
                    <a href="login.php">Back to Login</a>
                </td>
            </tr>
        </table>
    </form>

</body>
</html>