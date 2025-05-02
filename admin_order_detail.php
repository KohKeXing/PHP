<?php
session_start();
// Include database connection
if (file_exists('db.php')) {
    include 'db.php';
} else {
    // Use direct connection if db.php doesn't exist
    $host = 'localhost';
    $dbname = 'graduation_store';
    $username = 'root';
    $password = '';

    try {
        $conn = new mysqli($host, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    } catch(Exception $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

// Update order status if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update_sql = "UPDATE `order` SET OrderStatus = ? WHERE OrderID = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ss", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $status_message = "Order status updated successfully!";
    } else {
        $status_message = "Error updating order status: " . $conn->error;
    }
}

// Get order details
$order_sql = "SELECT o.*, c.name as customer_name, c.email, c.phonenum, c.address 
              FROM `order` o
              LEFT JOIN customer c ON o.customerid = c.customerid
              WHERE o.OrderID = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT od.*, p.name as product_name, p.image_url 
              FROM orderdetail od
              LEFT JOIN products p ON od.productId = p.productId
              WHERE od.OrderID = ?";
$stmt = $conn->prepare($items_sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

// Get payment information
$payment_sql = "SELECT * FROM payment WHERE OrderID = ?";
$stmt = $conn->prepare($payment_sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$payment_result = $stmt->get_result();
$payment = $payment_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Order Details</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin_product.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f4f6f8;
    }



    /* Main Content Area */
    .content {
  
      padding: 30px;
    }

    .content h1 {
      color: #5dade2;
      font-size: 28px;
      margin-bottom: 20px;
    }

    /* Back Button */
    .back-button {
      display: inline-flex;
      align-items: center;
      color: #5dade2;
      text-decoration: none;
      margin-bottom: 20px;
      font-weight: 500;
      transition: color 0.3s;
    }

    .back-button i {
      margin-right: 8px;
    }

    .back-button:hover {
      color: #3498db;
    }

    /* Status Update Form */
    .status-form {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      box-shadow: 0px 2px 8px rgba(0,0,0,0.05);
    }

    .status-form h3 {
      margin-top: 0;
      color: #2c3e50;
      font-size: 18px;
      margin-bottom: 15px;
    }

    .status-form form {
      display: flex;
      gap: 10px;
    }

    .status-form select {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      flex-grow: 1;
    }

    .status-form button {
      background-color: #5dade2;
      color: white;
      border: none;
      padding: 8px 18px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .status-form button:hover {
      background-color: #3498db;
    }

    .status-message {
      margin-top: 10px;
      padding: 8px 12px;
      border-radius: 6px;
    }

    .status-message.success {
      background-color: #d4edda;
      color: #155724;
    }

    .status-message.error {
      background-color: #f8d7da;
      color: #721c24;
    }

    /* Info Cards */
    .order-info-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .info-card {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 2px 8px rgba(0,0,0,0.05);
    }

    .info-card h3 {
      margin-top: 0;
      color: #2c3e50;
      font-size: 18px;
      margin-bottom: 15px;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }

    .info-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }

    .info-label {
      color: #7f8c8d;
      font-weight: 500;
    }

    /* Status Colors */
    .status-pending {
      color: #f39c12;
      font-weight: bold;
    }

    .status-processing {
      color: #3498db;
      font-weight: bold;
    }

    .status-completed {
      color: #27ae60;
      font-weight: bold;
    }

    .status-cancelled {
      color: #e74c3c;
      font-weight: bold;
    }

    /* Items Table */
    .items-table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0px 2px 8px rgba(0,0,0,0.05);
      margin-bottom: 30px;
    }

    .items-table thead {
      background-color: #5dade2;
      color: white;
    }

    .items-table th, 
    .items-table td {
      padding: 14px 18px;
      text-align: left;
    }

    .items-table tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .product-image {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 6px;
    }

    /* Order Summary */
    .order-summary {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 2px 8px rgba(0,0,0,0.05);
      max-width: 400px;
      margin-left: auto;
    }

    .order-summary h3 {
      margin-top: 0;
      color: #2c3e50;
      font-size: 18px;
      margin-bottom: 15px;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }

    .summary-item.total {
      font-weight: bold;
      font-size: 18px;
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px solid #eee;
    }

    .sidebar {
      width: 220px;
      background-color: #2c3e50;
      color: #ecf0f1;
      height: 100vh;
      padding: 20px;
      box-sizing: border-box;
    }

    .sidebar-title {
      margin-bottom: 20px;
      font-size: 22px;
      font-weight: bold;
    }

    .sidebar-menu {
      list-style: none;
      padding: 0;
    }

    .sidebar-item {
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      font-size: 16px;
      padding: 10px 12px;
      border-radius: 6px;
      transition: background-color 0.2s ease;
    }

    .sidebar-item a {
      text-decoration: none;
      color: #ecf0f1;
      margin-left: 10px;
      display: inline-block;
      width: 100%;
    }

    .sidebar-item i {
      width: 20px;
      text-align: center;
    }

    .sidebar-item:hover {
      background-color: #34495e;
    }

    .sidebar-item.active {
      background-color: #2980b9;
    }
    /* Main Content Styles */
.main-content {
    flex: 1;
    margin-left: 250px;
    padding: 20px;
    background: var(--light-bg);
}

.top-bar {
    background: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border-radius: 10px;
    margin-bottom: 20px;
}

.search input {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    width: 300px;
    font-size: 14px;
}

.search input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.1);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-info span {
    font-weight: 500;
}

