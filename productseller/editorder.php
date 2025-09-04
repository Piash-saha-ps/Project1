<?php
include 'includes/config.php';
include 'includes/dbConnection.php';
include 'includes/functions.php';
session_start();

$orderData = null;
$errors = [];
$productsellerData = [];

// Fetch productseller data for the dropdown
if ($conn) {
    $sql_productseller = "SELECT product_type FROM productseller GROUP BY product_type";
    $result_productseller = $conn->query($sql_productseller);
    if ($result_productseller) {
        while($row = $result_productseller->fetch_assoc()) {
            $productsellerData[] = $row;
        }
    } else {
        $_SESSION['error_message'] = "Database error fetching product types: " . $conn->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = sanitize($_GET['id']);
    $sql_fetch = "SELECT order_id, date_time, customer_name, product_type, quantity FROM orders WHERE order_id = ?";
    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $order_id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        if ($result_fetch->num_rows === 1) {
            $orderData = $result_fetch->fetch_assoc();
            list($orderData['orderDate'], $orderData['orderTime']) = explode(' ', $orderData['date_time']);
        } else {
            $_SESSION['error_message'] = "Order entry not found.";
            header("Location: orders-dashboard.php");
            exit();
        }
        $stmt_fetch->close();
    } else {
        $_SESSION['error_message'] = "Database error fetching record: " . $conn->error;
        header("Location: orders-dashboard.php");
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = sanitize($_POST['id']);
    $customer_name = sanitize($_POST['customer_name']);
    $product_type = sanitize($_POST['product_type']);
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_FLOAT);
    $orderDate = sanitize($_POST['orderDate']);
    $orderTime = sanitize($_POST['orderTime']);
    $dateTime = $orderDate . ' ' . $orderTime;

    if (empty($order_id) || !is_numeric($order_id)) {
        $errors[] = "Invalid order entry ID.";
    }
    if (empty($customer_name)) {
        $errors[] = "Customer name is required.";
    }
    if (empty($product_type)) {
        $errors[] = "Product Type is required.";
    }
    if ($quantity === false || $quantity <= 0) {
        $errors[] = "Quantity must be a positive number.";
    }
    if (empty($orderDate) || empty($orderTime)) {
        $errors[] = "Date & Time are required.";
    }

    if (empty($errors)) {
        $sql_update = "UPDATE orders SET date_time = ?, customer_name = ?, product_type = ?, quantity = ? WHERE order_id = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ssdsi", $dateTime, $customer_name, $product_type, $quantity, $order_id);
            if ($stmt_update->execute()) {
                if ($stmt_update->affected_rows > 0) {
                    $_SESSION['success_message'] = "Order entry updated successfully.";
                } else {
                    $_SESSION['info_message'] = "No changes were made to the order entry.";
                }
                header("Location: orders-dashboard.php");
                exit();
            } else {
                $errors[] = "Error updating order entry: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
             $errors[] = "Database error preparing update statement: " . $conn->error;
        }
        $orderData = [ 
            'order_id' => $order_id,
            'customer_name' => $customer_name, 
            'product_type' => $product_type,
            'quantity' => $_POST['quantity'],
            'orderDate' => $orderDate,
            'orderTime' => $orderTime,
            'date_time' => $dateTime
        ];
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: orders-dashboard.php");
    exit();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order Entry - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles3.css">
</head>
<body>
    <div class="app-container">
        <main class="main-content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card card-custom p-4">
                            <h2 class="card-title text-center">Edit Order Entry</h2>
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($orderData): ?>
                                <form action="editorder.php" method="POST">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($orderData['order_id']) ?>">
                                    <div class="mb-3">
                                        <label for="customer_name" class="form-label">Customer Name</label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?= htmlspecialchars($orderData['customer_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product_type" class="form-label">Product Type</label>
                                        <select class="form-select" id="product_type" name="product_type" required>
                                            <?php foreach ($productsellerData as $product): ?>
                                                <option value="<?= htmlspecialchars($product['product_type']) ?>" <?= $product['product_type'] == $orderData['product_type'] ? 'selected' : '' ?>><?= htmlspecialchars($product['product_type']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" value="<?= htmlspecialchars($orderData['quantity']) ?>" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="orderDate" class="form-label">Date</label>
                                            <input type="date" class="form-control" id="orderDate" name="orderDate" value="<?= htmlspecialchars($orderData['orderDate']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="orderTime" class="form-label">Time</label>
                                            <input type="time" class="form-control" id="orderTime" name="orderTime" value="<?= htmlspecialchars($orderData['orderTime']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                         <a href="orders-dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p class="text-white text-center">Order entry not found or invalid request.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>
