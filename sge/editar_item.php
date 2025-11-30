<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_profile'] != 'admin') {
    header("Location: index.php");
    exit();
}

require 'conexao.php';

$item = null;
$error = '';
$item_id = $_GET['id'] ?? null;

if ($item_id) {
    $stmt = $pdo->prepare("SELECT * FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
}

if (!$item) {
    $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Item não encontrado.'];
    header("Location: gerenciar_itens.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar_item'])) {
    $nome = $_POST['nome'];
    $quantidade = $_POST['quantidade'];

    try {
        $stmt = $pdo->prepare("UPDATE itens SET nome = ?, quantidade = ? WHERE id = ?");
        $stmt->execute([$nome, $quantidade, $item_id]);
        $_SESSION['form_feedback'] = ['status' => 'success', 'message' => 'Item atualizado com sucesso!'];
        header("Location: gerenciar_itens.php");
        exit();
    } catch (PDOException $e) {
        $error = "Erro ao atualizar o item.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Item - SGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-primary text-white text-center p-3">
        <h1>Editar Item</h1>
    </header>

    <main class="container flex-grow-1 mt-4">
        <div class="card">
            <div class="card-header">
                <h3>Editando Item #<?= htmlspecialchars($item['id']) ?></h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form action="editar_item.php?id=<?= $item_id ?>" method="post">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Item</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($item['nome']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" value="<?= htmlspecialchars($item['quantidade']) ?>" required min="0">
                    </div>
                    <a href="gerenciar_itens.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="atualizar_item" class="btn btn-primary">Atualizar</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-light text-center p-3 mt-auto">
        <p>© 2025 SGE - Sistema de Gestão de Equipamentos</p>
    </footer>
</body>
</html>
