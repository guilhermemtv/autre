<?php
require_login();

if (is_post()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = $pdo->prepare('INSERT INTO vendedores (nome, email, telefone, comissao_percentual, criado_em) VALUES (:nome, :email, :telefone, :comissao, NOW())');
        $stmt->execute([
            'nome' => $_POST['nome'],
            'email' => $_POST['email'] ?? null,
            'telefone' => $_POST['telefone'] ?? null,
            'comissao' => $_POST['comissao_percentual'] ?? 0,
        ]);
        flash('success', 'Vendedor cadastrado.');
        redirect('index.php?page=sellers');
    }
    if ($action === 'update') {
        $stmt = $pdo->prepare('UPDATE vendedores SET nome = :nome, email = :email, telefone = :telefone, comissao_percentual = :comissao WHERE id = :id');
        $stmt->execute([
            'nome' => $_POST['nome'],
            'email' => $_POST['email'] ?? null,
            'telefone' => $_POST['telefone'] ?? null,
            'comissao' => $_POST['comissao_percentual'] ?? 0,
            'id' => (int)$_POST['id'],
        ]);
        flash('success', 'Vendedor atualizado.');
        redirect('index.php?page=sellers');
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM vendedores WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['delete']]);
    flash('success', 'Vendedor removido.');
    redirect('index.php?page=sellers');
}

$vendedores = $pdo->query('SELECT * FROM vendedores ORDER BY nome')->fetchAll();
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM vendedores WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['edit']]);
    $editing = $stmt->fetch();
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Vendedores</h4>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#sellerModal"><i class="bi bi-plus-circle"></i> Novo Vendedor</button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Comissão (%)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($vendedores as $vendedor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($vendedor['nome']); ?></td>
                    <td><?php echo htmlspecialchars($vendedor['email']); ?></td>
                    <td><?php echo htmlspecialchars($vendedor['telefone']); ?></td>
                    <td><?php echo number_format($vendedor['comissao_percentual'], 2, ',', '.'); ?></td>
                    <td class="text-end">
                        <a href="index.php?page=sellers&edit=<?php echo $vendedor['id']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <a href="index.php?page=sellers&delete=<?php echo $vendedor['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover este vendedor?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($vendedores)): ?>
                <tr><td colspan="5" class="text-center text-muted">Nenhum vendedor cadastrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="sellerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Vendedor</h5>
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
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comissão (%)</label>
                        <input type="number" name="comissao_percentual" step="0.01" class="form-control" value="0">
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
        <h5>Editar Vendedor</h5>
        <a href="index.php?page=sellers" class="btn-close"></a>
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
                <input type="email" name="email" value="<?php echo htmlspecialchars($editing['email']); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" value="<?php echo htmlspecialchars($editing['telefone']); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Comissão (%)</label>
                <input type="number" name="comissao_percentual" step="0.01" value="<?php echo htmlspecialchars($editing['comissao_percentual']); ?>" class="form-control">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-warning">Atualizar</button>
            </div>
        </form>
    </div>
</div>
<div class="offcanvas-backdrop fade show"></div>
<?php endif; ?>
