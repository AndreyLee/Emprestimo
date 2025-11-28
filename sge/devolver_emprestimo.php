<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $emprestimo_id = $_POST['id'];
    $devolvido_por = $_SESSION['user_name'];

    try {
        $stmt = $pdo->prepare("UPDATE emprestimos SET status = 'Devolvido', devolvido_com = ?, data_devolucao = NOW() WHERE id = ?");
        $stmt->execute([$devolvido_por, $emprestimo_id]);
        $_SESSION['form_feedback'] = ['status' => 'success', 'message' => 'Empréstimo devolvido com sucesso!'];
    } catch (PDOException $e) {
        $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Erro ao devolver empréstimo.'];
    }
}

header("Location: index.php");
exit();
