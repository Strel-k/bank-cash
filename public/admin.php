<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent header issues
ob_start();

// Load required files
require_once '../app/helpers/SessionHelper.php';
require_once '../app/helpers/Response.php';

// Debug point 1
error_log("Admin.php - Script start");
error_log("Admin.php - Session status before config: " . session_status());

// Configure session
SessionHelper::configureSession();

// Debug point 2
error_log("Admin.php - After session config");
error_log("Admin.php - Session ID: " . session_id());
error_log("Admin.php - Session status: " . session_status());
error_log("Admin.php - Raw session data: " . print_r($_SESSION, true));

// Validate session and admin status
$userId = $_SESSION['user_id'] ?? null;
$isAdmin = isset($_SESSION['is_admin']) ? (bool)$_SESSION['is_admin'] : false;
$fullName = $_SESSION['full_name'] ?? 'Unknown';

// Debug point 3
error_log("Admin.php - Authorization check:");
error_log("Admin.php - User ID: " . ($userId ?? 'not set'));
error_log("Admin.php - Is Admin: " . ($isAdmin ? 'true' : 'false'));
error_log("Admin.php - Full Name: " . $fullName);

// Strict authentication check
if (!$userId || !$isAdmin) {
    error_log("Admin.php - Access denied - Invalid credentials");
    error_log("Admin.php - Headers sent status: " . (headers_sent() ? 'Yes' : 'No'));
    
    // Clean up any output
    ob_clean();
    
    // Destroy session for security
    session_destroy();
    
    // Clear all cookies
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time()-3600, '/');
        }
    }
    
    // Redirect to login
    if (!headers_sent()) {
        header('Location: login.php');
        exit;
    } else {
        echo '<script>window.location.href = "login.php";</script>';
        exit;
    }
    error_log("Admin.php - Headers sent: " . (headers_sent() ? 'Yes' : 'No'));
    if (headers_sent($file, $line)) {
        error_log("Headers were sent in $file on line $line");
    }
    header('Location: login.php');
    exit;
}

error_log("Admin.php - Access granted - User: $userId, Name: " . ($_SESSION['full_name'] ?? 'unknown'));

// Database configuration
class Config {
    const DB_HOST = 'localhost';
    const DB_NAME = 'b_cash_ajax';
    const DB_USER = 'root';
    const DB_PASS = '';
}

class Database {
    private $connection;

    public function connect() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME,
                Config::DB_USER,
                Config::DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            return $this->connection;
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}

