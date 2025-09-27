<?php
require_login();

$counts = [];
$tables = [
    'clients' => 'clientes',
    'projects' => 'obras',
    'budgets' => 'orcamentos',
    'products' => 'produtos'
];
foreach ($tables as $key => $table) {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM {$table}");
    $counts[$key] = (int)$stmt->fetchColumn();
}

$recentBudgets = $pdo->query("SELECT o.id, o.numero_ordem, c.nome AS cliente, o.status, o.total_final, o.created_at FROM orcamentos o LEFT JOIN clientes c ON c.id = o.cliente_id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();
?>
<div class="row g-3">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <span class="text-muted">Clientes</span>
                <h3 class="fw-bold mb-0"><?php echo $counts['clients']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <span class="text-muted">Obras</span>
                <h3 class="fw-bold mb-0"><?php echo $counts['projects']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <span class="text-muted">Orçamentos</span>
                <h3 class="fw-bold mb-0"><?php echo $counts['budgets']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <span class="text-muted">Produtos</span>
                <h3 class="fw-bold mb-0"><?php echo $counts['products']; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Últimos orçamentos</h5>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Valor Final</th>
                    <th>Criado em</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentBudgets as $budget): ?>
                <tr>
                    <td><a href="index.php?page=budget_edit&id=<?php echo $budget['id']; ?>"><?php echo htmlspecialchars($budget['numero_ordem']); ?></a></td>
                    <td><?php echo htmlspecialchars($budget['cliente'] ?? ''); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $budget['status'] === 'fechado' ? 'success' : 'secondary'; ?> badge-status">
                            <?php echo ucfirst($budget['status']); ?>
                        </span>
                    </td>
                    <td><?php echo format_currency($budget['total_final']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($budget['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recentBudgets)): ?>
                <tr><td colspan="5" class="text-center text-muted">Nenhum orçamento registrado ainda.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
