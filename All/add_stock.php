<?php
// This file provides a complete CRUD (Create, Read, Update, Delete) interface
// for managing stock inventory. It is designed to work with a database
// (e.g., MySQL via XAMPP) and includes functionality for adding, viewing,
// editing, and deleting stock entries.

// Include database configuration and helper functions
include 'includes/config.php';
include 'includes/functions.php';

// --- Database Table Assumptions ---
// This script assumes a table named 'inventory' with the following columns:
// - id (INT, primary key, auto-increment)
// - meat_type (VARCHAR)
// - batch_number (VARCHAR)
// - quantity (FLOAT)
// - supplier (VARCHAR)
// - cost (FLOAT)
// - processing_date (DATE)
// - expiration_date (DATE)
// - location (VARCHAR)

// --- Handle CRUD Operations ---

// 1. CREATE Operation: Handle form submission for adding new stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stock'])) {
    // Validate and sanitize input
    $type = sanitize($_POST['type']);
    $batch = sanitize($_POST['batch']);
    $quantity = (float) $_POST['quantity'];
    $supplier = sanitize($_POST['supplier']);
    $cost = (float) $_POST['cost'];
    $processingDate = sanitize($_POST['processingDate']);
    $expirationDate = sanitize($_POST['expirationDate']);
    $location = sanitize($_POST['location']);
   
    // Basic validation
    $errors = [];
    if (empty($type)) $errors[] = "Meat type is required.";
    if (empty($batch)) $errors[] = "Batch number is required.";
    if ($quantity <= 0) $errors[] = "Quantity must be greater than zero.";
    if (empty($supplier)) $errors[] = "Supplier is required.";
    if ($cost <= 0) $errors[] = "Cost must be greater than zero.";
    if (empty($processingDate)) $errors[] = "Processing date is required.";
    if (empty($expirationDate)) $errors[] = "Expiration date is required.";
    
    if (empty($errors)) {
        $sql = "INSERT INTO inventory1 (meat_type, batch_number, quantity, supplier, cost, processing_date, expiration_date, location) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsssss", $type, $batch, $quantity, $supplier, $cost, $processingDate, $expirationDate, $location);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "New stock added successfully!";
            header("Location: add_stock.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error adding stock: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}

// 2. UPDATE Operation: Handle form submission for editing stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    // Validate and sanitize input
    $id = (int) $_POST['stock_id'];
    $type = sanitize($_POST['type']);
    $batch = sanitize($_POST['batch']);
    $quantity = (float) $_POST['quantity'];
    $supplier = sanitize($_POST['supplier']);
    $cost = (float) $_POST['cost'];
    $processingDate = sanitize($_POST['processingDate']);
    $expirationDate = sanitize($_POST['expirationDate']);
    $location = sanitize($_POST['location']);

    $errors = [];
    if ($id <= 0) $errors[] = "Invalid stock ID.";
    if (empty($type)) $errors[] = "Meat type is required.";
    if (empty($batch)) $errors[] = "Batch number is required.";
    if ($quantity <= 0) $errors[] = "Quantity must be greater than zero.";
    if (empty($supplier)) $errors[] = "Supplier is required.";
    if ($cost <= 0) $errors[] = "Cost must be greater than zero.";
    if (empty($processingDate)) $errors[] = "Processing date is required.";
    if (empty($expirationDate)) $errors[] = "Expiration date is required.";

    if (empty($errors)) {
        $sql = "UPDATE inventory1 SET meat_type=?, batch_number=?, quantity=?, supplier=?, cost=?, processing_date=?, expiration_date=?, location=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdssssi", $type, $batch, $quantity, $supplier, $cost, $processingDate, $expirationDate, $location, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Stock updated successfully!";
            header("Location: add_stock.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating stock: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}

// 3. DELETE Operation: Handle form submission for deleting stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_stock'])) {
    $id = (int) $_POST['stock_id'];

    if ($id > 0) {
        $sql = "DELETE FROM inventory1 WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Stock deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting stock: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Invalid stock ID.";
    }
    header("Location: add_stock.php");
    exit();
}

// 4. READ Operation: Fetch all stock data to display in a table
$stock_data = [];
$sql = "SELECT * FROM inventory1  ";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $stock_data[] = $row;
    }
}
$conn->close();

