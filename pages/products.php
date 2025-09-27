<?php
require_login();
require_role(['admin']);

$categorias = fetch_lookup($pdo, 'categorias');

if (is_post()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $imagePath = handle_file_upload('imagem');
        $stmt = $pdo->prepare('INSERT INTO produtos (categoria_id, nome, descricao, codigo_inlumi, fornecedor, codigo_fornecedor, acabamento, custo, preco_venda, imagem_path, criado_em) VALUES (:categoria_id, :nome, :descricao, :codigo_inlumi, :fornecedor, :codigo_fornecedor, :acabamento, :custo, :preco_venda, :imagem_path, NOW())');
        $stmt->execute([
            'categoria_id' => $_POST['categoria_id'] ?: null,
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'] ?? null,
            'codigo_inlumi' => $_POST['codigo_inlumi'] ?? null,
            'fornecedor' => $_POST['fornecedor'] ?? null,
            'codigo_fornecedor' => $_POST['codigo_fornecedor'] ?? null,
            'acabamento' => $_POST['acabamento'] ?? null,
            'custo' => $_POST['custo'] ?? 0,
            'preco_venda' => $_POST['preco_venda'] ?? 0,
            'imagem_path' => $imagePath,
        ]);
        flash('success', 'Produto cadastrado.');
        redirect('index.php?page=products');
    }
    if ($action === 'update') {
        $imagePath = handle_file_upload('imagem');
        $stmt = $pdo->prepare('UPDATE produtos SET categoria_id = :categoria_id, nome = :nome, descricao = :descricao, codigo_inlumi = :codigo_inlumi, fornecedor = :fornecedor, codigo_fornecedor = :codigo_fornecedor, acabamento = :acabamento, custo = :custo, preco_venda = :preco_venda, imagem_path = :imagem_path WHERE id = :id');
        $stmt->execute([
            'categoria_id' => $_POST['categoria_id'] ?: null,
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'] ?? null,
            'codigo_inlumi' => $_POST['codigo_inlumi'] ?? null,
            'fornecedor' => $_POST['fornecedor'] ?? null,
            'codigo_fornecedor' => $_POST['codigo_fornecedor'] ?? null,
            'acabamento' => $_POST['acabamento'] ?? null,
            'custo' => $_POST['custo'] ?? 0,
            'preco_venda' => $_POST['preco_venda'] ?? 0,
            'imagem_path' => $imagePath,
            'id' => (int)$_POST['id'],
        ]);
        flash('success', 'Produto atualizado.');
        redirect('index.php?page=products');
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['delete']]);
    flash('success', 'Produto removido.');
    redirect('index.php?page=products');
}

$produtos = $pdo->query('SELECT p.*, c.nome AS categoria FROM produtos p LEFT JOIN categorias c ON c.id = p.categoria_id ORDER BY p.nome')->fetchAll();
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['edit']]);
    $editing = $stmt->fetch();
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Produtos</h4>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#productModal"><i class="bi bi-plus-circle"></i> Novo Produto</button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Código Inlumi</th>
                    <th>Fornecedor</th>
                    <th>Custo</th>
                    <th>Venda</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td>
                        <?php if ($produto['imagem_path']): ?>
                            <img src="<?php echo htmlspecialchars($produto['imagem_path']); ?>" width="60" class="rounded">
                        <?php else: ?>
                            <span class="text-muted">Sem imagem</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                    <td><?php echo htmlspecialchars($produto['categoria']); ?></td>
                    <td><?php echo htmlspecialchars($produto['codigo_inlumi']); ?></td>
                    <td><?php echo htmlspecialchars($produto['fornecedor']); ?></td>
                    <td><?php echo format_currency($produto['custo']); ?></td>
                    <td><?php echo format_currency($produto['preco_venda']); ?></td>
                    <td class="text-end">
                        <a href="index.php?page=products&edit=<?php echo $produto['id']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <a href="index.php?page=products&delete=<?php echo $produto['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover este produto?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($produtos)): ?>
                <tr><td colspan="8" class="text-center text-muted">Nenhum produto cadastrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoria</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">-- selecione --</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código Inlumi</label>
                            <input type="text" name="codigo_inlumi" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fornecedor</label>
                            <input type="text" name="fornecedor" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código Fornecedor</label>
                            <input type="text" name="codigo_fornecedor" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Acabamento</label>
                            <input type="text" name="acabamento" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Custo</label>
                            <input type="number" step="0.01" name="custo" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Preço de Venda</label>
                            <input type="number" step="0.01" name="preco_venda" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Imagem</label>
                            <input type="file" name="imagem" class="form-control" accept="image/*">
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
        <h5>Editar Produto</h5>
        <a href="index.php?page=products" class="btn-close"></a>
    </div>
    <div class="offcanvas-body">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $editing['id']; ?>">
            <input type="hidden" name="imagem_existing" value="<?php echo htmlspecialchars($editing['imagem_path']); ?>">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($editing['nome']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Categoria</label>
                <select name="categoria_id" class="form-select">
                    <option value="">-- selecione --</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id']; ?>" <?php echo $editing['categoria_id'] == $categoria['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($categoria['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Código Inlumi</label>
                <input type="text" name="codigo_inlumi" value="<?php echo htmlspecialchars($editing['codigo_inlumi']); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Fornecedor</label>
                <input type="text" name="fornecedor" value="<?php echo htmlspecialchars($editing['fornecedor']); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Código Fornecedor</label>
                <input type="text" name="codigo_fornecedor" value="<?php echo htmlspecialchars($editing['codigo_fornecedor']); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Acabamento</label>
                <input type="text" name="acabamento" value="<?php echo htmlspecialchars($editing['acabamento']); ?>" class="form-control">
            </div>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label">Custo</label>
                    <input type="number" step="0.01" name="custo" value="<?php echo htmlspecialchars($editing['custo']); ?>" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Preço de Venda</label>
                    <input type="number" step="0.01" name="preco_venda" value="<?php echo htmlspecialchars($editing['preco_venda']); ?>" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" class="form-control" rows="3"><?php echo htmlspecialchars($editing['descricao']); ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Atualizar Imagem</label>
                <input type="file" name="imagem" class="form-control" accept="image/*">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-warning">Atualizar</button>
            </div>
        </form>
    </div>
</div>
<div class="offcanvas-backdrop fade show"></div>
<?php endif; ?>
