<?php
// productseller.php

// Include required files
require_once 'includes/config.php';
require_once 'includes/dbConnection.php';
require_once 'includes/functions.php';
session_start();

// Initialize dashboard variables
$totalInventory = $nearExpiry = $spoiledThisWeek = $todaysOrders = 0;

// Helper function for safe SQL query execution
function fetch_single_value($conn, $sql, $key) {
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row[$key] ?? 0;
    }
    return 0;
}

// Fetch dashboard card data
$totalInventory = fetch_single_value($conn, "SELECT SUM(quantity) as total FROM invent", 'total');
$nearExpiry = fetch_single_value($conn, "SELECT SUM(quantity) as near_expiry FROM invent WHERE expiry_date <= NOW() + INTERVAL 48 HOUR AND expiry_date >= NOW()", 'near_expiry');
$spoiledThisWeek = fetch_single_value($conn, "SELECT SUM(quantity_lost) as spoiled FROM loststock WHERE stage = 'Spoiled' AND date_time >= CURDATE() - INTERVAL 7 DAY", 'spoiled');
$todaysOrders = fetch_single_value($conn, "SELECT COUNT(*) as total_orders FROM ord WHERE order_date >= CURDATE()", 'total_orders');

// Fetch recent activity
$recentActivity = [];
$sqlRecentActivity = "SELECT action, description, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 4";
if ($result = $conn->query($sqlRecentActivity)) {
    while ($row = $result->fetch_assoc()) {
        $recentActivity[] = $row;
    }
}

// Fetch inventory and order details
$tableData = [];
$sqlTable = "SELECT i.id, i.product_type, c.customer_name, i.quantity, i.status
             FROM invent i
             LEFT JOIN customers c ON i.customer_id = c.id
             ORDER BY i.id DESC";
if ($result = $conn->query($sqlTable)) {
    while ($row = $result->fetch_assoc()) {
        $tableData[] = $row;
    }
}

// Time elapsed string helper
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diffArray = (array)$diff;
    $diffArray['w'] = floor($diff->d / 7);
    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];
    foreach ($string as $k => &$v) {
        $value = ($k === 'w') ? $diffArray['w'] : $diff->$k;
        if ($value) {
            $v = $value . ' ' . $v . ($value > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - Brandson</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles-dashboard2.css">
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
                <a href="../piash/dashboard.php" class="nav-item">...</a>
                <a href="../piash/add_stock.php" class="nav-item">...</a>
                <a href="../muaz/dashboard-template.php" class="nav-item">...</a>
                <a href="../piash/dashboard-1.php" class="nav-item">...</a>
                <a href="../raisa/productseller.php" class="nav-item active">
                    <!-- SVG icon omitted for brevity -->
                    <span class="nav-item-name">Sales</span>
                </a>
                <a href="../saif/loss_dashboard.php" class="nav-item">...</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <div class="search-container">
                    <!-- SVG icon omitted for brevity -->
                    <input type="text" placeholder="Search inventory, batches..." class="search-input">
                </div>
                <h1 class="page-title">Dashboard</h1>
                <div class="profile-container">
                    <button id="profileButton" class="profile-button">
                        <div class="profile-avatar">JD</div>
                    </button>
                </div>
            </header>

            <div class="main-content-body">
                <p>db connection successful</p>
                <div class="dashboard-cards row">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <p class="card-title">Total Inventory</p>
                                <h2><?= htmlspecialchars($totalInventory) ?> kg/units</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <p class="card-title">Near Expiry (48 hrs)</p>
                                <h2><?= htmlspecialchars($nearExpiry) ?> kg/units</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <p class="card-title">Spoiled This Week</p>
                                <h2><?= htmlspecialchars($spoiledThisWeek) ?> kg (0.0%)</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <p class="card-title">Today's Orders</p>
                                <h2><?= htmlspecialchars($todaysOrders) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Inventory Overview</div>
                            <div class="card-body">
                                <p>No inventory data available to display chart.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Recent Activity</div>
                            <div class="card-body">
                                <ul class="list-unstyled recent-activity">
                                    <?php foreach ($recentActivity as $activity): ?>
                                        <li class="activity-item">
                                            <span class="activity-time"><?= time_elapsed_string($activity['timestamp']) ?></span>
                                            <span class="activity-action"><?= htmlspecialchars($activity['action']) ?></span>
                                            <span class="activity-description"><?= htmlspecialchars($activity['description']) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        Inventory and Order Details
                        <div>
                            <a href="add_stock.php" class="btn btn-success me-2">Add Inventory</a>
                            <a href="createorder.php" class="btn btn-primary">Create Order</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Product/Customer</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($tableData)): ?>
                                        <?php foreach ($tableData as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['id']) ?></td>
                                                <td><?= htmlspecialchars($row['product_type']) ?></td>
                                                <td><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['quantity']) ?></td>
                                                <td><?= htmlspecialchars($row['status'] ?? 'In Stock') ?></td>
                                                <td>
                                                    <a href="editstock.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="deletestock.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6">No inventory or order data found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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