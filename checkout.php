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

// Get cart items
$cartQuery = "SELECT ci.*, p.name, p.image_url, p.price, 
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

if (count($cartItems) === 0) {
    header("Location: shopping_cart.php");
    exit();
}

$finalAmount = $grandTotal + $totalTax;

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Generate estimated delivery date and tracking number
$estimatedDeliveryDate = date('Y-m-d', strtotime('+3 days'));
$trackingNumber = strtoupper(bin2hex(random_bytes(5)));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f4f2fb;
            color: #333;
        }
        .checkout-container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(112, 76, 160, 0.2);
        }
        h2 {
            color: #6b3fa0;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 30px;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"], input[type="tel"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .payment-options {
            display: flex;
            flex-direction: column;
        }
        .payment-options label {
            margin-top: 8px;
        }
        .order-summary {
            background: #f1e9ff;
            padding: 20px;
            border-radius: 10px;
        }
        .order-summary p {
            margin: 8px 0;
        }
        .total-line {
            font-weight: bold;
            font-size: 1.1em;
        }
        .checkout-btn {
            background: #6b3fa0;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .checkout-btn:hover {
            background: #5a2e8a;
        }
        .loader {
            display: none;
            margin-left: 10px;
            width: 20px;
            height: 20px;
            border: 3px solid #fff;
            border-top: 3px solid #6b3fa0;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .payment-icons {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 15px;
        }
        .payment-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 150px;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            background: #faf6ff;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        .payment-option:hover {
            box-shadow: 0 0 12px rgba(107, 63, 160, 0.3);
            transform: scale(1.05);
            background: #f1e9ff;
        }
        .payment-option input[type="radio"] {
            position: absolute;
            top: 10px;
            left: 10px;
            transform: scale(1.2);
        }
        .payment-option img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
            background: #f5edff;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(107, 63, 160, 0.1);
            transition: transform 0.3s ease;
        }
        .payment-option span {
            font-size: 14px;
            color: #6b3fa0;
            font-weight: 500;
        }
        @media (max-width: 600px) {
            .payment-icons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <h2>Checkout</h2>
        <form action="confirmation.php" method="POST" id="checkoutForm">
        <input type="hidden" name="final_amount" value="<?= $finalAmount ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="estimated_delivery" value="<?= $estimatedDeliveryDate ?>">
            <input type="hidden" name="tracking_number" value="<?= $trackingNumber ?>">

            <!-- Customer Info -->
            <div class="section">
                <h3>Customer Information</h3>
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required placeholder="e.g. John Doe">

                <label for="email">Email</label>
                <input type="email" name="email" id="email" required pattern="[^@\s]+@[^@\s]+\.[^@\s]+" placeholder="e.g. john@example.com">

                <label for="phone">Phone Number</label>
                <input type="tel" name="phone" id="phone" required pattern="^\+?6?01[0-46-9]-?[0-9]{7,8}$" placeholder="e.g. +60123456789">
            </div>

            <!-- Address -->
            <div class="section">
                <h3>Shipping Address</h3>
                <label for="address">Address</label>
                <input type="text" name="address" id="address" required placeholder="e.g. 123, Jalan XYZ, Kuala Lumpur">
            </div>

            <!-- Payment Method -->
            <div class="section">
                <h3>Payment Method</h3>
                <div class="payment-icons">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Credit/Debit Card" required checked onclick="showPaymentDetails('card')">
                        <div class="icon-wrapper">
                             <i class="fas fa-credit-card fa-2x"></i>
                        </div>
                        <span>Credit/Debit Card</span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="E-Wallet" onclick="showPaymentDetails('ewallet')">
                        <div class="icon-wrapper">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                        <span>E-Wallet</span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Bank Transfer" onclick="showPaymentDetails('bank')">
                        <div class="icon-wrapper">
                            <i class="fas fa-university fa-2x"></i>
                        </div>
                        <span>Bank Transfer</span>
                    </label>
                </div>
                <div id="card-details" class="payment-details">
                    <label>Card Number</label>
                    <input type="text" name="card_number" placeholder="Card Number">
                    <label>Expiry Date</label>
                    <input type="text" name="card_expiry" placeholder="MM/YY">
                    <label>CVV</label>
                    <input type="text" name="card_cvv" placeholder="CVV">
                </div>
                <div id="ewallet-details" class="payment-details" style="display:none;">
                    <label>E-Wallet Account</label>
                    <input type="text" name="ewallet_account" placeholder="E-Wallet Account/Phone">
                </div>
                <div id="bank-details" class="payment-details" style="display:none;">
                    <label>Bank Transfer Reference</label>
                    <input type="text" name="bank_reference" placeholder="Bank Transfer Reference Number">
                </div>
            </div>

<!-- Order Summary -->
<div class="section order-summary">
    <h3>Order Summary</h3>
    <?php foreach ($cartItems as $item): ?>
        <p><?= htmlspecialchars($item['name']) ?> × <?= $item['Quantity'] ?> - RM <?= number_format($item['Price'] * $item['Quantity'], 2) ?></p>
    <?php endforeach; ?>
    <p class="total-line">Subtotal: RM <?= number_format($grandTotal, 2) ?></p>
    <p class="total-line">Tax: RM <?= number_format($totalTax, 2) ?></p>
    <p class="total-line">Final Total: RM <?= number_format($finalAmount, 2) ?></p>
    <p>Estimated Delivery: <strong><?= $estimatedDeliveryDate ?></strong></p>
    <p>Tracking Number: <strong><?= $trackingNumber ?></strong></p>
</div>

<div style="display: flex; justify-content: space-between; margin-top: 20px;">
    <a href="shopping_cart.php" class="checkout-btn" style="background: #888;">
        <i class="fas fa-arrow-left"></i> Back to Cart
    </a>
    <button type="submit" class="checkout-btn" id="submitBtn">
        Place Order <i class="fas fa-arrow-right"></i>
        <span class="loader" id="loader"></span>
    </button>
</div>

 <script>
        function showPaymentDetails(type) {
            document.getElementById('card-details').style.display = (type === 'card') ? 'block' : 'none';
            document.getElementById('ewallet-details').style.display = (type === 'ewallet') ? 'block' : 'none';
            document.getElementById('bank-details').style.display = (type === 'bank') ? 'block' : 'none';
        }
      
        window.onload = function() {
            const checked = document.querySelector('input[name="payment_method"]:checked').value;
            if (checked === 'Credit/Debit Card') showPaymentDetails('card');
            else if (checked === 'E-Wallet') showPaymentDetails('ewallet');
            else showPaymentDetails('bank');
        };

        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loader = document.getElementById('loader');
            const phone = document.getElementById('phone');
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const address = document.getElementById('address');
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

           
            if (!name.value.trim()) {
                e.preventDefault();
                alert('Please enter the full name');
                name.focus();
                return;
            }
            if (!email.value.trim() || !email.checkValidity()) {
                e.preventDefault();
                alert('Please enter the email in valid');
                email.focus();
                return;
            }
            const phoneRegex = /^\+?6?01[0-46-9]-?[0-9]{7,8}$/;
            if (!phoneRegex.test(phone.value)) {
                e.preventDefault();
                alert('Please enter valid phone number');
                phone.focus();
                return;
            }
            if (!address.value.trim()) {
                e.preventDefault();
                alert('Please enter address');
                address.focus();
                return;
            }

           
            if (paymentMethod === 'Credit/Debit Card') {
                const cardNumber = document.querySelector('input[name="card_number"]');
                const cardExpiry = document.querySelector('input[name="card_expiry"]');
                const cardCVV = document.querySelector('input[name="card_cvv"]');
                if (!cardNumber.value.trim() || !/^\d{12,19}$/.test(cardNumber.value.replace(/\s/g, ''))) {
                    e.preventDefault();
                    alert('Please enter valid cardNumber（12-19 numbers）');
                    cardNumber.focus();
                    return;
                }
                if (!cardExpiry.value.trim() || !/^(0[1-9]|1[0-2])\/\d{2}$/.test(cardExpiry.value)) {
                    e.preventDefault();
                    alert('Please enter the valid date format（format MM/YY）');
                    cardExpiry.focus();
                    return;
                }
                if (!cardCVV.value.trim() || !/^\d{3,4}$/.test(cardCVV.value)) {
                    e.preventDefault();
                    alert('please enter valid CVV (3-4)');
                    cardCVV.focus();
                    return;
                }
            } else if (paymentMethod === 'E-Wallet') {
                const ewalletAccount = document.querySelector('input[name="ewallet_account"]');
                if (!ewalletAccount.value.trim()) {
                    e.preventDefault();
                    alert('Please enter ewallet account');
                    ewalletAccount.focus();
                    return;
                }
            } else if (paymentMethod === 'Bank Transfer') {
                const bankReference = document.querySelector('input[name="bank_reference"]');
                if (!bankReference.value.trim()) {
                    e.preventDefault();
                    alert('Please enter bank transfer reference number');
                    bankReference.focus();
                    return;
                }
            }

            submitBtn.disabled = true;
            loader.style.display = 'inline-block';
        });


document.querySelector('.close-btn').addEventListener('click', function() {
    document.querySelector('.announcement-bar').style.display = 'none';
});


</script>
</body>
</html>
