<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit();
}

require 'conexao.php';

if (isset($_POST['documento'])) {
    // Normaliza o número do documento, removendo qualquer formatação
    $documento = preg_replace('/[^0-9]/', '', $_POST['documento']);

    // Verifica se há empréstimos ativos (status diferente de 'Devolvido')
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprestimos WHERE documento = ? AND (status IS NULL OR status != 'Devolvido')");
    $stmt->execute([$documento]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Se houver empréstimos ativos, o usuário está bloqueado
        echo json_encode(['status' => 'bloqueado']);
    } else {
        // Caso contrário, está liberado para um novo empréstimo
        echo json_encode(['status' => 'liberado']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Documento não fornecido.']);
}
