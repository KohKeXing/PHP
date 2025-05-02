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


// Check if the user is already logged in
$managerid = isset($_SESSION['ManagerID']) ? $_SESSION['ManagerID'] : '';
if (!$managerid) {
    header("Location: admin_login.php");
    exit();
}


$sql = "SELECT * FROM graduate WHERE ManagerID = '$managerid'";
$result = mysqli_query($con, $sql);

if (!$result || mysqli_num_rows($result) != 1) {
    header("Location: admin_login.php");
    exit();
}

// Fetch customer details
$row = mysqli_fetch_assoc($result);
$managerid   = isset($row['managerID']) ? $row['managerID'] : "";
$managername   = isset($row['managername']) ? $row['managername'] : "";
$mgntelephone = isset($row['mgnTelephone']) ? $row['mgnTelephone'] : "";
$mgnemail     = isset($row['mgnemail']) ? $row['mgnemail'] : "";
$mgnpassword  = isset($row['mgnpassword']) ? $row['mgnpassword'] : "";
$department    = isset($row['department']) ? $row['department'] : "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signout'])) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="cust.css" rel="stylesheet">
    <title>Admin Detail</title>
    <link rel="icon" type="image/x-icon" href="image/icon2.png">
    <style>
        .mgn {
            border-radius: 2rem;
            background-color: rgb(242, 222, 210);
            width: 600px;
            margin: 100px auto 50px auto;
            padding: 20px 50px;
            box-shadow: 2.5px 3.5px 4px #828282;
            color: black;
            font-size: 25px;
        }
        .st {
            font-weight: bold;
            padding-right: 20px;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <?php include 'header.php';?>

    <form action="edit.php" method="POST">
        <div class="mgn">
            <h1 class="b">Admin details</h1>
            <table>
                
               <tr>
    <td class="st">Manager ID:</td>
    <td><?php echo $managerid; ?></td>
</tr>
  <tr>
    <td class="st">Manager Name:</td>
    <td><?php echo $managername; ?></td>
</tr>
<tr>
    <td class="st">Telephone:</td>
    <td><?php echo $mgntelephone; ?></td>
</tr>
<tr>
    <td class="st">Email:</td>
    <td><?php echo $mgnemail; ?></td>
</tr>
<tr>
    <td class="st"> Department :</td>
    <td><?php echo $department; ?></td>
</tr>
<tr>
    <td class="st">Password:</td>
    <td><?php echo $mgnpassword; ?></td>
</tr>

            </table>
            <br>
            <button type="submit">Edit</button>
           
        </div>
    </form>
    <form action="" method="POST" style="text-align: center; margin-top: 20px;">
        <button type="submit" name="signout" style="background-color: #f44; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Sign Out</button>
        <br><br>
    </form>

    
    
    <?php include 'footer.php';?>
</body>
</html>