CREATE TABLE invent (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_type VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    status VARCHAR(50) DEFAULT 'In Stock',
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE invent ADD COLUMN customer_id INT(11) NULL;