-- Green Bites Database Setup
-- Run this SQL in phpMyAdmin to create the necessary tables

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS green_bites;
USE green_bites;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu items table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    category_id INT NOT NULL,
    description VARCHAR(500),
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Insert default categories
INSERT INTO categories (name, description) VALUES 
('Drinks', 'Refreshing beverages and drinks'),
('Breakfast', 'Morning meals and snacks'),
('Lunch', 'Hearty lunch options'),
('Snacks', 'Quick bites and snacks');

-- Insert sample menu items for Drinks
INSERT INTO menu_items (title, price, image_url, category_id, description) VALUES 
('Mango Lassi', 60, 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/45/A_small_cup_of_coffee.JPG/640px-A_small_cup_of_coffee.JPG', 1, 'Creamy mango yogurt drink'),
('Iced Lemon Tea', 40, 'https://www.thespruceeats.com/thmb/WCF04P9fF5h-RMm9nqkOI2HJMos=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/iced-lemon-tea-recipe-765327-hero-01-1c336d17c8234bfcb34b393a13d0576e.jpg', 1, 'Refreshing iced tea with lemon'),
('Fresh Orange Juice', 70, 'https://www.allrecipes.com/thmb/3Lp2-4MtaL2HHCvGMeYx1tZaQQE=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/8842798-fresh-squeezed-orange-juice-DDMFS-4x3-d95b5cfd82894a549eeaebc3e9fbf36e.jpg', 1, 'Freshly squeezed orange juice'),
('Hot Coffee', 50, 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/45/A_small_cup_of_coffee.JPG/640px-A_small_cup_of_coffee.JPG', 1, 'Hot brewed coffee'),
('Green Tea', 35, 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Green_tea_3_appearances.jpg/640px-Green_tea_3_appearances.jpg', 1, 'Healthy green tea'),
('Coca Cola', 30, 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/CocaColaBottle_background_free.png/200px-CocaColaBottle_background_free.png', 1, 'Classic Coca Cola');

-- Insert sample menu items for Breakfast
INSERT INTO menu_items (title, price, image_url, category_id, description) VALUES 
('Paratha with Egg', 50, 'https://i.ytimg.com/vi/0ZdDTYhjOeo/maxresdefault.jpg', 2, 'Crispy paratha with fried egg'),
('Puri with Bhaji', 45, 'https://www.cookwithmanali.com/wp-content/uploads/2019/08/Puri-Bhaji-Recipe.jpg', 2, 'Fluffy puris with potato curry'),
('French Toast', 55, 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/French_toast_with_strawberries_and_cream.jpg/640px-French_toast_with_strawberries_and_cream.jpg', 2, 'Sweet French toast with syrup'),
('Omelette', 40, 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/Omelette_DSC00626.JPG/640px-Omelette_DSC00626.JPG', 2, 'Fluffy egg omelette'),
('Pancakes', 65, 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Blueberry_pancakes_%283%29.jpg/640px-Blueberry_pancakes_%283%29.jpg', 2, 'Fluffy pancakes with maple syrup'),
('Khichuri', 70, 'https://www.whiskaffair.com/wp-content/uploads/2020/09/Bengali-Khichuri-2-3.jpg', 2, 'Traditional Bengali khichuri');

-- Insert sample menu items for Lunch
INSERT INTO menu_items (title, price, image_url, category_id, description) VALUES 
('Steamed Rice', 50, 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Panta_Ilish.jpg', 3, 'Steamed white rice'),
('Chicken Curry', 150, 'https://www.allrecipes.com/thmb/249U3lsxHXdSPJdrTITzK_saOjE=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/6067251-bengali-chicken-curry-with-potatoes-Linda-Chor-4x3-1-94006a09cdec49ceaa26c1044f50359a.jpg', 3, 'Delicious chicken curry'),
('Mutton Korma', 250, 'https://www.bongcravings.com/wp-content/uploads/2017/01/IMG_3555.jpg', 3, 'Rich mutton korma'),
('Beef Tehari', 200, 'https://cookishcreation.com/wp-content/uploads/2021/05/Beef-Tehari-Cookish-Creation.jpg', 3, 'Spiced beef tehari'),
('Hilsa Fish Curry', 300, 'https://www.licious.in/blog/wp-content/uploads/2022/08/shutterstock_1810759399.jpg', 3, 'Traditional hilsa curry'),
('Chicken Biryani', 200, 'https://png.pngtree.com/png-clipart/20240830/original/pngtree-a-delicious-chicken-biryani-png-image_15893631.png', 3, 'Aromatic chicken biryani'),
('Lentil Dal', 80, 'https://i0.wp.com/veganbangla.com/wp-content/uploads/2020/05/fullsizeoutput_79b.jpeg?fit=1200%2C794&ssl=1', 3, 'Yellow lentil dal'),
('Vegetable Curry', 100, 'https://i0.wp.com/www.spiceandcolour.com/wp-content/uploads/2020/06/sabji-1.jpg?fit=1140%2C760&ssl=1', 3, 'Mixed vegetable curry'),
('Beef Haleem', 150, 'https://i.ytimg.com/vi/D2AgyOh0zJg/hq720.jpg?sqp=-oaymwEhCK4FEIIDSFryq4qpAxMIARUAAAAAGAElAADIQj0AgKJD&rs=AOn4CLCPbdGrghQeSTTZpm43XmAlRCh4Ag', 3, 'Slow-cooked beef haleem');

-- Insert sample menu items for Snacks
INSERT INTO menu_items (title, price, image_url, category_id, description) VALUES 
('Samosa', 20, 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cb/Samosa-and-Sweets.jpg/640px-Samosa-and-Sweets.jpg', 4, 'Crispy vegetable samosa'),
('Singara', 15, 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8f/Shingara.jpg/640px-Shingara.jpg', 4, 'Bengali style singara'),
('French Fries', 60, 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/French_Fries.JPG/640px-French_Fries.JPG', 4, 'Crispy golden fries'),
('Chicken Roll', 70, 'https://www.whiskaffair.com/wp-content/uploads/2020/06/Chicken-Kathi-Roll-2-3.jpg', 4, 'Spicy chicken roll'),
('Bakarkhani', 50, 'https://www.chainbaker.com/wp-content/uploads/2021/05/IMG_1918.jpg', 4, 'Traditional bakarkhani'),
('Veggie Burger', 80, 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Veggie_burger.jpg/640px-Veggie_burger.jpg', 4, 'Delicious veggie burger'),
('Chicken Nuggets', 90, 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Chicken_Nuggets.jpg/640px-Chicken_Nuggets.jpg', 4, 'Crispy chicken nuggets'),
('Spring Roll', 35, 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Springrolls.jpg/640px-Springrolls.jpg', 4, 'Crispy spring roll');
