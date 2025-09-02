CREATE TABLE ord (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATETIME NOT NULL,
    customer_id INT,
    product_id INT,
    quantity INT,
    status VARCHAR(50),
    -- Add other columns as needed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    product_type VARCHAR(100),
    quantity INT NOT NULL,
    status VARCHAR(50),
    expiry_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);