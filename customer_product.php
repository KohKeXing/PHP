<?php
// Database connection
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

// Fetch categories for navigation
$categoryQuery = "SELECT * FROM categories";
$categoryStmt = $pdo->query($categoryQuery);
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subcategories
$subcategoryQuery = "SELECT * FROM subcategories";
$subcategoryStmt = $pdo->query($subcategoryQuery);
$subcategories = $subcategoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if category or subcategory is selected
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
$selectedSubcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : null;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : null;

// Fetch products based on selection
if ($searchTerm) {
    $productQuery = "SELECT * FROM products WHERE name LIKE :searchTerm OR description LIKE :searchTerm";
    $productStmt = $pdo->prepare($productQuery);
    $productStmt->execute(['searchTerm' => "%$searchTerm%"]);
    $pageTitle = "Search Results for: " . htmlspecialchars($searchTerm);
} elseif ($selectedSubcategory) {
    $productQuery = "SELECT * FROM products WHERE subcategoryId = :subcategoryId";
    $productStmt = $pdo->prepare($productQuery);
    $productStmt->execute(['subcategoryId' => $selectedSubcategory]);
    $pageTitle = "Products in " . getSubcategoryName($selectedSubcategory, $subcategories);
} elseif ($selectedCategory) {
    $productQuery = "SELECT * FROM products WHERE categoryId = :categoryId";
    $productStmt = $pdo->prepare($productQuery);
    $productStmt->execute(['categoryId' => $selectedCategory]);
    $pageTitle = "Products in " . getCategoryName($selectedCategory, $categories);
} else {
    $productQuery = "SELECT * FROM products";
    $productStmt = $pdo->query($productQuery);
    $pageTitle = "All Products";
}

$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Helper functions to get names
function getCategoryName($categoryId, $categories) {
    foreach ($categories as $category) {
        if ($category['categoryId'] == $categoryId) {
            return $category['name'];
        }
    }
    return "Unknown Category";
}

