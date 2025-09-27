<?php
require_once __DIR__ . '/config.php';
require_login();

$page = $_GET['page'] ?? 'dashboard';
$allowedPages = [
    'dashboard' => 'pages/dashboard.php',
    'clients' => 'pages/clients.php',
    'projects' => 'pages/projects.php',
    'architects' => 'pages/architects.php',
    'sellers' => 'pages/sellers.php',
    'estimators' => 'pages/estimators.php',
    'products' => 'pages/products.php',
    'categories' => 'pages/categories.php',
    'budgets' => 'pages/budgets.php',
    'budget_edit' => 'pages/budget_edit.php',
    'reports_sales' => 'pages/reports_sales.php',
    'reports_budgets' => 'pages/reports_budgets.php',
    'reports_financial' => 'pages/reports_financial.php',
    'users' => 'pages/users.php',
];

if (!isset($allowedPages[$page])) {
    http_response_code(404);
    echo '<h1>Página não encontrada</h1>';
    exit;
}

$currentUser = current_user();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="app-content">
    <div class="container-fluid py-4">
        <?php
        $flashSuccess = flash('success');
        $flashError = flash('error');
        if ($flashSuccess) {
            echo '<div class="alert alert-success">' . htmlspecialchars($flashSuccess) . '</div>';
        }
        if ($flashError) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($flashError) . '</div>';
        }
        include __DIR__ . '/' . $allowedPages[$page];
        ?>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
