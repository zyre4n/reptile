<?php
require_once 'crud.php';

/**
 * Transaction CRUD class for inventory transaction operations
 */
class TransactionCRUD extends CRUD {
    
    /**
     * Constructor
     * 
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        parent::__construct($conn, 'inventory_transactions');
    }
    
    /**
     * Get recent transactions
     * 
     * @param int $limit Number of transactions to retrieve
     * @return array Recent transactions
     */
    public function getRecentTransactions($limit = 10) {
        $sql = "
            SELECT t.*, p.name as product_name, u.username 
            FROM {$this->table} t
            JOIN products p ON t.product_id = p.id
            JOIN users u ON t.user_id = u.id
            ORDER BY t.date DESC
            LIMIT ?
        ";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = [];
            
            while ($row = $result->fetch_assoc()) {
                $transactions[] = $row;
            }
            
            $stmt->close();
            return $transactions;
        }
        
        return [];
    }
    
    /**
     * Get transactions by product
     * 
     * @param int $productId Product ID
     * @return array Transactions for the product
     */
    public function getTransactionsByProduct($productId) {
        $sql = "
            SELECT t.*, p.name as product_name, u.username 
            FROM {$this->table} t
            JOIN products p ON t.product_id = p.id
            JOIN users u ON t.user_id = u.id
            WHERE t.product_id = ?
            ORDER BY t.date DESC
        ";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = [];
            
            while ($row = $result->fetch_assoc()) {
                $transactions[] = $row;
            }
            
            $stmt->close();
            return $transactions;
        }
        
        return [];
    }
    
    /**
     * Get transactions by user
     * 
     * @param int $userId User ID
     * @return array Transactions by the user
     */
    public function getTransactionsByUser($userId) {
        $sql = "
            SELECT t.*, p.name as product_name, u.username 
            FROM {$this->table} t
            JOIN products p ON t.product_id = p.id
            JOIN users u ON t.user_id = u.id
            WHERE t.user_id = ?
            ORDER BY t.date DESC
        ";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = [];
            
            while ($row = $result->fetch_assoc()) {
                $transactions[] = $row;
            }
            
            $stmt->close();
            return $transactions;
        }
        
        return [];
    }
    
    /**
     * Add stock to a product
     * 
     * @param int $productId Product ID
     * @param int $quantity Quantity to add
     * @param int $userId User ID
     * @param string $notes Transaction notes
     * @return bool True on success, false on failure
     */
    public function addStock($productId, $quantity, $userId, $notes = '') {
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Update product quantity
            $sql = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $stmt->bind_param('ii', $quantity, $productId);
            $result = $stmt->execute();
            $stmt->close();
            
            if (!$result) {
                throw new Exception("Failed to update product quantity");
            }
            
            // Record transaction
            $transactionData = [
                'product_id' => $productId,
                'quantity_change' => $quantity,
                'transaction_type' => 'stock_in',
                'notes' => $notes,
                'user_id' => $userId
            ];
            
            $transactionId = $this->create($transactionData);
            
            if (!$transactionId) {
                throw new Exception("Failed to record transaction");
            }
            
            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            return false;
        }
    }
    
    /**
     * Remove stock from a product
     * 
     * @param int $productId Product ID
     * @param int $quantity Quantity to remove
     * @param int $userId User ID
     * @param string $notes Transaction notes
     * @return bool True on success, false on failure
     */
    public function removeStock($productId, $quantity, $userId, $notes = '') {
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Check if there's enough stock
            $sql = "SELECT quantity FROM products WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            if ($result->num_rows != 1) {
                throw new Exception("Product not found");
            }
            
            $row = $result->fetch_assoc();
            $currentQuantity = $row['quantity'];
            
            if ($currentQuantity < $quantity) {
                throw new Exception("Not enough stock");
            }
            
            // Update product quantity
            $sql = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $stmt->bind_param('ii', $quantity, $productId);
            $result = $stmt->execute();
            $stmt->close();
            
            if (!$result) {
                throw new Exception("Failed to update product quantity");
            }
            
            // Record transaction
            $transactionData = [
                'product_id' => $productId,
                'quantity_change' => -$quantity,
                'transaction_type' => 'stock_out',
                'notes' => $notes,
                'user_id' => $userId
            ];
            
            $transactionId = $this->create($transactionData);
            
            if (!$transactionId) {
                throw new Exception("Failed to record transaction");
            }
            
            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            return false;
        }
    }
}
?>
