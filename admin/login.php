<?php
require __DIR__ . '/../config.php';

if (is_admin()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_admin = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'is_admin' => true];
        header('Location: index.php');
        exit;
    }
    $error = 'Wrong admin email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin login | Everly</title>
<link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-login-page">
    <div class="panel">
        <div style="text-align:center;margin-bottom:12px"><img src="../assets/img/mark.svg" width="56" height="56" alt=""></div>
        <h1 style="font-size:1.8rem;text-align:center">Admin login</h1>
        <p class="muted" style="text-align:center">Demo: admin@everly.test / admin123</p>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form class="form" method="post" action="login.php">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:18px">Log in</button>
        </form>
        <p style="text-align:center;margin:18px 0 0"><a href="../index.php">Back to the store</a></p>
    </div>
</div>
</body>
</html>
