<?php
    session_start(); // Start the session at the beginning
    
    // Check if user is logged in
    if (!isset($_SESSION['customerid'])) {
        header('Location: login.php');
        exit();
    }

    // Database connection
    $host = 'localhost';
    $dbname = 'graduation_store';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

    // Initialize variables
    $cartItems = [];
    $total = 0;
    $totalTax = 0;
    $grandTotal = 0;
    
    // Get user's current shopping cart
    $cartQuery = "SELECT CartID FROM shoppingcart WHERE CustomerID = ? AND Status = 'Active'";
    $cartStmt = $pdo->prepare($cartQuery);
    $cartStmt->execute([$_SESSION['customerid']]);
    $cart = $cartStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cart) {
        // Get items in cart with collation fix
        $itemsQuery = "SELECT ci.CartItemID, ci.ProductId, ci.Quantity, 
                      p.name, p.image_url, p.price as Price, p.stock 
                      FROM cartitem ci 
                      JOIN products p ON ci.productId COLLATE utf8mb4_general_ci = p.productId COLLATE utf8mb4_general_ci
                      WHERE ci.CartID = ?";
        $itemsStmt = $pdo->prepare($itemsQuery);
        $itemsStmt->execute([$cart['CartID']]);
        $cartItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate totals and tax
        foreach($cartItems as &$item) {
            $subtotal = $item['Price'] * $item['Quantity'];
            $tax = $subtotal * 0.06;
            
            $item['subtotal'] = $subtotal;
            $item['tax'] = $tax;
            
            $total += $subtotal;
            $totalTax += $tax;
        }
        unset($item);
        $grandTotal = $total + $totalTax;
    } else {
        // If no active cart exists, create one
        $cartId = 'C' . uniqid();
        $createCartQuery = "INSERT INTO shoppingcart (CartID, CustomerID, Status, CreatedAt) VALUES (?, ?, 'Active', NOW())";
        $createCartStmt = $pdo->prepare($createCartQuery);
        $createCartStmt->execute([$cartId, $_SESSION['customerid']]);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - TARUMT Graduation Store</title>
    <link rel="stylesheet" href="customer_product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', 'PingFang SC', sans-serif;
            background-color: #f7f6fc;
            margin: 0;
            padding: 0;
        }

        .cart-container {
            max-width: 1200px;
            margin: 50px auto 140px;
            background: linear-gradient(145deg, #ffffff, #f8f7fd);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(124, 58, 237, 0.12);
        }

        .cart-container h2 {
            font-size: 36px;
            font-weight: 800;
            background: linear-gradient(90deg, #7c3aed, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 40px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(124, 58, 237, 0.1);
            transition: all 0.4s ease;
        }

        .cart-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.15);
        }

        .cart-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 16px;
            border: 2px solid #f0ebfe;
            transition: transform 0.3s ease;
        }

        .cart-item:hover img {
            transform: scale(1.05);
        }

        .item-details {
            flex: 1;
            margin-left: 30px;
            position: relative;
        }

        .item-details h3 {
            font-size: 22px;
            margin: 0 0 15px;
            color: #4c1d95;
        }

        .item-details p {
            margin: 8px 0;
            font-size: 16px;
            color: #6b7280;
        }

        .price {
            font-weight: 600;
            color: #7c3aed;
            font-size: 18px;
        }

        .qty-btn {
            width: 36px;
            height: 36px;
            background: linear-gradient(145deg, #f1ebff, #ffffff);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 50%;
            font-size: 18px;
            color: #7c3aed;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .qty-btn:hover {
            background: linear-gradient(145deg, #7c3aed, #8b5cf6);
            color: white;
            transform: scale(1.1);
        }

        .qty-input {
            width: 60px;
            height: 36px;
            text-align: center;
            border: 2px solid #e9e4fd;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            color: #7c3aed;
            margin: 0 12px;
        }

        .cart-summary {
            background: linear-gradient(145deg, #ffffff, #f8f7fd);
            border-radius: 20px;
            padding: 30px;
            margin-top: 40px;
            box-shadow: 0 8px 30px rgba(124, 58, 237, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.1);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px dashed rgba(124, 58, 237, 0.1);
        }

        .grand-total {
            font-size: 26px;
            background: linear-gradient(90deg, #7c3aed, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        .checkout-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 45px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white;
            border-radius: 100px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.4s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(124, 58, 237, 0.3);
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
            background: linear-gradient(135deg, #8b5cf6, #c084fc);
        }

        .empty-cart {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-cart p {
            font-size: 22px;
            color: #666;
            margin-bottom: 30px;
        }

        .remove-btn {
            position: absolute;
            top: 0;
            right: 0;
            background: none;
            border: none;
            color: #dc2626;
            font-size: 20px;
            padding: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 50%;
        }

        .remove-btn:hover {
            background: rgba(220, 38, 38, 0.1);
            transform: scale(1.1);
        }

        .qty-container {
            display: inline-flex;
            align-items: center;
            background: #f8f7ff;
            padding: 8px 16px;
            border-radius: 100px;
            margin: 15px 0;
            border: 1px solid rgba(124, 58, 237, 0.1);
        }

        .error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error:before {
            content: '⚠️';
            font-size: 20px;
        }

        .error-toast, .success-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .error-toast {
            background: #dc2626;
            color: white;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }

        .success-toast {
            background: #10b981;
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .error-toast.show, .success-toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        @media (max-width: 768px) {
            .cart-container {
                margin: 20px 15px;
                padding: 25px;
            }

            .cart-item {
                flex-direction: column;
                text-align: center;
            }

            .checkout-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h2>Shopping Cart</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <p>Your shopping cart is empty</p>
                <a href="customer_product.php" class="checkout-btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach($cartItems as $item): ?>
                    <div class="cart-item" data-id="<?= htmlspecialchars($item['ProductId']) ?>" data-stock="<?= htmlspecialchars($item['stock']) ?>">
                        <img src="<?= !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'img/placeholder.jpg' ?>" 
                            alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p>Price: RM <?= number_format($item['Price'], 2) ?></p>
                            <div class="qty-container">
                                <form action="add_to_cart.php" method="POST" class="quantity-form">
                                    <input type="hidden" name="product_id" value="<?= $item['ProductId'] ?>">
                                    <input type="hidden" name="action" value="update">
                                    <button type="button" class="qty-btn decrease" onclick="decreaseQuantity(this)">-</button>
                                    <input type="text" name="quantity" class="qty-input" value="<?= $item['Quantity'] ?>" readonly>
                                    <button type="button" class="qty-btn increase" onclick="increaseQuantity(this, <?= $item['stock'] ?>)">+</button>
                                    <button type="submit" style="display:none" id="update-btn-<?= $item['ProductId'] ?>">Update</button>
                                </form>
                            </div>
                            <p class="tax">Tax (6%): RM <?= number_format($item['tax'], 2) ?></p>
                            <p class="total">Total: RM <?= number_format($item['subtotal'], 2) ?></p>
                            <form action="add_to_cart.php" method="POST" class="remove-form">
                                <input type="hidden" name="product_id" value="<?= $item['ProductId'] ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="remove-btn" title="Remove Item"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>RM <?= number_format($total, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (6%):</span>
                    <span>RM <?= number_format($totalTax, 2) ?></span>
                </div>
                <div class="summary-row grand-total">
                    <span>Grand Total:</span>
                    <span>RM <?= number_format($grandTotal, 2) ?></span>
                </div>
            </div>

            <div style="text-align: right; margin-top: 20px;">
                <a href="customer_product.php" class="checkout-btn" style="margin-right: 10px;">Continue Shopping</a>
                <a href="checkout.php" class="checkout-btn">Checkout</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function decreaseQuantity(btn) {
            const form = btn.closest('form');
            const input = form.querySelector('.qty-input');
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
                document.getElementById('update-btn-' + form.querySelector('[name="product_id"]').value).click();
            } else {
                showError('Quantity cannot be less than 1');
            }
        }

        function increaseQuantity(btn, maxStock) {
            const form = btn.closest('form');
            const input = form.querySelector('.qty-input');
            let value = parseInt(input.value);
            if (value < maxStock) {
                input.value = value + 1;
                document.getElementById('update-btn-' + form.querySelector('[name="product_id"]').value).click();
            } else {
                showError('Insufficient stock. Maximum quantity is ' + maxStock);
            }
        }

        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-toast';
            errorDiv.textContent = message;
            document.body.appendChild(errorDiv);
            
            setTimeout(() => {
                errorDiv.classList.add('show');
                setTimeout(() => {
                    errorDiv.classList.remove('show');
                    setTimeout(() => errorDiv.remove(), 300);
                }, 2000);
            }, 100);
        }

        function showSuccess(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-toast';
            successDiv.textContent = message;
            document.body.appendChild(successDiv);
            
            setTimeout(() => {
                successDiv.classList.add('show');
                setTimeout(() => {
                    successDiv.classList.remove('show');
                    setTimeout(() => successDiv.remove(), 300);
                }, 2000);
            }, 100);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.remove-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Are you sure you want to remove this item?')) {
                        e.preventDefault();
                    }
                });
            });
        });

        <?php if (isset($_SESSION['success'])): ?>
            showSuccess('<?= htmlspecialchars($_SESSION['success']) ?>');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            showError('<?= htmlspecialchars($_SESSION['error']) ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>