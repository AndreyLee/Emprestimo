<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_profile'] != 'admin') {
    header("Location: index.php");
    exit();
}

require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    if ($id && $id != $_SESSION['user_id']) { // Prevenção de auto-exclusão
        $stmt = $pdo->prepare("DELETE FROM funcionarios WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header("Location: cadastro_funcionarios.php");
exit();
?>
