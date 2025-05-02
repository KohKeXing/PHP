<?php
include 'help.php';
session_start();

// Initialize variables
$productId = '';
$productName = '';
$category = '';
$subcategory = '';
$price = '';
$stock = '';
$description = '';
$imageUrl = '';
$isEdit = false;
$successMessage = '';
$errorMessage = '';

// Check if we're editing an existing product
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $isEdit = true;
    $productId = $_GET['id'];
    
    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE productId = ?");
    $stmt->bind_param("s", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $productName = $product['name'];
        $category = $product['categoryId'];
        $subcategory = $product['subcategoryId'];
        $price = $product['price'];
        $stock = $product['stock'];
        $description = $product['description'];
        $imageUrl = $product['image_url'];
    } else {
        // Product not found
        header("Location: admin_product.php");
        exit;
    }
}

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = $_POST['productId'];
    $newStock = $_POST['stock'];
    $notes = $_POST['notes'];
    
    // Update stock in products table
    $updateStmt = $conn->prepare("UPDATE products SET stock = ? WHERE productId = ?");
    $updateStmt->bind_param("is", $newStock, $productId);
    
    if ($updateStmt->execute()) {
        // Log the inventory change
        $changeType = ($_POST['change_type'] == 'add') ? 'Stock Added' : 'Stock Removed';
        $changeAmount = abs($_POST['change_amount']);
        
        // Create inventory_log table if it doesn't exist
        $createTableQuery = "CREATE TABLE IF NOT EXISTS inventory_log (
            log_id INT AUTO_INCREMENT PRIMARY KEY,
            productId VARCHAR(20) NOT NULL,
            change_type VARCHAR(50) NOT NULL,
            change_amount INT NOT NULL,
            notes TEXT,
            admin_id INT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (productId) REFERENCES products(productId)
        )";
        $conn->query($createTableQuery);
        
        $logStmt = $conn->prepare("INSERT INTO inventory_log (productId, change_type, change_amount, notes, manager_id) VALUES (?, ?, ?, ?, 1)");
        $logStmt->bind_param("ssis", $productId, $changeType, $changeAmount, $notes);
        $logStmt->execute();
        
        $successMessage = "Stock updated successfully!";
        
        // Refresh product data
        $stmt = $conn->prepare("SELECT * FROM products WHERE productId = ?");
        $stmt->bind_param("s", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $stock = $product['stock'];
        }
    } else {
        $errorMessage = "Error updating stock: " . $conn->error;
    }
}

// Handle form submission for product details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_product'])) {
    // Get form data
    $productName = $_POST['productName'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'] ?? null;
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    
    // Image handling
    $uploadedImagePath = '';
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'][0] == 0) {
        $uploadDir = 'images/products/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . $_FILES['productImage']['name'][0];
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['productImage']['tmp_name'][0], $targetFile)) {
            $uploadedImagePath = $targetFile;
        } else {
            $errorMessage = "Failed to upload image.";
        }
    }
    
    if (empty($errorMessage)) {
        if ($isEdit) {
            // Update existing product
            $sql = "UPDATE products SET 
                    name = ?, 
                    categoryId = ?, 
                    subcategoryId = ?, 
                    price = ?, 
                    stock = ?, 
                    description = ?";
            
            $params = [$productName, $category, $subcategory, $price, $stock, $description];
            $types = "sssdis";
            
            // Only update image if a new one was uploaded
            if (!empty($uploadedImagePath)) {
                $sql .= ", image_url = ?";
                $params[] = $uploadedImagePath;
                $types .= "s";
            }
            
            $sql .= " WHERE productId = ?";
            $params[] = $productId;
            $types .= "s";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $successMessage = "Product updated successfully!";
            } else {
                $errorMessage = "Error updating product: " . $conn->error;
            }
        } else {
            // Generate new product ID (format: P + 3-digit number)
            $result = $conn->query("SELECT MAX(SUBSTRING(productId, 2)) as maxId FROM products WHERE productId LIKE 'P%'");
            $row = $result->fetch_assoc();
            $maxId = intval($row['maxId']);
            $newId = 'P' . str_pad($maxId + 1, 3, '0', STR_PAD_LEFT);
            
            // Create new product
            $sql = "INSERT INTO products (productId, name, categoryId, subcategoryId, price, stock, description, image_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssdiss", $newId, $productName, $category, $subcategory, $price, $stock, $description, $uploadedImagePath);
            
            if ($stmt->execute()) {
                $successMessage = "Product added successfully!";
                // Clear form data after successful submission
                $productName = $category = $subcategory = $price = $stock = $description = '';
            } else {
                $errorMessage = "Error adding product: " . $conn->error;
            }
        }
    }
}

