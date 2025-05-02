<?php
session_start();
if (!isset($_SESSION['customerid'])) {
    echo json_encode(['count' => 0]);
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
    echo json_encode(['count' => 0, 'error' => $e->getMessage()]);
    exit();
}

// Get cart count
$cartQuery = "SELECT SUM(ci.Quantity) as count 
              FROM cartitem ci 
              JOIN shoppingcart sc ON ci.CartID = sc.CartID 
              WHERE sc.CustomerID = ? 
              AND sc.Status = 'Active'";
$cartStmt = $pdo->prepare($cartQuery);
$cartStmt->execute([$_SESSION['customerid']]);
$result = $cartStmt->fetch(PDO::FETCH_ASSOC);

$count = $result['count'] ? intval($result['count']) : 0;
echo json_encode(['count' => $count]);
?>