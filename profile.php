<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['customerid'])) {
    die("You are not logged in.");
}

$customerid = $_SESSION['customerid'];

$con = new mysqli("localhost", "root", "", "graduation_store");
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$sql = "SELECT * FROM customer WHERE customerid = '$customerid'";
$result = $con->query($sql);
if ($result->num_rows !== 1) {
    die("Customer not found.");
}
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile | Golden Gown</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #a759f5;
            --primary-light: #c1b1f7;
            --primary-dark: #8a4fd3;
            --secondary-color: #353535;
            --text-color: #353535;
            --light-text: #666;
            --white: #ffffff;
            --light-bg: #f8f9fa;
            --border-color: #eee;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(-45deg, #e3eefe 0%, #efddfb 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--text-color);
        }
        
        .profile-container {
            width: 100%;
            max-width: 900px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .profile-header {
            background-color: var(--white);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
            z-index: 0;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            position: relative;
            z-index: 1;
            border: 5px solid var(--white);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 40px;
        }
        
        .profile-avatar i {
            font-size: 50px;
            color: var(--white);
        }
        
        .profile-title {
            position: relative;
            z-index: 1;
        }
        
        .profile-title h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .profile-title p {
            color: var(--light-text);
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .customer-id {
            display: inline-block;
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .profile-content {
            background-color: var(--white);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--primary-color);
        }
        
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .info-item {
            position: relative;
            padding: 15px;
            background-color: var(--light-bg);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .info-item i {
            color: var(--primary-color);
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .info-item label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--light-text);
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .info-item p {
            font-size: 16px;
            color: var(--secondary-color);
            font-weight: 500;
            word-break: break-word;
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(167, 89, 245, 0.3);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(167, 89, 245, 0.4);
        }
        
        .btn-secondary {
            background: var(--white);
            color: var(--secondary-color);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .btn-secondary:hover {
            background: var(--light-bg);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .profile-info {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="header-bg"></div>
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-title">
                <h2><?= htmlspecialchars($row['name']) ?></h2>
                <p><?= htmlspecialchars($row['email']) ?></p>
                <span class="customer-id"><?= htmlspecialchars($customerid) ?></span>
            </div>
        </div>
        
        <div class="profile-content">
            <h3 class="section-title"><i class="fas fa-info-circle"></i> Personal Information</h3>
            
            <div class="profile-info">
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <label>Email Address</label>
                    <p><?= htmlspecialchars($row['email']) ?></p>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-phone-alt"></i>
                    <label>Phone Number</label>
                    <p><?= htmlspecialchars($row['phonenum']) ?></p>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-birthday-cake"></i>
                    <label>Date of Birth</label>
                    <p><?= htmlspecialchars($row['dateofbirth']) ?></p>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-venus-mars"></i>
                    <label>Gender</label>
                    <p><?= $row['gender'] == 'M' ? 'Male' : 'Female' ?></p>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <label>Address</label>
                    <p><?= htmlspecialchars($row['address']) ?></p>
                </div>
            </div>
            
            <div class="actions">
                <a href="editCustomer.php" class="btn"><i class="fas fa-edit"></i> Edit Profile</a>
                <a href="Homepage.php" class="btn btn-secondary"><i class="fas fa-home"></i> Back to Homepage</a>
            </div>
        </div>
    </div>
</body>
</html>