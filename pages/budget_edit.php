<?php
require_login();

function recalculate_budget(PDO $pdo, int $budgetId): void
{
    $stmt = $pdo->prepare('SELECT * FROM orcamentos WHERE id = :id');
    $stmt->execute(['id' => $budgetId]);
    $budget = $stmt->fetch();
    if (!$budget) {
        return;
    }
    $itemsStmt = $pdo->prepare('SELECT custo_unitario, preco_unitario, quantidade FROM orcamento_itens WHERE orcamento_id = :id');
    $itemsStmt->execute(['id' => $budgetId]);
    $items = $itemsStmt->fetchAll();

    $totals = calculate_budget_totals($items, (float)$budget['markup'], (float)$budget['desconto'], (float)$budget['frete']);
    $pdo->prepare('UPDATE orcamentos SET total_custo = :total_custo, total_venda = :total_venda, total_final = :total_final, updated_at = NOW() WHERE id = :id')
        ->execute([
            'total_custo' => $totals['total_cost'],
            'total_venda' => $totals['subtotal'],
            'total_final' => $totals['total'],
            'id' => $budgetId,
        ]);
}

$budgetId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orcamentos WHERE id = :id');
$stmt->execute(['id' => $budgetId]);
$budget = $stmt->fetch();

if (!$budget) {
    echo '<div class="alert alert-warning">Orçamento não encontrado.</div>';
    return;
}

$clientes = fetch_lookup($pdo, 'clientes');
$obras = fetch_lookup($pdo, 'obras');
$arquitetos = fetch_lookup($pdo, 'arquitetos');
$vendedores = fetch_lookup($pdo, 'vendedores');
$projetistas = fetch_lookup($pdo, 'projetistas');
$produtos = $pdo->query('SELECT id, nome, codigo_inlumi, custo, preco_venda, acabamento, imagem_path FROM produtos ORDER BY nome')->fetchAll();

if (is_post()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_budget') {
        $stmt = $pdo->prepare('UPDATE orcamentos SET cliente_id = :cliente_id, obra_id = :obra_id, arquiteto_id = :arquiteto_id, vendedor_id = :vendedor_id, projetista_id = :projetista_id, markup = :markup, desconto = :desconto, frete = :frete, observacoes = :observacoes WHERE id = :id');
        $stmt->execute([
            'cliente_id' => $_POST['cliente_id'] ?: null,
            'obra_id' => $_POST['obra_id'] ?: null,
            'arquiteto_id' => $_POST['arquiteto_id'] ?: null,
            'vendedor_id' => $_POST['vendedor_id'] ?: null,
            'projetista_id' => $_POST['projetista_id'] ?: null,
            'markup' => $_POST['markup'] ?? 0,
            'desconto' => $_POST['desconto'] ?? 0,
            'frete' => $_POST['frete'] ?? 0,
            'observacoes' => $_POST['observacoes'] ?? null,
            'id' => $budgetId,
        ]);
        recalculate_budget($pdo, $budgetId);
        flash('success', 'Orçamento atualizado.');
        redirect('index.php?page=budget_edit&id=' . $budgetId);
    }
    if ($action === 'add_item') {
        $produtoId = (int)($_POST['produto_id'] ?? 0);
        $codigo = $_POST['codigo_produto'] ?? null;
        $descricao = $_POST['descricao'] ?? null;
        $acabamento = $_POST['acabamento'] ?? null;
        $custo = (float)($_POST['custo_unitario'] ?? 0);
        $preco = (float)($_POST['preco_unitario'] ?? $custo);
        $quantidade = (float)($_POST['quantidade'] ?? 1);
        $agrupamento = $_POST['agrupamento'] ?? null;
        $imagemPath = $_POST['imagem_path'] ?? null;

        if ($produtoId) {
            foreach ($produtos as $produto) {
                if ($produto['id'] == $produtoId) {
                    $codigo = $codigo ?: $produto['codigo_inlumi'];
                    $descricao = $descricao ?: $produto['nome'];
                    $acabamento = $acabamento ?: $produto['acabamento'];
                    $custo = $custo ?: $produto['custo'];
                    $preco = $_POST['preco_unitario'] === '' ? ($produto['preco_venda'] ?: $produto['custo']) : $preco;
                    $imagemPath = $imagemPath ?: $produto['imagem_path'];
                    break;
                }
            }
        }

        $stmt = $pdo->prepare('INSERT INTO orcamento_itens (orcamento_id, produto_id, codigo_produto, descricao, acabamento, custo_unitario, preco_unitario, quantidade, agrupamento, imagem_path) VALUES (:orcamento_id, :produto_id, :codigo_produto, :descricao, :acabamento, :custo_unitario, :preco_unitario, :quantidade, :agrupamento, :imagem_path)');
        $stmt->execute([
            'orcamento_id' => $budgetId,
            'produto_id' => $produtoId ?: null,
            'codigo_produto' => $codigo,
            'descricao' => $descricao,
            'acabamento' => $acabamento,
            'custo_unitario' => $custo,
            'preco_unitario' => $preco,
            'quantidade' => $quantidade,
            'agrupamento' => $agrupamento,
            'imagem_path' => $imagemPath,
        ]);
        recalculate_budget($pdo, $budgetId);
        flash('success', 'Item adicionado.');
        redirect('index.php?page=budget_edit&id=' . $budgetId);
    }
    if ($action === 'update_item') {
        $itemId = (int)$_POST['item_id'];
        $stmt = $pdo->prepare('UPDATE orcamento_itens SET codigo_produto = :codigo_produto, descricao = :descricao, acabamento = :acabamento, custo_unitario = :custo_unitario, preco_unitario = :preco_unitario, quantidade = :quantidade, agrupamento = :agrupamento WHERE id = :id AND orcamento_id = :orcamento_id');
        $stmt->execute([
            'codigo_produto' => $_POST['codigo_produto'] ?? null,
            'descricao' => $_POST['descricao'] ?? null,
            'acabamento' => $_POST['acabamento'] ?? null,
            'custo_unitario' => $_POST['custo_unitario'] ?? 0,
            'preco_unitario' => $_POST['preco_unitario'] ?? 0,
            'quantidade' => $_POST['quantidade'] ?? 1,
            'agrupamento' => $_POST['agrupamento'] ?? null,
            'id' => $itemId,
            'orcamento_id' => $budgetId,
        ]);
        recalculate_budget($pdo, $budgetId);
        flash('success', 'Item atualizado.');
        redirect('index.php?page=budget_edit&id=' . $budgetId);
    }
}

