<?php
include 'includes/config.php';
include 'includes/dbConnection.php';
include 'includes/functions.php';

session_start();
$productsellerData = [];

// Fetch productseller data to populate the product dropdown
if ($conn) {
    $sql_productseller = "SELECT product_type FROM productseller GROUP BY product_type";
    $result_productseller = $conn->query($sql_productseller);
    if ($result_productseller) {
        if ($result_productseller->num_rows > 0) {
            while($row = $result_productseller->fetch_assoc()) {
                $productsellerData[] = $row;
            }
        }
    } else {
        $_SESSION['error_message'] = "Database error fetching product types: " . $conn->error;
    }
} else {
     $_SESSION['error_message'] = "Database connection error.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_time = $_POST['date_time'];
    $customer_name = sanitize($_POST['customer_name']);
    $product_type = sanitize($_POST['product_type']);
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_FLOAT);

    if ($quantity === false || $quantity <= 0) {
        $_SESSION['error_message'] = "Quantity must be a positive number.";
    } else {
        $sql = "INSERT INTO orders (date_time, customer_name, product_type, quantity) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssd", $date_time, $customer_name, $product_type, $quantity);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Order added successfully!";
                header("Location: orders-dashboard.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Error adding order: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Error preparing statement: " . $conn->error;
        }
    }
    header("Location: addorder.php");
    exit();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Order - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card p-4">
                    <h2 class="card-title text-center mb-4">Add New Order</h2>
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>
                    <form action="addorder.php" method="POST">
                        <div class="mb-3">
                            <label for="date_time" class="form-label">Date & Time</label>
                            <input type="datetime-local" class="form-control" id="date_time" name="date_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="product_type" class="form-label">Product Type</label>
                            <select class="form-select" id="product_type" name="product_type" required>
                                <?php if (!empty($productsellerData)): ?>
                                    <?php foreach ($productsellerData as $product): ?>
                                        <option value="<?= htmlspecialchars($product['product_type']) ?>"><?= htmlspecialchars($product['product_type']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">No products available</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required min="0.01">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <a href="orders-dashboard.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
