<?php
require_login();

if (is_post()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = $pdo->prepare('INSERT INTO categorias (nome, descricao, criado_em) VALUES (:nome, :descricao, NOW())');
        $stmt->execute([
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'] ?? null,
        ]);
        flash('success', 'Categoria criada.');
        redirect('index.php?page=categories');
    }
    if ($action === 'update') {
        $stmt = $pdo->prepare('UPDATE categorias SET nome = :nome, descricao = :descricao WHERE id = :id');
        $stmt->execute([
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'] ?? null,
            'id' => (int)$_POST['id'],
        ]);
        flash('success', 'Categoria atualizada.');
        redirect('index.php?page=categories');
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['delete']]);
    flash('success', 'Categoria removida.');
    redirect('index.php?page=categories');
}

$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll();
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM categorias WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['edit']]);
    $editing = $stmt->fetch();
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Categorias de Produto</h4>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="bi bi-plus-circle"></i> Nova Categoria</button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td><?php echo htmlspecialchars($categoria['nome']); ?></td>
                    <td><?php echo htmlspecialchars($categoria['descricao']); ?></td>
                    <td class="text-end">
                        <a href="index.php?page=categories&edit=<?php echo $categoria['id']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <a href="index.php?page=categories&delete=<?php echo $categoria['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover esta categoria?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($categorias)): ?>
                <tr><td colspan="3" class="text-center text-muted">Nenhuma categoria cadastrada.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
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
        <h5>Editar Categoria</h5>
        <a href="index.php?page=categories" class="btn-close"></a>
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
                <label class="form-label">Descrição</label>
                <textarea name="descricao" class="form-control" rows="3"><?php echo htmlspecialchars($editing['descricao']); ?></textarea>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-warning">Atualizar</button>
            </div>
        </form>
    </div>
</div>
<div class="offcanvas-backdrop fade show"></div>
<?php endif; ?>