.user-info img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

/* Dashboard Content */
.dashboard {
    padding: 20px;
}

.dashboard h1 {
    margin-bottom: 30px;
    color: var(--secondary-color);
    font-weight: 600;
}
 /* Make the sidebar taller and adjust its layout */
 .sidebar {
    height: 100%; /* Make it take full height */
    min-height: 100vh; /* Ensure it's at least as tall as the viewport */
    position: fixed; /* Keep it fixed on the left side */
    left: 0;
    top: 0;
    width: 205px; /* Match your current sidebar width */
    background-color: #1e272e; /* Maintain your dark theme */
    overflow-y: auto; /* Enable scrolling if needed */
    width:200px;
}
/* Sidebar Styles */
.sidebar {
    width: 250px;
    background: var(--dark-bg);
    color: white;
    padding: 20px;
    position: fixed;
    height: 100%;
    z-index: 100;
}

.logo h2 {
    padding: 15px 0;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 20px;
}

.nav-links {
    margin-top: 30px;
    list-style: none;
}

.nav-links li {
    margin-bottom: 10px;
}

.nav-links a {
    color: white;
    text-decoration: none;
    padding: 12px 15px;
    display: flex;
    align-items: center;
    border-radius: 8px;
    transition: all 0.3s;
}

.nav-links a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.nav-links a:hover,
.nav-links .active a {
    background: rgba(255,255,255,0.1);
}
/* Main Content Styles */
.main-content {
    flex: 1;
    margin-left: 250px;
    padding: 20px;
    background: var(--light-bg);
}

.top-bar {
    background: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border-radius: 10px;
    margin-bottom: 20px;
}

.search input {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    width: 300px;
    font-size: 14px;
}

.search input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.1);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-info span {
    font-weight: 500;
}

.user-info img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

/* Dashboard Content */
.dashboard {
    padding: 20px;
}

.dashboard h1 {
    margin-bottom: 30px;
    color: var(--secondary-color);
    font-weight: 600;
}
a {
    text-decoration: none;
    color: inherit; /* Optional: keeps the same color as surrounding text */
}
/* Make the sidebar taller and adjust its layout */
.sidebar {
    height: 100%; /* Make it take full height */
    min-height: 100vh; /* Ensure it's at least as tall as the viewport */
    position: fixed; /* Keep it fixed on the left side */
    left: 0;
    top: 0;
    width: 205px; /* Match your current sidebar width */
    background-color: #1e272e; /* Maintain your dark theme */
    overflow-y: auto; /* Enable scrolling if needed */
    width:250px;
}

  </style>
</head>
<body>

<nav class="sidebar">
            <div class="logo">
                <h2>Admin Panel</h2>
            </div>
            <ul class="nav-links">
            <li ><a href="admin_product.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="admin_order_list.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="admindetails.php"><i class="fas fa-user-shield"></i> Admin</a></li>
                <li><a href="custdetails.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</li>
            </ul>
        </nav>

       
        <main class="main-content">
            <header class="top-bar">
                <div class="search">
                    <input type="text" id="productSearch" placeholder="Search products...">
                </div>
                <div class="user-info">
    <span>Admin User</span>
    <i class="fas fa-user-circle" style="font-size: 24px; color: #333;"></i>
</div>

            </header>

            <div class="dashboard">
                <h1 style="margin-left:50px;">Order Management</h1>

