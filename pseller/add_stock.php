<?php
// add_stock.php - Form to add new stock
include 'includes/config.php';
include 'includes/dbConnection.php';
include 'includes/functions.php';
session_start();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form submission
    $product_type = $_POST['product_type'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $unit = $_POST['unit'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';

    // Validate input
    if (empty($product_type) || empty($product_name) || $quantity <= 0 || empty($unit)) {
        $_SESSION['error_message'] = "All fields are required and quantity must be greater than zero.";
        header("Location: add_stock.php");
        exit();
    }

    // Insert into database
    $sql = "INSERT INTO invent (product_type, product_name, quantity, unit, expiry_date) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssids", $product_type, $product_name, $quantity, $unit, $expiry_date);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Stock added successfully!";
            // Log the activity
            logActivity($conn, 'Add', "Added {$quantity} {$unit} of {$product_name}");
            header("Location: productseller.php");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Error: " . $conn->error;
    }
    $conn->close();
}

// Function to log activity (add this to includes/functions.php)
function logActivity($conn, $action, $description) {
    $sql = "INSERT INTO activity_log (action, description, timestamp) VALUES (?, ?, NOW())";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $action, $description);
        $stmt->execute();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    </head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <a href="../piash/add_stock.php" class="nav-item active"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"> 
                    <rect x="1" y="3" width="15" height="13"></rect> 
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon> 
                    <circle cx="5.5" cy="18.5" r="2.5"></circle> 
                    <circle cx="18.5" cy="18.5" r="2.5"></circle> 
                </svg> 
                <span class="nav-item-name">Stock Entry</span>
            </a>
            </aside>

        <main class="main-content">
            <header class="header">
                <h1 class="page-title">Add Stock</h1>
                </header>

            <div class="main-content-body">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>
                
                <div class="card p-4">
                    <form action="add_stock.php" method="POST">
                        <div class="mb-3">
                            <label for="product_type" class="form-label">Product Type</label>
                            <input type="text" class="form-control" id="product_type" name="product_type" required>
                        </div>
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <select class="form-select" id="unit" name="unit" required>
                                <option value="kg">kg</option>
                                <option value="unit">unit</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Stock</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>