// Check for success or error messages from session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock - Perishable Meat Products</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS for sidebar and dashboard elements -->
    <link rel="stylesheet" href="css/styles-dashboard.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar and Header (assumed to be included from other files) -->
        <!-- You can include the sidebar and header from dashboard-template.php or similar files -->
        
        <div class="main-content">
            <div class="container-fluid p-4">
                <h1 class="mb-4">Stock Management</h1>

                <!-- Success and Error Messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $success_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Form to Add New Stock -->
                <div class="card p-4 shadow-sm mb-5">
                    <h2 class="card-title mb-4">Add New Stock</h2>
                    <form id="add-stock-form" action="add_stock.php" method="POST">
                        <input type="hidden" name="add_stock" value="1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label">Meat Type</label>
                                <input type="text" class="form-control" id="type" name="type" required>
                            </div>
                            <div class="col-md-6">
                                <label for="batch" class="form-label">Batch Number</label>
                                <input type="text" class="form-control" id="batch" name="batch" required>
                            </div>
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Quantity (kg/lbs)</label>
                                <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                            </div>
                            <div class="col-md-6">
                                <label for="supplier" class="form-label">Supplier</label>
                                <input type="text" class="form-control" id="supplier" name="supplier" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cost" class="form-label">Cost ($)</label>
                                <input type="number" step="0.01" class="form-control" id="cost" name="cost" required>
                            </div>
                            <div class="col-md-6">
                                <label for="processingDate" class="form-label">Processing Date</label>
                                <input type="date" class="form-control" id="processingDate" name="processingDate" required>
                            </div>
                            <div class="col-md-6">
                                <label for="expirationDate" class="form-label">Expiration Date</label>
                                <input type="date" class="form-control" id="expirationDate" name="expirationDate" required>
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label">Storage Location</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-modern-add">Add Stock</button>
                        </div>
                    </form>
                </div>

                <!-- Table to Display Existing Stock (READ Operation) -->
                <div class="card p-4 shadow-sm">
                    <h2 class="card-title mb-4">Current Stock Inventory</h2>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Meat Type</th>
                                    <th>Batch #</th>
                                    <th>Quantity</th>
                                    <th>Supplier</th>
                                    <th>Cost</th>
                                    <th>Processing Date</th>
                                    <th>Expiration Date</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stock_data)): ?>
                                    <?php foreach ($stock_data as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['id']) ?></td>
                                            <td><?= htmlspecialchars($item['meat_type']) ?></td>
                                            <td><?= htmlspecialchars($item['batch_number']) ?></td>
                                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                                            <td><?= htmlspecialchars($item['supplier']) ?></td>
                                            <td><?= htmlspecialchars($item['cost']) ?></td>
                                            <td><?= htmlspecialchars($item['processing_date']) ?></td>
                                            <td><?= htmlspecialchars($item['expiration_date']) ?></td>
                                            <td><?= htmlspecialchars($item['location']) ?></td>
                                            <td>
                                                <!-- Edit button opens modal with data -->
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStockModal"
                                                    data-id="<?= htmlspecialchars($item['id']) ?>"
                                                    data-type="<?= htmlspecialchars($item['meat_type']) ?>"
                                                    data-batch="<?= htmlspecialchars($item['batch_number']) ?>"
                                                    data-quantity="<?= htmlspecialchars($item['quantity']) ?>"
                                                    data-supplier="<?= htmlspecialchars($item['supplier']) ?>"
                                                    data-cost="<?= htmlspecialchars($item['cost']) ?>"
                                                    data-procdate="<?= htmlspecialchars($item['processing_date']) ?>"
                                                    data-expdate="<?= htmlspecialchars($item['expiration_date']) ?>"
                                                    data-location="<?= htmlspecialchars($item['location']) ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <!-- Delete button -->
                                                <form action="add_stock.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="delete_stock" value="1">
                                                    <input type="hidden" name="stock_id" value="<?= htmlspecialchars($item['id']) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this stock item?');">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center">No stock found. Please add a new entry.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Stock Modal (UPDATE Operation) -->
    <div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStockModalLabel">Edit Stock Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-stock-form" action="add_stock.php" method="POST">
                        <input type="hidden" name="update_stock" value="1">
                        <input type="hidden" id="edit-id" name="stock_id">
                        <div class="mb-3">
                            <label for="edit-type" class="form-label">Meat Type</label>
                            <input type="text" class="form-control" id="edit-type" name="type" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-batch" class="form-label">Batch Number</label>
                            <input type="text" class="form-control" id="edit-batch" name="batch" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-quantity" class="form-label">Quantity (kg/lbs)</label>
                            <input type="number" step="0.01" class="form-control" id="edit-quantity" name="quantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-supplier" class="form-label">Supplier</label>
                            <input type="text" class="form-control" id="edit-supplier" name="supplier" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-cost" class="form-label">Cost ($)</label>
                            <input type="number" step="0.01" class="form-control" id="edit-cost" name="cost" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-processingDate" class="form-label">Processing Date</label>
                            <input type="date" class="form-control" id="edit-processingDate" name="processingDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-expirationDate" class="form-label">Expiration Date</label>
                            <input type="date" class="form-control" id="edit-expirationDate" name="expirationDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-location" class="form-label">Storage Location</label>
                            <input type="text" class="form-control" id="edit-location" name="location" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Populate the edit modal with data from the table
            var editStockModal = document.getElementById('editStockModal');
            editStockModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var type = button.getAttribute('data-type');
                var batch = button.getAttribute('data-batch');
                var quantity = button.getAttribute('data-quantity');
                var supplier = button.getAttribute('data-supplier');
                var cost = button.getAttribute('data-cost');
                var procDate = button.getAttribute('data-procdate');
                var expDate = button.getAttribute('data-expdate');
                var location = button.getAttribute('data-location');
                
                var modalForm = document.getElementById('edit-stock-form');
                modalForm.querySelector('#edit-id').value = id;
                modalForm.querySelector('#edit-type').value = type;
                modalForm.querySelector('#edit-batch').value = batch;
                modalForm.querySelector('#edit-quantity').value = quantity;
                modalForm.querySelector('#edit-supplier').value = supplier;
                modalForm.querySelector('#edit-cost').value = cost;
                modalForm.querySelector('#edit-processingDate').value = procDate;
                modalForm.querySelector('#edit-expirationDate').value = expDate;
                modalForm.querySelector('#edit-location').value = location;
            });
        });
    </script>
    <script src="js/dashboard.js"></script>

</body>
</html>
