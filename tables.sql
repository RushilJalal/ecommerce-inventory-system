-- Create Products table
CREATE TABLE Products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL
);

-- Create Categories table
CREATE TABLE Categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) UNIQUE NOT NULL
);

-- Create Orders table with foreign key reference to Products
CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    order_quantity INT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
);

-- Create Order Logs table for order tracking with foreign key reference to Orders
CREATE TABLE Order_Logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    order_quantity INT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
);

--trigger to log entry in order_logs
DELIMITER //
CREATE TRIGGER after_order_insert
AFTER INSERT ON Orders
FOR EACH ROW
BEGIN
    INSERT INTO Order_Logs (order_id, order_quantity) VALUES (NEW.order_id, NEW.order_quantity);
END;
//
DELIMITER ;

--stored procedure accepts a product_id and returns the product’s name, its category, and the total number of orders placed for that product.
DELIMITER //
CREATE PROCEDURE GetProductOrders(IN product_id INT)
BEGIN
    SELECT Products.product_name, Categories.category_name,
           (SELECT COUNT(*) FROM Orders WHERE Orders.product_id = product_id) AS total_orders
    FROM Products
    LEFT JOIN Categories ON Categories.category_id = Products.product_id
    WHERE Products.product_id = product_id;
END;
//
DELIMITER ;
