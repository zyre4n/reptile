<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'crud.php';

/**
 * Configuration CRUD class for system configuration operations
 */
class ConfigCRUD extends CRUD {
    
    /**
     * Constructor
     * 
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        parent::__construct($conn, 'configurations');
    }
    
    /**
     * Get a configuration value by key
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value or default
     */
    public function getConfig($key, $default = null) {
        $sql = "SELECT config_value, config_type FROM {$this->table} WHERE config_key = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('s', $key);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $value = $row['config_value'];
                
                // Convert value based on type
                switch ($row['config_type']) {
                    case 'number':
                        return is_numeric($value) ? (float)$value : $default;
                    case 'boolean':
                        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    default:
                        return $value;
                }
            }
            
            $stmt->close();
        }
        
        return $default;
    }
    
    /**
     * Set a configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return bool True on success, false on failure
     */
    public function setConfig($key, $value) {
        // Check if config exists
        $sql = "SELECT id FROM {$this->table} WHERE config_key = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('s', $key);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            if ($result->num_rows > 0) {
                // Update existing config
                $row = $result->fetch_assoc();
                $id = $row['id'];
                
                return $this->update($id, ['config_value' => $value]);
            } else {
                // Create new config
                return $this->create([
                    'config_key' => $key,
                    'config_value' => $value,
                    'config_description' => '',
                    'config_type' => 'text'
                ]) ? true : false;
            }
        }
        
        return false;
    }
    
    /**
     * Get all configurations grouped by system and user configs
     * 
     * @return array Configurations grouped by system and user
     */
    public function getAllConfigsGrouped() {
        $sql = "SELECT * FROM {$this->table} ORDER BY is_system, config_key";
        $result = $this->conn->query($sql);
        
        $configs = [
            'system' => [],
            'user' => []
        ];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($row['is_system']) {
                    $configs['system'][] = $row;
                } else {
                    $configs['user'][] = $row;
                }
            }
        }
        
        return $configs;
    }
    
    /**
     * Get all configurations as key-value pairs
     * 
     * @return array Configurations as key-value pairs
     */
    public function getAllConfigsAsKeyValue() {
        $sql = "SELECT config_key, config_value, config_type FROM {$this->table}";
        $result = $this->conn->query($sql);
        
        $configs = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $value = $row['config_value'];
                
                // Convert value based on type
                switch ($row['config_type']) {
                    case 'number':
                        $value = is_numeric($value) ? (float)$value : $value;
                        break;
                    case 'boolean':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        break;
                }
                
                $configs[$row['config_key']] = $value;
            }
        }
        
        return $configs;
    }
    
    /**
     * Parse options for select type configs
     * 
     * @param string $optionsString Comma-separated options string
     * @return array Options as array
     */
    public function parseOptions($optionsString) {
        if (empty($optionsString)) {
            return [];
        }
        
        return explode(',', $optionsString);
    }
}
?>