// Fetch categories for dropdown
$categoriesQuery = "SELECT * FROM categories";
$categoriesResult = $conn->query($categoriesQuery);

// Fetch subcategories for dropdown
$subcategoriesQuery = "SELECT * FROM subcategories";
$subcategoriesResult = $conn->query($subcategoriesQuery);

// Get recent inventory changes for this product
$recentChangesQuery = "SELECT l.*, p.name as productName 
                      FROM inventory_log l
                      JOIN products p ON l.productId = p.productId
                      WHERE l.productId = ?
                      ORDER BY l.timestamp DESC
                      LIMIT 5";
$recentChangesStmt = $conn->prepare($recentChangesQuery);

if ($isEdit) {
    $recentChangesStmt->bind_param("s", $productId);
    $recentChangesStmt->execute();
    $recentChangesResult = $recentChangesStmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add New'; ?> Product - Admin Dashboard</title>
    <link rel="stylesheet" href="admin_product.css">
    <link rel="stylesheet" href="admin_productDetail.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab {
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 500;
            color: #555;
            position: relative;
        }
        
        .tab.active {
            color: var(--primary-color);
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .inventory-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .inventory-card h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: var(--secondary-color);
        }
        
        .stock-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stock-count {
            font-size: 24px;
            font-weight: 600;
        }
        
        .stock-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .stock-normal {
            background-color: rgba(46, 204, 113, 0.2);
            color: #27ae60;
        }
        
        .stock-warning {
            background-color: rgba(243, 156, 18, 0.2);
            color: #f39c12;
        }
        
        .stock-critical {
            background-color: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }
        
        .stock-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .stock-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .add-stock {
            background-color: #e3f2fd;
            color: #2196f3;
        }
        
        .remove-stock {
            background-color: #ffebee;
            color: #f44336;
        }
        
        .recent-changes h4 {
            margin-bottom: 10px;
            color: var(--secondary-color);
        }
        
        .recent-change {
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .recent-change:last-child {
            border-bottom: none;
        }
        
        .change-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .change-time {
            font-size: 12px;
            color: #777;
        }
        
        .change-type {
            font-size: 13px;
            padding: 3px 8px;
            border-radius: 15px;
        }
        
        .change-add {
            background-color: rgba(46, 204, 113, 0.2);
            color: #27ae60;
        }
        
        .change-remove {
            background-color: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }
        
        .change-notes {
            font-size: 13px;
            color: #555;
            margin-top: 5px;
        }
        
        .stock-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .close-modal {
            font-size: 24px;
            cursor: pointer;
            color: #777;
        }
        
        .stock-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-group {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="admin-container">
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
            <div class="dashboard">
                <div class="page-header">
                    <h1><?php echo $isEdit ? 'Edit' : 'Add New'; ?> Product</h1>
                    <a href="admin_product.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Products</a>
                </div>

                <?php if (!empty($successMessage)): ?>
                <div class="alert success">
                    <?php echo $successMessage; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                <div class="alert error">
                    <?php echo $errorMessage; ?>
                </div>
                <?php endif; ?>

                <?php if ($isEdit): ?>
                <div class="tabs">
                    <div class="tab active" data-tab="product-info">Product Information</div>
                    <div class="tab" data-tab="inventory">Inventory Management</div>
                </div>
                <?php endif; ?>

                <div id="product-info" class="tab-content active">
                    <div class="add-product-form">
                        <form action="<?php echo $_SERVER['PHP_SELF'] . ($isEdit ? '?id=' . $productId : ''); ?>" method="POST" enctype="multipart/form-data">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="productName">Product Name</label>
                                    <input type="text" id="productName" name="productName" value="<?php echo htmlspecialchars($productName); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select id="category" name="category" required onchange="loadSubcategories()">
                                        <option value="">Select Category</option>
                                        <?php 
                                        // Reset the result pointer
                                        $categoriesResult->data_seek(0);
                                        while ($category_row = $categoriesResult->fetch_assoc()): 
                                        ?>
                                        <option value="<?php echo $category_row['categoryId']; ?>" <?php echo ($category == $category_row['categoryId']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category_row['name']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="subcategory">Subcategory</label>
                                    <select id="subcategory" name="subcategory">
                                        <option value="">Select Subcategory</option>
                                        <?php 
                                        // Reset the result pointer
                                        $subcategoriesResult->data_seek(0);
                                        while ($subcategory_row = $subcategoriesResult->fetch_assoc()): 
                                        ?>
                                        <option value="<?php echo $subcategory_row['subcategoryId']; ?>" 
                                                data-category="<?php echo $subcategory_row['categoryId']; ?>"
                                                <?php echo ($subcategory == $subcategory_row['subcategoryId']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subcategory_row['name']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="price">Price (RM)</label>
                                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="stock">Stock</label>
                                    <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($stock); ?>" required>
                                </div>

                                <div class="form-group full-width">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                                </div>

                                <div class="form-group full-width">
                                    <label>Product Image</label>
                                    <div class="image-upload-container">
                                        <div class="image-upload">
                                            <input type="file" name="productImage[]" id="productImage" accept="image/*">
                                            <div class="upload-placeholder">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <p>Drag & drop an image or click to browse</p>
                                            </div>
                                        </div>
                                        <div class="image-preview" id="imagePreview">
                                            <?php if (!empty($imageUrl)): ?>
                                            <div class="preview-item">
                                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Product Image">
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="reset" class="reset-btn">Reset</button>
                                <button type="submit" name="save_product" class="submit-btn">Save Product</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($isEdit): ?>
                <div id="inventory" class="tab-content">
                    <div class="inventory-card">
                        <h3>Stock Management</h3>
                        
                        <div class="stock-info">
                            <div class="stock-count"><?php echo $stock; ?> units</div>
                            <div class="stock-status <?php echo ($stock < 5) ? 'stock-critical' : (($stock < 10) ? 'stock-warning' : 'stock-normal'); ?>">
                                <?php 
                                if ($stock < 5) {
                                    echo 'Critical Low Stock';
                                } elseif ($stock < 10) {
                                    echo 'Low Stock';
                                } else {
                                    echo 'In Stock';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="stock-actions">
                            <button class="stock-btn add-stock" onclick="openStockModal('add')">
                                <i class="fas fa-plus-circle"></i> Add Stock
                            </button>
                            <button class="stock-btn remove-stock" onclick="openStockModal('remove')">
                                <i class="fas fa-minus-circle"></i> Remove Stock
                            </button>
                        </div>
                        
                        <div class="recent-changes">
                            <h4>Recent Stock Changes</h4>
                            <?php if (isset($recentChangesResult) && $recentChangesResult->num_rows > 0): ?>
                                <?php while ($change = $recentChangesResult->fetch_assoc()): ?>
                                    <div class="recent-change">
                                        <div class="change-header">
                                            <span class="change-type <?php echo ($change['change_type'] == 'Stock Added') ? 'change-add' : 'change-remove'; ?>">
                                                <?php echo $change['change_type']; ?> (<?php echo $change['change_amount']; ?>)
                                            </span>
                                            <span class="change-time"><?php echo date('M d, Y H:i', strtotime($change['timestamp'])); ?></span>
                                        </div>
                                        <?php if (!empty($change['notes'])): ?>
                                            <div class="change-notes"><?php echo htmlspecialchars($change['notes']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No recent inventory changes for this product.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Stock Update Modal -->
    <?php if ($isEdit): ?>
    <div id="stockModal" class="stock-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Update Stock</h3>
                <span class="close-modal" onclick="closeStockModal()">&times;</span>
            </div>
            <form id="stockForm" method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $productId; ?>">
                <input type="hidden" id="productId" name="productId" value="<?php echo $productId; ?>">
                <div class="stock-form">
                    <div class="form-group">
                        <label for="productNameModal">Product</label>
                        <input type="text" id="productNameModal" class="form-control" value="<?php echo htmlspecialchars($productName); ?>" readonly>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="currentStock">Current Stock</label>
                            <input type="number" id="currentStock" class="form-control" value="<?php echo $stock; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="stockModal">New Stock</label>
                            <input type="number" id="stockModal" name="stock" class="form-control" value="<?php echo $stock; ?>" required>
                        </div>
                    </div>
                    
                    <input type="hidden" id="change_type" name="change_type" value="add">
                    
                    <div class="form-group">
                        <label for="change_amount">Amount Changed</label>
                        <input type="number" id="change_amount" name="change_amount" class="form-control" min="1" value="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Reason for stock change..."></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary" onclick="closeStockModal()">Cancel</button>
                        <button type="submit" name="update_stock" class="btn btn-primary">Update Stock</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and content
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                document.getElementById(this.dataset.tab).classList.add('active');
            });
        });
        
        // Subcategory filtering
        function loadSubcategories() {
            const categorySelect = document.getElementById('category');
            const subcategorySelect = document.getElementById('subcategory');
            const selectedCategory = categorySelect.value;
            
            // Hide all subcategories first
            Array.from(subcategorySelect.options).forEach(option => {
                if (option.dataset.category) {
                    option.style.display = option.dataset.category === selectedCategory ? '' : 'none';
                }
            });
            
            // Select first visible option or default
            const visibleOptions = Array.from(subcategorySelect.options).filter(option => 
                option.style.display !== 'none'
            );
            
            if (visibleOptions.length > 1) { // First is the default "Select Subcategory"
                subcategorySelect.value = visibleOptions[1].value;
            } else {
                subcategorySelect.value = '';
            }
        }
        
        // Image preview
        document.getElementById('productImage').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Product Image';
                    
                    previewItem.appendChild(img);
                    preview.appendChild(previewItem);
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        <?php if ($isEdit): ?>
        // Stock modal functions
        function openStockModal(type) {
            document.getElementById('modalTitle').textContent = type === 'add' ? 'Add Stock' : 'Remove Stock';
            document.getElementById('change_type').value = type;
            document.getElementById('change_amount').value = 0;
            document.getElementById('stockModal').value = <?php echo $stock; ?>;
            document.getElementById('stockModal').style.display = 'flex';
        }
        
        function closeStockModal() {
            document.getElementById('stockModal').style.display = 'none';
        }
        
        // Update stock calculation
        document.getElementById('change_amount').addEventListener('input', updateStockCalculation);
        
        function updateStockCalculation() {
            const currentStock = parseInt(document.getElementById('currentStock').value) || 0;
            const changeType = document.getElementById('change_type').value;
            const changeAmount = parseInt(document.getElementById('change_amount').value) || 0;
            
            let newStock = currentStock;
            if (changeType === 'add') {
                newStock = currentStock + changeAmount;
            } else {
                newStock = Math.max(0, currentStock - changeAmount);
            }
            
            document.getElementById('stockModal').value = newStock;
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('stockModal');
            if (event.target === modal) {
                closeStockModal();
            }
        }
        <?php endif; ?>
        
        // Initialize subcategories on page load
        window.addEventListener('DOMContentLoaded', function() {
            loadSubcategories();
        });

        
    </script>
</body>
</html>