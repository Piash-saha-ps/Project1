<?php
// edit_stock.php
session_start();
include 'includes/config.php';
include 'includes/dbConnection.php';
include 'includes/functions.php'; // Assuming a sanitize function exists

$errors = [];
$success_message = '';
$product = null;

// Handle GET request to retrieve product data for editing
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $itemId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    
    if ($itemId === false || $itemId <= 0) {
        $_SESSION['error_message'] = "Invalid product ID provided.";
        header("Location: productseller.php");
        exit();
    }

    if ($conn) {
        $sql = "SELECT id, name, quantity, price FROM inventory WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
            } else {
                $_SESSION['error_message'] = "Product not found.";
                header("Location: productseller.php");
                exit();
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Database error preparing statement: " . $conn->error;
            header("Location: productseller.php");
            exit();
        }
        $conn->close();
    } else {
        $_SESSION['error_message'] = "Database connection failed.";
        header("Location: productseller.php");
        exit();
    }
}

// Handle POST request to update product data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $product_name = sanitize($_POST['product_name']);
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);

    // Basic validation
    if ($itemId === false || $itemId <= 0) {
        $errors[] = "Invalid product ID.";
    }
    if (empty($product_name)) {
        $errors[] = "Product name is required.";
    }
    if ($quantity === false || $quantity <= 0) {
        $errors[] = "Quantity must be a positive number.";
    }
    if ($price === false || $price <= 0) {
        $errors[] = "Price must be a positive number.";
    }
    
    if (empty($errors)) {
     
        if ($conn) {
            $sql = "UPDATE inventory SET name = ?, quantity = ?, price = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sidi", $product_name, $quantity, $price, $itemId);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Product '{$product_name}' updated successfully.";
                    header("Location: productseller.php");
                    exit();
                } else {
                    $errors[] = "Error updating product: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "Database error preparing statement: " . $conn->error;
            }
            $conn->close();
        } else {
            $errors[] = "Database connection failed.";
        }
    }
    // If there are errors, reload the page with the old values for display
    $product = [
        'id' => $itemId,
        'name' => $product_name,
        'quantity' => $quantity,
        'price' => $price
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles3.css">
</head>
<body class="bg-dark text-light">
    <div class="app-container">
        <main class="main-content">
            <header class="header">
                 <h1 class="page-title">Edit Product Stock</h1>
                 <div class="profile-container">
                    <button id="profileButton" class="profile-button">
                        <div class="profile-avatar">PS</div>
                    </button>
                </div>
            </header>
            
            <div class="container py-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="card bg-dark text-light border-secondary">
                    <div class="card-header">
                        <h4 class="card-title">Edit Product Details</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($product): ?>
                        <form action="edit_stock.php" method="POST">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="<?= htmlspecialchars($product['quantity']) ?>" required min="1">
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required min="0.01">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                            <a href="productseller.php" class="btn btn-secondary">Cancel</a>
                        </form>
                        <?php else: ?>
                            <div class="alert alert-warning">No product data available.</div>
                            <a href="productseller.php" class="btn btn-secondary">Go Back to Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>