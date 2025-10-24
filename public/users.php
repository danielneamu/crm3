<?php
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $fullName = trim($_POST['full_name']);
    $password = $_POST['password'];

    if ($username && $email && $fullName && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$username, $email, $hash, $fullName]);
            $message = "User created successfully";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $userId = $_POST['user_id'];
    $newPassword = $_POST['new_password'];

    if ($userId && $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id_user = ?");
            $stmt->execute([$hash, $userId]);
            $message = "Password changed successfully";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Handle Toggle Active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_active') {
    $userId = $_POST['user_id'];
    $isActive = $_POST['is_active'] == 1 ? 0 : 1;

    try {
        $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id_user = ?");
        $stmt->execute([$isActive, $userId]);
        $message = "User status updated";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get all users
$stmt = $conn->query("SELECT id_user, username, email, full_name, is_active, created_at, last_login FROM users ORDER BY id_user");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../includes/navbar.php'; ?>


    <div class="container-fluid mt-4">
        <div class="row mb-3">
            <div class="col">
                <h2>User Management</h2>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle"></i> Add User
                </button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id_user'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td><?= $user['last_login'] ?? 'Never' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="changePassword(<?= $user['id_user'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="user_id" value="<?= $user['id_user'] ?>">
                                        <input type="hidden" name="is_active" value="<?= $user['is_active'] ?>">
                                        <button type="submit" class="btn btn-sm btn-<?= $user['is_active'] ? 'secondary' : 'success' ?>">
                                            <i class="bi bi-<?= $user['is_active'] ? 'x-circle' : 'check-circle' ?>"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Password for <span id="pwUsername"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="user_id" id="pwUserId">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changePassword(userId, username) {
            document.getElementById('pwUserId').value = userId;
            document.getElementById('pwUsername').textContent = username;
            new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
        }
    </script>
</body>

</html>