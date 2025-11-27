<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'vendor/autoload.php';
require 'conexao.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

$emprestimos = $pdo->query("SELECT * FROM emprestimos")->fetchAll(PDO::FETCH_ASSOC);

$html = '<h1>Relatório de Empréstimos</h1>';
$html .= '<table border="1" width="100%" style="border-collapse: collapse;">';
$html .= '<thead><tr><th>Data</th><th>Nº Documento</th><th>Item</th><th>Nome</th><th>Status</th><th>Retirada com</th><th>Devolvido com</th><th>Data Devolução</th></tr></thead>';
$html .= '<tbody>';
foreach ($emprestimos as $emprestimo) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($emprestimo['data']) . '</td>';
    $html .= '<td>' . htmlspecialchars($emprestimo['documento']) . '</td>';
    $html .= '<td>' . htmlspecialchars($emprestimo['item']) . '</td>';
    $html .= '<td>' . htmlspecialchars($emprestimo['nome']) . '</td>';
    $html .= '<td>' . htmlspecialchars($emprestimo['status']) . '</td>';
    $html .= '<td>' . htmlspecialchars($emprestimo['retirada_com']) . '</td>';
    $html .= '<td>' . htmlspecialchars($emprestimo['devolvido_com']) . '</td>';
    $html .= '<td>' . htmlspecialchars($emprestimo['data_devolucao']) . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("emprestimos.pdf", ["Attachment" => true]);
exit();
?>
