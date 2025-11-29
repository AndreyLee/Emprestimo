<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_profile'] != 'admin') {
    header("Location: index.php");
    exit();
}

require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM itens WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['form_feedback'] = ['status' => 'success', 'message' => 'Item excluído com sucesso!'];
    } catch (PDOException $e) {
        $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Erro ao excluir item.'];
    }
}

header("Location: gerenciar_itens.php");
exit();
?>
