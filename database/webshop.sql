-- Create the database
CREATE DATABASE IF NOT EXISTS blattwerk_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blattwerk_shop;

-- ------------------------
-- Table: users
-- ------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    salutation VARCHAR(10),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    address VARCHAR(255),
    postal_code VARCHAR(10),
    city VARCHAR(100),
    email VARCHAR(255) UNIQUE,
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    payment_info TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    active BOOLEAN DEFAULT TRUE
);

-- ------------------------
-- Table: categories
-- ------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

-- Insert dummy categories
INSERT INTO categories (name) VALUES
('Zimmerpflanzen'),
('Kräuter'),
('Kakteen');

-- ------------------------
-- Table: products
-- ------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2),
    rating DECIMAL(2,1) DEFAULT 0.0,
    image VARCHAR(255),
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Insert dummy products
INSERT INTO products (name, description, price, rating, image, category_id) VALUES
('Monstera Deliciosa', 'Tropische Zimmerpflanze mit großen Blättern.', 29.99, 4.5, 'productpictures/monstera.jpg', 1),
('Basilikum', 'Frisches Basilikum im Topf.', 3.49, 4.0, 'productpictures/basilikum.jpg', 2),
('Aloe Vera', 'Pflegeleichte Sukkulente mit heilender Wirkung.', 12.90, 4.8, 'productpictures/aloe.jpg', 1),
('Kaktus', 'Kleiner Deko-Kaktus mit Blüte.', 5.95, 3.9, 'productpictures/kaktus.jpg', 3);

-- ------------------------
-- Table: cart_items 
-- ------------------------
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
