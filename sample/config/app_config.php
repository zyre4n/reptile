<?php
require_once 'database.php';
require_once __DIR__ . '/../crud/config_crud.php';

// Define the application name
$app_name = 'Inventory Management System'; // Replace with your app name

// Cache for configuration values
$config_cache = null;

/**
 * Get a configuration value
 * 
 * @param string $key Configuration key
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value or default
 */
function get_config($key, $default = null) {
    global $conn;

    // Check if the cache is initialized
    global $config_cache;
    if ($config_cache !== null && isset($config_cache[$key])) {
        return $config_cache[$key];
    }

    // Fetch from database if not in cache
    $sql = "SELECT config_value FROM configurations WHERE config_key = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return $default;
    }
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['config_value'];
    }
    return $default;
}

// Initialize cache if not already done
if ($config_cache === null) {
    $configCrud = new ConfigCRUD($conn);
    $config_cache = $configCrud->getAllConfigsAsKeyValue();
}

/**
 * Get the store name
 * 
 * @return string Store name
 */
function get_store_name() {
    return get_config('store_name', 'MAYA RETAIL STORE');
}

/**
 * Get the low stock threshold
 * 
 * @return int Low stock threshold
 */
function get_low_stock_threshold() {
    return (int)get_config('low_stock_threshold', 10);
}
?>