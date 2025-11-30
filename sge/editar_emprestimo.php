<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'conexao.php';

$emprestimo = null;
$error = '';
$emprestimo_id = $_GET['id'] ?? null;

// Buscar dados do empréstimo para edição
if ($emprestimo_id) {
    $stmt = $pdo->prepare("
        SELECT e.*, i.nome as item_nome
        FROM emprestimos e
        JOIN itens i ON e.item_id = i.id
        WHERE e.id = ?
    ");
    $stmt->execute([$emprestimo_id]);
    $emprestimo = $stmt->fetch();
}

if (!$emprestimo) {
    $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Empréstimo não encontrado.'];
    header("Location: index.php");
    exit();
}

// Buscar todos os itens para o dropdown
$itens = $pdo->query("SELECT * FROM itens")->fetchAll();

// Lógica para atualizar o empréstimo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar_emprestimo'])) {
    $novo_item_id = $_POST['item_id'];
    $nova_quantidade = $_POST['quantidade'];
    $nome_solicitante = $_POST['nome'];

    $antigo_item_id = $emprestimo['item_id'];
    $antiga_quantidade = $emprestimo['quantidade'];

    try {
        $pdo->beginTransaction();

        // 1. Devolver a quantidade antiga ao estoque do item antigo
        $stmt = $pdo->prepare("UPDATE itens SET quantidade = quantidade + ? WHERE id = ?");
        $stmt->execute([$antiga_quantidade, $antigo_item_id]);

        // 2. Verificar se a nova quantidade está disponível no novo item
        $stmt = $pdo->prepare("SELECT quantidade FROM itens WHERE id = ?");
        $stmt->execute([$novo_item_id]);
        $item_novo = $stmt->fetch();

        if (!$item_novo || $item_novo['quantidade'] < $nova_quantidade) {
            throw new Exception("Quantidade indisponível para o novo item selecionado.");
        }

        // 3. Retirar a nova quantidade do estoque do novo item
        $stmt = $pdo->prepare("UPDATE itens SET quantidade = quantidade - ? WHERE id = ?");
        $stmt->execute([$nova_quantidade, $novo_item_id]);

        // 4. Atualizar o registro do empréstimo
        $stmt = $pdo->prepare(
            "UPDATE emprestimos SET item_id = ?, quantidade = ?, nome = ? WHERE id = ?"
        );
        $stmt->execute([$novo_item_id, $nova_quantidade, $nome_solicitante, $emprestimo_id]);

        $pdo->commit();
        $_SESSION['form_feedback'] = ['status' => 'success', 'message' => 'Empréstimo atualizado com sucesso!'];
        header("Location: index.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erro ao atualizar o empréstimo: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empréstimo - SGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-primary text-white text-center p-3">
        <h1>Editar Empréstimo</h1>
    </header>

    <main class="container flex-grow-1 mt-4">
        <div class="card">
            <div class="card-header">
                <h3>Editando Empréstimo #<?= htmlspecialchars($emprestimo['id']) ?></h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form action="editar_emprestimo.php?id=<?= $emprestimo_id ?>" method="post">
                    <div class="mb-3">
                        <label for="item_id" class="form-label">Item</label>
                        <select class="form-select" id="item_id" name="item_id" required>
                            <?php foreach ($itens as $item): ?>
                                <option value="<?= $item['id'] ?>" <?= ($item['id'] == $emprestimo['item_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['nome']) ?> (Disponível: <?= $item['quantidade'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" value="<?= htmlspecialchars($emprestimo['quantidade']) ?>" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Solicitante</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($emprestimo['nome']) ?>" required>
                    </div>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="atualizar_emprestimo" class="btn btn-primary">Atualizar</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-light text-center p-3 mt-auto">
        <p>© 2025 SGE - Sistema de Gestão de Equipamentos</p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Adicione aqui JS para validação de quantidade se necessário
    </script>
</body>
</html>
