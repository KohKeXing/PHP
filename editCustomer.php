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


$customerid = $_SESSION['customerid'] ?? null;
if (!$customerid) {
    die("Invalid Customer ID.");
}

$nameErr = $telErr = $emailErr = $dobErr = $addressErr = $genderErr = "";
$name = $email = $tel = $dob = $address = $gender = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $isValid = true;

    // Validation
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $tel = trim($_POST["phonenum"]);
    $dob = $_POST["dateofbirth"];
    $address = trim($_POST["address"]);
    $gender = $_POST["gender"];

    if (empty($name) || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $nameErr = "Valid name required.";
        $isValid = false;
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Valid email required.";
        $isValid = false;
    }

    if (empty($tel) || !preg_match("/^\d{3}-\d{7,8}$/", $tel)) {
        $telErr = "Phone format: 012-3456789";
        $isValid = false;
    }

    if (empty($dob)) {
        $dobErr = "Date of Birth required.";
        $isValid = false;
    }

    if (empty($address)) {
        $addressErr = "Address required.";
        $isValid = false;
    }

    if (empty($gender) || !in_array($gender, ['M', 'F'])) {
        $genderErr = "Gender required.";
        $isValid = false;
    }

    if ($isValid) {
        $stmt = $con->prepare("UPDATE customer SET name=?, email=?, phonenum=?, dateofbirth=?, address=?, gender=? WHERE customerid=?");
        $stmt->bind_param("sssssss", $name, $email, $tel, $dob, $address, $gender, $customerid);
        if ($stmt->execute()) {
            echo "<script>alert('Customer updated successfully.'); window.location.href='profile.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error updating record.');</script>";
        }
        $stmt->close();
    }
} else {
    $sql = "SELECT * FROM customer WHERE customerid = '$customerid'";
    $result = $con->query($sql);
    if ($result->num_rows !== 1) {
        die("Customer not found.");
    }
    $row = $result->fetch_assoc();
    $name = $row['name'];
    $email = $row['email'];
    $tel = $row['phonenum'];
    $dob = $row['dateofbirth'];
    $address = $row['address'];
    $gender = $row['gender'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Customer</title>
  <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f0eaff;
        padding: 20px;
    }

    .form-container {
        background-color: white;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: 0 auto;
        border-radius: 8px;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #555;
    }

    label {
        font-weight: bold;
        display: block;
        margin-top: 10px;
        color: #333;
    }

    input, select {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    input[type="submit"] {
        background-color: #6b3fa0;
        color: white;
        border: none;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #6b3fa0;
    }

    .error {
        color: red;
        font-size: 13px;
    }

    .back-link {
        display: inline-block;
        margin-top: 15px;
        text-decoration: none;
        color: #6b3fa0;
    }
  </style>
</head>
<body>
<div class="form-container">
  <h2>Edit Your Profile</h2>
  <form method="POST">
    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
    <div class="error"><?= $nameErr ?></div>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
    <div class="error"><?= $emailErr ?></div>

    <label>Phone</label>
    <input type="text" name="phonenum" value="<?= htmlspecialchars($tel) ?>" required>
    <div class="error"><?= $telErr ?></div>

    <label>Date of Birth</label>
    <input type="date" name="dateofbirth" value="<?= htmlspecialchars($dob) ?>" required>
    <div class="error"><?= $dobErr ?></div>

    <label>Address</label>
    <input type="text" name="address" value="<?= htmlspecialchars($address) ?>" required>
    <div class="error"><?= $addressErr ?></div>

    <label>Gender</label>
    <select name="gender" required>
      <option value="">-- Select Gender --</option>
      <option value="M" <?= $gender == 'M' ? 'selected' : '' ?>>Male</option>
      <option value="F" <?= $gender == 'F' ? 'selected' : '' ?>>Female</option>
    </select>
    <div class="error"><?= $genderErr ?></div>

    <input type="submit" value="Update">
  </form>
  <a class="back-link" href="profile.php">← Back to Profile</a>
</div>
</body>
</html>

<?php $con->close(); ?>
