CREATE TABLE activity_log (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    timestamp DATETIME
);

