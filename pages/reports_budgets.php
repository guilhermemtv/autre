<?php
require_login();

$statusData = $pdo->query('SELECT status, COUNT(*) AS total, SUM(total_final) AS valor FROM orcamentos GROUP BY status')->fetchAll();
$topClientes = $pdo->query('SELECT c.nome, COUNT(o.id) AS total_orcamentos, SUM(o.total_final) AS valor_total FROM orcamentos o LEFT JOIN clientes c ON c.id = o.cliente_id GROUP BY c.nome ORDER BY valor_total DESC LIMIT 10')->fetchAll();
$topArquitetos = $pdo->query('SELECT a.nome, COUNT(o.id) AS total_orcamentos, SUM(o.total_final) AS valor_total FROM orcamentos o LEFT JOIN arquitetos a ON a.id = o.arquiteto_id GROUP BY a.nome ORDER BY valor_total DESC LIMIT 10')->fetchAll();
?>
<div class="mb-3">
    <h4 class="mb-0">Relatório de Orçamentos</h4>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><strong>Distribuição por status</strong></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Quantidade</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($statusData as $row): ?>
                        <tr>
                            <td><?php echo ucfirst($row['status']); ?></td>
                            <td><?php echo (int)$row['total']; ?></td>
                            <td><?php echo format_currency($row['valor']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($statusData)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Nenhum orçamento cadastrado.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><strong>Top clientes</strong></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Orçamentos</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($topClientes as $cliente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cliente['nome'] ?? 'Sem cadastro'); ?></td>
                            <td><?php echo (int)$cliente['total_orcamentos']; ?></td>
                            <td><?php echo format_currency($cliente['valor_total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($topClientes)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Sem dados.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><strong>Top arquitetos</strong></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Arquiteto</th>
                            <th>Orçamentos</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($topArquitetos as $arquiteto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($arquiteto['nome'] ?? 'Sem cadastro'); ?></td>
                            <td><?php echo (int)$arquiteto['total_orcamentos']; ?></td>
                            <td><?php echo format_currency($arquiteto['valor_total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($topArquitetos)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Sem dados.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
