<?php
require_login();

if (is_post()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $errors = validate_required(['nome' => 'Nome', 'email' => 'E-mail'], $_POST);
        if (!$errors) {
            $stmt = $pdo->prepare('INSERT INTO clientes (nome, email, telefone, empresa, criado_em) VALUES (:nome, :email, :telefone, :empresa, NOW())');
            $stmt->execute([
                'nome' => $_POST['nome'],
                'email' => $_POST['email'],
                'telefone' => $_POST['telefone'] ?? null,
                'empresa' => $_POST['empresa'] ?? null,
            ]);
            flash('success', 'Cliente cadastrado com sucesso.');
            redirect('index.php?page=clients');
        } else {
            flash('error', implode(' ', $errors));
        }
    }
    if ($action === 'update') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare('UPDATE clientes SET nome = :nome, email = :email, telefone = :telefone, empresa = :empresa WHERE id = :id');
        $stmt->execute([
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'telefone' => $_POST['telefone'] ?? null,
            'empresa' => $_POST['empresa'] ?? null,
            'id' => $id,
        ]);
        flash('success', 'Cliente atualizado com sucesso.');
        redirect('index.php?page=clients');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM clientes WHERE id = :id');
    $stmt->execute(['id' => $id]);
    flash('success', 'Cliente removido.');
    redirect('index.php?page=clients');
}

$clientes = $pdo->query('SELECT * FROM clientes ORDER BY nome')->fetchAll();
$editing = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $editing = $stmt->fetch();
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Clientes</h4>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#clientModal">
        <i class="bi bi-plus-circle"></i> Novo Cliente
    </button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Empresa</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clientes as $cliente): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['empresa']); ?></td>
                    <td class="text-end">
                        <a href="index.php?page=clients&edit=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <a href="index.php?page=clients&delete=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja remover este cliente?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($clientes)): ?>
                <tr><td colspan="5" class="text-center text-muted">Nenhum cliente cadastrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Empresa</label>
                        <input type="text" name="empresa" class="form-control">
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
<div class="offcanvas offcanvas-end show" tabindex="-1" id="editClient" style="visibility: visible;">
    <div class="offcanvas-header">
        <h5>Editar Cliente</h5>
        <a href="index.php?page=clients" class="btn-close"></a>
    </div>
    <div class="offcanvas-body">
        <form method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $editing['id']; ?>">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($editing['nome']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($editing['email']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" value="<?php echo htmlspecialchars($editing['telefone']); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Empresa</label>
                <input type="text" name="empresa" value="<?php echo htmlspecialchars($editing['empresa']); ?>" class="form-control">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-warning">Atualizar</button>
            </div>
        </form>
    </div>
</div>
<div class="offcanvas-backdrop fade show"></div>
<?php endif; ?>
