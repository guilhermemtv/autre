<?php
require_login();

$clientes = fetch_lookup($pdo, 'clientes');
$obras = fetch_lookup($pdo, 'obras');
$arquitetos = fetch_lookup($pdo, 'arquitetos');
$vendedores = fetch_lookup($pdo, 'vendedores');
$projetistas = fetch_lookup($pdo, 'projetistas');

if (is_post()) {
    if (($_POST['action'] ?? '') === 'create') {
        $numero = 'ORC-' . date('Y') . '-' . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('INSERT INTO orcamentos (numero_ordem, cliente_id, obra_id, arquiteto_id, vendedor_id, projetista_id, markup, desconto, frete, status, created_at, updated_at) VALUES (:numero, :cliente_id, :obra_id, :arquiteto_id, :vendedor_id, :projetista_id, :markup, :desconto, :frete, "aberto", NOW(), NOW())');
        $stmt->execute([
            'numero' => $numero,
            'cliente_id' => $_POST['cliente_id'] ?: null,
            'obra_id' => $_POST['obra_id'] ?: null,
            'arquiteto_id' => $_POST['arquiteto_id'] ?: null,
            'vendedor_id' => $_POST['vendedor_id'] ?: null,
            'projetista_id' => $_POST['projetista_id'] ?: null,
            'markup' => $_POST['markup'] ?? 0,
            'desconto' => $_POST['desconto'] ?? 0,
            'frete' => $_POST['frete'] ?? 0,
        ]);
        $budgetId = $pdo->lastInsertId();
        flash('success', 'Orçamento criado.');
        redirect('index.php?page=budget_edit&id=' . $budgetId);
    }
}

