<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';
include_once 'config/database.php';

// Fetch users from the database
function getUsers($conn) {
    $sql = "SELECT id, username, fullname, email, role, status, last_login FROM users ORDER BY username";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Handle adding a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addUser'])) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $fullname = $conn->real_escape_string(trim($_POST['fullname']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = $conn->real_escape_string($_POST['role']);
    $status = $conn->real_escape_string($_POST['status']);

    if (!empty($username) && !empty($fullname) && !empty($email) && !empty($password)) {
        $sql = "INSERT INTO users (username, fullname, email, password, role, status) 
                VALUES ('$username', '$fullname', '$email', '$password', '$role', '$status')";
        if ($conn->query($sql)) {
            header("Location: users.php");
            exit();
        } else {
            $error_message = "Error adding user: " . $conn->error;
        }
    } else {
        $error_message = "All fields are required.";
    }
}

// Fetch users
$users = getUsers($conn);
?>

<!-- Main Content -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center page-header">
                    <h1 class="h3 mb-0">Users</h1>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-person-plus me-1"></i> Add User
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Users Card -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">User List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $index => $user): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['role'] == 'Administrator'): ?>
                                                <span class="badge bg-danger"><?php echo $user['role']; ?></span>
                                            <?php elseif ($user['role'] == 'Manager'): ?>
                                                <span class="badge bg-warning"><?php echo $user['role']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-info"><?php echo $user['role']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['status'] == 'Active'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $user['last_login']; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">
                                                    <i class="bi bi-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="users.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="Administrator">Administrator</option>
                            <option value="Manager">Manager</option>
                            <option value="Cashier">Cashier</option>
                            <option value="Inventory Staff">Inventory Staff</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="addUser">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>