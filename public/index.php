<?php
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';

if (isLoggedIn()) {
    header('Location: projects.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        try {
            $db = new Database();
            $stmt = $db->getConnection()->prepare(
                "SELECT id_user, username, password_hash, full_name, is_active 
                 FROM users WHERE username = ? AND is_active = 1"
            );
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                login($user['id_user'], $user['username'], $user['full_name']);

                // Store login info for toast
                $_SESSION['login_success'] = true;
                $_SESSION['login_time'] = time();

                header('Location: projects.php');
                exit;
            }
            $error = 'Invalid username or password';
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = 'Login error. Try again.';
        }
    } else {
        $error = 'Enter username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h3 class="text-center mb-4"><?= APP_NAME ?></h3>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>