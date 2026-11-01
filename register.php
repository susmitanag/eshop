<?php
require __DIR__ . '/config.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Create Account';
$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 8) $errors[] = 'The password must be at least 8 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);
        $userId = (int) $pdo->lastInsertId();

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => $userId,
            'name'     => $name,
            'email'    => $email,
            'is_admin' => false,
        ];

        header('Location: index.php');
        exit;
    }
}

$currentPage = 'login';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap auth-wrap">
    <div class="panel auth-card">
        <h1>Create your account</h1>
        <p class="muted">Already have one? <a href="login.php">Log in</a></p>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="post" action="register.php" class="form">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="<?= e($name) ?>" required autocomplete="name">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="email">
            <div class="form-row">
                <div><label for="password">Password</label><input type="password" id="password" name="password" minlength="8" required autocomplete="new-password"></div>
                <div><label for="confirm_password">Repeat password</label><input type="password" id="confirm_password" name="confirm_password" minlength="8" required autocomplete="new-password"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:18px">Create account</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
