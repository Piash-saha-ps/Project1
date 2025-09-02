CREATE TABLE invent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_type VARCHAR(100) NOT NULL,
    customer_id INT,
    quantity INT NOT NULL,
    status VARCHAR(50),
    expiry_date DATETIME,
    -- Add other columns as needed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);