if (isset($_GET['duplicate'])) {
    $id = (int)$_GET['duplicate'];
    $stmt = $pdo->prepare('SELECT * FROM orcamentos WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $original = $stmt->fetch();
    if ($original) {
        $numero = 'ORC-' . date('Y') . '-' . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('INSERT INTO orcamentos (numero_ordem, cliente_id, obra_id, arquiteto_id, vendedor_id, projetista_id, markup, desconto, frete, status, total_custo, total_venda, total_final, created_at, updated_at) VALUES (:numero, :cliente_id, :obra_id, :arquiteto_id, :vendedor_id, :projetista_id, :markup, :desconto, :frete, "aberto", :total_custo, :total_venda, :total_final, NOW(), NOW())');
        $stmt->execute([
            'numero' => $numero,
            'cliente_id' => $original['cliente_id'],
            'obra_id' => $original['obra_id'],
            'arquiteto_id' => $original['arquiteto_id'],
            'vendedor_id' => $original['vendedor_id'],
            'projetista_id' => $original['projetista_id'],
            'markup' => $original['markup'],
            'desconto' => $original['desconto'],
            'frete' => $original['frete'],
            'total_custo' => $original['total_custo'],
            'total_venda' => $original['total_venda'],
            'total_final' => $original['total_final'],
        ]);
        $newId = $pdo->lastInsertId();
        $items = $pdo->prepare('SELECT * FROM orcamento_itens WHERE orcamento_id = :id');
        $items->execute(['id' => $id]);
        foreach ($items->fetchAll() as $item) {
            $stmtItem = $pdo->prepare('INSERT INTO orcamento_itens (orcamento_id, produto_id, codigo_produto, descricao, acabamento, custo_unitario, preco_unitario, quantidade, agrupamento, imagem_path) VALUES (:orcamento_id, :produto_id, :codigo_produto, :descricao, :acabamento, :custo_unitario, :preco_unitario, :quantidade, :agrupamento, :imagem_path)');
            $stmtItem->execute([
                'orcamento_id' => $newId,
                'produto_id' => $item['produto_id'],
                'codigo_produto' => $item['codigo_produto'],
                'descricao' => $item['descricao'],
                'acabamento' => $item['acabamento'],
                'custo_unitario' => $item['custo_unitario'],
                'preco_unitario' => $item['preco_unitario'],
                'quantidade' => $item['quantidade'],
                'agrupamento' => $item['agrupamento'],
                'imagem_path' => $item['imagem_path'],
            ]);
        }
        flash('success', 'Orçamento duplicado para revisão.');
        redirect('index.php?page=budget_edit&id=' . $newId);
    }
}

if (isset($_GET['close'])) {
    $id = (int)$_GET['close'];
    $stmt = $pdo->prepare('SELECT * FROM orcamentos WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $budget = $stmt->fetch();
    if ($budget) {
        $orderNumber = 'PED-' . date('Ymd') . '-' . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('UPDATE orcamentos SET status = "fechado", pedido_numero = :pedido, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['pedido' => $orderNumber, 'id' => $id]);
        flash('success', 'Orçamento fechado. Pedido #' . $orderNumber);
    }
    redirect('index.php?page=budgets');
}

$orcamentos = $pdo->query('SELECT o.*, c.nome AS cliente, ob.nome AS obra, v.nome AS vendedor FROM orcamentos o LEFT JOIN clientes c ON c.id = o.cliente_id LEFT JOIN obras ob ON ob.id = o.obra_id LEFT JOIN vendedores v ON v.id = o.vendedor_id ORDER BY o.created_at DESC')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Orçamentos</h4>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#newBudgetModal"><i class="bi bi-plus-circle"></i> Novo Orçamento</button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Ordem</th>
                    <th>Cliente</th>
                    <th>Obra</th>
                    <th>Vendedor</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Pedido</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orcamentos as $orcamento): ?>
                <tr>
                    <td><a href="index.php?page=budget_edit&id=<?php echo $orcamento['id']; ?>" class="fw-semibold text-decoration-none"><?php echo htmlspecialchars($orcamento['numero_ordem']); ?></a></td>
                    <td><?php echo htmlspecialchars($orcamento['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($orcamento['obra']); ?></td>
                    <td><?php echo htmlspecialchars($orcamento['vendedor']); ?></td>
                    <td><span class="badge bg-<?php echo $orcamento['status'] === 'fechado' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($orcamento['status']); ?></span></td>
                    <td><?php echo format_currency($orcamento['total_final']); ?></td>
                    <td><?php echo htmlspecialchars($orcamento['pedido_numero']); ?></td>
                    <td class="text-end">
                        <a href="budget_pdf.php?id=<?php echo $orcamento['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="bi bi-file-earmark-pdf"></i></a>
                        <a href="index.php?page=budgets&duplicate=<?php echo $orcamento['id']; ?>" class="btn btn-sm btn-outline-secondary">Duplicar</a>
                        <?php if ($orcamento['status'] !== 'fechado'): ?>
                            <a href="index.php?page=budgets&close=<?php echo $orcamento['id']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('Fechar este orçamento e gerar pedido?');">Fechar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orcamentos)): ?>
                <tr><td colspan="8" class="text-center text-muted">Nenhum orçamento cadastrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="newBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Orçamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select name="cliente_id" class="form-select">
                                <option value="">-- selecione --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Obra</label>
                            <select name="obra_id" class="form-select">
                                <option value="">-- selecione --</option>
                                <?php foreach ($obras as $obra): ?>
                                    <option value="<?php echo $obra['id']; ?>"><?php echo htmlspecialchars($obra['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Arquiteto</label>
                            <select name="arquiteto_id" class="form-select">
                                <option value="">-- selecione --</option>
                                <?php foreach ($arquitetos as $arquiteto): ?>
                                    <option value="<?php echo $arquiteto['id']; ?>"><?php echo htmlspecialchars($arquiteto['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vendedor</label>
                            <select name="vendedor_id" class="form-select">
                                <option value="">-- selecione --</option>
                                <?php foreach ($vendedores as $vendedor): ?>
                                    <option value="<?php echo $vendedor['id']; ?>"><?php echo htmlspecialchars($vendedor['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Projetista/Orçamentista</label>
                            <select name="projetista_id" class="form-select">
                                <option value="">-- selecione --</option>
                                <?php foreach ($projetistas as $proj): ?>
                                    <option value="<?php echo $proj['id']; ?>"><?php echo htmlspecialchars($proj['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Markup (%)</label>
                            <input type="number" step="0.01" name="markup" class="form-control" value="20">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Desconto (%)</label>
                            <input type="number" step="0.01" name="desconto" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Frete (R$)</label>
                            <input type="number" step="0.01" name="frete" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Criar orçamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
