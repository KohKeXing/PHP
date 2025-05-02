<?php
session_start();
if (!isset($_SESSION['customerid'])) {
    header('Location: login.php');
    exit();
}

$host = 'localhost';
$dbname = 'graduation_store';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Security validation failed";
    header("Location: checkout.php");
    exit();
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
$finalAmount = isset($_POST['final_amount']) ? floatval($_POST['final_amount']) : 0;
$estimatedDelivery = isset($_POST['estimated_delivery']) ? $_POST['estimated_delivery'] : '';
$trackingNumber = isset($_POST['tracking_number']) ? $_POST['tracking_number'] : '';

// Validate required fields
if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($paymentMethod)) {
    $_SESSION['error'] = "All fields are required";
    header("Location: checkout.php");
    exit();
}

// Get cart items
$cartQuery = "SELECT ci.*, p.name, p.price, 
              (ci.Quantity * p.price) as TotalPrice,
              (p.price * 0.06) as SalesTax
              FROM cartitem ci 
              JOIN products p ON ci.productId = p.productId 
              JOIN shoppingcart sc ON ci.CartID = sc.CartID 
              WHERE sc.CustomerID = ? 
              AND sc.Status = 'Active'";
$cartStmt = $pdo->prepare($cartQuery);
$cartStmt->execute([$_SESSION['customerid']]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$grandTotal = 0;
$totalTax = 0;
foreach($cartItems as $item) {
    $grandTotal += $item['TotalPrice'];
    $totalTax += $item['SalesTax'] * $item['Quantity'];
}

$finalAmount = $grandTotal + $totalTax;

// Get cart ID
$cartQuery = "SELECT CartID FROM shoppingcart WHERE CustomerID = ? AND Status = 'Active'";
$cartStmt = $pdo->prepare($cartQuery);
$cartStmt->execute([$_SESSION['customerid']]);
$cart = $cartStmt->fetch(PDO::FETCH_ASSOC);
$cartId = $cart['CartID'];

// Process the order
try {
    $pdo->beginTransaction();
    
    // Generate OrderID (format: O0001, O0002, etc.)
    $orderIdQuery = "SELECT MAX(CAST(SUBSTRING(OrderID, 2) AS UNSIGNED)) as max_id FROM `order`";
    $orderIdStmt = $pdo->query($orderIdQuery);
    $maxId = $orderIdStmt->fetch(PDO::FETCH_ASSOC)['max_id'];
    $nextId = ($maxId) ? $maxId + 1 : 1;
    $orderId = 'O' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    
    // Insert into order table
    $orderQuery = "INSERT INTO `order` (OrderID, customerid, OrderDate, TotalAmount, DiscountAmount, FinalAmount, OrderStatus) 
                  VALUES (?, ?, NOW(), ?, 0, ?, 'Pending')";
    $orderStmt = $pdo->prepare($orderQuery);
    $orderStmt->execute([$orderId, $_SESSION['customerid'], $grandTotal, $finalAmount]);
    
    // Insert order details
    foreach($cartItems as $item) {
        // Generate OrderDetailID (format: OD0001, OD0002, etc.)
        $detailIdQuery = "SELECT MAX(CAST(SUBSTRING(OrderDetailID, 3) AS UNSIGNED)) as max_id FROM orderdetail";
        $detailIdStmt = $pdo->query($detailIdQuery);
        $maxDetailId = $detailIdStmt->fetch(PDO::FETCH_ASSOC)['max_id'];
        $nextDetailId = ($maxDetailId) ? $maxDetailId + 1 : 1;
        $orderDetailId = 'OD' . str_pad($nextDetailId, 3, '0', STR_PAD_LEFT);
        
        $detailQuery = "INSERT INTO orderdetail (OrderDetailID, OrderID, productId, Quantity, UnitPrice, TotalAmount, DiscountAmount, FinalAmount) 
                       VALUES (?, ?, ?, ?, ?, ?, 0, ?)";
        $detailStmt = $pdo->prepare($detailQuery);
        $detailStmt->execute([
            $orderDetailId,
            $orderId,
            isset($item['ProductId']) ? $item['ProductId'] : (isset($item['productId']) ? $item['productId'] : ''),
            $item['Quantity'],
            $item['price'],
            $item['TotalPrice'],
            $item['TotalPrice']
        ]);
        
        // Update product stock
       $updateStockQuery = "UPDATE products SET stock = stock - ? WHERE productId = ?";
        $updateStockStmt = $pdo->prepare($updateStockQuery);
        $updateStockStmt->execute([$item['Quantity'], isset($item['ProductId']) ? $item['ProductId'] : (isset($item['productId']) ? $item['productId'] : '')]);
    }
    
    // Generate PaymentID (format: P0001, P0002, etc.)
    $paymentIdQuery = "SELECT MAX(CAST(SUBSTRING(PaymentID, 2) AS UNSIGNED)) as max_id FROM payment";
    $paymentIdStmt = $pdo->query($paymentIdQuery);
    $maxPaymentId = $paymentIdStmt->fetch(PDO::FETCH_ASSOC)['max_id'];
    $nextPaymentId = ($maxPaymentId) ? $maxPaymentId + 1 : 1;
    $paymentId = 'P' . str_pad($nextPaymentId, 4, '0', STR_PAD_LEFT);
    
    // Insert payment record
    $paymentQuery = "INSERT INTO payment (PaymentID, OrderID, PaymentMethod, PaymentDate, PaymentAmount, PaymentStatus, TransactionID, name) 
                 VALUES (?, ?, ?, NOW(), ?, 'Completed', ?, ?)";
$paymentStmt = $pdo->prepare($paymentQuery);
$paymentStmt->execute([
    $paymentId,
    $orderId,
    $paymentMethod,
    $finalAmount,
    'TXN' . strtoupper(bin2hex(random_bytes(4))),
    $name  // Customer's name to be added
]);
    
    // Update cart status to Completed
    $updateCartQuery = "UPDATE shoppingcart SET Status = 'Completed' WHERE CartID = ?";
    $updateCartStmt = $pdo->prepare($updateCartQuery);
    $updateCartStmt->execute([$cartId]);
    
    $pdo->commit();
    $orderSuccess = true;
} catch (PDOException $e) {
    $pdo->rollBack();
    $orderSuccess = false;
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f4f2fb;
            color: #333;
        }
        .confirmation-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(112, 76, 160, 0.2);
            text-align: center;
        }
        .success-icon {
            font-size: 60px;
            color: #27ae60;
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 60px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        h2 {
            color: #6b3fa0;
            margin-bottom: 20px;
        }
        .order-details {
            background: #f1e9ff;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        .order-details p {
            margin: 8px 0;
        }
        .order-id {
            font-size: 1.2em;
            font-weight: bold;
            color: #6b3fa0;
        }
        .btn {
            background: #6b3fa0;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #5a2e8a;
        }
        .order-items {
            margin: 20px 0;
            width: 100%;
            border-collapse: collapse;
        }
        .order-items th {
            background: #6b3fa0;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .order-items td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .print-btn {
            background: #3498db;
            margin-left: 10px;
        }
        .print-btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <?php if ($orderSuccess): ?>
            <i class="fas fa-check-circle success-icon"></i>
            <h2>Order Confirmed!</h2>
            <p>Thank you for your purchase. Your order has been successfully placed.</p>
            
            <div class="order-details">
                <p class="order-id">Order ID: <?= htmlspecialchars($orderId) ?></p>
                <p>Customer: <?= htmlspecialchars($name) ?></p>
                <p>Email: <?= htmlspecialchars($email) ?></p>
                <p>Phone: <?= htmlspecialchars($phone) ?></p>
                <p>Shipping Address: <?= htmlspecialchars($address) ?></p>
                <p>Payment Method: <?= htmlspecialchars($paymentMethod) ?></p>
                <p>Estimated Delivery: <?= htmlspecialchars($estimatedDelivery) ?></p>
                <p>Tracking Number: <?= htmlspecialchars($trackingNumber) ?></p>
            </div>
            
            <table class="order-items">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cartItems as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= $item['Quantity'] ?></td>
                            <td>RM <?= number_format($item['price'], 2) ?></td>
                            <td>RM <?= number_format($item['TotalPrice'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Subtotal:</strong></td>
                        <td>RM <?= number_format($grandTotal, 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Tax:</strong></td>
                        <td>RM <?= number_format($totalTax, 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Final Total:</strong></td>
                        <td>RM <?= number_format($finalAmount, 2) ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div>
                <a href="customer_product.php" class="btn">Continue Shopping</a>
                <button onclick="window.print()" class="btn print-btn">Print Receipt</button>
            </div>
        <?php else: ?>
            <i class="fas fa-times-circle error-icon"></i>
            <h2>Order Failed</h2>
            <p>We're sorry, but there was an error processing your order.</p>
            <p>Error: <?= htmlspecialchars($errorMessage ?? 'Unknown error') ?></p>
            <a href="checkout.php" class="btn">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>