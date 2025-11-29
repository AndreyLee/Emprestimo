<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_profile'] != 'admin') {
    header("Location: index.php");
    exit();
}

require 'conexao.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: gerenciar_itens.php");
    exit();
}

// Processar a atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['salvar_alteracoes'])) {
    $nome = $_POST['nome'];
    $quantidade_total = $_POST['quantidade_total'];
    $quantidade_disponivel = $_POST['quantidade_disponivel'];

    try {
        $stmt = $pdo->prepare("UPDATE itens SET nome = ?, quantidade_total = ?, quantidade_disponivel = ? WHERE id = ?");
        $stmt->execute([$nome, $quantidade_total, $quantidade_disponivel, $id]);
        $_SESSION['form_feedback'] = ['status' => 'success', 'message' => 'Item atualizado com sucesso!'];
    } catch (PDOException $e) {
        $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Erro ao atualizar item.'];
    }
    header("Location: gerenciar_itens.php");
    exit();
}

// Buscar dados do item para preencher o formulário
$stmt = $pdo->prepare("SELECT * FROM itens WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Item não encontrado.'];
    header("Location: gerenciar_itens.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Item - SGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-primary text-white p-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="mb-0 fs-4">Sistema de Gestão de Empréstimos</h1>
            <a href="logout.php" class="btn btn-danger">Sair</a>
        </div>
    </header>

    <main class="container flex-grow-1 mt-4">
        <h2>Editar Item</h2>
        <form action="editar_item.php?id=<?= $id ?>" method="post">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Item</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($item['nome']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade_total" class="form-label">Quantidade Total</label>
                        <input type="number" class="form-control" id="quantidade_total" name="quantidade_total" value="<?= htmlspecialchars($item['quantidade_total']) ?>" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade_disponivel" class="form-label">Quantidade Disponível</label>
                        <input type="number" class="form-control" id="quantidade_disponivel" name="quantidade_disponivel" value="<?= htmlspecialchars($item['quantidade_disponivel']) ?>" min="0" required>
                    </div>
                    <button type="submit" name="salvar_alteracoes" class="btn btn-success">Salvar Alterações</button>
                    <a href="gerenciar_itens.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </main>

    <footer class="bg-light text-center p-3 mt-auto">
        <p>© 2025 SGE - Sistema de Gestão de Equipamentos</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
