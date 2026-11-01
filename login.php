<?php
require __DIR__ . '/config.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Log in';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => $user['id'],
            'name'     => $user['name'],
            'email'    => $user['email'],
            'is_admin' => (bool) $user['is_admin'],
        ];

        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    }

    $error = 'Invalid email or password.';
}

$currentPage = 'login';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap auth-wrap">
    <div class="panel auth-card">
        <h1>Log in</h1>
        <p class="muted">New to Everly? <a href="register.php">Create an account</a></p>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="login.php" class="form">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus autocomplete="email">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:18px">Log in</button>
        </form>
        <p class="muted small" style="margin-top:18px">Demo customer: demo@everly.test / demo123</p>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