// Admin class for CRUD operations
class AdminPanel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // User CRUD operations
    public function getUsers($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("
            SELECT u.*, w.balance, w.account_number
            FROM users u
            LEFT JOIN wallets w ON u.id = w.user_id
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getUserCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        return $stmt->fetch()['count'];
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, w.balance, w.account_number
            FROM users u
            LEFT JOIN wallets w ON u.id = w.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createUser($data) {
        try {
            $this->db->beginTransaction();

            // Hash password
            $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (phone_number, email, full_name, password_hash, birthdate, address, gender, is_verified)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['phone_number'],
                $data['email'],
                $data['full_name'],
                $password_hash,
                $data['birthdate'] ?: null,
                $data['address'] ?: null,
                $data['gender'] ?: null,
                isset($data['is_verified']) ? 1 : 0
            ]);

            $user_id = $this->db->lastInsertId();

            // Create wallet
            $account_number = 'BC' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("
                INSERT INTO wallets (user_id, account_number, balance)
                VALUES (?, ?, 0.00)
            ");
            $stmt->execute([$user_id, $account_number]);

            $this->db->commit();
            return ['success' => true, 'message' => 'User created successfully'];
        } catch(PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error creating user: ' . $e->getMessage()];
        }
    }

    public function updateUser($id, $data) {
        try {
            $fields = [];
            $values = [];

            if (!empty($data['full_name'])) {
                $fields[] = "full_name = ?";
                $values[] = $data['full_name'];
            }
            if (!empty($data['email'])) {
                $fields[] = "email = ?";
                $values[] = $data['email'];
            }
            if (!empty($data['phone_number'])) {
                $fields[] = "phone_number = ?";
                $values[] = $data['phone_number'];
            }
            if (isset($data['birthdate'])) {
                $fields[] = "birthdate = ?";
                $values[] = $data['birthdate'] ?: null;
            }
            if (isset($data['address'])) {
                $fields[] = "address = ?";
                $values[] = $data['address'] ?: null;
            }
            if (isset($data['gender'])) {
                $fields[] = "gender = ?";
                $values[] = $data['gender'] ?: null;
            }
            if (isset($data['is_verified'])) {
                $fields[] = "is_verified = ?";
                $values[] = $data['is_verified'] ? 1 : 0;
            }

            if (empty($fields)) {
                return ['success' => false, 'message' => 'No data to update'];
            }

            $values[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            return ['success' => true, 'message' => 'User updated successfully'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Error updating user: ' . $e->getMessage()];
        }
    }

    public function deleteUser($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()];
        }
    }

    // Transaction CRUD operations
    public function getTransactions($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("
            SELECT
                t.*,
                sender.account_number as sender_account,
                receiver.account_number as receiver_account,
                sender_user.full_name as sender_name,
                receiver_user.full_name as receiver_name
            FROM transactions t
            JOIN wallets sender ON t.sender_wallet_id = sender.id
            JOIN wallets receiver ON t.receiver_wallet_id = receiver.id
            JOIN users sender_user ON sender.user_id = sender_user.id
            JOIN users receiver_user ON receiver.user_id = receiver_user.id
            ORDER BY t.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getTransactionCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM transactions");
        return $stmt->fetch()['count'];
    }

    public function getTransactionById($id) {
        $stmt = $this->db->prepare("
            SELECT
                t.*,
                sender.account_number as sender_account,
                receiver.account_number as receiver_account,
                sender_user.full_name as sender_name,
                receiver_user.full_name as receiver_name
            FROM transactions t
            JOIN wallets sender ON t.sender_wallet_id = sender.id
            JOIN wallets receiver ON t.receiver_wallet_id = receiver.id
            JOIN users sender_user ON sender.user_id = sender_user.id
            JOIN users receiver_user ON receiver.user_id = receiver_user.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateTransaction($id, $data) {
        try {
            $fields = [];
            $values = [];

            if (isset($data['status'])) {
                $fields[] = "status = ?";
                $values[] = $data['status'];
            }
            if (!empty($data['description'])) {
                $fields[] = "description = ?";
                $values[] = $data['description'];
            }

            if (empty($fields)) {
                return ['success' => false, 'message' => 'No data to update'];
            }

            $values[] = $id;
            $sql = "UPDATE transactions SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            return ['success' => true, 'message' => 'Transaction updated successfully'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Error updating transaction: ' . $e->getMessage()];
        }
    }

    public function deleteTransaction($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Transaction deleted successfully'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting transaction: ' . $e->getMessage()];
        }
    }

    // Dashboard stats
    public function getDashboardStats() {
        $stats = [];

        // User stats
        $stmt = $this->db->query("SELECT COUNT(*) as total_users FROM users");
        $stats['total_users'] = $stmt->fetch()['total_users'];

        $stmt = $this->db->query("SELECT COUNT(*) as verified_users FROM users WHERE is_verified = 1");
        $stats['verified_users'] = $stmt->fetch()['verified_users'];

        // Transaction stats
        $stmt = $this->db->query("SELECT COUNT(*) as total_transactions FROM transactions");
        $stats['total_transactions'] = $stmt->fetch()['total_transactions'];

        $stmt = $this->db->query("SELECT SUM(amount) as total_amount FROM transactions WHERE status = 'completed'");
        $stats['total_transaction_amount'] = $stmt->fetch()['total_amount'] ?: 0;

        // Wallet stats
        $stmt = $this->db->query("SELECT SUM(balance) as total_balance FROM wallets");
        $stats['total_balance'] = $stmt->fetch()['total_balance'] ?: 0;

        return $stats;
    }
}

// Initialize admin panel
$admin = new AdminPanel();

// Handle actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_user':
                $result = $admin->createUser($_POST);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;

            case 'update_user':
                $result = $admin->updateUser($_POST['id'], $_POST);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;

            case 'delete_user':
                $result = $admin->deleteUser($_POST['id']);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;

            case 'update_transaction':
                $result = $admin->updateTransaction($_POST['id'], $_POST);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;

            case 'delete_transaction':
                $result = $admin->deleteTransaction($_POST['id']);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;
        }
    }
}

// Get current page and section
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : null;

// Get data based on section
$users = [];
$transactions = [];
$user_data = null;
$transaction_data = null;
$stats = [];

