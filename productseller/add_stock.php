<?php
include 'includes/config.php';
include 'includes/dbConnection.php';
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_time = $_POST['date_time'];
    $productType = sanitize($_POST['productType']);
    $quantity = $_POST['quantity'];
    $adjustmentReason = $_POST['adjustmentReason'];
    
    $sql = "INSERT INTO productseller (date_time, product_type, quantity, adjustment_reason) 
            VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssds", $date_time, $productType, $quantity, $adjustmentReason);
        if ($stmt->execute()) {
            $success_message = "Productseller data added successfully!";
            header("Location: productseller.php");
            exit;
        } else {
            $error_message = "Error adding productseller data.";
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $conn->error;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Productseller - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card p-4">
                    <h2 class="card-title text-center mb-4">Add New Productseller Entry</h2>
                    <form action="addstock.php" method="POST">
                        <div class="mb-3">
                            <label for="date_time" class="form-label">Date & Time</label>
                            <input type="datetime-local" class="form-control" id="date_time" name="date_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="productType" class="form-label">Product Type</label>
                            <input type="text" class="form-control" id="productType" name="productType" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="adjustmentReason" class="form-label">Adjustment Reason</label>
                            <textarea class="form-control" id="adjustmentReason" name="adjustmentReason" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <a href="productseller.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>