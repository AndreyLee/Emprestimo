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
        $stmt = $pdo->prepare("DELETE FROM emprestimos WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header("Location: index.php");
exit();
?>
