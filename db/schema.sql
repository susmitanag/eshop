-- Everly database schema and sample data (v3)
-- Import with: mysql -u root -p < db/schema.sql
--
-- Demo logins:
--   Admin:     admin@everly.test / admin123
--   Customer:  demo@everly.test / demo123
--   (You can also create your own customer account on the Sign up page.)

CREATE DATABASE IF NOT EXISTS eshop CHARACTER SET utf8mb4;
USE eshop;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2) NULL,
    stock INT NOT NULL DEFAULT 0,
    art VARCHAR(20) NOT NULL DEFAULT 'book',
    swatch VARCHAR(7) NOT NULL DEFAULT '#3B4A6B',
    image VARCHAR(150) NULL,
    is_new TINYINT(1) NOT NULL DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FULLTEXT KEY ft_name_description (name, description)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(30) NOT NULL DEFAULT '',
    customer_address TEXT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('Processing', 'Shipped', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Processing',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT INTO users (name, email, password_hash, is_admin) VALUES
('Everly Admin', 'admin@everly.test', '$2y$10$ZODp4VMvnqByslQ30I2mS.WybH7lw1epTZ/6xTj.K2xQIgkVrTgD.', 1),
('Demo Customer', 'demo@everly.test', '$2y$10$CFWQkLHZ19sIGvwhNwvswOhC/oG/w/Ob9w2.j577PqgVQg3emXC8q', 0);

INSERT INTO categories (name, slug) VALUES
('Electronics', 'electronics'),
('Fashion', 'fashion'),
('Home & Living', 'home-living'),
('Books & Stationery', 'books-stationery');

INSERT INTO products (category_id, name, slug, description, price, old_price, stock, art, swatch, is_new, is_featured) VALUES
(1, 'Aero Wireless Headphones', 'aero-wireless-headphones', 'Over-ear headphones with active noise cancelling and a 30 hour battery. Soft ear cushions that stay comfortable all day.', 99.00, NULL, 25, 'headphones', '#3B4A6B', 0, 1),
(1, 'Pulse Smart Watch', 'pulse-smart-watch', 'Tracks your steps, sleep and heart rate. Water resistant, with a bright screen and 5 days of battery life.', 169.00, 199.00, 15, 'watch', '#C0682A', 0, 1),
(1, 'Boom Mini Speaker', 'boom-mini-speaker', 'A pocket sized Bluetooth speaker with deep bass. Splash resistant, so it can come along to the beach.', 59.00, NULL, 40, 'speaker', '#2F6F6E', 1, 1),
(1, 'Link 6-in-1 USB-C Hub', 'link-6-in-1-usb-c-hub', 'Adds HDMI, USB-A, SD card and fast charging to your laptop through one USB-C port.', 34.00, NULL, 33, 'hub', '#8E3B46', 0, 0),
(1, 'Tap Mechanical Keyboard', 'tap-mechanical-keyboard', 'A compact keyboard with quiet switches and a solid metal base. Works with Bluetooth or a cable.', 79.00, 99.00, 12, 'keyboard', '#5C5470', 0, 0),
(2, 'Indigo Denim Jacket', 'indigo-denim-jacket', 'A classic unisex jacket in a soft, mid-wash cotton denim. Two chest pockets and metal buttons.', 69.00, NULL, 20, 'jacket', '#4E7C59', 0, 1),
(2, 'Everyday Canvas Sneakers', 'everyday-canvas-sneakers', 'Light low-top sneakers with a rubber sole. Easy to pair with jeans, shorts or a skirt.', 49.00, NULL, 30, 'sneakers', '#D9A441', 1, 1),
(2, 'Chunky Wool Scarf', 'chunky-wool-scarf', 'A thick knitted scarf with a fringe. Soft on the skin, and warm on cold flights and rainy mornings.', 22.00, NULL, 50, 'scarf', '#7A6C5D', 0, 0),
(2, 'Full Grain Leather Belt', 'full-grain-leather-belt', 'Made from one piece of full grain leather with a brushed metal buckle. It gets better with age.', 28.00, NULL, 27, 'belt', '#3B4A6B', 0, 0),
(2, 'Trail Everyday Backpack', 'trail-everyday-backpack', 'Fits a 15 inch laptop, a lunch box and a jacket. Water repellent fabric and padded straps.', 59.00, 69.00, 18, 'backpack', '#C0682A', 0, 1),
(2, 'Round Frame Sunglasses', 'round-frame-sunglasses', 'Round frames with UV400 lenses. Light enough to forget you are wearing them.', 32.00, NULL, 35, 'sunglasses', '#2F6F6E', 1, 0),
(2, 'Market Canvas Tote', 'market-canvas-tote', 'A strong cotton tote for groceries, books or the gym. Washable and built to last.', 16.00, NULL, 60, 'tote', '#8E3B46', 0, 0),
(3, 'Hana Ceramic Mug Set', 'hana-ceramic-mug-set', 'A set of 4 handmade stoneware mugs. Each one holds about 300 ml and is safe in the dishwasher.', 28.00, NULL, 35, 'mugs', '#5C5470', 0, 1),
(3, 'Oak Table Lamp', 'oak-table-lamp', 'A minimal lamp with a wooden base and a fabric shade. Gives a warm light that is easy on the eyes.', 42.00, NULL, 18, 'lamp', '#4E7C59', 0, 0),
(3, 'Chunky Knit Throw', 'chunky-knit-throw', 'A heavy knit blanket, 130 by 170 cm. Fringed ends and a soft feel for the sofa or the bed.', 36.00, 45.00, 22, 'blanket', '#D9A441', 0, 1),
(3, 'Soy Candle Trio', 'soy-candle-trio', 'Three hand poured soy candles: cedar, vanilla and sea salt. Each one burns for about 25 hours.', 24.00, NULL, 40, 'candles', '#7A6C5D', 1, 0),
(3, 'Steel Water Bottle 750ml', 'steel-water-bottle-750ml', 'Keeps drinks cold for 24 hours and hot for 12. Leak proof lid and a wide mouth for ice.', 22.00, NULL, 45, 'bottle', '#3B4A6B', 0, 0),
(3, 'Monstera in Clay Pot', 'monstera-in-clay-pot', 'A healthy, easy to care for monstera in a terracotta pot. Likes bright light and a little water.', 18.00, NULL, 9, 'plant', '#C0682A', 0, 0),
(4, 'The Design of Everyday Things', 'the-design-of-everyday-things', 'A classic book on how good design works and why bad design fails. Great for anyone who builds things people use.', 19.00, NULL, 60, 'book', '#2F6F6E', 0, 0),
(4, 'Clean Code', 'clean-code', 'A handbook of agile software craftsmanship, with practical rules for writing code that other people can read.', 24.00, NULL, 45, 'book', '#1F2937', 0, 0),
(4, 'Atomic Habits', 'atomic-habits', 'A practical guide to building good habits and breaking bad ones, one small change at a time.', 16.00, NULL, 55, 'book', '#C0682A', 0, 1),
(4, 'Pocket Ring Notebook', 'pocket-ring-notebook', 'A5 notebook with 120 pages of dotted paper and a strong spiral binding. Lies flat when open.', 9.00, NULL, 80, 'notebook', '#8E3B46', 1, 0);

-- Sample orders for the demo customer, so the dashboards are not empty
INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, subtotal, shipping, total, status, created_at) VALUES
(2, 'Demo Customer', 'demo@everly.test', '555 010 1234', '12 Maple Street, Springfield, IL 62701', 131.00, 0.00, 131.00, 'Delivered', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(2, 'Demo Customer', 'demo@everly.test', '555 010 1234', '12 Maple Street, Springfield, IL 62701', 50.00, 5.00, 55.00, 'Shipped', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 'Demo Customer', 'demo@everly.test', '555 010 1234', '12 Maple Street, Springfield, IL 62701', 59.00, 5.00, 64.00, 'Processing', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 99.00), (1, 12, 1, 16.00), (1, 21, 1, 16.00),
(2, 13, 1, 28.00), (2, 17, 1, 22.00),
(3, 3, 1, 59.00);
