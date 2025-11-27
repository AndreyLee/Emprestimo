<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_profile'] != 'admin') {
    header("Location: index.php");
    exit();
}

require 'conexao.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: cadastro_funcionarios.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $setor = $_POST['setor'];
    $perfil = $_POST['perfil'];
    $senha = $_POST['senha'];

    if (!empty($senha)) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE funcionarios SET nome=?, setor=?, perfil=?, senha=? WHERE id=?");
        $stmt->execute([$nome, $setor, $perfil, $senha_hash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE funcionarios SET nome=?, setor=?, perfil=? WHERE id=?");
        $stmt->execute([$nome, $setor, $perfil, $id]);
    }

    header("Location: cadastro_funcionarios.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE id = ?");
$stmt->execute([$id]);
$funcionario = $stmt->fetch();

if (!$funcionario) {
    header("Location: cadastro_funcionarios.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Funcionário - SGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-primary text-white text-center p-3">
        <h1>Sistema de Gestão de Empréstimos</h1>
    </header>

    <main class="container flex-grow-1 mt-4">
        <h2>Editar Funcionário</h2>
        <form action="editar_funcionario.php?id=<?= $id ?>" method="post">
            <div class="row g-3">
                <div class="col-md-6"><label for="nome" class="form-label">Nome</label><input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($funcionario['nome']) ?>" required></div>
                <div class="col-md-6"><label for="setor" class="form-label">Setor</label><input type="text" class="form-control" id="setor" name="setor" value="<?= htmlspecialchars($funcionario['setor']) ?>" required></div>
                <div class="col-md-6">
                    <label for="perfil" class="form-label">Perfil</label>
                    <select name="perfil" id="perfil" class="form-select" required>
                        <option value="admin" <?= $funcionario['perfil'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="usuario" <?= $funcionario['perfil'] == 'usuario' ? 'selected' : '' ?>>Usuário</option>
                    </select>
                </div>
                <div class="col-md-6"><label for="senha" class="form-label">Nova Senha (deixe em branco para não alterar)</label><input type="password" class="form-control" id="senha" name="senha"></div>
            </div>
            <button type="submit" class="btn btn-success mt-3">Salvar Alterações</button>
            <a href="cadastro_funcionarios.php" class="btn btn-secondary mt-3">Cancelar</a>
        </form>
    </main>

    <footer class="bg-light text-center p-3 mt-auto">
        <p>© 2025 SGE - Sistema de Gestão de Equipamentos</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
