<?php
include_once 'includes/config.php';
include_once 'includes/header.php';
include_once 'includes/sidebar.php';
?>

<!-- Main Content -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center page-header">
                    <h1 class="h3 mb-0">Configuration</h1>
                    <div class="d-flex align-items-center">
                        <button id="saveConfigBtn" class="btn btn-success">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Configuration Tabs -->
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="configTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="store-tab" data-bs-toggle="tab" data-bs-target="#store" type="button" role="tab" aria-controls="store" aria-selected="true">Store Information</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab" aria-controls="system" aria-selected="false">System Settings</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="receipt-tab" data-bs-toggle="tab" data-bs-target="#receipt" type="button" role="tab" aria-controls="receipt" aria-selected="false">Receipt Template</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab" aria-controls="backup" aria-selected="false">Backup & Restore</button>
                    </li>
                </ul>
                
                <div class="tab-content p-3" id="configTabContent">
                    <!-- Store Information Tab -->
                    <div class="tab-pane fade show active" id="store" role="tabpanel" aria-labelledby="store-tab">
                        <form id="storeInfoForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="storeName" class="form-label">Store Name</label>
                                    <input type="text" class="form-control" id="storeName" name="storeName" value="<?php echo $app_name; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="storeTagline" class="form-label">Tagline/Description</label>
                                    <input type="text" class="form-control" id="storeTagline" name="storeTagline" value="<?php echo $app_description; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="storeAddress" class="form-label">Address</label>
                                <textarea class="form-control" id="storeAddress" name="storeAddress" rows="2"><?php echo $store_address; ?></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="storeContact" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="storeContact" name="storeContact" value="<?php echo $store_contact; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="storeEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="storeEmail" name="storeEmail" value="<?php echo $store_email; ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="taxRate" class="form-label">Tax Rate (%)</label>
                                    <input type="number" class="form-control" id="taxRate" name="taxRate" value="12" min="0" max="100" step="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label for="currencySymbol" class="form-label">Currency Symbol</label>
                                    <input type="text" class="form-control" id="currencySymbol" name="currencySymbol" value="₱">
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- System Settings Tab -->
                    <div class="tab-pane fade" id="system" role="tabpanel" aria-labelledby="system-tab">
                        <form id="systemSettingsForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="lowStockAlert" class="form-label">Low Stock Alert Threshold</label>
                                    <input type="number" class="form-control" id="lowStockAlert" name="lowStockAlert" value="10" min="1">
                                    <small class="form-text text-muted">Set the quantity threshold for low stock alerts</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-select" id="timezone" name="timezone">
                                        <option value="Asia/Manila" selected>Asia/Manila (Philippines)</option>
                                        <option value="Asia/Singapore">Asia/Singapore</option>
                                        <option value="Asia/Hong_Kong">Asia/Hong_Kong</option>
                                        <option value="Asia/Tokyo">Asia/Tokyo</option>
                                        <option value="Australia/Sydney">Australia/Sydney</option>
                                        <option value="America/Los_Angeles">America/Los_Angeles</option>
                                        <option value="America/New_York">America/New_York</option>
                                        <option value="Europe/London">Europe/London</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="dateFormat" class="form-label">Date Format</label>
                                    <select class="form-select" id="dateFormat" name="dateFormat">
                                        <option value="Y-m-d" selected>YYYY-MM-DD</option>
                                        <option value="m/d/Y">MM/DD/YYYY</option>
                                        <option value="d/m/Y">DD/MM/YYYY</option>
                                        <option value="M d, Y">Month DD, YYYY</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="timeFormat" class="form-label">Time Format</label>
                                    <select class="form-select" id="timeFormat" name="timeFormat">
                                        <option value="H:i:s" selected>24-hour (HH:MM:SS)</option>
                                        <option value="h:i:s A">12-hour (HH:MM:SS AM/PM)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enableEmailAlerts" checked>
                                    <label class="form-check-label" for="enableEmailAlerts">Enable Email Alerts</label>
                                </div>
                                <small class="form-text text-muted">Receive email notifications for low stock and other important alerts</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enableActivityLog" checked>
                                    <label class="form-check-label" for="enableActivityLog">Enable Activity Logging</label>
                                </div>
                                <small class="form-text text-muted">Log all user activities and system changes</small>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Receipt Template Tab -->
                    <div class="tab-pane fade" id="receipt" role="tabpanel" aria-labelledby="receipt-tab">
                        <form id="receiptTemplateForm">
                            <div class="mb-3">
                                <label for="receiptHeader" class="form-label">Receipt Header</label>
                                <textarea class="form-control" id="receiptHeader" name="receiptHeader" rows="3"><?php echo $app_name; ?>
