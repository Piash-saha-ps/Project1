<?php
// Start session for messages (ensure this is at the very top before any output)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Includes
include 'includes/config.php';
include 'includes/dbConnection.php';
include 'includes/functions.php';

// Check for success message
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Fetch all inventory items
$inventoryItems = [];
 // Assuming this function exists in dbConnection.php
if ($conn) {
    $sql_inventory = "SELECT id, name, quantity, price FROM inventory ORDER BY name ASC";
$result_inventory = $conn->query($sql_inventory);

if ($result_inventory) {
    while ($row = $result_inventory->fetch_assoc()) {
        $inventoryItems[] = $row;
    }
    $result_inventory->free();
} else {
    $error_message = "Database error fetching inventory: " . $conn->error;
    error_log("MySQL Query Error (productseller.php): " . $conn->error);
}
$conn->close();
} else {
    $error_message = "Database connection failed.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Seller Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles-dashboard2.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="app-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="logo.png" alt="Brandson Logo" width="28" height="28">
                <span class="brand-name">Brandson</span>
            </div>
        </div>
        <nav class="sidebar-nav">
             <a href="../samiul/dashboard.php" class="nav-item">
                <svg ...>...</svg>
                <span class="nav-item-name">Dashboard</span>
            </a>
            <a href="add_stock.php" class="nav-item">
                <svg ...>...</svg>
                <span class="nav-item-name">Add Stock</span>
            </a>
            <a href="..\jugumaya\dashboard-1.php" class="nav-item">
                <svg ...>...</svg>
                <span class="nav-item-name">Loss Auditor</span>
            </a>
            <a href="productseller.php" class="nav-item active">
                <svg ...>...</svg>
                <span class="nav-item-name">Product Seller</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1 class="page-title">Product Seller Dashboard</h1>
            <div class="profile-container">
                <button id="profileButton" class="profile-button">
                    <div class="profile-avatar">PS</div>
                </button>
                <div id="profileDropdown" class="profile-dropdown">
                    </div>
            </div>
        </header>

        <section class="dashboard-content">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert"><?= $success_message ?></div>
            <?php endif; ?>
            <?php if (isset($error_message) && !empty($error_message)): ?>
                <div class="alert alert-danger" role="alert"><?= $error_message ?></div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Product Inventory</h2>
                <div>
                    <a href="add_stock.php" class="btn btn-primary me-2"><i class="bi bi-plus-circle"></i> Add Stock</a>
                    <a href="createorder.php" class="btn btn-success"><i class="bi bi-bag-plus"></i> Create Order</a>
                </div>
            </div>

            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($inventoryItems) > 0): ?>
                                    <?php foreach ($inventoryItems as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['id']) ?></td>
                                            <td><?= htmlspecialchars($item['name']) ?></td>
                                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                                            <td>$<?= htmlspecialchars($item['price']) ?></td>
                                            <td>
                                                <a href="edit_stock.php?id=<?= htmlspecialchars($item['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <form action="delete_stock.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['id']) ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No products in inventory.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>