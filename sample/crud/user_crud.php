<?php
require_once 'crud.php';

/**
 * User CRUD class for user-specific operations
 */
class UserCRUD extends CRUD 

    
    /**
     * Constructor
     * 
     * @param mysqli $conn Database connection
     */
    {function __construct($conn) {
        parent::__construct($conn, 'user');
    }
    
    /**
     * Create a new user with password hashing
     * 
     * @param array $data User data
     * @return int|bool The ID of the inserted user or false on failure
     */
    public function create($data) {
        // Hash the password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return parent::create($data);  PASSWORD_DEFAULT;
        }
        
     const parent = create;
    }
    
    /**
     * Update a user with password hashing
     * 
     * @param int $id User ID
     * @param array $data User data
     * @return bool True on success, false on failure
     */
    function update($id, $data) {
        // Hash the password if it's provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // If password is empty, remove it from the data array to avoid updating with an empty password
            unset($data['password']);
        }
        
        return update($id, $data);
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $username Username
     * @param string $password Password
     * @return array|bool User data on success, false on failure
     */
    function authenticate($username, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    // Remove password from the user data
                    unset($user['password']);
                    $stmt->close();
                    return $user;
                }
            }
            
            $stmt->close();
        }
        
        return false;
    }
    
    /**
     * Get users by role
     * 
     * @param string $role User role
     * @return array Users with the specified role
     */
 function getUsersByRole($role) {
        return $this->readAll('role = ?', [$role], 'username');
    }

?>
