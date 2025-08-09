<?php
// productseller.php
include 'includes/config.php';
include 'includes/dbConnection.php';
include 'includes/functions.php';
session_start();

// Database queries for the dashboard cards
$totalInventory = 0;
$nearExpiry = 0;
$spoiledThisWeek = 0;
$todaysOrders = 0;

// Fetch Total Inventory
$sqlTotalInventory = "SELECT SUM(quantity) as total FROM invent";
$result = $conn->query($sqlTotalInventory);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalInventory = $row['total'] ?? 0;
}

// Fetch Near Expiry (within 48 hours)
$sqlNearExpiry = "SELECT SUM(quantity) as near_expiry FROM invent WHERE expiry_date <= NOW() + INTERVAL 48 HOUR AND expiry_date >= NOW()";
$result = $conn->query($sqlNearExpiry);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nearExpiry = $row['near_expiry'] ?? 0;
}

// Fetch Spoiled This Week (This is an example, assuming you have a `loststock` table and a `loss_reason` or `stage` column)
$sqlSpoiled = "SELECT SUM(quantity_lost) as spoiled FROM loststock WHERE stage = 'Spoiled' AND date_time >= CURDATE() - INTERVAL 7 DAY";
$result = $conn->query($sqlSpoiled);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $spoiledThisWeek = $row['spoiled'] ?? 0;
}

// Fetch Today's Orders
$sqlTodaysOrders = "SELECT COUNT(*) as total_orders FROM orders WHERE order_date >= CURDATE()";
$result = $conn->query($sqlTodaysOrders);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $todaysOrders = $row['total_orders'] ?? 0;
}

// Fetch Recent Activity (Example, adjust query to your log table)
$sqlRecentActivity = "SELECT action, description, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 4";
$recentActivity = [];
$result = $conn->query($sqlRecentActivity);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentActivity[] = $row;
    }
}

// Fetch Inventory and Order Details
$sqlTable = "SELECT i.id, i.product_type, c.customer_name, i.quantity, i.status 
             FROM invent i
             LEFT JOIN customers c ON i.customer_id = c.id
             ORDER BY i.id DESC"; // Example query, adjust to your table structure
$tableData = [];
$result = $conn->query($sqlTable);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tableData[] = $row;
    }
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Add 'w' property to $diff as an array key
    $diffArray = (array)$diff;
    $diffArray['w'] = floor($diff->d / 7);
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
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
    <link rel="stylesheet" href="css/styles-dashboard2.css"> </head>
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
                <a href="../raisa/productseller.php" class="nav-item active"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span class="nav-item-name">Sales</span>
                </a>
                <a href="../saif/loss_dashboard.php" class="nav-item">...</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <div class="search-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
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
                                <h2><?php echo htmlspecialchars($totalInventory); ?> kg/units</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <p class="card-title">Near Expiry (48 hrs)</p>
                                <h2><?php echo htmlspecialchars($nearExpiry); ?> kg/units</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <p class="card-title">Spoiled This Week</p>
                                <h2><?php echo htmlspecialchars($spoiledThisWeek); ?> kg (0.0%)</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <p class="card-title">Today's Orders</p>
                                <h2><?php echo htmlspecialchars($todaysOrders); ?></h2>
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
                                            <span class="activity-time"><?php echo time_elapsed_string($activity['timestamp']); ?></span>
                                            <span class="activity-action"><?php echo htmlspecialchars($activity['action']); ?></span>
                                            <span class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></span>
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
                            <a href="create_order.php" class="btn btn-primary">Create Order</a>
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
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['product_type']); ?></td>
                                                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($row['status'] ?? 'In Stock'); ?></td>
                                                <td>
                                                    <a href="edit_stock.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="delete_stock.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
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
    <script src="js/dashboard.js"></script> </body>
</html>