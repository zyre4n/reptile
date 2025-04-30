<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
include_once 'config/database.php';
include_once 'config/app_config.php';
require_once 'crud/config_crud.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Set page title
$page_title = "System Configuration";

// Initialize ConfigCRUD
$configCrud = new ConfigCRUD($conn);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $success = true;
    $error_message = '';
    
    // Get all user configurations
    $userConfigs = $configCrud->readAll('is_system = 0', [], 'config_key');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        foreach ($userConfigs as $config) {
            $key = $config['config_key'];
            
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                
                // For boolean values from checkboxes
                if ($config['config_type'] == 'boolean') {
                    $value = isset($_POST[$key]) ? 'true' : 'false';
                }
                
                if (!$configCrud->setConfig($key, $value)) {
                    throw new Exception("Failed to update configuration: $key");
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        $success_message = "Configuration updated successfully";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $success = false;
        $error_message = $e->getMessage();
    }
}

// Get all configurations
$configs = $configCrud->getAllConfigsGrouped();
?>

<?php include 'includes/header.php'; ?>

<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center page-header">
                        <h1 class="h3 mb-0">System Configuration</h1>
                    </div>
                </div>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message) && !empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <ul class="nav nav-tabs mb-4" id="configTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="store-tab" data-bs-toggle="tab" data-bs-target="#store" type="button" role="tab" aria-controls="store" aria-selected="true">
                                            <i class="bi bi-shop me-2"></i>Store Information
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab" aria-controls="inventory" aria-selected="false">
                                            <i class="bi bi-box-seam me-2"></i>Inventory Settings
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab" aria-controls="system" aria-selected="false">
                                            <i class="bi bi-gear me-2"></i>System Preferences
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="configTabContent">
                                    <!-- Store Information Tab -->
                                    <div class="tab-pane fade show active" id="store" role="tabpanel" aria-labelledby="store-tab">
                                        <div class="row">
                                            <?php foreach ($configs['user'] as $config): ?>
                                                <?php if (in_array($config['config_key'], ['store_name', 'store_address', 'store_contact', 'store_email'])): ?>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="<?php echo $config['config_key']; ?>" class="form-label">
                                                            <?php echo ucwords(str_replace('_', ' ', $config['config_key'])); ?>
                                                        </label>
                                                        
                                                        <?php if ($config['config_type'] == 'textarea'): ?>
                                                            <textarea class="form-control" id="<?php echo $config['config_key']; ?>" name="<?php echo $config['config_key']; ?>" rows="3"><?php echo $config['config_value']; ?></textarea>
                                                        <?php elseif ($config['config_type'] == 'boolean'): ?>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" id="<?php echo $config['config_key']; ?>" name="<?php echo $config['config_key']; ?>" <?php echo $config['config_value'] == 'true' ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="<?php echo $config['config_key']; ?>">Enabled</label>
                                                            </div>
                                                        <?php elseif ($config['config_type'] == 'select'): ?>
                                                            <?php 
                                                            // Find options for this select
                                                            $optionsKey = $config['config_key'] . '_options';
                                                            $optionsConfig = null;
                                                            
                                                            foreach ($configs['system'] as $sysConfig) {
                                                                if ($sysConfig['config_key'] == $optionsKey) {
                                                                    $optionsConfig = $sysConfig;
                                                                    break;
                                                                }
                                                            }
                                                            
                                                            $options = $optionsConfig ? $configCrud->parseOptions($optionsConfig['config_value']) : [];
                                                            ?>
                                                            
                                                            <select class="form-select" id="<?php echo $config['config_key']; ?>" name="<?php echo $config['config_key']; ?>">
                                                                <?php foreach ($options as $option): ?>
                                                                    <option value="<?php echo $option; ?>" <?php echo $config['config_value'] == $option ? 'selected' : ''; ?>>
                                                                        <?php echo ucfirst($option); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        <?php else: ?>
                                                            <input type="<?php echo $config['config_type'] == 'number' ? 'number' : 'text'; ?>" 
                                                                class="form-control" 
                                                                id="<?php echo $config['config_key']; ?>" 
                                                                name="<?php echo $config['config_key']; ?>" 
                                                                value="<?php echo $config['config_value']; ?>"
                                                                <?php echo $config['config_type'] == 'number' ? 'step="0.01"' : ''; ?>>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($config['config_description'])): ?>
                                                            <div class="form-text"><?php echo $config['config_description']; ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Inventory Settings Tab -->
                                    <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                                        <div class="row">
                                            <?php foreach ($configs['user'] as $config): ?>
                                                <?php if (in_array($config['config_key'], ['low_stock_threshold', 'inventory_count_schedule'])): ?>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="<?php echo $config['config_key']; ?>" class="form-label">
                                                            <?php echo ucwords(str_replace('_', ' ', $config['config_key'])); ?>
                                                        </label>
                                                        
                                                        <?php if ($config['config_type'] == 'textarea'): ?>
                                                            <textarea class="form-control" id="<?php echo $config['config_key']; ?>" name="<?php echo $config['config_key']; ?>" rows="3"><?php echo $config['config_value']; ?></textarea>
                                                        <?php elseif ($config['config_type'] == 'boolean'): ?>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" id="<?php echo $config['config_key']; ?>" name="<?php echo $config['config_key']; ?>" <?php echo $config['config_value'] == 'true' ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="<?php echo $config['config_key']; ?>">Enabled</label>
                                                            </div>
                                                        <?php elseif ($config['config_type'] == 'select'): ?>
                                                            <?php 
                                                            // Find options for this select
                                                            $optionsKey = $config['config_key'] . '_options';
                                                            $optionsConfig = null;
                                                            
                                                            foreach ($configs['system'] as $sysConfig) {
                                                                if ($sysConfig['config_key'] == $optionsKey) {
                                                                    $optionsConfig = $sysConfig;
                                                                    break;
                                                                }
                                                            }
                                                            
                                                            $options = $optionsConfig ? $configCrud->parseOptions($optionsConfig['config_value']) : [];
                                                            ?>
                                                            
                                                            <select class="form-select" id="<?php echo $config['config_key']; ?>" name="<?php echo $config['config_key']; ?>">
                                                                <?php foreach ($options as $option): ?>
                                                                    <option value="<?php echo $option; ?>" <?php echo $config['config_value'] == $option ? 'selected' : ''; ?>>
                                                                        <?php echo ucfirst($option); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        <?php else: ?>
                                                            <input type="<?php echo $config['config_type'] == 'number' ? 'number' : 'text'; ?>" 
                                                                class="form-control" 
                                                                id="<?php echo $config['config_key']; ?>" 
                                                                name="<?php echo $config['config_key']; ?>" 
                                                                value="<?php echo $config['config_value']; ?>"
                                                                <?php echo $config['config_type'] == 'number' ? 'step="0.01"' : ''; ?>>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($config['config_description'])): ?>
                                                            <div class="form-text"><?php echo $config['config_description']; ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- System Preferences Tab -->
                                    <div class="tab-pane fade" id="system" role="tabpanel" aria-labelledby="system-tab">
                                        <div class="row">
                                            <?php foreach ($configs['user'] as $config): ?>
                                                <?php if (in_array($config['config_key'], ['currency_symbol', 'enable_email_notifications', 'default_tax_rate', 'receipt_footer_text'])): ?>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="<?php echo $config['config_key']; ?>" class="form-label">
                                                            <?php echo ucwords(str_replace('_', ' ', $config['config_key'])); ?>
                                                        </label>
                                                        
                                                        <?php if ($config['config_type'] == 'textarea'): ?>
                                                            <textarea class="form-control" id="<?php echo $config['config_key']; ?>" name="<?php echo $config['config_key']; ?>" rows="3"><?php echo $config['config_value']; ?></textarea>
                                                        <?php elseif ($config['config_type'] == 'boolean'): ?>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" id="<?php echo $config['config_key']; ?>" name="<?php echo $config['config_key']; ?>" <?php echo $config['config_value'] == 'true' ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="<?php echo $config['config_key']; ?>">Enabled</label>
                                                            </div>
                                                        <?php elseif ($config['config_type'] == 'select'): ?>
                                                            <?php 
                                                            // Find options for this select
                                                            $optionsKey = $config['config_key'] . '_options';
                                                            $optionsConfig = null;
                                                            
                                                            foreach ($configs['system'] as $sysConfig) {
                                                                if ($sysConfig['config_key'] == $optionsKey) {
                                                                    $optionsConfig = $sysConfig;
                                                                    break;
                                                                }
                                                            }
                                                            
                                                            $options = $optionsConfig ? $configCrud->parseOptions($optionsConfig['config_value']) : [];
                                                            ?>
                                                            
                                                            <select class="form-select" id="<?php echo $config['config_key']; ?>" name="<?php echo $config['config_key']; ?>">
                                                                <?php foreach ($options as $option): ?>
                                                                    <option value="<?php echo $option; ?>" <?php echo $config['config_value'] == $option ? 'selected' : ''; ?>>
                                                                        <?php echo ucfirst($option); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        <?php else: ?>
                                                            <input type="<?php echo $config['config_type'] == 'number' ? 'number' : 'text'; ?>" 
                                                                class="form-control" 
                                                                id="<?php echo $config['config_key']; ?>" 
                                                                name="<?php echo $config['config_key']; ?>" 
                                                                value="<?php echo $config['config_value']; ?>"
                                                                <?php echo $config['config_type'] == 'number' ? 'step="0.01"' : ''; ?>>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($config['config_description'])): ?>
                                                            <div class="form-text"><?php echo $config['config_description']; ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-4">
                                    <a href="../dashboard.php" class="btn btn-secondary me-2">
                                        <i class="bi bi-x-circle me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Save Configuration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
