<?php
session_start();
// Include database connection
if (file_exists('db.php')) {
    include 'db.php';
} else {
    
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

// Initialize parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query conditions
$where = "WHERE 1";
if (!empty($search)) {
  $search = $conn->real_escape_string($search);
  $where .= " AND OrderID LIKE '%$search%'";
}
if (!empty($status_filter)) {
  $status_filter = $conn->real_escape_string($status_filter);
  $where .= " AND OrderStatus = '$status_filter'";
}

// Query total order count
$total_sql = "SELECT COUNT(*) as total FROM `order` $where";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_orders = $total_row['total'];
$total_pages = ceil($total_orders / $limit);

// Get order data
$sql = "SELECT o.*, c.name as customer_name FROM `order` o 
        LEFT JOIN customer c ON o.customerid = c.customerid 
        $where ORDER BY OrderDate DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Order List</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin_product.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
    --primary-color: #4A90E2;
    --secondary-color: #2C3E50;
    --success-color: #2ECC71;
    --danger-color: #E74C3C;
    --dark-bg: #1E272E;
    --light-bg: #F5F6FA;
    --text-color: #2C3E50;
}

    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f4f6f8;
    }

    /* Sidebar */
    .sidebar {
      width: 220px;
      height: 100vh;
      background-color: #2c3e50;
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      padding-top: 20px;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 30px;
    }

    .sidebar a {
      display: block;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      transition: background 0.3s;
    }

    .sidebar a:hover {
      background-color: #34495e;
    }

    .sidebar a i {
      margin-right: 10px;
    }

    /* Main Content Area */
    .content {

      padding: 30px;

    }

    .content h1 {
      color: #5dade2; /* Light blue title */
      font-size: 28px;
      margin-bottom: 20px;
    
    }

    /* Search and Filter */
    .filter-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .filter-bar input, 
    .filter-bar select {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .filter-bar button {
      background-color: #5dade2;
      color: white;
      border: none;
      padding: 8px 18px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .filter-bar button:hover {
      background-color: #3498db;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0px 2px 8px rgba(0,0,0,0.05);
    }

    thead {
      background-color: #5dade2; /* Light blue header */
      color: white;
    }

    th, td {
      padding: 14px 18px;
      text-align: left;
    }

    tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    tbody tr:hover {
      background-color: #eef4fb;
      transition: background-color 0.3s;
    }

    /* Status text colors */
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

    /* View icon */
    .view-icon {
      color: #5dade2;
      font-size: 18px;
      cursor: pointer;
      transition: color 0.3s;
    }

    .view-icon:hover {
      color: #3498db;
    }

    /* Pagination */
    .pagination {
      margin-top: 20px;
      display: flex;
      gap: 8px;
    }

    .pagination a {
      padding: 8px 12px;
      border: 1px solid #5dade2;
      color: #5dade2;
      border-radius: 6px;
      text-decoration: none;
      transition: all 0.3s;
    }

    .pagination a.active,
    .pagination a:hover {
      background-color: #5dade2;
      color: white;
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
                <h1>Order Management</h1>

<div class="content">


  <form class="filter-bar" method="GET">
    <input type="text" name="search" placeholder="Search Order ID..." value="<?php echo htmlspecialchars($search); ?>">
    <select name="status">
      <option value="">All Status</option>
      <option value="Pending" <?php if($status_filter=='Pending') echo 'selected'; ?>>Pending</option>
      <option value="Processing" <?php if($status_filter=='Processing') echo 'selected'; ?>>Processing</option>
      <option value="Completed" <?php if($status_filter=='Completed') echo 'selected'; ?>>Completed</option>
    </select>
    <button type="submit">Filter</button>
  </form>
<div style="margin-bottom: 10px; font-size: 16px; color: #555;">
  Total Orders: <?php echo $total_orders; ?>
</div>

  <table>
    <thead>
      <tr>
        <th>Order ID</th>
        <th>Customer</th>
        <th>Order Date</th>
        <th>Total Amount</th>
        <th>Discount</th>
        <th>Final Amount</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
            <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Unknown'); ?></td>
            <td><?php echo htmlspecialchars($row['OrderDate']); ?></td>
            <td>RM<?php echo number_format($row['TotalAmount'], 2); ?></td>
            <td>RM<?php echo number_format($row['DiscountAmount'], 2); ?></td>
            <td>RM<?php echo number_format($row['FinalAmount'], 2); ?></td>
            <td class="status-<?php echo strtolower($row['OrderStatus']); ?>">
              <?php echo htmlspecialchars($row['OrderStatus']); ?>
            </td>
            <td><a href="admin_order_detail.php?order_id=<?php echo $row['OrderID']; ?>"><i class="fas fa-eye view-icon"></i></a></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" style="text-align: center; padding: 20px;">No orders found</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="pagination">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
      <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="<?php if($i == $page) echo 'active'; ?>">
        <?php echo $i; ?>
      </a>
    <?php endfor; ?>
  </div>
</div>

</body>
</html>