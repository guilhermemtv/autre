<?php
require_login();
require_role(['admin']);

if (is_post()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (:name, :email, :password, :role, NOW())');
        $stmt->execute([
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'role' => $_POST['role'],
        ]);
        flash('success', 'Usuário criado.');
        redirect('index.php?page=users');
    }
    if ($action === 'update') {
        $params = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'role' => $_POST['role'],
            'id' => (int)$_POST['id'],
        ];
        $sql = 'UPDATE users SET name = :name, email = :email, role = :role';
        if (!empty($_POST['password'])) {
            $sql .= ', password = :password';
            $params['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }
        $sql .= ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        flash('success', 'Usuário atualizado.');
        redirect('index.php?page=users');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id === current_user()['id']) {
        flash('error', 'Você não pode excluir seu próprio usuário.');
    } else {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        flash('success', 'Usuário removido.');
    }
    redirect('index.php?page=users');
}

$users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY name')->fetchAll();
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['edit']]);
    $editing = $stmt->fetch();
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Usuários</h4>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#userModal"><i class="bi bi-plus-circle"></i> Novo Usuário</button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Criado em</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><span class="badge bg-secondary text-uppercase"><?php echo htmlspecialchars($user['role']); ?></span></td>
                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                    <td class="text-end">
                        <a href="index.php?page=users&edit=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <a href="index.php?page=users&delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir este usuário?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr><td colspan="5" class="text-center text-muted">Nenhum usuário cadastrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perfil</label>
                        <select name="role" class="form-select">
                            <option value="admin">Administrador</option>
                            <option value="vendedor">Vendedor</option>
                            <option value="orcamentista">Orçamentista</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($editing): ?>
<div class="offcanvas offcanvas-end show" tabindex="-1" style="visibility: visible;">
    <div class="offcanvas-header">
        <h5>Editar Usuário</h5>
        <a href="index.php?page=users" class="btn-close"></a>
    </div>
    <div class="offcanvas-body">
        <form method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $editing['id']; ?>">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($editing['name']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($editing['email']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nova senha</label>
                <input type="password" name="password" class="form-control" placeholder="Deixe em branco para manter">
            </div>
            <div class="mb-3">
                <label class="form-label">Perfil</label>
                <select name="role" class="form-select">
                    <option value="admin" <?php echo $editing['role'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                    <option value="vendedor" <?php echo $editing['role'] === 'vendedor' ? 'selected' : ''; ?>>Vendedor</option>
                    <option value="orcamentista" <?php echo $editing['role'] === 'orcamentista' ? 'selected' : ''; ?>>Orçamentista</option>
                </select>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-warning">Atualizar</button>
            </div>
        </form>
    </div>
</div>
<div class="offcanvas-backdrop fade show"></div>
<?php endif; ?>
