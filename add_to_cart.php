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
    die("Database connection failed: " . $e->getMessage());
}

// Get or create shopping cart
function getOrCreateCart($pdo, $customerId) {
    $stmt = $pdo->prepare("SELECT CartID FROM shoppingcart WHERE customerid = ? AND Status = 'Active' LIMIT 1");
    $stmt->execute([$customerId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($cart) {
        return $cart['CartID'];
    } else {
        $cartId = 'C' . uniqid();
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("INSERT INTO shoppingcart (CartID, CustomerID, CreatedDate, TotalAmount, Status) VALUES (?, ?, ?, 0.00, 'Active')");
        $success = $stmt->execute([$cartId, $customerId, $now]);
        if ($success) {
            return $cartId;
        } else {
            error_log(print_r($stmt->errorInfo(), true));
            return false;
        }
    }
}

// Add or update cart
function addOrUpdateCart($pdo, $cartId, $productId, $quantity, $action) {
    $productStmt = $pdo->prepare("SELECT stock FROM products WHERE productId = ?");
    $productStmt->execute([$productId]);
    $product = $productStmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        return ['success' => false, 'message' => 'Product does not exist.'];
    }
    if ($quantity < 1) {
        return ['success' => false, 'message' => 'Quantity cannot be less than 1.'];
    }
    $cartItemStmt = $pdo->prepare("SELECT CartItemID, Quantity FROM cartitem WHERE CartID = ? AND ProductId = ?");
    $cartItemStmt->execute([$cartId, $productId]);
    $cartItem = $cartItemStmt->fetch(PDO::FETCH_ASSOC);

    if ($action === 'update') {
        // Update quantity
        if (!$cartItem) {
            return ['success' => false, 'message' => 'Product not found in cart.'];
        }
        if ($quantity > $product['stock']) {
            return ['success' => false, 'message' => 'Insufficient stock.'];
        }
        $updateStmt = $pdo->prepare("UPDATE cartitem SET Quantity = ? WHERE CartItemID = ?");
        $updateStmt->execute([$quantity, $cartItem['CartItemID']]);
        return ['success' => true, 'message' => 'Product quantity updated.'];
    } elseif ($action === 'remove') {
        // Remove product
        if (!$cartItem) {
            return ['success' => false, 'message' => 'Product not found in cart.'];
        }
        $deleteStmt = $pdo->prepare("DELETE FROM cartitem WHERE CartItemID = ?");
        $deleteStmt->execute([$cartItem['CartItemID']]);
        return ['success' => true, 'message' => 'Product removed from cart.'];
    } else {
        // Add product
        if ($cartItem) {
            $newQty = $cartItem['Quantity'] + $quantity;
            if ($newQty > $product['stock']) {
                return ['success' => false, 'message' => 'Insufficient stock.'];
            }
            $updateStmt = $pdo->prepare("UPDATE cartitem SET Quantity = ? WHERE CartItemID = ?");
            $updateStmt->execute([$newQty, $cartItem['CartItemID']]);
        } else {
            if ($quantity > $product['stock']) {
                return ['success' => false, 'message' => 'Insufficient stock.'];
            }
            $cartItemId = 'CI' . uniqid(mt_rand(), true);
            $insertStmt = $pdo->prepare("INSERT INTO cartitem (CartItemID, CartID, ProductId, Quantity) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$cartItemId, $cartId, $productId, $quantity]);
        }
        return ['success' => true, 'message' => 'Product added to cart.'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? $_POST['product_id'] : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';

    if (empty($productId)) {
        $_SESSION['error'] = "Product ID cannot be empty.";
        header('Location: shopping_cart.php');
        exit();
    }

    $cartId = getOrCreateCart($pdo, $_SESSION['customerid']);
    if (!$cartId) {
        $_SESSION['error'] = "Unable to get or create cart.";
        header('Location: shopping_cart.php');
        exit();
    }

    $result = addOrUpdateCart($pdo, $cartId, $productId, $quantity, $action);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    header('Location: shopping_cart.php');
    exit();
} else {
    header('Location: customer_product.php');
    exit();
}