if ($section === 'users') {
    $users = $admin->getUsers($page);
    if ($edit_id) {
        $user_data = $admin->getUserById($edit_id);
    }
} elseif ($section === 'transactions') {
    $transactions = $admin->getTransactions($page);
    if ($edit_id) {
        $transaction_data = $admin->getTransactionById($edit_id);
    }
} elseif ($section === 'dashboard') {
    $stats = $admin->getDashboardStats();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - B-Cash</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        .header { background: #343a40; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; }
        .logout-btn { background: #dc3545; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; }
        .logout-btn:hover { background: #c82333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .nav { background: white; padding: 1rem; margin-bottom: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav-tabs { display: flex; list-style: none; }
        .nav-tabs li { margin-right: 1rem; }
        .nav-tabs a { padding: 0.5rem 1rem; text-decoration: none; color: #007bff; border-radius: 4px; }
        .nav-tabs a.active { background: #007bff; color: white; }
        .nav-tabs a:hover { background: #e9ecef; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .card-header { padding: 1rem; border-bottom: 1px solid #dee2e6; background: #f8f9fa; }
        .card-body { padding: 1rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { color: #007bff; margin-bottom: 0.5rem; }
        .stat-card .number { font-size: 2rem; font-weight: bold; color: #343a40; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px; }
        .form-row { display: flex; gap: 1rem; }
        .form-row .form-group { flex: 1; }
        .pagination { display: flex; justify-content: center; margin-top: 2rem; }
        .pagination a, .pagination span { padding: 0.5rem 0.75rem; margin: 0 0.25rem; border: 1px solid #dee2e6; text-decoration: none; border-radius: 4px; }
        .pagination a:hover, .pagination .current { background: #007bff; color: white; border-color: #007bff; }
        .message { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .actions { display: flex; gap: 0.5rem; }
        @media (max-width: 768px) {
            .form-row { flex-direction: column; }
            .nav-tabs { flex-direction: column; }
            .nav-tabs li { margin-bottom: 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>B-Cash Admin Panel</h1>
        <a href="?logout=1" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <nav class="nav">
            <ul class="nav-tabs">
                <li><a href="?section=dashboard" class="<?php echo $section === 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="?section=users" class="<?php echo $section === 'users' ? 'active' : ''; ?>">Users</a></li>
                <li><a href="?section=transactions" class="<?php echo $section === 'transactions' ? 'active' : ''; ?>">Transactions</a></li>
            </ul>
        </nav>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($section === 'dashboard'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="number"><?php echo number_format($stats['total_users'] ?? 0); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Verified Users</h3>
                    <div class="number"><?php echo number_format($stats['verified_users'] ?? 0); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Transactions</h3>
                    <div class="number"><?php echo number_format($stats['total_transactions'] ?? 0); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Balance</h3>
                    <div class="number">$<?php echo number_format($stats['total_balance'] ?? 0, 2); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($section === 'users'): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Users Management</h2>
                </div>
                <div class="card-body">
                    <?php if ($edit_id && $user_data): ?>
                        <h3>Edit User</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_user">
                            <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name">Full Name:</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email:</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone_number">Phone Number:</label>
                                    <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user_data['phone_number'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="birthdate">Birthdate:</label>
                                    <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user_data['birthdate'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="gender">Gender:</label>
                                    <select id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male" <?php echo ($user_data['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo ($user_data['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo ($user_data['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="is_verified">Verified:</label>
                                    <input type="checkbox" id="is_verified" name="is_verified" value="1" <?php echo ($user_data['is_verified'] ?? 0) ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="address">Address:</label>
                                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="actions">
                                <button type="submit" class="btn btn-primary">Update User</button>
                                <a href="?section=users" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div style="margin-bottom: 1rem;">
                            <a href="?section=users&action=create" class="btn btn-success">Add New User</a>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Account</th>
                                    <th>Balance</th>
                                    <th>Verified</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                                        <td><?php echo htmlspecialchars($user['account_number'] ?? 'N/A'); ?></td>
                                        <td>$<?php echo number_format($user['balance'] ?? 0, 2); ?></td>
                                        <td><?php echo ($user['is_verified'] ?? 0) ? 'Yes' : 'No'; ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                        <td class="actions">
                                            <a href="?section=users&edit=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- Pagination -->
                        <?php
                        $total_users = $admin->getUserCount();
                        $total_pages = ceil($total_users / 20);
                        if ($total_pages > 1):
                        ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?section=users&page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'current' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($section === 'transactions'): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Transactions Management</h2>
                </div>
                <div class="card-body">
                    <?php if ($edit_id && $transaction_data): ?>
                        <h3>Edit Transaction</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_transaction">
                            <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select id="status" name="status" required>
                                        <option value="pending" <?php echo ($transaction_data['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo ($transaction_data['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="failed" <?php echo ($transaction_data['status'] ?? '') === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($transaction_data['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="actions">
                                <button type="submit" class="btn btn-primary">Update Transaction</button>
                                <a href="?section=transactions" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Reference</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Sender</th>
                                    <th>Receiver</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo $transaction['id']; ?></td>
                                        <td><?php echo htmlspecialchars($transaction['reference_number']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['transaction_type']); ?></td>
                                        <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['sender_name']); ?> (<?php echo htmlspecialchars($transaction['sender_account']); ?>)</td>
                                        <td><?php echo htmlspecialchars($transaction['receiver_name']); ?> (<?php echo htmlspecialchars($transaction['receiver_account']); ?>)</td>
                                        <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
                                        <td class="actions">
                                            <a href="?section=transactions&edit=<?php echo $transaction['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this transaction?')">
                                                <input type="hidden" name="action" value="delete_transaction">
                                                <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- Pagination -->
                        <?php
                        $total_transactions = $admin->getTransactionCount();
                        $total_pages = ceil($total_transactions / 20);
                        if ($total_pages > 1):
                        ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?section=transactions&page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'current' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
