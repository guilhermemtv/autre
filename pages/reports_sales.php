<?php
require_login();

$period = $_GET['period'] ?? date('Y');

$stmt = $pdo->prepare('SELECT DATE_FORMAT(updated_at, "%Y-%m") AS mes, COUNT(*) AS total_orcamentos, SUM(total_final) AS valor_total FROM orcamentos WHERE status = "fechado" AND DATE_FORMAT(updated_at, "%Y") = :ano GROUP BY mes ORDER BY mes');
$stmt->execute(['ano' => $period]);
$monthly = $stmt->fetchAll();

$vendedorStmt = $pdo->prepare('SELECT v.nome, COUNT(o.id) AS total, SUM(o.total_final) AS valor FROM orcamentos o LEFT JOIN vendedores v ON v.id = o.vendedor_id WHERE o.status = "fechado" AND DATE_FORMAT(o.updated_at, "%Y") = :ano GROUP BY v.nome ORDER BY valor DESC');
$vendedorStmt->execute(['ano' => $period]);
$vendedores = $vendedorStmt->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Relatório de Vendas</h4>
    <form method="get" class="d-flex align-items-center gap-2">
        <input type="hidden" name="page" value="reports_sales">
        <label class="form-label mb-0">Ano</label>
        <input type="number" name="period" value="<?php echo htmlspecialchars($period); ?>" class="form-control" style="width: 120px;">
        <button class="btn btn-warning">Filtrar</button>
    </form>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <strong>Fechamentos por mês</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Mês</th>
                            <th>Quantidade</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($monthly as $row): ?>
                        <tr>
                            <td><?php echo date('m/Y', strtotime($row['mes'] . '-01')); ?></td>
                            <td><?php echo (int)$row['total_orcamentos']; ?></td>
                            <td><?php echo format_currency($row['valor_total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($monthly)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Sem dados para o período selecionado.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <strong>Ranking por vendedor</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>Fechamentos</th>
                            <th>Faturamento</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($vendedores as $vend): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vend['nome'] ?? 'Sem vendedor'); ?></td>
                            <td><?php echo (int)$vend['total']; ?></td>
                            <td><?php echo format_currency($vend['valor']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($vendedores)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Sem dados para o período selecionado.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
