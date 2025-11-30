<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_profile'] != 'admin') {
    header("Location: index.php");
    exit();
}

require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $item_id = $_POST['id'];

    try {
        // Verificar se o item está em algum empréstimo ativo
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprestimos WHERE item_id = ? AND status = 'Emprestado'");
        $stmt->execute([$item_id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Não é possível excluir o item, pois ele está em um empréstimo ativo.'];
        } else {
            // Se não estiver em empréstimos ativos, pode excluir
            $stmt = $pdo->prepare("DELETE FROM itens WHERE id = ?");
            $stmt->execute([$item_id]);
            $_SESSION['form_feedback'] = ['status' => 'success', 'message' => 'Item excluído com sucesso!'];
        }
    } catch (PDOException $e) {
        $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Erro ao excluir o item.'];
    }
}

header("Location: gerenciar_itens.php");
exit();
?>