<?php echo $app_description; ?>
<?php echo $store_address; ?>
Tel: <?php echo $store_contact; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="receiptFooter" class="form-label">Receipt Footer</label>
                                <textarea class="form-control" id="receiptFooter" name="receiptFooter" rows="3">Thank you for shopping!
Please come again.
Follow us on Facebook: facebook.com/sarisaristore</textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="receiptWidth" class="form-label">Receipt Width (characters)</label>
                                    <input type="number" class="form-control" id="receiptWidth" name="receiptWidth" value="40" min="30" max="80">
                                </div>
                                <div class="col-md-6">
                                    <label for="receiptFontSize" class="form-label">Font Size</label>
                                    <select class="form-select" id="receiptFontSize" name="receiptFontSize">
                                        <option value="small">Small</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="large">Large</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="showLogo" checked>
                                    <label class="form-check-label" for="showLogo">Display Logo on Receipt</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="showBarcode" checked>
                                    <label class="form-check-label" for="showBarcode">Display Barcode/QR Code</label>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h6>Receipt Preview</h6>
                                <div class="border p-3 bg-light" style="font-family: monospace; white-space: pre-wrap; font-size: 0.9rem;">
****************************************
            <?php echo $app_name; ?>
        <?php echo $app_description; ?>
  <?php echo $store_address; ?>
      Tel: <?php echo $store_contact; ?>
****************************************
Receipt No: TRX-123
Date: <?php echo date('Y-m-d H:i:s'); ?>
Cashier: Admin

----------------------------------------
Item             Qty     Price    Total
----------------------------------------
Canned Sardines   2     ₱22.50   ₱45.00
Instant Noodles   3     ₱15.00   ₱45.00
Rice (1kg)        1     ₱50.00   ₱50.00
----------------------------------------
                        Subtotal: ₱140.00
                             Tax: ₱16.80
                           Total: ₱156.80
                          
Paid (Cash):                  ₱200.00
Change:                        ₱43.20
----------------------------------------
      Thank you for shopping!
         Please come again.
Follow us on Facebook: facebook.com/sarisaristore
****************************************
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="bi bi-printer me-1"></i> Test Print
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Preview
                </button>
            </div>
                        </form>
                    </div>
                    
                    <!-- Backup & Restore Tab -->
                    <div class="tab-pane fade" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Backup Database</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Create a backup of your current database. This will download a SQL file containing all your data.</p>
                                        <button type="button" class="btn btn-primary mt-2">
                                            <i class="bi bi-download me-1"></i> Create Backup
                                        </button>
                                        
                                        <div class="mt-4">
                                            <h6>Scheduled Backups</h6>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="enableAutoBackup" checked>
                                                <label class="form-check-label" for="enableAutoBackup">Enable Automatic Backups</label>
                                            </div>
                                            <select class="form-select mb-3">
                                                <option value="daily">Daily</option>
                                                <option value="weekly" selected>Weekly</option>
                                                <option value="monthly">Monthly</option>
                                            </select>
                                            <p class="text-muted small">Next scheduled backup: <?php echo date('Y-m-d', strtotime('+7 days')); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Restore Database</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-danger">Warning: Restoring a database will overwrite all current data. Make sure to back up your existing data first.</p>
                                        
                                        <div class="mb-3">
                                            <label for="restoreFile" class="form-label">Select Backup File</label>
                                            <input class="form-control" type="file" id="restoreFile">
                                        </div>
                                        
                                        <button type="button" class="btn btn-warning mt-2">
                                            <i class="bi bi-upload me-1"></i> Restore from Backup
                                        </button>
                                        
                                        <div class="mt-4">
                                            <h6>Recent Backups</h6>
                                            <ul class="list-group">
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    backup_2023-10-14.sql
                                                    <span>
                                                        <button class="btn btn-sm btn-outline-primary me-1">
                                                            <i class="bi bi-download"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    backup_2023-10-07.sql
                                                    <span>
                                                        <button class="btn btn-sm btn-outline-primary me-1">
                                                            <i class="bi bi-download"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Save Changes Confirmation Modal -->
<div class="modal fade" id="saveChangesModal" tabindex="-1" aria-labelledby="saveChangesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveChangesModalLabel">Confirm Changes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to save these configuration changes?</p>
                <p class="text-muted">The system may need to restart for some changes to take effect.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
