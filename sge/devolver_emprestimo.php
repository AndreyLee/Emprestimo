<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $emprestimo_id = $_POST['id'];
    $devolvido_com = $_SESSION['user_name'];

    try {
        $pdo->beginTransaction();

        // 1. Obter os detalhes do empréstimo
        $stmt = $pdo->prepare("SELECT item_id, quantidade FROM emprestimos WHERE id = ?");
        $stmt->execute([$emprestimo_id]);
        $emprestimo = $stmt->fetch();

        if (!$emprestimo) {
            throw new Exception("Empréstimo não encontrado.");
        }

        // 2. Atualizar o status do empréstimo para 'Devolvido'
        $stmt = $pdo->prepare(
            "UPDATE emprestimos
             SET status = 'Devolvido', devolvido_com = ?, data_devolucao = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$devolvido_com, $emprestimo_id]);

        // 3. Devolver a quantidade ao estoque do item
        $stmt = $pdo->prepare("UPDATE itens SET quantidade = quantidade + ? WHERE id = ?");
        $stmt->execute([$emprestimo['quantidade'], $emprestimo['item_id']]);

        $pdo->commit();
        $_SESSION['form_feedback'] = ['status' => 'success', 'message' => 'Devolução registrada com sucesso!'];

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Erro ao registrar devolução: ' . $e->getMessage()];
    }
}

header("Location: index.php");
exit();
?>
