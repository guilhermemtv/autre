<?php
require_login();

$taxRate = isset($_GET['tax']) ? (float)$_GET['tax'] : 8.0; // percentual estimado de impostos
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-t');

$stmt = $pdo->prepare('SELECT o.*, c.nome AS cliente, v.nome AS vendedor, v.comissao_percentual FROM orcamentos o LEFT JOIN clientes c ON c.id = o.cliente_id LEFT JOIN vendedores v ON v.id = o.vendedor_id WHERE o.status = "fechado" AND DATE(o.updated_at) BETWEEN :from AND :to ORDER BY o.updated_at DESC');
$stmt->execute(['from' => $from, 'to' => $to]);
$rows = $stmt->fetchAll();

$totais = [
    'faturamento' => 0,
    'custo' => 0,
    'impostos' => 0,
    'comissoes' => 0,
    'lucro' => 0,
];

foreach ($rows as &$row) {
    $commissionRate = (float)($row['comissao_percentual'] ?? 0);
    $imposto = $row['total_final'] * ($taxRate / 100);
    $comissao = $row['total_final'] * ($commissionRate / 100);
    $lucro = $row['total_final'] - $row['total_custo'] - $imposto - $comissao;

    $row['imposto'] = $imposto;
    $row['comissao'] = $comissao;
    $row['lucro'] = $lucro;

    $totais['faturamento'] += $row['total_final'];
    $totais['custo'] += $row['total_custo'];
    $totais['impostos'] += $imposto;
    $totais['comissoes'] += $comissao;
    $totais['lucro'] += $lucro;
}
unset($row);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Relatório Financeiro</h4>
    <form method="get" class="row row-cols-lg-auto g-2 align-items-center">
        <input type="hidden" name="page" value="reports_financial">
        <div class="col-12">
            <label class="form-label">De</label>
            <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>" class="form-control">
        </div>
        <div class="col-12">
            <label class="form-label">Até</label>
            <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>" class="form-control">
        </div>
        <div class="col-12">
            <label class="form-label">Imposto (%)</label>
            <input type="number" name="tax" value="<?php echo htmlspecialchars($taxRate); ?>" step="0.01" class="form-control">
        </div>
        <div class="col-12">
            <button class="btn btn-warning mt-4">Aplicar</button>
        </div>
    </form>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <span class="text-muted">Faturamento</span>
                <h4 class="fw-bold mb-0"><?php echo format_currency($totais['faturamento']); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <span class="text-muted">Custo</span>
                <h4 class="fw-bold mb-0"><?php echo format_currency($totais['custo']); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <span class="text-muted">Impostos</span>
                <h4 class="fw-bold mb-0"><?php echo format_currency($totais['impostos']); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <span class="text-muted">Comissões</span>
                <h4 class="fw-bold mb-0"><?php echo format_currency($totais['comissoes']); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-header bg-white"><strong>Detalhamento</strong></div>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Valor final</th>
                    <th>Custo</th>
                    <th>Imposto</th>
                    <th>Comissão</th>
                    <th>Lucro</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['pedido_numero'] ?: $row['numero_ordem']); ?></td>
                    <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($row['vendedor']); ?></td>
                    <td><?php echo format_currency($row['total_final']); ?></td>
                    <td><?php echo format_currency($row['total_custo']); ?></td>
                    <td><?php echo format_currency($row['imposto']); ?></td>
                    <td><?php echo format_currency($row['comissao']); ?></td>
                    <td class="fw-bold text-<?php echo $row['lucro'] >= 0 ? 'success' : 'danger'; ?>"><?php echo format_currency($row['lucro']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['updated_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="9" class="text-center text-muted">Nenhum orçamento fechado no período.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
