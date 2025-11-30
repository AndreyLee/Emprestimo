<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM emprestimos WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['form_feedback'] = ['status' => 'success', 'message' => 'Empréstimo excluído com sucesso!'];
        } catch (PDOException $e) {
            $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Erro ao excluir empréstimo.'];
        }
    }
}

header("Location: index.php");
exit();
?>
