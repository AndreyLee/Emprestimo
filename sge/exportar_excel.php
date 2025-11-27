<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'vendor/autoload.php';
require 'conexao.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Data');
$sheet->setCellValue('B1', 'Nº Documento');
$sheet->setCellValue('C1', 'Item');
$sheet->setCellValue('D1', 'Nome');
$sheet->setCellValue('E1', 'Status');
$sheet->setCellValue('F1', 'Retirada com');
$sheet->setCellValue('G1', 'Devolvido com');
$sheet->setCellValue('H1', 'Data Devolução');

$emprestimos = $pdo->query("SELECT * FROM emprestimos")->fetchAll(PDO::FETCH_ASSOC);

$row = 2;
foreach ($emprestimos as $emprestimo) {
    $sheet->setCellValue('A' . $row, $emprestimo['data']);
    $sheet->setCellValue('B' . $row, $emprestimo['documento']);
    $sheet->setCellValue('C' . $row, $emprestimo['item']);
    $sheet->setCellValue('D' . $row, $emprestimo['nome']);
    $sheet->setCellValue('E' . $row, $emprestimo['status']);
    $sheet->setCellValue('F' . $row, $emprestimo['retirada_com']);
    $sheet->setCellValue('G' . $row, $emprestimo['devolvido_com']);
    $sheet->setCellValue('H' . $row, $emprestimo['data_devolucao']);
    $row++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="emprestimos.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
