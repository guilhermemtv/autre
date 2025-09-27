<?php
require_once __DIR__ . '/config.php';
require_login();

require_once __DIR__ . '/vendor/fpdf/fpdf.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT o.*, c.nome AS cliente, ob.nome AS obra, a.nome AS arquiteto, v.nome AS vendedor, p.nome AS projetista FROM orcamentos o LEFT JOIN clientes c ON c.id = o.cliente_id LEFT JOIN obras ob ON ob.id = o.obra_id LEFT JOIN arquitetos a ON a.id = o.arquiteto_id LEFT JOIN vendedores v ON v.id = o.vendedor_id LEFT JOIN projetistas p ON p.id = o.projetista_id WHERE o.id = :id');
$stmt->execute(['id' => $id]);
$budget = $stmt->fetch();

if (!$budget) {
    die('Orçamento não encontrado.');
}

$itemStmt = $pdo->prepare('SELECT * FROM orcamento_itens WHERE orcamento_id = :id ORDER BY agrupamento, id');
$itemStmt->execute(['id' => $id]);
$items = $itemStmt->fetchAll();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Orçamento ' . $budget['numero_ordem'], 0, 1);
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(0, 6, 'Cliente: ' . ($budget['cliente'] ?: '-'), 0, 1);
$pdf->Cell(0, 6, 'Obra: ' . ($budget['obra'] ?: '-'), 0, 1);
$pdf->Cell(0, 6, 'Arquiteto: ' . ($budget['arquiteto'] ?: '-'), 0, 1);
$pdf->Cell(0, 6, 'Vendedor: ' . ($budget['vendedor'] ?: '-'), 0, 1);
$pdf->Cell(0, 6, 'Projetista: ' . ($budget['projetista'] ?: '-'), 0, 1);
$pdf->Ln(4);

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(15, 8, 'Qtd', 1);
$pdf->Cell(45, 8, 'Produto', 1);
$pdf->Cell(35, 8, 'Código', 1);
$pdf->Cell(35, 8, 'Acabamento', 1);
$pdf->Cell(30, 8, 'Custo', 1);
$pdf->Cell(30, 8, 'Venda', 1);
$pdf->Ln();
$pdf->SetFont('Helvetica', '', 11);

foreach ($items as $item) {
    $pdf->Cell(15, 8, number_format($item['quantidade'], 2, ',', '.'), 1);
    $pdf->Cell(45, 8, utf8_decode(substr($item['descricao'] ?? '', 0, 30)), 1);
    $pdf->Cell(35, 8, utf8_decode(substr($item['codigo_produto'] ?? '', 0, 20)), 1);
    $pdf->Cell(35, 8, utf8_decode(substr($item['acabamento'] ?? '', 0, 20)), 1);
    $pdf->Cell(30, 8, 'R$ ' . number_format($item['custo_unitario'], 2, ',', '.'), 1);
    $pdf->Cell(30, 8, 'R$ ' . number_format($item['preco_unitario'], 2, ',', '.'), 1);
    $pdf->Ln();
    if (!empty($item['imagem_path'])) {
        $imgPath = $item['imagem_path'];
        if (strpos($imgPath, 'http') === 0) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'pdfimg');
            if ($tmpFile && ($contents = @file_get_contents($imgPath))) {
                $tmpFileWithExt = $tmpFile . '.jpg';
                file_put_contents($tmpFileWithExt, $contents);
                $pdf->Image($tmpFileWithExt, $pdf->GetX(), $pdf->GetY(), 30, 0);
                @unlink($tmpFileWithExt);
                @unlink($tmpFile);
            }
        } elseif (file_exists($imgPath)) {
            $pdf->Image($imgPath, $pdf->GetX(), $pdf->GetY(), 30, 0);
        }
        $pdf->Ln(30);
    }
}

$pdf->Ln(5);
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Resumo Financeiro', 0, 1);
$pdf->SetFont('Helvetica', '', 11);
$pdf->Cell(0, 6, 'Custo total: R$ ' . number_format($budget['total_custo'], 2, ',', '.'), 0, 1);
$pdf->Cell(0, 6, 'Subtotal com markup: R$ ' . number_format($budget['total_venda'], 2, ',', '.'), 0, 1);
$pdf->Cell(0, 6, 'Frete: R$ ' . number_format($budget['frete'], 2, ',', '.'), 0, 1);
$pdf->Cell(0, 6, 'Desconto: ' . number_format($budget['desconto'], 2, ',', '.') . '%', 0, 1);
$pdf->Cell(0, 6, 'Total Final: R$ ' . number_format($budget['total_final'], 2, ',', '.'), 0, 1);

$pdf->Output('I', 'orcamento-' . $budget['numero_ordem'] . '.pdf');
