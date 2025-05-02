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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch categories for navigation
$categoryQuery = "SELECT * FROM categories";
$categoryStmt = $pdo->query($categoryQuery);
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subcategories
$subcategoryQuery = "SELECT * FROM subcategories";
$subcategoryStmt = $pdo->query($subcategoryQuery);
$subcategories = $subcategoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: customer_product.php');
    exit;
}

$productId = $_GET['id'];

// Fetch product details
$productQuery = "SELECT * FROM products WHERE productId = :productId";
$productStmt = $pdo->prepare($productQuery);
$productStmt->execute(['productId' => $productId]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);

// If product not found, redirect to products page
if (!$product) {
    header('Location: customer_product.php');
    exit;
}

// Get category and subcategory names
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

$categoryName = getCategoryName($product['categoryId'], $categories);
$subcategoryName = isset($product['subcategoryId']) ? getSubcategoryName($product['subcategoryId'], $subcategories) : "None";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - TARUMT Graduation Store</title>
    <link rel="stylesheet" href="customer_product.css">
    <link rel="stylesheet" href="customer_productDetail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<style>
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

.product-actions .add-to-cart-btn {
      flex-grow: 1;
      height: 45px;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width:300px;
  }
  
  .product-actions .add-to-cart-btn:hover {
      background: linear-gradient(45deg, var(--primary-light), var(--primary-color));
      transform: translateY(-2px);
  }
  .main-image {
      width: 100%;
      max-height: 500px;
      object-fit: contain; /* Changed to contain to preserve aspect ratio */
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      background-color: white;
      padding: 20px;
  }
  
  .main-image img {
      width: 340px;
      height: 80%;
      object-fit: contain;
      margin-left:100px;
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
       <a href="editCustomer.php?customerid=<?= $_SESSION['customerid'] ?>"><i class="fas fa-stream"></i> Profile</a> 
       <a href="customer_product.php"><i class="fas fa-graduation-cap"></i> Graduation Gift </a> 
       <a href="#feedback"><i class="fas fa-sliders-h"></i> Feedback</a> 
       <a href="shopping_cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i> Cart <span class="cart-quantity">0</span></a> 
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

    <div class="container">
        <div class="breadcrumb">
            <a href="customer_product.php">Home</a> &gt;
            <a href="customer_product.php?category=<?php echo $product['categoryId']; ?>"><?php echo htmlspecialchars($categoryName); ?></a> &gt;
            <?php if(isset($product['subcategoryId']) && $product['subcategoryId']): ?>
            <a href="customer_product.php?subcategory=<?php echo $product['subcategoryId']; ?>"><?php echo htmlspecialchars($subcategoryName); ?></a> &gt;
            <?php endif; ?>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <div class="product-detail">
            <div class="product-gallery">
                <div class="main-image">
                    <?php if(!empty($product['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                    <img src="img/placeholder.jpg" alt="Product Image">
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="product-info-detail">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                
                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
                
                <div class="product-actions">
                    <div class="quantity-selector">
                        <button class="quantity-btn minus" type="button">-</button>
                        <input type="number" value="1" min="1" max="10" id="quantity" class="quantity-input">
                        <button class="quantity-btn plus" type="button">+</button>
                    </div>
                    
                    <form action="add_to_cart.php" method="post" id="add-to-cart-form" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo $product['productId']; ?>">
                        <input type="hidden" name="quantity" id="form-quantity" value="1">
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="add-to-cart-btn" data-product-id="<?php echo $product['productId']; ?>">Add to Cart</button>
                    </form>
                </div>
                
             
            </div>
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
         // Quantity selector
         const quantityInput = document.getElementById('quantity');
        const formQuantityInput = document.getElementById('form-quantity');
        const minusBtn = document.querySelector('.quantity-btn.minus');
        const plusBtn = document.querySelector('.quantity-btn.plus');
        const addToCartForm = document.getElementById('add-to-cart-form');
        
        // Update form quantity when quantity input changes
        quantityInput.addEventListener('change', function() {
            formQuantityInput.value = this.value;
        });
        
        minusBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
                formQuantityInput.value = currentValue - 1;
            }
        });
        
        plusBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue < 10) {
                quantityInput.value = currentValue + 1;
                formQuantityInput.value = currentValue + 1;
            }
        });
        
        // Add to cart button animation
        const addToCartBtn = document.querySelector('.add-to-cart-btn');
        addToCartBtn.addEventListener('click', function(e) {
            // Don't prevent default - let the form submit
            
            // Add animation to button
            this.classList.add('added');
            this.textContent = 'Added to Cart';
            
            // We'll let the form submit naturally to add_to_cart.php
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize cart quantity from localStorage or set to 0
            updateCartQuantity();
        });
        
        // Function to update cart quantity display
        function updateCartQuantity() {
            // This will be updated when the page loads to show the current cart count
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartQuantityElement = document.querySelector('.cart-quantity');
                    const quantity = data.count;
                    
                    cartQuantityElement.textContent = quantity;
                    
                    if (quantity === 0) {
                        cartQuantityElement.classList.add('hidden');
                    } else {
                        cartQuantityElement.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching cart count:', error);
                });
        }
    </script>
</body>
</html>