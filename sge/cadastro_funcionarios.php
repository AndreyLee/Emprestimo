<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_profile'] != 'admin') {
    header("Location: index.php");
    exit();
}

require 'conexao.php';

$message = '';

// Lógica para cadastrar funcionário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_funcionario'])) {
    $nome = $_POST['nome'];
    $senha = $_POST['senha'];
    $setor = $_POST['setor'];
    $perfil = $_POST['perfil'];

    if (empty($nome) || empty($senha) || empty($setor) || empty($perfil)) {
        $message = '<div class="alert alert-danger">Todos os campos são obrigatórios.</div>';
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO funcionarios (nome, senha, setor, perfil) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nome, $senha_hash, $setor, $perfil])) {
            $message = '<div class="alert alert-success">Funcionário cadastrado com sucesso!</div>';
        } else {
            $message = '<div class="alert alert-danger">Erro ao cadastrar funcionário.</div>';
        }
    }
}

// Buscar todos os funcionários para exibir na tabela
$funcionarios = $pdo->query("SELECT id, nome, setor, perfil, data_cadastro FROM funcionarios ORDER BY nome ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Funcionários - SGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-primary text-white p-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <!-- Espaço em branco para equilíbrio -->
                </div>
                <div class="col-md-4 text-center">
                    <h1 class="mb-0 fs-4">Sistema de Gestão de Empréstimos</h1>
                </div>
                <div class="col-md-4 text-end">
                    <a href="logout.php" class="btn btn-danger">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container flex-grow-1 mt-4">
        <div class="mb-3">
            <a href="index.php" class="btn btn-secondary">Voltar para a Página Principal</a>
        </div>

        <h2>Gerenciamento de Funcionários</h2>

        <?= $message ?>

        <div class="card mb-4">
            <div class="card-header">
                <h3>Cadastrar Novo Funcionário</h3>
            </div>
            <div class="card-body">
                <form action="cadastro_funcionarios.php" method="post">
                    <div class="row g-3">
                        <div class="col-md-6"><input type="text" class="form-control" name="nome" placeholder="Nome" required></div>
                        <div class="col-md-6"><input type="password" class="form-control" name="senha" placeholder="Senha" required></div>
                        <div class="col-md-6">
                            <select name="setor" class="form-select" required>
                                <option value="" disabled selected>Selecione o Setor</option>
                                <option value="GERÊNCIA">GERÊNCIA</option>
                                <option value="NTI">NTI</option>
                                <option value="INFRAESTRUTURA">INFRAESTRUTURA</option>
                                <option value="SERVIÇOS">SERVIÇOS</option>
                                <option value="COMUNICAÇÃO">COMUNICAÇÃO</option>
                                <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select name="perfil" class="form-select" required>
                                <option value="" disabled selected>Selecione o Perfil</option>
                                <option value="admin">Admin</option>
                                <option value="usuario">Usuário</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="cadastrar_funcionario" class="btn btn-primary mt-3">Cadastrar</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Funcionários Cadastrados</h3>
            </div>
            <div class="card-body">
                <table id="funcionariosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Setor</th>
                            <th>Perfil</th>
                            <th>Data de Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($funcionarios as $funcionario): ?>
                            <tr>
                                <td><?= htmlspecialchars($funcionario['nome']) ?></td>
                                <td><?= htmlspecialchars($funcionario['setor']) ?></td>
                                <td><?= htmlspecialchars($funcionario['perfil']) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($funcionario['data_cadastro']))) ?></td>
                                <td>
                                    <a href="editar_funcionario.php?id=<?= $funcionario['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <form action="excluir_funcionario.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $funcionario['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="bg-light text-center p-3 mt-auto">
        <p>© 2025 SGE - Sistema de Gestão de Equipamentos</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#funcionariosTable').DataTable();
        });
    </script>
</body>
</html>
