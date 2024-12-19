USE pizza_shop;
CREATE TABLE users ( id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(255) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL );
CREATE TRIGGER reject_update BEFORE UPDATE ON users FOR EACH ROW SET NEW.password = OLD.password, NEW.username = OLD.username;
INSERT INTO users SET username = 'admin', password = SUBSTRING(MD5(RAND()), 1, 20);
CREATE TABLE orders ( order_id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, pizzas VARCHAR(64) NOT NULL, quantities VARCHAR(64) NOT NULL, total DOUBLE NOT NULL,  instructions TEXT, order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id));