<?php
// models/Wishlist.php
class Wishlist {
    private $conn;
    private $table = 'wishlist';

    // Wishlist properties
    public $id;
    public $user_id;
    public $book_id;
    public $created_at;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Add book to wishlist
    public function addToWishlist() {
        // Check if book is already in wishlist
        if ($this->isInWishlist()) {
            return true; // Already in wishlist
        }
        
        // SQL query
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id = ?, book_id = ?, created_at = NOW()";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        
        // Bind parameters
        $stmt->bind_param("ii", $this->user_id, $this->book_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Check if book is in wishlist
    public function isInWishlist() {
        // SQL query
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE user_id = ? AND book_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $this->user_id, $this->book_id);
        
        // Execute query
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    // Remove book from wishlist
    public function removeFromWishlist() {
        // SQL query
        $query = "DELETE FROM " . $this->table . " 
                  WHERE user_id = ? AND book_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $this->user_id, $this->book_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Get user's wishlist
    public function getWishlist() {
        // SQL query
        $query = "SELECT w.*, b.title, b.author, b.price, b.discount_percent, 
                         b.cover_image, b.stock_quantity, b.approval_status
                  FROM " . $this->table . " w
                  JOIN books b ON w.book_id = b.id
                  WHERE w.user_id = ? AND b.approval_status = 'approved'
                  ORDER BY w.created_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        $result = $stmt->get_result();
        $wishlist = [];
        
        while($row = $result->fetch_assoc()) {
            $wishlist[] = $row;
        }
        
        return $wishlist;
    }
    
    // Get wishlist count
    public function getWishlistCount() {
        // SQL query
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
}
?>