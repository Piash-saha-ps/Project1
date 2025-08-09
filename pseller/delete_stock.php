<?php
// delete_stock.php
include 'includes/config.php';
include 'includes/dbConnection.php';
include 'includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$itemId = filter_var($_POST['item_id'] ?? null, FILTER_VALIDATE_INT);

if ($itemId === false || $itemId <= 0) {
    $_SESSION['error_message'] = "Invalid item ID provided for deletion.";
} else {
    $conn = getdbConnection();
    if ($conn) {
        $sql = "DELETE FROM inventory WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $itemId);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $_SESSION['success_message'] = "Product deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "No product found with ID: " . htmlspecialchars($itemId);
                }
            } else {
                $_SESSION['error_message'] = "Error deleting product: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Database error preparing delete statement: " . $conn->error;
        }
        $conn->close();
    } else {
        $_SESSION['error_message'] = "Database connection failed. Cannot delete item.";
    }
}

header("Location: productseller.php");
exit();
?>