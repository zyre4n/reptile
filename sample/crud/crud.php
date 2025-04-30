<?php
/**
 * Base CRUD class for database operations
 */
class CRUD {
    protected $conn;
    protected $table;
    protected $primaryKey = 'id';
    
    /**
     * Constructor
     * 
     * @param mysqli $conn Database connection
     * @param string $table Table name
     */
    public function __construct($conn, $table) {
        $this->conn = $conn;
        $this->table = $table;
    }
    
    /**
     * Create a new record
     * 
     * @param array $data Associative array of column names and values
     * @return int|bool The ID of the inserted record or false on failure
     */
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $types = '';
        $values = [];
        
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param($types, ...$values);
            $result = $stmt->execute();
            
            if ($result) {
                $id = $stmt->insert_id;
                $stmt->close();
                return $id;
            }
            
            $stmt->close();
        }
        
        return false;
    }
    
    /**
     * Read a single record by ID
     * 
     * @param int $id Record ID
     * @return array|bool Record data or false if not found
     */
    public function read($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $record = $result->fetch_assoc();
                $stmt->close();
                return $record;
            }
            
            $stmt->close();
        }
        
        return false;
    }
    
    /**
     * Read all records with optional conditions
     * 
     * @param string $where Optional WHERE clause
     * @param array $params Optional parameters for WHERE clause
     * @param string $orderBy Optional ORDER BY clause
     * @return array Array of records
     */
    public function readAll($where = '', $params = [], $orderBy = '') {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        
        if (!empty($orderBy)) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt) {
                $types = '';
                $values = [];
                
                foreach ($params as $value) {
                    if (is_int($value)) {
                        $types .= 'i';
                    } elseif (is_float($value)) {
                        $types .= 'd';
                    } elseif (is_string($value)) {
                        $types .= 's';
                    } else {
                        $types .= 's';
                    }
                    $values[] = $value;
                }
                
                $stmt->bind_param($types, ...$values);
                $stmt->execute();
                $result = $stmt->get_result();
                $records = [];
                
                while ($row = $result->fetch_assoc()) {
                    $records[] = $row;
                }
                
                $stmt->close();
                return $records;
            }
        } else {
            $result = $this->conn->query($sql);
            $records = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $records[] = $row;
                }
            }
            
            return $records;
        }
        
        return [];
    }
    
    /**
     * Update a record
     * 
     * @param int $id Record ID
     * @param array $data Associative array of column names and values
     * @return bool True on success, false on failure
     */
    public function update($id, $data) {
        $setClause = [];
        $types = 'i'; // First parameter is always the ID (integer)
        $values = [$id];
        
        foreach ($data as $column => $value) {
            $setClause[] = "$column = ?";
            
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 's';
            }
            
            $values[] = $value;
        }
        
        $setClauseStr = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET $setClauseStr WHERE {$this->primaryKey} = ?";
        
        // Move the ID to the end for the WHERE clause
        $values[] = array_shift($values);
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param($types, ...$values);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        
        return false;
    }
    
    /**
     * Delete a record
     * 
     * @param int $id Record ID
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        
        return false;
    }
    
    /**
     * Count records with optional conditions
     * 
     * @param string $where Optional WHERE clause
     * @param array $params Optional parameters for WHERE clause
     * @return int Number of records
     */
    public function count($where = '', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        
        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt) {
                $types = '';
                $values = [];
                
                foreach ($params as $value) {
                    if (is_int($value)) {
                        $types .= 'i';
                    } elseif (is_float($value)) {
                        $types .= 'd';
                    } elseif (is_string($value)) {
                        $types .= 's';
                    } else {
                        $types .= 's';
                    }
                    $values[] = $value;
                }
                
                $stmt->bind_param($types, ...$values);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
                return $row['count'];
            }
        } else {
            $result = $this->conn->query($sql);
            
            if ($result) {
                $row = $result->fetch_assoc();
                return $row['count'];
            }
        }
        
        return 0;
    }
}
?>
