<?php
function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        redirect('login.php');
    }
}

function require_role(array $roles): void
{
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo '<div class="container py-5"><h1>Acesso negado</h1><p>Você não tem permissão para acessar este recurso.</p></div>';
        exit;
    }
}

function flash(string $key, ?string $message = null)
{
    if ($message === null) {
        if (!empty($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }
    $_SESSION['flash'][$key] = $message;
}

function validate_required(array $fields, array $data): array
{
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty(trim($data[$field] ?? ''))) {
            $errors[$field] = "$label é obrigatório.";
        }
    }
    return $errors;
}

function fetch_all(PDO $pdo, string $table): array
{
    $stmt = $pdo->query("SELECT * FROM {$table} ORDER BY nome");
    return $stmt->fetchAll();
}

function fetch_lookup(PDO $pdo, string $table, string $labelColumn = 'nome'): array
{
    $stmt = $pdo->query("SELECT id, {$labelColumn} AS label FROM {$table} ORDER BY {$labelColumn}");
    return $stmt->fetchAll();
}

function format_currency($value): string
{
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

function format_percentage($value): string
{
    return number_format((float)$value, 2, ',', '.') . '%';
}

function handle_file_upload(string $field, string $targetDir = 'uploads'): ?string
{
    if (empty($_FILES[$field]['name'])) {
        return $_POST[$field . '_existing'] ?? null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('upload_', true) . ($ext ? ".{$ext}" : '');
    $destination = rtrim($targetDir, '/') . '/' . $filename;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $destination;
    }

    return null;
}

function calculate_budget_totals(array $items, float $markup, float $discount, float $freight): array
{
    $totalCost = 0.0;
    $saleBase = 0.0;
    foreach ($items as $item) {
        $cost = (float)($item['cost_price'] ?? $item['custo_unitario'] ?? 0);
        $qty = (float)($item['quantity'] ?? $item['quantidade'] ?? 0);
        $sale = (float)($item['sale_price'] ?? $item['preco_unitario'] ?? 0);
        $totalCost += $cost * $qty;
        $saleBase += $sale * $qty;
    }

    $subtotal = $saleBase > 0 ? $saleBase : $totalCost * (1 + ($markup / 100));
    $discountAmount = $subtotal * ($discount / 100);
    $final = $subtotal - $discountAmount + $freight;

    return [
        'total_cost' => $totalCost,
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'freight' => $freight,
        'total' => $final,
    ];
}
