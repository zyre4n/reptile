<?php
require_once 'crud.php';

/**
 * Category CRUD class for category-specific operations
 */
class CategoryCRUD extends CRUD {
    
    /**
     * Constructor
     * 
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        parent::__construct($conn, 'categories');
    }
    
    /**
     * Get category with its subcategories using recursive CTE
     * 
     * @param int $categoryId Category ID
     * @return array Category with subcategories
     */
    public function getCategoryWithSubcategories($categoryId) {
        // For MySQL 8.0+ we can use recursive CTE
        if ($this->isMySQLVersionSupported()) {
            $sql = "
                WITH RECURSIVE category_hierarchy AS (
                    SELECT id, name, description, parent_id, 0 as level
                    FROM categories
                    WHERE id = ?
                    
                    UNION ALL
                    
                    SELECT c.id, c.name, c.description, c.parent_id, ch.level + 1
                    FROM categories c
                    JOIN category_hierarchy ch ON c.parent_id = ch.id
                )
                SELECT * FROM category_hierarchy ORDER BY level, name
            ";
            
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param('i', $categoryId);
                $stmt->execute();
                $result = $stmt->get_result();
                $categories = [];
                
                while ($row = $result->fetch_assoc()) {
                    $categories[] = $row;
                }
                
                $stmt->close();
                return $categories;
            }
        } else {
            // Fallback for older MySQL versions
            return $this->getCategoryWithSubcategoriesFallback($categoryId);
        }
        
        return [];
    }
    
    /**
     * Fallback method for older MySQL versions that don't support recursive CTEs
     * 
     * @param int $categoryId Category ID
     * @return array Category with subcategories
     */
    private function getCategoryWithSubcategoriesFallback($categoryId) {
        $categories = [];
        
        // Get the main category
        $mainCategory = $this->read($categoryId);
        
        if ($mainCategory) {
            $mainCategory['level'] = 0;
            $categories[] = $mainCategory;
            
            // Get direct subcategories
            $sql = "SELECT * FROM categories WHERE parent_id = ?";
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param('i', $categoryId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $row['level'] = 1;
                    $categories[] = $row;
                    
                    // Get second-level subcategories
                    $subCategoryId = $row['id'];
                    $subSql = "SELECT * FROM categories WHERE parent_id = ?";
                    $subStmt = $this->conn->prepare($subSql);
                    
                    if ($subStmt) {
                        $subStmt->bind_param('i', $subCategoryId);
                        $subStmt->execute();
                        $subResult = $subStmt->get_result();
                        
                        while ($subRow = $subResult->fetch_assoc()) {
                            $subRow['level'] = 2;
                            $categories[] = $subRow;
                        }
                        
                        $subStmt->close();
                    }
                }
                
                $stmt->close();
            }
        }
        
        return $categories;
    }
    
    /**
     * Get all categories with their subcategories
     * 
     * @return array All categories with their subcategories
     */
    public function getAllCategoriesWithSubcategories() {
        // Get all parent categories (categories without a parent)
        $parentCategories = $this->readAll('parent_id IS NULL', [], 'name');
        $result = [];
        
        foreach ($parentCategories as $category) {
            $subcategories = $this->getCategoryWithSubcategories($category['id']);
            $result[] = [
                'category' => $category,
                'subcategories' => array_slice($subcategories, 1) // Skip the first element (the parent category)
            ];
        }
        
        return $result;
    }
    
    /**
     * Get categories as a hierarchical tree
     * 
     * @return array Categories in a hierarchical structure
     */
    public function getCategoryTree() {
        // Get all categories
        $allCategories = $this->readAll('', [], 'name');
        $categoryTree = [];
        
        // Build a lookup array
        $lookup = [];
        foreach ($allCategories as $category) {
            $lookup[$category['id']] = $category;
            $lookup[$category['id']]['children'] = [];
        }
        
        // Build the tree
        foreach ($allCategories as $category) {
            $categoryId = $category['id'];
            $parentId = $category['parent_id'];
            
            if ($parentId === null) {
                // This is a root category
                $categoryTree[] = &$lookup[$categoryId];
            } else {
                // This is a child category
                $lookup[$parentId]['children'][] = &$lookup[$categoryId];
            }
        }
        
        return $categoryTree;
    }
    
    /**
     * Add a subcategory
     * 
     * @param int $parentId Parent category ID
     * @param array $data Subcategory data
     * @return int|bool The ID of the inserted subcategory or false on failure
     */
    public function addSubcategory($parentId, $data) {
        // Ensure the parent category exists
        $parentCategory = $this->read($parentId);
        
        if (!$parentCategory) {
            return false;
        }
        
        // Set parent category ID
        $data['parent_id'] = $parentId;
        
        // Create the subcategory
        return $this->create($data);
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
     * Get products count by category
     * 
     * @return array Categories with product counts
     */
    public function getCategoriesWithProductCount() {
        $sql = "
            SELECT c.id, c.name, c.description, c.parent_id, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY c.name
        ";
        
        $result = $this->conn->query($sql);
        $categories = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        
        return $categories;
    }
}
?>
