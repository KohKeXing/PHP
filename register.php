<?php
// Removed session_start();

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "graduation_store"); // Changed from "graduate" to match your database

$con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$errors = [];

// Generate next customer ID
$sql = "SELECT customerid FROM customer ORDER BY customerid DESC LIMIT 1";
$result = $con->query($sql);
$next_id = "C0001"; // Default ID
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $num = intval(substr($row['customerid'], 1)) + 1;
    $next_id = "C" . str_pad($num, 4, "0", STR_PAD_LEFT);
}

function validatePhone($phonenum) {
    if (strlen($phonenum) != 11 && strlen($phonenum) != 12) {
        return "Phone number must be 11 or 12 digits long.";
    }
    return "";
}

function validateDOB($dateofbirth) {
    $currentDate = date("Y-m-d");
    if ($dateofbirth > $currentDate) {
        return "Date of birth cannot be in the future.";
    }
    return "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phonenum = trim($_POST["phone"]);
    $dateofbirth = $_POST["dob"];
    $address = trim($_POST["address"]);
    $gender = $_POST["gender"] ?? "";
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($phonenum)) $errors[] = "Phone is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($dateofbirth)) $errors[] = "Date of Birth is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";

    $phoneError = validatePhone($phonenum);
    if (!empty($phoneError)) $errors[] = $phoneError;

    $dobError = validateDOB($dateofbirth);
    if (!empty($dobError)) $errors[] = $dobError;

    if (empty($errors)) {
        $gender_char = ($gender == "Male") ? "M" : "F";

        $insert_sql = "INSERT INTO customer (customerid, name, email, phonenum, dateofbirth, address, gender, password)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($insert_sql);
        $stmt->bind_param("ssssssss", $next_id, $name, $email, $phonenum, $dateofbirth, $address, $gender_char, $password);

        if ($stmt->execute()) {
            echo "<p class='success'>Registration successful. Your Customer ID is $next_id.</p>";
            header('Location: login.php');
            exit();
        } else {
            echo "<p class='error'>Error: " . $stmt->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #080710;
            font-family: 'Poppins', sans-serif;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        
        .background {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }
        
        .shape {
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
        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-container {
            background-color: rgba(255,255,255,0.13);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.1);
            box-shadow: 0 0 40px rgba(8,7,16,0.6);
            padding: 30px;
        }
        
        h2 {
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 25px;
            color: #fff;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-col {
            flex: 1 0 calc(50% - 20px);
            margin: 0 10px 20px;
        }
        
        .form-col.full-width {
            flex: 1 0 calc(100% - 20px);
        }
        
        .form-group {
            margin-bottom: 5px;
        }
        
        label {
            display: block;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        input, textarea, select {
            width: 100%;
            background-color: rgba(255,255,255,0.07);
            border-radius: 5px;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: 300;
            color: #fff;
            border: none;
            outline: none;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        input[type="date"] {
            color: #fff;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }
        
        .gender-group {
            display: flex;
            gap: 30px;
            margin-top: 10px;
        }
        
        .gender-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .gender-option input[type="radio"] {
            width: auto;
            margin: 0;
        }
        
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        button {
            flex: 1;
            min-width: 150px;
            background-color: #ffffff;
            color: #080710;
            padding: 15px 0;
            font-size: 16px;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        button:hover {
            background-color: #e0e0e0;
        }
        
        button.secondary {
            background-color: transparent;
            border: 1px solid #ffffff;
            color: #ffffff;
        }
        
        button.secondary:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .error {
            background-color: rgba(255, 99, 71, 0.3);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .error ul {
            list-style-position: inside;
            margin-left: 10px;
        }
        
        .error li {
            margin-bottom: 5px;
        }
        
        .success {
            background-color: rgba(50, 205, 50, 0.3);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .readonly-field {
            background-color: rgba(255,255,255,0.05);
            color: #ccc;
        }
        
        @media (max-width: 768px) {
            .form-col {
                flex: 1 0 calc(100% - 20px);
            }
            
            .container {
                padding: 15px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Create Account</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="register.php" method="POST">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="customerid">Customer ID</label>
                            <input type="text" name="customerid" id="customerid" value="<?= $next_id ?>" class="readonly-field" readonly>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" id="name" placeholder="Enter your full name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" placeholder="Enter your email address" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" name="phone" id="phone" placeholder="Enter your phone number (11-12 digits)" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob" id="dob" required value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label>Gender</label>
                            <div class="gender-group">
                                <div class="gender-option">
                                    <input type="radio" name="gender" id="male" value="Male" <?= (isset($_POST['gender']) && $_POST['gender'] == "Male") ? "checked" : "" ?>>
                                    <label for="male">Male</label>
                                </div>
                                <div class="gender-option">
                                    <input type="radio" name="gender" id="female" value="Female" <?= (isset($_POST['gender']) && $_POST['gender'] == "Female") ? "checked" : "" ?>>
                                    <label for="female">Female</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col full-width">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" placeholder="Enter your full address" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" placeholder="Create a password" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col full-width">
                        <div class="btn-group">
                            <button type="submit">Create Account</button>
                            <a href="login.php"><button type="button" class="secondary">Back to Login</button></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>