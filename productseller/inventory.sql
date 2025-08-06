
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_time DATETIME NOT NULL,
    item VARCHAR(100) NOT NULL,
    product_type VARCHAR(100) NOT NULL,
    quantity_change DECIMAL(10,2) NOT NULL,
    adjustment_reason TEXT,
    adjustment_type VARCHAR(50),
    notes TEXT
);
