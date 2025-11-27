<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'conexao.php';

// Lógica para salvar empréstimo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['salvar_emprestimo'])) {
    $documento = $_POST['documento'];
    $item = $_POST['item'];
    $nome = $_POST['nome'];
    $status = $_POST['status'];
    $retirada_com = $_POST['retirada_com'];
    $devolvido_com = $_POST['devolvido_com'];

    // A data do empréstimo é preenchida com a data atual (CURDATE()) e a data de devolução fica como NULL
    $stmt = $pdo->prepare("INSERT INTO emprestimos (data, documento, item, nome, status, retirada_com, devolvido_com) VALUES (CURDATE(), ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$documento, $item, $nome, $status, $retirada_com, $devolvido_com]);

    header("Location: index.php");
    exit();
}

// Lógica para buscar empréstimos
$emprestimos = $pdo->query("SELECT * FROM emprestimos ORDER BY data DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGE - Página Principal</title>
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
        <div class="alert alert-info" role="alert">
            Empréstimo com Credencial ou Documento com Foto - Somente a partir das 10h.
        </div>

        <?php if ($_SESSION['user_profile'] == 'admin'): ?>
            <div class="mb-3">
                <a href="cadastro_funcionarios.php" class="btn btn-secondary">Gerenciar Funcionários</a>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h3>Cadastro de Empréstimo</h3>
            </div>
            <div class="card-body">
                <form action="index.php" method="post">
                    <div class="row g-3">
                        <div class="col-md-6"><input type="text" class="form-control" name="documento" placeholder="Nº Documento" required></div>
                        <div class="col-md-6"><input type="text" class="form-control" name="item" placeholder="Item" required></div>
                        <div class="col-md-6"><input type="text" class="form-control" name="nome" placeholder="Nome" required></div>
                        <div class="col-md-6"><input type="text" class="form-control" name="status" placeholder="Status"></div>
                        <div class="col-md-6"><input type="text" class="form-control" name="retirada_com" placeholder="Retirada com"></div>
                        <div class="col-md-6"><input type="text" class="form-control" name="devolvido_com" placeholder="Devolvido com"></div>
                    </div>
                    <button type="submit" name="salvar_emprestimo" class="btn btn-success mt-3">Salvar</button>
                </form>
            </div>
        </div>

        <hr>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Registros de Empréstimos</h3>
                <div>
                    <a href="exportar_excel.php" class="btn btn-success">Exportar para Excel</a>
                    <a href="exportar_pdf.php" class="btn btn-danger">Exportar para PDF</a>
                </div>
            </div>
            <div class="card-body">
                <table id="emprestimosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Nº Documento</th>
                            <th>Item</th>
                            <th>Nome</th>
                            <th>Status</th>
                            <th>Retirada com</th>
                            <th>Devolvido com</th>
                            <th>Data Devolução</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emprestimos as $emprestimo): ?>
                            <tr>
                                <td><?= htmlspecialchars($emprestimo['data']) ?></td>
                                <td><?= htmlspecialchars($emprestimo['documento']) ?></td>
                                <td><?= htmlspecialchars($emprestimo['item']) ?></td>
                                <td><?= htmlspecialchars($emprestimo['nome']) ?></td>
                                <td><?= htmlspecialchars($emprestimo['status']) ?></td>
                                <td><?= htmlspecialchars($emprestimo['retirada_com']) ?></td>
                                <td><?= htmlspecialchars($emprestimo['devolvido_com']) ?></td>
                                <td><?= htmlspecialchars($emprestimo['data_devolucao']) ?></td>
                                <td>
                                    <a href="editar_emprestimo.php?id=<?= $emprestimo['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <form action="excluir_emprestimo.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $emprestimo['id'] ?>">
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
            $('#emprestimosTable').DataTable();
        });
    </script>
</body>
</html>
