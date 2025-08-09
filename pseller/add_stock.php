<?php
// add_stock.php
include 'includes/config.php';
include 'includes/dbConnection.php';
include 'includes/functions.php';

session_start();

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = sanitize($_POST['product_name']);
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);

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
        $conn = getdbConnection();
        if ($conn) {
            $sql = "INSERT INTO inventory (name, quantity, price) VALUES (?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sid", $product_name, $quantity, $price);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Product '{$product_name}' added successfully.";
                    header("Location: productseller.php");
                    exit();
                } else {
                    $errors[] = "Error adding product: " . $stmt->error;
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles3.css">
</head>
<body class="bg-dark text-light">
    <div class="app-container">
        <main class="main-content">
            <header class="header">
                 <h1 class="page-title">Add New Product Stock</h1>
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
                        <h4 class="card-title">Add Product to Inventory</h4>
                    </div>
                    <div class="card-body">
                        <form action="add_stock.php" method="POST">
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required min="1">
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required min="0.01">
                            </div>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                            <a href="productseller.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>