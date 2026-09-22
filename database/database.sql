



CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    service VARCHAR(100) NOT NULL,
    budget VARCHAR(50),
    message TEXT NOT NULL,
    status ENUM('New', 'Read', 'Replied') DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);