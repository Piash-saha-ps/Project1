- This SQL statement creates the 'inventory1' table, which is used
-- by the add_stock.php file to store information about perishable
-- meat products.

CREATE TABLE inventory1 (
id INT(11) NOT NULL AUTO_INCREMENT,
meat_type VARCHAR(255) NOT NULL,
batch_number VARCHAR(255) NOT NULL,
quantity FLOAT NOT NULL,
supplier VARCHAR(255) NOT NULL,
cost FLOAT NOT NULL,
processing_date DATE NOT NULL,
expiration_date DATE NOT NULL,
location VARCHAR(255) NOT NULL,
PRIMARY KEY (id)
);
ALTER TABLE inventory1 ADD COLUMN meat_type VARCHAR(100);