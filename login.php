<?php
require_once __DIR__ . '/config.php';

if (current_user()) {
    redirect('index.php');
}

$error = null;

if (is_post()) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        redirect('index.php');
    } else {
        $error = 'Credenciais inválidas. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autre - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #ffcc66, #ffe6b3);
            min-height: 100vh;
        }
        .login-card {
            max-width: 420px;
            margin: 6rem auto;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card login-card">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h4 class="fw-bold">Autre</h4>
                <p class="text-muted mb-0">Entre com suas credenciais</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-warning fw-bold">Entrar</button>
                </div>
            </form>
            <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.85rem;">Área restrita aos colaboradores autorizados.</p>
        </div>
    </div>
</div>
</body>
</html>
