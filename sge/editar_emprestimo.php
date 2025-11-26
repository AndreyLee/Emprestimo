<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'conexao.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = $_POST['data'];
    $documento = $_POST['documento'];
    $item = $_POST['item'];
    $nome = $_POST['nome'];
    $status = $_POST['status'];
    $retirada_com = $_POST['retirada_com'];
    $devolvido_com = $_POST['devolvido_com'];
    $data_devolucao = $_POST['data_devolucao'];

    $stmt = $pdo->prepare("UPDATE emprestimos SET data=?, documento=?, item=?, nome=?, status=?, retirada_com=?, devolvido_com=?, data_devolucao=? WHERE id=?");
    $stmt->execute([$data, $documento, $item, $nome, $status, $retirada_com, $devolvido_com, $data_devolucao, $id]);

    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM emprestimos WHERE id = ?");
$stmt->execute([$id]);
$emprestimo = $stmt->fetch();

if (!$emprestimo) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empréstimo - SGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-primary text-white text-center p-3">
        <h1>Sistema de Gestão de Empréstimos</h1>
    </header>

    <main class="container flex-grow-1 mt-4">
        <h2>Editar Empréstimo</h2>
        <form action="editar_emprestimo.php?id=<?= $id ?>" method="post">
            <div class="row g-3">
                <div class="col-md-6"><label for="data" class="form-label">Data</label><input type="date" class="form-control" id="data" name="data" value="<?= htmlspecialchars($emprestimo['data']) ?>" required></div>
                <div class="col-md-6"><label for="documento" class="form-label">Nº Documento</label><input type="text" class="form-control" id="documento" name="documento" value="<?= htmlspecialchars($emprestimo['documento']) ?>" required></div>
                <div class="col-md-6"><label for="item" class="form-label">Item</label><input type="text" class="form-control" id="item" name="item" value="<?= htmlspecialchars($emprestimo['item']) ?>" required></div>
                <div class="col-md-6"><label for="nome" class="form-label">Nome</label><input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($emprestimo['nome']) ?>" required></div>
                <div class="col-md-6"><label for="status" class="form-label">Status</label><input type="text" class="form-control" id="status" name="status" value="<?= htmlspecialchars($emprestimo['status']) ?>"></div>
                <div class="col-md-6"><label for="retirada_com" class="form-label">Retirada com</label><input type="text" class="form-control" id="retirada_com" name="retirada_com" value="<?= htmlspecialchars($emprestimo['retirada_com']) ?>"></div>
                <div class="col-md-6"><label for="devolvido_com" class="form-label">Devolvido com</label><input type="text" class="form-control" id="devolvido_com" name="devolvido_com" value="<?= htmlspecialchars($emprestimo['devolvido_com']) ?>"></div>
                <div class="col-md-6"><label for="data_devolucao" class="form-label">Data Devolução</label><input type="date" class="form-control" id="data_devolucao" name="data_devolucao" value="<?= htmlspecialchars($emprestimo['data_devolucao']) ?>"></div>
            </div>
            <button type="submit" class="btn btn-success mt-3">Salvar Alterações</button>
            <a href="index.php" class="btn btn-secondary mt-3">Cancelar</a>
        </form>
    </main>

    <footer class="bg-light text-center p-3 mt-auto">
        <p>© 2025 SGE - Sistema de Gestão de Equipamentos</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