<div class="content">
  <a href="admin_order_list.php" class="back-button">
    <i class="fas fa-arrow-left"></i> Back to Order List
  </a>
  
  <h1>Order Details: #<?php echo htmlspecialchars($order_id); ?></h1>
  
  <?php if (!$order): ?>
    <div class="info-card">
      <p>Order not found. Please check the order ID and try again.</p>
    </div>
  <?php else: ?>
  
    <!-- Status Update Form -->
    <div class="status-form">
      <h3>Update Order Status</h3>
      <form method="POST">
        <select name="status">
          <option value="Pending" <?php if($order['OrderStatus'] == 'Pending') echo 'selected'; ?>>Pending</option>
          <option value="Processing" <?php if($order['OrderStatus'] == 'Processing') echo 'selected'; ?>>Processing</option>
          <option value="Completed" <?php if($order['OrderStatus'] == 'Completed') echo 'selected'; ?>>Completed</option>
          <option value="Cancelled" <?php if($order['OrderStatus'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
        </select>
        <button type="submit" name="update_status">Update Status</button>
      </form>
      
      <?php if (isset($status_message)): ?>
        <div class="status-message <?php echo strpos($status_message, 'Error') !== false ? 'error' : 'success'; ?>">
          <?php echo $status_message; ?>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Order Information -->
    <div class="order-info-container">
      <div class="info-card">
        <h3>Order Information</h3>
        <div class="info-item">
          <span class="info-label">Order ID:</span>
          <span><?php echo htmlspecialchars($order['OrderID']); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">Order Date:</span>
          <span><?php echo htmlspecialchars($order['OrderDate']); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">Status:</span>
          <span class="status-<?php echo strtolower($order['OrderStatus']); ?>">
            <?php echo htmlspecialchars($order['OrderStatus']); ?>
          </span>
        </div>
      </div>
      
      <div class="info-card">
        <h3>Customer Information</h3>
        <div class="info-item">
          <span class="info-label">Name:</span>
          <span><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">Email:</span>
          <span><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">Phone:</span>
          <span><?php echo htmlspecialchars($order['phonenum'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">Address:</span>
          <span><?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></span>
        </div>
      </div>
      
      <?php if ($payment): ?>
      <div class="info-card">
        <h3>Payment Information</h3>
        <div class="info-item">
          <span class="info-label">Payment ID:</span>
          <span><?php echo htmlspecialchars($payment['PaymentID']); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">Method:</span>
          <span><?php echo htmlspecialchars($payment['PaymentMethod']); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">Date:</span>
          <span><?php echo htmlspecialchars($payment['PaymentDate']); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">Status:</span>
          <span><?php echo htmlspecialchars($payment['PaymentStatus']); ?></span>
        </div>
        <?php if (!empty($payment['TransactionID'])): ?>
        <div class="info-item">
          <span class="info-label">Transaction ID:</span>
          <span><?php echo htmlspecialchars($payment['TransactionID']); ?></span>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    
    <!-- Order Items -->
    <h3>Order Items</h3>
    <table class="items-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Product</th>
          <th>Unit Price</th>
          <th>Quantity</th>
          <th>Discount</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($items_result->num_rows > 0): ?>
          <?php while($item = $items_result->fetch_assoc()): ?>
            <tr>
              <td>
                <?php if (!empty($item['image_url'])): ?>
                  <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Product" class="product-image">
                <?php else: ?>
                  <div class="product-image" style="background-color: #eee; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-image" style="color: #aaa;"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($item['product_name'] ?? $item['productId']); ?></td>
              <td>RM<?php echo number_format($item['UnitPrice'], 2); ?></td>
              <td><?php echo $item['Quantity']; ?></td>
              <td>RM<?php echo number_format($item['DiscountAmount'], 2); ?></td>
              <td>RM<?php echo number_format($item['FinalAmount'], 2); ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; padding: 20px;">No items found for this order</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    
    <!-- Order Summary -->
    <div class="order-summary">
      <h3>Order Summary</h3>
      <div class="summary-item">
        <span>Subtotal:</span>
        <span>RM<?php echo number_format($order['TotalAmount'], 2); ?></span>
      </div>
      <div class="summary-item">
        <span>Discount:</span>
        <span>RM<?php echo number_format($order['DiscountAmount'], 2); ?></span>
      </div>
      <div class="summary-item total">
        <span>Total:</span>
        <span>RM<?php echo number_format($order['FinalAmount'], 2); ?></span>
      </div>
    </div>
    
  <?php endif; ?>
</div>

</body>
</html>