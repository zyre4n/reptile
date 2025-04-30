<?php
/**
 * Database Utility Functions
 * 
 * This file contains helper functions for common database operations.
 */

// Include database connection
require_once __DIR__ . '/../config/database.php';

/**
 * Get a single record by ID
 * 
 * @param string $table Table name
 * @param int $id Record ID
 * @param string $id_column Primary key column name (default: 'id')
 * @return array|null Record data or null if not found
 */
function get_record_by_id($table, $id, $id_column = 'id') {
    global $conn;
    
    $table = $conn->real_escape_string($table);
    $id_column = $conn->real_escape_string($id_column);
    $id = (int)$id;
    
    $sql = "SELECT * FROM $table WHERE $id_column = $id LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get all records from a table with optional conditions
 * 
 * @param string $table Table name
 * @param string $where Optional WHERE clause
 * @param string $order_by Optional ORDER BY clause
 * @param int $limit Optional LIMIT clause
 * @param int $offset Optional OFFSET clause
 * @return array Array of records
 */
function get_all_records($table, $where = '', $order_by = '', $limit = 0, $offset = 0) {
    global $conn;
    
    $table = $conn->real_escape_string($table);
    $sql = "SELECT * FROM $table";
    
    if (!empty($where)) {
        $sql .= " WHERE $where";
    }
    
    if (!empty($order_by)) {
        $sql .= " ORDER BY $order_by";
    }
    
    if ($limit > 0) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    
    $result = $conn->query($sql);
    $records = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
    }
    
    return $records;
}

/**
 * Insert a record into a table
 * 
 * @param string $table Table name
 * @param array $data Associative array of column names and values
 * @return int|bool The ID of the inserted record or false on failure
 */
function insert_record($table, $data) {
    global $conn;
    
    $table = $conn->real_escape_string($table);
    $columns = [];
    $values = [];
    $placeholders = [];
    
    foreach ($data as $column => $value) {
        $columns[] = $conn->real_escape_string($column);
        $values[] = $value;
        $placeholders[] = '?';
    }
    
    $columns_str = implode(', ', $columns);
    $placeholders_str = implode(', ', $placeholders);
    
    $sql = "INSERT INTO $table ($columns_str) VALUES ($placeholders_str)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    // Create types string for bind_param
    $types = '';
    foreach ($values as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } elseif (is_string($value)) {
            $types .= 's';
        } else {
            $types .= 's';
        }
    }
    
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    }
    
    $stmt->close();
    return false;
}

/**
 * Update a record in a table
 * 
 * @param string $table Table name
 * @param int $id Record ID
 * @param array $data Associative array of column names and values
 * @param string $id_column Primary key column name (default: 'id')
 * @return bool True on success, false on failure
 */
function update_record($table, $id, $data, $id_column = 'id') {
    global $conn;
    
    $table = $conn->real_escape_string($table);
    $id_column = $conn->real_escape_string($id_column);
    $id = (int)$id;
    
    $set_clauses = [];
    $values = [];
    
    foreach ($data as $column => $value) {
        $set_clauses[] = $conn->real_escape_string($column) . ' = ?';
        $values[] = $value;
    }
    
    $set_clause_str = implode(', ', $set_clauses);
    
    $sql = "UPDATE $table SET $set_clause_str WHERE $id_column = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    // Create types string for bind_param
    $types = '';
    foreach ($values as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } elseif (is_string($value)) {
            $types .= 's';
        } else {
            $types .= 's';
        }
    }
    $types .= 'i'; // For the ID parameter
    
    // Add ID to values array
    $values[] = $id;
    
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows > 0;
    }
    
    $stmt->close();
    return false;
}

/**
 * Delete a record from a table
 * 
 * @param string $table Table name
 * @param int $id Record ID
 * @param string $id_column Primary key column name (default: 'id')
 * @return bool True on success, false on failure
 */
function delete_record($table, $id, $id_column = 'id') {
    global $conn;
    
    $table = $conn->real_escape_string($table);
    $id_column = $conn->real_escape_string($id_column);
    $id = (int)$id;
    
    $sql = "DELETE FROM $table WHERE $id_column = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows > 0;
    }
    
    $stmt->close();
    return false;
}

/**
 * Count records in a table with optional conditions
 * 
 * @param string $table Table name
 * @param string $where Optional WHERE clause
 * @return int Number of records
 */
function count_records($table, $where = '') {
    global $conn;
    
    $table = $conn->real_escape_string($table);
    $sql = "SELECT COUNT(*) as count FROM $table";
    
    if (!empty($where)) {
        $sql .= " WHERE $where";
    }
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return (int)$row['count'];
    }
    
    return 0;
}

/**
 * Execute a custom SQL query
 * 
 * @param string $sql SQL query
 * @return mysqli_result|bool Query result or false on failure
 */
function execute_query($sql) {
    global $conn;
    return $conn->query($sql);
}

/**
 * Execute a custom SQL query with prepared statement
 * 
 * @param string $sql SQL query with placeholders
 * @param string $types Parameter types (s: string, i: integer, d: double, b: blob)
 * @param array $params Parameters to bind
 * @return array|bool Array of results or false on failure
 */
function execute_prepared_query($sql, $types, $params) {
    return db_prepared_query($sql, $types, $params);
}

/**
 * Begin a database transaction
 * 
 * @return bool True on success, false on failure
 */
function begin_transaction() {
    global $conn;
    return $conn->begin_transaction();
}

/**
 * Commit a database transaction
 * 
 * @return bool True on success, false on failure
 */
function commit_transaction() {
    global $conn;
    return $conn->commit();
}

/**
 * Rollback a database transaction
 * 
 * @return bool True on success, false on failure
 */
function rollback_transaction() {
    global $conn;
    return $conn->rollback();
}

/**
 * Log an action to the audit log
 * 
 * @param string $action Action performed
 * @param string $entity_type Type of entity (e.g., 'product', 'user')
 * @param int $entity_id ID of the entity
 * @param string $details Additional details
 * @return bool True on success, false on failure
 */
function log_action($action, $entity_type, $entity_id = null, $details = '') {
    global $conn;
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $data = [
        'user_id' => $user_id,
        'action' => $action,
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'details' => $details,
        'ip_address' => $ip_address
    ];
    
    return insert_record('audit_log', $data);
}
?>
