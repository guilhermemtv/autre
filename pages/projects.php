<?php
require_login();

$clientes = fetch_lookup($pdo, 'clientes');

if (is_post()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = $pdo->prepare('INSERT INTO obras (nome, cliente_id, endereco, cidade, estado, criado_em) VALUES (:nome, :cliente_id, :endereco, :cidade, :estado, NOW())');
        $stmt->execute([
            'nome' => $_POST['nome'],
            'cliente_id' => $_POST['cliente_id'] ?: null,
            'endereco' => $_POST['endereco'] ?? null,
            'cidade' => $_POST['cidade'] ?? null,
            'estado' => $_POST['estado'] ?? null,
        ]);
        flash('success', 'Obra cadastrada.');
        redirect('index.php?page=projects');
    }
    if ($action === 'update') {
        $stmt = $pdo->prepare('UPDATE obras SET nome = :nome, cliente_id = :cliente_id, endereco = :endereco, cidade = :cidade, estado = :estado WHERE id = :id');
        $stmt->execute([
            'nome' => $_POST['nome'],
            'cliente_id' => $_POST['cliente_id'] ?: null,
            'endereco' => $_POST['endereco'] ?? null,
            'cidade' => $_POST['cidade'] ?? null,
            'estado' => $_POST['estado'] ?? null,
            'id' => (int)$_POST['id'],
        ]);
        flash('success', 'Obra atualizada.');
        redirect('index.php?page=projects');
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM obras WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['delete']]);
    flash('success', 'Obra removida.');
    redirect('index.php?page=projects');
}

$obras = $pdo->query('SELECT o.*, c.nome AS cliente FROM obras o LEFT JOIN clientes c ON c.id = o.cliente_id ORDER BY o.nome')->fetchAll();
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM obras WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['edit']]);
    $editing = $stmt->fetch();
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Obras</h4>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#projectModal"><i class="bi bi-plus-circle"></i> Nova Obra</button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cliente</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($obras as $obra): ?>
                <tr>
                    <td><?php echo htmlspecialchars($obra['nome']); ?></td>
                    <td><?php echo htmlspecialchars($obra['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($obra['cidade']); ?></td>
                    <td><?php echo htmlspecialchars($obra['estado']); ?></td>
                    <td class="text-end">
                        <a href="index.php?page=projects&edit=<?php echo $obra['id']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <a href="index.php?page=projects&delete=<?php echo $obra['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja remover esta obra?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($obras)): ?>
                <tr><td colspan="5" class="text-center text-muted">Nenhuma obra cadastrada.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Obra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <select name="cliente_id" class="form-select">
                            <option value="">-- selecione --</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="endereco" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label class="form-label">Cidade</label>
                                <input type="text" name="cidade" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label class="form-label">Estado</label>
                                <input type="text" name="estado" class="form-control">
                            </div>
                        </div>
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
        <h5>Editar Obra</h5>
        <a href="index.php?page=projects" class="btn-close"></a>
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
                <label class="form-label">Cliente</label>
                <select name="cliente_id" class="form-select">
                    <option value="">-- selecione --</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo $cliente['id']; ?>" <?php echo $editing['cliente_id'] == $cliente['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cliente['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Endereço</label>
                <input type="text" name="endereco" value="<?php echo htmlspecialchars($editing['endereco']); ?>" class="form-control">
            </div>
            <div class="row">
                <div class="col-md-7">
                    <div class="mb-3">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="cidade" value="<?php echo htmlspecialchars($editing['cidade']); ?>" class="form-control">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <input type="text" name="estado" value="<?php echo htmlspecialchars($editing['estado']); ?>" class="form-control">
                    </div>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-warning">Atualizar</button>
            </div>
        </form>
    </div>
</div>
<div class="offcanvas-backdrop fade show"></div>
<?php endif; ?>