function getSubcategoryName($subcategoryId, $subcategories) {
    foreach ($subcategories as $subcategory) {
        if ($subcategory['subcategoryId'] == $subcategoryId) {
            return $subcategory['name'];
        }
    }
    return "Unknown Subcategory";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TARUMT Graduation Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
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
    --shadow: 0 4px 12px rgba(0,0,0,0.1);
    --hover-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }
  
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
  }
  
  body {
    background: linear-gradient(-45deg, #e3eefe 0%, #efddfb 100%);
    overflow-x: hidden;
    color: var(--text-color);
  }
  
  .main-header {
    width: 100%;
    background: #fff;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    position: fixed;
    top: 0;
    z-index: 1000;
  }
  
  .logo {
    font-size: 24px;
    font-weight: bold;
    color: var(--primary-color);
  }
  
  .menu-icon {
    display: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--primary-color);
  }
  
  #menu-toggle {
    display: none;
  }
  
  .menu {
    display: flex;
    gap: 20px;
  }
  
  .menu a {
    color: var(--text-color);
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 5px;
    transition: all 0.3s ease;
  }
  
  .menu a:hover,
  .menu a.active {
    background: linear-gradient(to right, var(--primary-light), var(--primary-color));
    color: #fff;
  }
  
  .menu a i {
    margin-right: 8px;
  }
  
  /* Navigation */
  .main-nav {
    background-color: white;
    padding: 0 50px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    justify-content: center;
    margin-top: 70px;
  }
  
  .nav-menu {
    list-style: none;
    display: flex;
    gap: 40px;
    margin: 0;
    padding: 0;
    max-width: 1200px;
    width: 100%;
    justify-content: center;
  }
  
  .nav-item {
    position: relative;
    padding: 15px 0;
  }
  
  .nav-item a {
    text-decoration: none;
    color: var(--text-color);
    padding: 15px 10px;
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.5px;
    transition: all 0.3s;
  }
  
  .nav-item > a:after {
    content: '';
    display: block;
    width: 0;
    height: 2px;
    background: var(--primary-color);
    transition: width 0.3s;
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
  }
  
  .nav-item:hover > a:after {
    width: 100%;
  }
  
  /* Dropdown Menu */
  .dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    background-color: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    min-width: 200px;
    border-radius: 8px;
    padding: 15px 0;
    transform: translateY(10px);
    transition: all 0.3s;
    opacity: 0;
    visibility: hidden;
    display: block;
    z-index: 1000;
  }
  
  .nav-item:hover .dropdown {
    transform: translateY(0);
    opacity: 1;
    visibility: visible;
  }
  
  .dropdown li {
    list-style: none;
  }
  
  .dropdown li a {
    padding: 12px 25px;
    display: block;
    color: var(--text-color);
    font-weight: 500;
    text-transform: none;
    letter-spacing: normal;
    transition: all 0.3s;
  }
  
  .dropdown li a:hover {
    background: linear-gradient(to right, rgba(167, 89, 245, 0.1), transparent);
    color: var(--primary-color);
    padding-left: 30px;
  }
  
    /* Products Grid */
    .container {
    max-width: 1200px;
    margin: 80px auto 40px; /* Reduced from 120px to 80px */
    padding: 0 20px;
  }
  
  .products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
    padding: 20px 0;
  }
  
  /* Section Title */
  .section-title {
    text-align: center;
    margin: 0 0 30px; /* Changed from 20px 0 40px to 0 0 30px */
    font-size: 32px;
    color: var(--secondary-color);
    position: relative;
  }
  .section-title:after {
    content: '';
    display: block;
    width: 80px;
    height: 3px;
    background: var(--primary-color);
    margin: 15px auto 0;
  }
  
  
  .product-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
  }
  
  .product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 25px rgba(167, 89, 245, 0.2);
  }
  
  .product-card img {
    width: 70%;
    height: 280px;
    object-fit: cover;
    transition: transform 0.5s ease;
    display: block;
    margin: 0 auto;
    margin-top:100px;
  }
  
  .product-image-link {
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    height: 250px;
  }
  
  .product-card:hover img {
    transform: scale(1.05);
  }
  
  .product-info {
    padding: 20px;
  }
  
  .product-title {
    font-size: 1.2em;
    font-weight: 600;
    color: var(--secondary-color);
    margin-bottom: 10px;
  }
  
  .product-title a {
    color: var(--secondary-color);
    text-decoration: none;
    transition: color 0.3s;
  }
  
  .product-title a:hover {
    color: var(--primary-color);
  }
  
  .product-price {
    font-size: 1.4em;
    color: var(--primary-color);
    font-weight: 700;
    margin: 10px 0;
  }
  
  .product-description {
    color: var(--light-text);
    font-size: 0.9em;
    margin-bottom: 15px;
    line-height: 1.5;
  }
  
  .add-to-cart-btn {
    width: 280px;
    padding: 12px;
    background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .eye {
    width: 10%; /* Reduced from 15% to 10% */
    padding: 8px; /* Reduced from 12px to 8px */
    background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .add-to-cart-btn:hover {
    background: linear-gradient(45deg, var(--primary-light), var(--primary-color));
    transform: translateY(-2px);
  }
  
  .eye:hover {
    background: linear-gradient(45deg, var(--primary-light), var(--primary-color));
    transform: translateY(-2px);
  }
  
  .product-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #E74C3C;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
    z-index: 1;
  }
  
  .product-rating {
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 10px 0;
  }
  
  .product-rating .stars {
    color: #FFD700;
  }
  
  .product-rating .count {
    color: var(--light-text);
    font-size: 0.9em;
  }
  
  /* No Products Message */
  .no-products {
    text-align: center;
    padding: 50px 0;
    font-size: 18px;
    color: var(--light-text);
  }
  
  .no-products p {
    margin-bottom: 20px;
  }
  
  /* Footer */
  .main-footer {
    background: #2C3E50;
    color: white;
    padding: 50px 0 0;
  }
  
  .footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    padding: 0 20px;
  }
  
  .footer-section h3 {
    font-size: 18px;
    margin-bottom: 20px;
    position: relative;
  }
  
  .footer-section h3:after {
    content: '';
    display: block;
    width: 50px;
    height: 2px;
    background: var(--primary-color);
    margin-top: 10px;
  }
  
  .footer-section p {
    margin-bottom: 10px;
    opacity: 0.8;
  }
  
  .footer-section ul {
    list-style: none;
  }
  
  .footer-section ul li {
    margin-bottom: 10px;
  }
  
  .footer-section ul li a {
    color: white;
    text-decoration: none;
    opacity: 0.8;
    transition: all 0.3s;
  }
  
  .footer-section ul li a:hover {
    opacity: 1;
    padding-left: 5px;
    color: var(--primary-light);
  }
  
  .social-icons {
    display: flex;
    gap: 15px;
    margin-top: 15px;
  }
  
  .social-icons a {
    color: white;
    font-size: 18px;
    transition: all 0.3s;
  }
  
  .social-icons a:hover {
    color: var(--primary-color);
    transform: translateY(-3px);
  }
  
  .footer-bottom {
    background: #1a252f;
    text-align: center;
    padding: 20px 0;
    margin-top: 40px;
  }
  
  .footer-bottom p {
    font-size: 14px;
    opacity: 0.7;
  }
  
  /* Product action buttons */
  .product-actions {
    display: flex;
    gap: 10px;
  }
  
  .view-details-btn {
    width: auto;
    padding: 0;
    background: transparent;
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .view-details-btn:hover {
    background: transparent;
    transform: none;
  }
  
  /* Announcement Bar */
  .announcement-bar {
    background: linear-gradient(to right, var(--primary-color), var(--primary-light));
    color: white;
    text-align: center;
    padding: 10px 20px;
    position: relative;
    font-size: 14px;
  }
  
  .close-btn {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
  }
  
  /* Top Bar */
  .top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 5%;
    background: #f8f9fa;
    font-size: 13px;
    color: var(--light-text);
  }
  
  .rating .stars {
    color: #FFD700;
  }
  
  .contact {
    display: flex;
    align-items: center;
    gap: 15px;
  }
  
  .contact a {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.3s;
  }
  
  .contact a:hover {
    color: var(--primary-dark);
  }
  
  .currency {
    border: none;
    background: transparent;
    cursor: pointer;
  }
  
  /* Search Bar */
  .search-bar {
    flex: 1;
    max-width: 500px;
    margin: 0 30px;
  }
  
  .search-bar form {
    display: flex;
    height: 40px;
  }
  
  .search-bar input {
    flex: 1;
    border: 1px solid var(--border-color);
    border-right: none;
    border-radius: 4px 0 0 4px;
    padding: 0 15px;
    font-size: 14px;
    outline: none;
  }
  
  .search-bar button {
    width: 50px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    transition: background 0.3s;
  }
  
  .search-bar button:hover {
    background: var(--primary-dark);
  }
  
  /* User Actions */
  .user-actions {
    display: flex;
    gap: 20px;
  }
  
  .user-actions a {
    color: var(--secondary-color);
    font-size: 20px;
    text-decoration: none;
    position: relative;
  }
  
  .user-actions a:hover {
    color: var(--primary-color);
  }
  
  .cart span {
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--primary-color);
    color: white;
    font-size: 12px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  /* Responsive Design */
  @media (max-width: 1024px) {
    .search-bar {
      margin: 0 20px;
    }
  }
  
  @media (max-width: 768px) {
    .top-bar {
      flex-direction: column;
      text-align: center;
      padding: 10px;
    }
  
    .main-header {
      flex-direction: column;
      padding: 15px;
    }
  
    .search-bar {
      margin: 20px 0;
      width: 100%;
    }
  
    .nav-menu {
      flex-direction: column;
      gap: 0;
    }
  
    .dropdown {
      position: static;
      box-shadow: none;
      opacity: 1;
      visibility: visible;
      display: none;
    }
  
    .nav-item:hover .dropdown {
      display: block;
    }
  
    .products-grid {
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
    
    .main-nav {
      margin-top: 150px;
    }
    
    .container {
      margin-top: 200px;
    }
  }
  
  @media (max-width: 480px) {
    .product-actions {
      flex-direction: column;
    }
    
    .eye {
      width: 100%;
    }
    
    .add-to-cart-btn {
      width: 100%;
    }
    
    .main-nav {
      padding: 0 20px;
    }
    
    .nav-menu {
      gap: 20px;
    }
  }
  /* Cart Quantity Indicator */
.cart-quantity {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  background-color: var(--primary-color);
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 12px;
  margin-left: 5px;
}

/* Hide the cart quantity when it's zero */
.cart-quantity.hidden {
  display: none;
}

/* Add to cart button animation */
.add-to-cart-btn.added {
  background: linear-gradient(45deg, #27ae60, #2ecc71);
}
.error-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #dc2626;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
    }

    .error-toast.show {
        opacity: 1;
        transform: translateX(0);
    }
    
    .success-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    .success-toast.show {
        opacity: 1;
        transform: translateX(0);
    }
    </style>
<body>
<header class="main-header"> 
     <div class="logo">Golden Gown</div> 
     <input type="checkbox" id="menu-toggle" /> 
     <label for="menu-toggle" class="menu-icon"> 
       <i class="fas fa-bars"></i> 
     </label> 
     <nav class="menu">
    <a href="Homepage.php" class="active"><i class="fas fa-qrcode"></i> Homepage</a>
    <a href="profile.php?customerid=<?= $_SESSION['customerid'] ?>"><i class="fas fa-stream"></i> Profile</a>
      <a href="customer_product.php"><i class="fas fa-graduation-cap"></i> Graduation Gift </a>
      <a href="shopping_cart.php"><i class="fas fa-shopping-cart"></i> Cart </a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a> <!-- Log out link -->
    </nav>
  </header>

        <!-- Dynamic navigation menu from database -->
        <nav class="main-nav">
            <ul class="nav-menu">
                <?php foreach($categories as $category): ?>
                <li class="nav-item">
                    <a href="customer_product.php?category=<?php echo $category['categoryId']; ?>">
                        <?php echo htmlspecialchars($category['name']); ?> 
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown">
                        <?php foreach($subcategories as $subcategory): 
                            if($subcategory['categoryId'] == $category['categoryId']): ?>
                            <li>
                                <a href="customer_product.php?subcategory=<?php echo $subcategory['subcategoryId']; ?>">
                                    <?php echo htmlspecialchars($subcategory['name']); ?>
                                </a>
                            </li>
                        <?php endif; endforeach; ?>
                    </ul>
                </li>
                
                <?php endforeach; ?>
            </ul>
        </nav>
    </header>

    <!-- Hero banner removed -->

    <div class="container">
    <h1 class="section-title"><?php echo $pageTitle; ?></h1>
    
    <div class="products-grid">
        <?php foreach($products as $product): ?>
            <div class="product-card">
                <?php if($product['stock'] < 5): ?>
                    <div class="product-badge">Low Stock</div>
                <?php endif; ?>
                
                <a href="customer_productDetail.php?id=<?php echo $product['productId']; ?>" class="product-image-link">
                    <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'img/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                </a>
                
                <div class="product-info">
                    <h3 class="product-title">
                        <a href="customer_productDetail.php?id=<?php echo $product['productId']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>
                    
                    <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                    
                    <p class="product-description">
                        <?php echo substr(htmlspecialchars($product['description'] ?? ''), 0, 100) . '...'; ?>
                    </p>
                    
                    <div class="product-actions">
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['productId']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </form>
                        <a href="customer_productDetail.php?id=<?php echo $product['productId']; ?>" class="eye">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(count($products) == 0): ?>
            <div class="no-products">
                <p>No products found in this category.</p>
                <a href="customer_product.php" class="add-to-cart-btn" style="max-width: 200px; margin: 0 auto; display: block;">Back to All Products</a>
            </div>
        <?php endif; ?>
    </div>
</div>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>TARUMT Golden Gown provides high-quality graduation essentials for all TARUMT graduates.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="Homepage.php">Home</a></li>
                    <li><a href="customer_product.php">Product</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> TARUMT, Penang Branch </p>
                <p><i class="fas fa-phone"></i> +60 12-345-6789</p>
                <p><i class="fas fa-envelope"></i> info@goldengow.com</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2010 TARUMT Golden Gown. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Close announcement bar if it exists
        const closeBtn = document.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                document.querySelector('.announcement-bar').style.display = 'none';
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
  document.addEventListener('DOMContentLoaded', function() {
    // Product filtering functionality
    const filterBtns = document.querySelectorAll('.filter-btn');
    const products = document.querySelectorAll('.product-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            
            products.forEach(product => {
                if (filter === 'all') {
                    product.style.display = 'block';
                } else {
                    const category = product.getAttribute('data-category');
                    if (category === filter) {
                        product.style.display = 'block';
                    } else {
                        product.style.display = 'none';
                    }
                }
            });
        });
    });
    
    // Quick view functionality
    const quickViewBtns = document.querySelectorAll('.quick-view-btn');
    const quickViewModal = document.getElementById('quick-view-modal');
    const closeModalBtn = document.querySelector('.close-modal');
    
    quickViewBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            const productPrice = this.getAttribute('data-product-price');
            const productImage = this.getAttribute('data-product-image');
            const productDesc = this.getAttribute('data-product-desc');
            
            // Populate modal with product details
            document.getElementById('modal-product-name').textContent = productName;
            document.getElementById('modal-product-price').textContent = '$' + productPrice;
            document.getElementById('modal-product-image').src = productImage;
            document.getElementById('modal-product-desc').textContent = productDesc;
            document.getElementById('modal-add-to-cart').setAttribute('data-product-id', productId);
            
            // Show modal
            quickViewModal.style.display = 'flex';
        });
    });
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            quickViewModal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === quickViewModal) {
            quickViewModal.style.display = 'none';
        }
    });
    
    // Add to cart functionality
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const quantity = 1; // Default quantity
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'add_to_cart.php';
            
            const productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'product_id';
            productIdInput.value = productId;
            
            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = 'quantity';
            quantityInput.value = quantity;
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'add';
            
            form.appendChild(productIdInput);
            form.appendChild(quantityInput);
            form.appendChild(actionInput);
            
            document.body.appendChild(form);
            form.submit();
        });
    });
});
  
    </script>
</body>
</html>