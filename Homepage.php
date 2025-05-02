<?php
session_start();
include 'help.php'; // Include database connection

// Ensure customerid is stored in session (e.g., from login process)
if (!isset($_SESSION['customerid'])) {
    // If customer is not logged in, redirect to login page
    header("Location: login.php");
    exit;
}

// Fetch top 3 products
$productsQuery = "SELECT p.*, c.name as categoryName 
                 FROM products p
                 LEFT JOIN categories c ON p.categoryId = c.categoryId
                 ORDER BY p.stock DESC LIMIT 3"; // Using stock as a measure of popularity
$productsResult = $conn->query($productsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Golden Gown - Graduation Gifts</title>
  <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
  <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
  <link rel="stylesheet" href="Homepage.css">
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
    /* Slideshow styles */
    .slideshow-container {
      margin-top: 80px;
      position: relative;
      height: 500px;
      overflow: hidden;
    }
    
    .slide {
      position: absolute;
      width: 100%;
      height: 100%;
      opacity: 0;
      transition: opacity 1s ease-in-out;
      background-size: cover;
      background-position: center;
    }
    
    .slide.active {
      opacity: 1;
    }
    
    .slide-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      color: white;
      width: 80%;
      max-width: 800px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    
    .slide-content h2 {
      font-size: 2.5rem;
      margin-bottom: 20px;
    }
    
    .slide-content p {
      font-size: 1.2rem;
      margin-bottom: 30px;
    }
    
    .slide-btn {
      display: inline-block;
      background: linear-gradient(to right, #c1b1f7, #a890fe);
      color: white;
      padding: 12px 30px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .slide-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    /* Slideshow navigation */
    .slide-nav {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 10px;
    }
    
    .slide-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.5);
      cursor: pointer;
      transition: background 0.3s;
    }
    
    .slide-dot.active {
      background: white;
    }
    
    /* Product section styles */
    .popular-products {
      padding: 3rem 1rem;
      background-color: #fff;
      font-family: 'Lato', sans-serif;
    }
    
    .section-title {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .section-title h2 {
      font-size: 2.5rem;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .section-title p {
      color: #666;
      font-size: 1.1rem;
    }
    
    .products-grid {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }
    
    .product-card {
      background-color: #ffffff;
      border-radius: 1rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.3s ease;
    }
    
    .product-card:hover {
      transform: translateY(-10px);
    }
    
    .product-image {
  height: 200px;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
}

.product-image img {
  width: 70%;
  height: 250px;
  object-fit: cover;
  transition: transform 0.5s ease;
}
    
    .product-card:hover .product-image img {
      transform: scale(1.1);
    }
    
    .product-details {
      padding: 1.5rem;
    }
    
    .product-name {
      font-size: 1.2rem;
      font-weight: bold;
      margin-bottom: 0.5rem;
      color: #333;
    }
    
    .product-category {
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
    }
    
    .product-price {
      font-size: 1.3rem;
      font-weight: bold;
      color: #a759f5;
      margin-bottom: 1rem;
    }
    
    .add-to-cart {
      display: block;
      width: 100%;
      padding: 0.8rem;
      background: linear-gradient(to right, #c1b1f7, #a890fe);
      color: white;
      border: none;
      border-radius: 0.5rem;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .add-to-cart:hover {
      background: linear-gradient(to right, #a890fe, #8a70fe);
    }
    
    .view-all {
      text-align: center;
      margin-top: 2rem;
    }
    
    .view-all-btn {
      display: inline-block;
      padding: 0.8rem 2rem;
      background: transparent;
      color: #a759f5;
      border: 2px solid #a759f5;
      border-radius: 2rem;
      font-weight: bold;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .view-all-btn:hover {
      background: #a759f5;
      color: white;
    }
    
    /* About Us section styles */
    .about-us-section {
      padding: 4rem 1rem;
      background-color: #f8f5ff;
      position: relative;
      overflow: hidden;
    }
    
    .about-us-container {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 3rem;
      position: relative;
      z-index: 2;
    }
    
    @media (max-width: 768px) {
      .about-us-container {
        grid-template-columns: 1fr;
      }
    }
    
    .about-us-image {
      position: relative;
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: var(--shadow);
      height: 100%;
      min-height: 400px;
    }
    
    .about-us-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }
    
    .about-us-image:hover img {
      transform: scale(1.05);
    }
    
    .about-us-content {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    
    .about-us-title {
      font-size: 2.5rem;
      color: var(--primary-dark);
      margin-bottom: 1.5rem;
      position: relative;
      padding-bottom: 1rem;
    }
    
    .about-us-title:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 80px;
      height: 4px;
      background: linear-gradient(to right, var(--primary-color), var(--primary-light));
      border-radius: 2px;
    }
    
    .about-us-subtitle {
      font-size: 1.2rem;
      color: var(--primary-color);
      margin-bottom: 1rem;
      font-weight: 600;
    }
    
    .about-us-text {
      color: var(--light-text);
      font-size: 1.1rem;
      line-height: 1.8;
      margin-bottom: 2rem;
    }
    
    .about-us-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 1.5rem;
      margin-top: 1rem;
    }
    
    .stat-item {
      text-align: center;
      padding: 1.5rem 1rem;
      background-color: white;
      border-radius: 0.8rem;
      box-shadow: var(--shadow);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-item:hover {
      transform: translateY(-5px);
      box-shadow: var(--hover-shadow);
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      color: var(--primary-color);
      margin-bottom: 0.5rem;
    }
    
    .stat-label {
      font-size: 0.9rem;
      color: var(--light-text);
    }
    
    .about-us-cta {
      margin-top: 2rem;
    }
    
    .about-us-btn {
      display: inline-block;
      padding: 0.8rem 2rem;
      background: linear-gradient(to right, var(--primary-color), var(--primary-light));
      color: white;
      border: none;
      border-radius: 2rem;
      font-weight: bold;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .about-us-btn:hover {
      transform: translateY(-3px);
      box-shadow: var(--hover-shadow);
    }
    
    /* Decorative elements */
    .about-us-section:before {
      content: '';
      position: absolute;
      top: -100px;
      right: -100px;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: rgba(167, 89, 245, 0.1);
      z-index: 1;
    }
    
    .about-us-section:after {
      content: '';
      position: absolute;
      bottom: -100px;
      left: -100px;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(167, 89, 245, 0.1);
      z-index: 1;
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
</head>
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

  <!-- Slideshow Hero Banner (replacing welcome section) -->
  <div class="slideshow-container">
    <div class="slide active" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/background1.jpg');">
      <div class="slide-content">
        <h2> WELCOME </h2>
        <p>Find the perfect graduation gifts to commemorate your special day</p>
        <a href="#customer_product.php" class="slide-btn">Shop Now</a>
      </div>
    </div>
    
    <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/background2.jpg');">
      <div class="slide-content">
        <h2>Personalized Graduation Gifts</h2>
        <p>Create lasting memories with our customizable graduation items</p>
        <a href="customer_product.php?category=C002" class="slide-btn">Explore</a>
      </div>
    </div>
    
    <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/background3.jpg');">
      <div class="slide-content">
        <h2>Special Graduation Offers</h2>
        <p>Limited time discounts on selected graduation merchandise</p>
        <a href="customer_product.php" class="slide-btn">View Offers</a>
      </div>
    </div>
    
    <div class="slide-nav">
      <div class="slide-dot active" onclick="currentSlide(1)"></div>
      <div class="slide-dot" onclick="currentSlide(2)"></div>
      <div class="slide-dot" onclick="currentSlide(3)"></div>
    </div>
  </div>

  <!-- Popular Products Section -->
  <section id="popular-products" class="popular-products">
    <div class="section-title">
      <h2>Popular Graduation Gifts</h2>
      <p>Discover our most loved graduation items</p>
    </div>
    
    <div class="products-grid">
      <?php if ($productsResult && $productsResult->num_rows > 0): ?>
        <?php while ($product = $productsResult->fetch_assoc()): ?>
          <div class="product-card">
            <div class="product-image">
              <?php if (!empty($product['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
              <?php else: ?>
                <img src="images/placeholder.jpg" alt="No Image">
              <?php endif; ?>
            </div>
            <div class="product-details">
              <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
              <p class="product-category"><?php echo htmlspecialchars($product['categoryName'] ?? 'Uncategorized'); ?></p>
              <p class="product-price">RM <?php echo number_format($product['price'], 2); ?></p>
              <form action="add_to_cart.php" method="post" id="add-to-cart-form" style="display:inline; ">
                        <input type="hidden" name="product_id" value="<?php echo $product['productId']; ?>">
                        <input type="hidden" name="quantity" id="form-quantity" value="1">
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="add-to-cart-btn" style="height: 45px;font-size: 16px;display: flex;align-items: center;justify-content: center;gap: 10px;background: linear-gradient(45deg, var(--primary-color), var(--primary-light));color: white;border: none;border-radius: 8px;font-weight: 600;cursor: pointer;transition: all 0.3s ease;width:280px;" data-product-id="<?php echo $product['productId']; ?>">Add to Cart</button>
                    </form>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center;">
          <p>No products available at the moment. Check back soon!</p>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="view-all">
      <a href="customer_product.php" class="view-all-btn">View All Products</a>
    </div>
  </section>

  <!-- Testimonials Section -->
  <section id="testimonials" style="padding: 3rem 1rem; background-color: #f3f4f6; font-family: 'Lato', sans-serif;">
    <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 2rem; color: #333;">
      💬 What Our Customers Say
    </h2>

    <div style="max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;height:250px;">
      <div style="background-color: #ffffff; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <p style="font-size: 22px; color: #555;">🗣️ "Fast service, great quality, and beautifully packaged. The graduation gift looked super classy!"</p>
        <p style="margin-top: 1rem; font-weight: bold; color: #222;">— Chan Wei Qiang</p>
      </div>

      <div style="background-color: #ffffff; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <p style="font-size: 22px; color: #555;">🗣️ "The customized T-shirt fit perfectly and the print was very clear. My whole class loved it!"</p>
        <p style="margin-top: 1rem; font-weight: bold; color: #222;">— Huang Zi Xuan</p>
      </div>

      <div style="background-color: #ffffff; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <p style="font-size: 22px; color: #555;">🗣️ "I buy graduation gifts here every year. The prices are reasonable, and the designs are always updated. Highly recommended!"</p>
        <p style="margin-top: 1rem; font-weight: bold; color: #222;">— Wong Pei Yi</p>
      </div>
    </div>
  </section>

  <!-- About Us Section (Replacing Feedback Section) -->
  <section id="about-us" class="about-us-section">
    <div class="section-title">
      <h2>About Golden Gown</h2>
      <p>Celebrating Academic Excellence Since 2010</p>
    </div>
    
    <div class="about-us-container">
      <div class="about-us-image">
        <img src="img/background4.jpg" alt="Golden Gown Team" onerror="this.src='img/placeholder.jpg'">
      </div>
      
      <div class="about-us-content">
        <h3 class="about-us-subtitle">Our Story</h3>
        <h2 class="about-us-title">Crafting Memorable Graduation Moments</h2>
        
        <p class="about-us-text">
          Golden Gown was founded with a simple mission: to provide high-quality, meaningful graduation gifts that celebrate academic achievement. What started as a small campus shop has grown into Malaysia's premier destination for graduation merchandise.
        </p>
        
        <p class="about-us-text">
          We understand that graduation is more than just a ceremony—it's a milestone that represents years of hard work, dedication, and growth. Our team of designers and craftspeople work tirelessly to create products that capture the essence of this special occasion.
        </p>
        
        <div class="about-us-stats">
          <div class="stat-item">
            <div class="stat-number">15+</div>
            <div class="stat-label">Years of Excellence</div>
          </div>
          
          <div class="stat-item">
            <div class="stat-number">10k+</div>
            <div class="stat-label">Happy Graduates</div>
          </div>
          
          <div class="stat-item">
            <div class="stat-number">200+</div>
            <div class="stat-label">Product Designs</div>
          </div>
        </div>
        
  
      </div>
    </div>
  </section>
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
    // Slideshow functionality
    let slideIndex = 1;
    showSlides(slideIndex);
    
    // Auto-advance slides every 5 seconds
    setInterval(function() {
      plusSlides(1);
    }, 5000);
    
    function plusSlides(n) {
      showSlides(slideIndex += n);
    }
    
    function currentSlide(n) {
      showSlides(slideIndex = n);
    }
    
    function showSlides(n) {
      let slides = document.getElementsByClassName("slide");
      let dots = document.getElementsByClassName("slide-dot");
      
      if (n > slides.length) {slideIndex = 1}
      if (n < 1) {slideIndex = slides.length}
      
      for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove("active");
      }
      
      for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
      }
      
      slides[slideIndex-1].classList.add("active");
      dots[slideIndex-1].classList.add("active");
    }
    
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