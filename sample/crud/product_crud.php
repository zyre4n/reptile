<?php
require_once 'crud.php';

/**
 * Product CRUD class for product-specific operations
 */
class ProductCRUD extends CRUD {
    
    /**
     * Constructor
     * 
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        parent::__construct($conn, 'products');
    }
    
    /**
     * Get product with its variants using recursive CTE
     * 
     * @param int $productId Product ID
     * @return array Product with variants
     */
    public function getProductWithVariants($productId) {
        // For MySQL 8.0+ we can use recursive CTE
        if ($this->isMySQLVersionSupported()) {
            $sql = "
                WITH RECURSIVE product_hierarchy AS (
                    SELECT id, name, description, price, quantity, category_id, parent_product_id, is_variant, 0 as level
                    FROM products
                    WHERE id = ?
                    
                    UNION ALL
                    
                    SELECT p.id, p.name, p.description, p.price, p.quantity, p.category_id, p.parent_product_id, p.is_variant, ph.level + 1
                    FROM products p
                    JOIN product_hierarchy ph ON p.parent_product_id = ph.id
                )
                SELECT * FROM product_hierarchy ORDER BY level, name
            ";
            
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                $products = [];
                
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
                
                $stmt->close();
                return $products;
            }
        } else {
            // Fallback for older MySQL versions
            return $this->getProductWithVariantsFallback($productId);
        }
        
        return [];
    }
    
    /**
     * Fallback method for older MySQL versions that don't support recursive CTEs
     * 
     * @param int $productId Product ID
     * @return array Product with variants
     */
    private function getProductWithVariantsFallback($productId) {
        $products = [];
        
        // Get the main product
        $mainProduct = $this->read($productId);
        
        if ($mainProduct) {
            $mainProduct['level'] = 0;
            $products[] = $mainProduct;
            
            // Get direct variants
            $sql = "SELECT * FROM products WHERE parent_product_id = ?";
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $row['level'] = 1;
                    $products[] = $row;
                }
                
                $stmt->close();
            }
        }
        
        return $products;
    }
    
    /**
     * Get all products with their variants
     * 
     * @return array All products with their variants
     */
    public function getAllProductsWithVariants() {
        // Get all parent products (products without a parent)
        $parentProducts = $this->readAll('parent_product_id IS NULL', [], 'name');
        $result = [];
        
        foreach ($parentProducts as $product) {
            $variants = $this->getProductWithVariants($product['id']);
            $result[] = [
                'product' => $product,
                'variants' => array_slice($variants, 1) // Skip the first element (the parent product)
            ];
        }
        
        return $result;
    }
    
    /**
     * Get low stock products
     * 
     * @param int $threshold Low stock threshold
     * @return array Low stock products
     */
    public function getLowStockProducts($threshold = 10) {
        return $this->readAll('quantity < ?', [$threshold], 'quantity ASC');
    }
    
    /**
     * Check if MySQL version supports recursive CTEs
     * 
     * @return bool True if MySQL version is 8.0 or higher
     */
    private function isMySQLVersionSupported() {
        $versionQuery = "SELECT VERSION() as version";
        $result = $this->conn->query($versionQuery);
        
        if ($result) {
            $row = $result->fetch_assoc();
            $version = $row['version'];
            $majorVersion = (int)explode('.', $version)[0];
            
            return $majorVersion >= 8;
        }
        
        return false;
    }
    
    /**
     * Add a product variant
     * 
     * @param int $parentId Parent product ID
     * @param array $data Variant data
     * @return int|bool The ID of the inserted variant or false on failure
     */
    public function addVariant($parentId, $data) {
        // Ensure the parent product exists
        $parentProduct = $this->read($parentId);
        
        if (!$parentProduct) {
            return false;
        }
        
        // Set parent product ID and variant flag
        $data['parent_product_id'] = $parentId;
        $data['is_variant'] = true;
        
        // Create the variant
        return $this->create($data);
    }
    
    /**
     * Get products by category
     * 
     * @param int $categoryId Category ID
     * @return array Products in the category
     */
    public function getProductsByCategory($categoryId) {
        return $this->readAll('category_id = ?', [$categoryId], 'name');
    }
}
?>