if (isset($_GET['delete_item'])) {
    $stmt = $pdo->prepare('DELETE FROM orcamento_itens WHERE id = :id AND orcamento_id = :orcamento_id');
    $stmt->execute([
        'id' => (int)$_GET['delete_item'],
        'orcamento_id' => $budgetId,
    ]);
    recalculate_budget($pdo, $budgetId);
    flash('success', 'Item removido.');
    redirect('index.php?page=budget_edit&id=' . $budgetId);
}

$stmt = $pdo->prepare('SELECT * FROM orcamentos WHERE id = :id');
$stmt->execute(['id' => $budgetId]);
$budget = $stmt->fetch();

$itemsStmt = $pdo->prepare('SELECT * FROM orcamento_itens WHERE orcamento_id = :id ORDER BY agrupamento, id');
$itemsStmt->execute(['id' => $budgetId]);
$items = $itemsStmt->fetchAll();

$totals = calculate_budget_totals($items, (float)$budget['markup'], (float)$budget['desconto'], (float)$budget['frete']);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Orçamento <?php echo htmlspecialchars($budget['numero_ordem']); ?></h4>
        <small class="text-muted">Criado em <?php echo date('d/m/Y', strtotime($budget['created_at'])); ?></small>
    </div>
    <div class="d-flex gap-2">
        <a href="budget_pdf.php?id=<?php echo $budgetId; ?>" target="_blank" class="btn btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
        <a href="index.php?page=budgets" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <strong>Informações do orçamento</strong>
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <input type="hidden" name="action" value="update_budget">
                    <div class="col-12">
                        <label class="form-label">Cliente</label>
                        <select name="cliente_id" class="form-select">
                            <option value="">-- selecione --</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>" <?php echo $budget['cliente_id'] == $cliente['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cliente['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Obra</label>
                        <select name="obra_id" class="form-select">
                            <option value="">-- selecione --</option>
                            <?php foreach ($obras as $obra): ?>
                                <option value="<?php echo $obra['id']; ?>" <?php echo $budget['obra_id'] == $obra['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($obra['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Arquiteto</label>
                        <select name="arquiteto_id" class="form-select">
                            <option value="">-- selecione --</option>
                            <?php foreach ($arquitetos as $arquiteto): ?>
                                <option value="<?php echo $arquiteto['id']; ?>" <?php echo $budget['arquiteto_id'] == $arquiteto['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($arquiteto['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Vendedor</label>
                        <select name="vendedor_id" class="form-select">
                            <option value="">-- selecione --</option>
                            <?php foreach ($vendedores as $vendedor): ?>
                                <option value="<?php echo $vendedor['id']; ?>" <?php echo $budget['vendedor_id'] == $vendedor['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($vendedor['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Projetista/Orçamentista</label>
                        <select name="projetista_id" class="form-select">
                            <option value="">-- selecione --</option>
                            <?php foreach ($projetistas as $proj): ?>
                                <option value="<?php echo $proj['id']; ?>" <?php echo $budget['projetista_id'] == $proj['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($proj['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="form-label">Markup (%)</label>
                        <input type="number" step="0.01" name="markup" value="<?php echo htmlspecialchars($budget['markup']); ?>" class="form-control">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Desconto (%)</label>
                        <input type="number" step="0.01" name="desconto" value="<?php echo htmlspecialchars($budget['desconto']); ?>" class="form-control">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Frete (R$)</label>
                        <input type="number" step="0.01" name="frete" value="<?php echo htmlspecialchars($budget['frete']); ?>" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3"><?php echo htmlspecialchars($budget['observacoes']); ?></textarea>
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-warning">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Itens do orçamento</strong>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addItemForm"><i class="bi bi-plus-circle"></i> Adicionar item</button>
            </div>
            <div class="collapse" id="addItemForm">
                <div class="card-body">
                    <form method="post" class="row g-3">
                        <input type="hidden" name="action" value="add_item">
                        <div class="col-md-6">
                            <label class="form-label">Produto</label>
                            <select name="produto_id" id="produto_id" class="form-select">
                                <option value="">-- selecione --</option>
                                <?php foreach ($produtos as $produto): ?>
                                    <option value="<?php echo $produto['id']; ?>" data-prod='<?php echo json_encode($produto); ?>'><?php echo htmlspecialchars($produto['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agrupamento</label>
                            <input type="text" name="agrupamento" class="form-control" placeholder="Ex: Sala de estar">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo_produto" id="codigo_produto" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Custo</label>
                            <input type="number" step="0.01" name="custo_unitario" id="custo_unitario" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Preço Venda</label>
                            <input type="number" step="0.01" name="preco_unitario" id="preco_unitario" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" id="descricao" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Acabamento</label>
                            <input type="text" name="acabamento" id="acabamento" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantidade</label>
                            <input type="number" step="0.01" name="quantidade" value="1" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Imagem</label>
                            <input type="text" name="imagem_path" id="imagem_path" class="form-control" placeholder="URL ou caminho">
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-warning">Adicionar item</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Grupo</th>
                            <th>Qtd</th>
                            <th>Custo</th>
                            <th>Venda</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <?php $formId = 'item-form-' . $item['id']; ?>
                            <td>
                                <input type="text" name="descricao" form="<?php echo $formId; ?>" value="<?php echo htmlspecialchars($item['descricao']); ?>" class="form-control form-control-sm" placeholder="Descrição">
                                <div class="row g-1 mt-1">
                                    <div class="col-6">
                                        <input type="text" name="codigo_produto" form="<?php echo $formId; ?>" value="<?php echo htmlspecialchars($item['codigo_produto']); ?>" class="form-control form-control-sm" placeholder="Código">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="acabamento" form="<?php echo $formId; ?>" value="<?php echo htmlspecialchars($item['acabamento']); ?>" class="form-control form-control-sm" placeholder="Acabamento">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="agrupamento" form="<?php echo $formId; ?>" value="<?php echo htmlspecialchars($item['agrupamento']); ?>" class="form-control form-control-sm" placeholder="Grupo">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="quantidade" form="<?php echo $formId; ?>" value="<?php echo htmlspecialchars($item['quantidade']); ?>" class="form-control form-control-sm">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="custo_unitario" form="<?php echo $formId; ?>" value="<?php echo htmlspecialchars($item['custo_unitario']); ?>" class="form-control form-control-sm">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="preco_unitario" form="<?php echo $formId; ?>" value="<?php echo htmlspecialchars($item['preco_unitario']); ?>" class="form-control form-control-sm">
                            </td>
                            <td><?php echo format_currency($item['preco_unitario'] * $item['quantidade']); ?></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <form id="<?php echo $formId; ?>" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="update_item">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check"></i></button>
                                    </form>
                                    <a href="index.php?page=budget_edit&id=<?php echo $budgetId; ?>&delete_item=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover item?');"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="7" class="text-center text-muted">Nenhum item adicionado.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <strong>Resumo financeiro</strong>
            </div>
            <div class="card-body">
                <div class="budget-summary">
                    <div class="card">
                        <div class="card-body">
                            <span class="text-muted">Custo total</span>
                            <h5 class="fw-bold mb-0"><?php echo format_currency($totals['total_cost']); ?></h5>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <span class="text-muted">Subtotal com markup</span>
                            <h5 class="fw-bold mb-0"><?php echo format_currency($totals['subtotal']); ?></h5>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <span class="text-muted">Desconto</span>
                            <h5 class="fw-bold mb-0"><?php echo format_currency($totals['discount_amount']); ?></h5>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <span class="text-muted">Frete</span>
                            <h5 class="fw-bold mb-0"><?php echo format_currency($totals['freight']); ?></h5>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <span class="text-muted">Total Final</span>
                            <h4 class="fw-bold mb-0 text-success"><?php echo format_currency($totals['total']); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const productSelect = document.getElementById('produto_id');
if (productSelect) {
    productSelect.addEventListener('change', (event) => {
        const option = event.target.selectedOptions[0];
        if (!option) return;
        const data = option.dataset.prod ? JSON.parse(option.dataset.prod) : null;
        if (!data) return;
        document.getElementById('codigo_produto').value = data.codigo_inlumi || '';
        document.getElementById('descricao').value = data.nome || '';
        document.getElementById('acabamento').value = data.acabamento || '';
        document.getElementById('custo_unitario').value = data.custo || 0;
        document.getElementById('preco_unitario').value = data.preco_venda || data.custo || 0;
        document.getElementById('imagem_path').value = data.imagem_path || '';
    });
}
</script>
