<?php
class Database {
    private $host = 'localhost';
    private $db = 'bookstore';
    private $user = 'root';
    private $pass = '';
    public $conn;

    public function __construct() {
        $this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $this->initializeDatabase();
    }

    private function initializeDatabase() {
        $create_db = "CREATE DATABASE IF NOT EXISTS books";
        mysqli_query($this->conn, $create_db);
        mysqli_select_db($this->conn, $this->db);

        $tables = [
            "CREATE TABLE IF NOT EXISTS User (
                UserID INT PRIMARY KEY AUTO_INCREMENT,
                Name VARCHAR(255) NOT NULL,
                Email VARCHAR(255) UNIQUE NOT NULL,
                Password VARCHAR(255) NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS Customer (
                UserID INT PRIMARY KEY,
                Address VARCHAR(255) NOT NULL,
                Phone VARCHAR(20) NOT NULL,
                FOREIGN KEY (UserID) REFERENCES User(UserID)
            )",
            "CREATE TABLE IF NOT EXISTS Seller (
                UserID INT PRIMARY KEY,
                StoreName VARCHAR(255) NOT NULL,
                SalesReport TEXT,
                FOREIGN KEY (UserID) REFERENCES User(UserID)
            )",
            "CREATE TABLE IF NOT EXISTS Admin (
                UserID INT PRIMARY KEY,
                FOREIGN KEY (UserID) REFERENCES User(UserID)
            )",
            "CREATE TABLE IF NOT EXISTS Category (
                CategoryID INT PRIMARY KEY AUTO_INCREMENT,
                Name VARCHAR(100) NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS Book (
                BookID INT PRIMARY KEY AUTO_INCREMENT,
                Title VARCHAR(255) NOT NULL,
                Author VARCHAR(255) NOT NULL,
                Price DECIMAL(10,2) NOT NULL,
                Stock INT NOT NULL,
                CategoryID INT,
                Rating DECIMAL(3,2) DEFAULT 0,
                SellerID INT,
                CoverImage VARCHAR(255),
                Status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
                FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID),
                FOREIGN KEY (SellerID) REFERENCES Seller(UserID)
            )",
            "CREATE TABLE IF NOT EXISTS Cart (
                CartID INT PRIMARY KEY AUTO_INCREMENT,
                CustomerID INT,
                TotalPrice DECIMAL(10,2) DEFAULT 0,
                FOREIGN KEY (CustomerID) REFERENCES Customer(UserID)
            )",
            "CREATE TABLE IF NOT EXISTS CartItem (
                CartID INT,
                BookID INT,
                Quantity INT NOT NULL,
                PRIMARY KEY (CartID, BookID),
                FOREIGN KEY (CartID) REFERENCES Cart(CartID),
                FOREIGN KEY (BookID) REFERENCES Book(BookID)
            )",
            "CREATE TABLE IF NOT EXISTS `Order` (
                OrderID INT PRIMARY KEY AUTO_INCREMENT,
                CustomerID INT,
                OrderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
                TotalAmount DECIMAL(10,2) NOT NULL,
                Status ENUM('Pending', 'Shipped', 'Delivered', 'Canceled') DEFAULT 'Pending',
                FOREIGN KEY (CustomerID) REFERENCES Customer(UserID)
            )",
            "CREATE TABLE IF NOT EXISTS OrderItem (
                OrderID INT,
                BookID INT,
                Quantity INT NOT NULL,
                Subtotal DECIMAL(10,2) NOT NULL,
                PRIMARY KEY (OrderID, BookID),
                FOREIGN KEY (OrderID) REFERENCES `Order`(OrderID),
                FOREIGN KEY (BookID) REFERENCES Book(BookID)
            )",
            "CREATE TABLE IF NOT EXISTS Review (
                ReviewID INT PRIMARY KEY AUTO_INCREMENT,
                CustomerID INT,
                BookID INT,
                Rating DECIMAL(2,1) CHECK (Rating BETWEEN 1 AND 5),
                Comment TEXT,
                FOREIGN KEY (CustomerID) REFERENCES Customer(UserID),
                FOREIGN KEY (BookID) REFERENCES Book(BookID)
            )",
            "CREATE TABLE IF NOT EXISTS Wishlist (
                WishlistID INT PRIMARY KEY AUTO_INCREMENT,
                CustomerID INT,
                BookID INT,
                FOREIGN KEY (CustomerID) REFERENCES Customer(UserID),
                FOREIGN KEY (BookID) REFERENCES Book(BookID)
            )"
        ];

        foreach ($tables as $table) {
            mysqli_query($this->conn, $table);
        }

        $check_data = "SELECT COUNT(*) as count FROM User";
        $result = mysqli_query($this->conn, $check_data);
        $count = mysqli_fetch_assoc($result)['count'];

        if ($count == 0) {
            $sample_data = [
                "INSERT INTO User (Name, Email, Password) VALUES 
                    ('Admin User', 'admin@bookstore.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "'),
                    ('Customer User', 'customer@bookstore.com', '" . password_hash('customer123', PASSWORD_DEFAULT) . "'),
                    ('Seller User', 'seller@bookstore.com', '" . password_hash('seller123', PASSWORD_DEFAULT) . "')",
                "INSERT INTO Admin (UserID) VALUES (1)",
                "INSERT INTO Customer (UserID, Address, Phone) VALUES (2, '123 Customer St', '1234567890')",
                "INSERT INTO Seller (UserID, StoreName) VALUES (3, 'Sample Store')",
                "INSERT INTO Category (Name) VALUES ('Fiction'), ('Non-Fiction')",
                "INSERT INTO Book (Title, Author, Price, Stock, CategoryID, SellerID, CoverImage) VALUES 
                    ('Sample Book 1', 'Author 1', 19.99, 10, 1, 3, 'sample1.jpg'),
                    ('Sample Book 2', 'Author 2', 29.99, 5, 2, 3, 'sample2.png')"
            ];
            foreach ($sample_data as $data) {
                mysqli_query($this->conn, $data);
            }
        }
    }
}
?>