<?php
// adminEdit.php

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "graduation_store");

$con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$errors = [];

$managerID = $_GET['managerID'] ?? null;
$managerID = $con->real_escape_string($managerID);

if (!$managerID) {
    die("Invalid Manager ID.");
}

$nameErr = $telErr = $emailErr = $deptErr = "";
$name = $tel = $email = $dept = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $isValid = true;

    // Name validation
    if (empty($_POST["managername"])) {
        $nameErr = "Name is required.";
        $isValid = false;
    } else {
        $name = trim($_POST["managername"]);
        if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
            $nameErr = "Only letters and spaces allowed.";
            $isValid = false;
        }
    }

    // Telephone validation
    if (empty($_POST["mgnTelephone"])) {
        $telErr = "Telephone is required.";
        $isValid = false;
    } else {
        $tel = trim($_POST["mgnTelephone"]);
        if (!preg_match("/^\d{3}-\d{7,8}$/", $tel)) {
            $telErr = "Enter a valid phone number like 011-22228888.";
            $isValid = false;
        }
    }

    // Email validation
    if (empty($_POST["mgnemail"])) {
        $emailErr = "Email is required.";
        $isValid = false;
    } else {
        $email = trim($_POST["mgnemail"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format.";
            $isValid = false;
        }
    }

    // Department validation
    if (empty($_POST["department"])) {
        $deptErr = "Department is required.";
        $isValid = false;
    } else {
        $dept = trim($_POST["department"]);
    }

    // Update if valid
    if ($isValid) {
        $name = $con->real_escape_string($name);
        $tel = $con->real_escape_string($tel);
        $email = $con->real_escape_string($email);
        $dept = $con->real_escape_string($dept);

        $update = "UPDATE manager SET managername='$name', mgnTelephone='$tel', mgnemail='$email', department='$dept' WHERE managerID='$managerID'";
        if ($con->query($update)) {
            echo "<script>alert('Manager details updated successfully.'); window.location.href='admindetails.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error updating record.');</script>";
        }
    }
} else {
    // Initial data load
    $sql = "SELECT * FROM manager WHERE managerID = '$managerID'";
    $result = $con->query($sql);
    if ($result->num_rows !== 1) {
        die("Manager not found.");
    }
    $row = $result->fetch_assoc();
    $name = $row['managername'];
    $tel = $row['mgnTelephone'];
    $email = $row['mgnemail'];
    $dept = $row['department'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Manager | Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f3f7;
      color: #333;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .container {
      width: 100%;
      max-width: 600px;
    }

    .card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .card-header {
      background: #3498db;
      color: white;
      padding: 20px;
      text-align: center;
      position: relative;
    }

    .card-header h2 {
      margin: 0;
      font-size: 24px;
      font-weight: 600;
    }

    .back-link {
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
      color: white;
      font-size: 20px;
      text-decoration: none;
      display: flex;
      align-items: center;
      transition: 0.3s;
    }

    .back-link:hover {
      opacity: 0.8;
    }

    .card-body {
      padding: 30px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #555;
    }

    .form-control {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      transition: border 0.3s;
    }

    .form-control:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    .error-text {
      color: #e74c3c;
      font-size: 14px;
      margin-top: 5px;
      display: block;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 12px;
      background: #3498db;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #2980b9;
    }

    .footer {
      text-align: center;
      margin-top: 20px;
    }

    .footer-link {
      color: #3498db;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }

    .footer-link:hover {
      color: #2980b9;
      text-decoration: underline;
    }

    .manager-id {
      text-align: center;
      margin-bottom: 20px;
      font-size: 15px;
      color: #777;
    }

    .input-group {
      position: relative;
    }

    .input-icon {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      right: 15px;
      color: #777;
    }

    @media (max-width: 600px) {
      .card-header h2 {
        font-size: 20px;
      }
      
      .back-link {
        font-size: 16px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header">
        <a href="admindetails.php" class="back-link">
          <i class="fas fa-arrow-left"></i>
        </a>
        <h2>Edit Manager Profile</h2>
      </div>
      
      <div class="card-body">
        <div class="manager-id">
          <span>Manager ID: <strong><?= htmlspecialchars($managerID) ?></strong></span>
        </div>
        
        <form method="POST">
          <div class="form-group">
            <label class="form-label" for="managername">Full Name</label>
            <div class="input-group">
              <input type="text" class="form-control" name="managername" id="managername" value="<?= htmlspecialchars($name) ?>" required>
              <span class="input-icon"><i class="fas fa-user"></i></span>
            </div>
            <?php if ($nameErr): ?>
              <span class="error-text"><?= $nameErr ?></span>
            <?php endif; ?>
          </div>
          
          <div class="form-group">
            <label class="form-label" for="mgnTelephone">Telephone</label>
            <div class="input-group">
              <input type="text" class="form-control" name="mgnTelephone" id="mgnTelephone" value="<?= htmlspecialchars($tel) ?>" placeholder="e.g. 011-22228888" required>
              <span class="input-icon"><i class="fas fa-phone"></i></span>
            </div>
            <?php if ($telErr): ?>
              <span class="error-text"><?= $telErr ?></span>
            <?php endif; ?>
          </div>
          
          <div class="form-group">
            <label class="form-label" for="mgnemail">Email Address</label>
            <div class="input-group">
              <input type="email" class="form-control" name="mgnemail" id="mgnemail" value="<?= htmlspecialchars($email) ?>" required>
              <span class="input-icon"><i class="fas fa-envelope"></i></span>
            </div>
            <?php if ($emailErr): ?>
              <span class="error-text"><?= $emailErr ?></span>
            <?php endif; ?>
          </div>
          
          <div class="form-group">
            <label class="form-label" for="department">Department</label>
            <div class="input-group">
              <input type="text" class="form-control" name="department" id="department" value="<?= htmlspecialchars($dept) ?>" required>
              <span class="input-icon"><i class="fas fa-building"></i></span>
            </div>
            <?php if ($deptErr): ?>
              <span class="error-text"><?= $deptErr ?></span>
            <?php endif; ?>
          </div>
          
          <button type="submit" class="btn">
            <i class="fas fa-save"></i> Update Manager
          </button>
        </form>
      </div>
    </div>
    
    <div class="footer">
      <a href="admindetails.php" class="footer-link">Back to Manager List</a>
    </div>
  </div>
</body>
</html>

<?php $con->close(); ?>