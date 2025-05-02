<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in as admin
function isAdminLoggedIn() {
    if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
        return true;
    }
    return false;
}

// Function to redirect with a message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $url");
    exit;
}

// Function to display a formatted price
function formatPrice($price) {
    return 'RM ' . number_format($price, 2);
}

// Function to generate a random string (for file names, etc.)
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Function to check if a product exists
function productExists($conn, $productId) {
    $stmt = $conn->prepare("SELECT productId FROM products WHERE productId = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Function to check if a category exists
function categoryExists($conn, $categoryId) {
    $stmt = $conn->prepare("SELECT categoryId FROM categories WHERE categoryId = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Function to log admin actions
function logAdminAction($conn, $adminId, $action, $details) {
    $stmt = $conn->prepare("INSERT INTO admin_logs (adminId, action, details, actionDate) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $adminId, $action, $details);
    $stmt->execute();
}

// Function to get product details
function getProductDetails($conn, $productId) {
    $stmt = $conn->prepare("SELECT p.*, c.categoryName FROM products p 
                           LEFT JOIN categories c ON p.categoryId = c.categoryId 
                           WHERE p.productId = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to upload an image
function uploadImage($file, $directory = 'images/products/') {
    // Create directory if it doesn't exist
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    $fileName = basename($file["name"]);
    $targetFile = $directory . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Generate unique filename
    $uniqueName = generateRandomString() . '.' . $imageFileType;
    $targetFile = $directory . $uniqueName;
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }
    
    // Check file size (limit to 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        return false;
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $uniqueName;
    } else {
        return false;
    }
}

// Function to delete a file
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

// Function to get all categories
function getAllCategories($conn) {
    $sql = "SELECT * FROM categories ORDER BY categoryName";
    $result = $conn->query($sql);
    $categories = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

// Function to validate product data
function validateProductData($data) {
    $errors = [];
    
    if (empty($data['productName'])) {
        $errors[] = "Product name is required";
    }
    
    if (empty($data['categoryId'])) {
        $errors[] = "Category is required";
    }
    
    if (!is_numeric($data['price']) || $data['price'] <= 0) {
        $errors[] = "Price must be a positive number";
    }
    
    if (!is_numeric($data['stock']) || $data['stock'] < 0) {
        $errors[] = "Stock must be a non-negative number";
    }
    
    return $errors;
}
?>