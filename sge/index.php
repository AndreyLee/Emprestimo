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
    $retirada_com = $_POST['retirada_com'];

    // A data do empréstimo é a data atual, o status padrão é 'Emprestado'
    $stmt = $pdo->prepare("INSERT INTO emprestimos (data, documento, item, nome, status, retirada_com) VALUES (CURDATE(), ?, ?, ?, 'Emprestado', ?)");
    $stmt->execute([$documento, $item, $nome, $retirada_com]);

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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <?php if ($_SESSION['user_profile'] == 'admin'): ?>
                    <a href="cadastro_funcionarios.php" class="btn btn-secondary">Gerenciar Funcionários</a>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loanModal">
                Adicionar Novo Empréstimo
            </button>
        </div>

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

    <!-- Modal -->
    <div class="modal fade" id="loanModal" tabindex="-1" aria-labelledby="loanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loanModalLabel">Novo Empréstimo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Etapa 1: Pesquisa -->
                    <div id="searchStep">
                        <div class="mb-3">
                            <label for="searchDocumento" class="form-label">Nº do Documento</label>
                            <input type="text" class="form-control" id="searchDocumento" placeholder="Digite o documento para consultar">
                        </div>
                        <button type="button" class="btn btn-primary" id="checkLoanStatusBtn">Consultar</button>
                        <div id="searchResult" class="mt-3"></div>
                    </div>

                    <!-- Etapa 2: Formulário (oculto) -->
                    <div id="formStep" style="display: none;">
                        <form id="loanForm" action="index.php" method="post">
                            <input type="hidden" id="formDocumento" name="documento">
                            <div class="mb-3">
                                <label for="formItem" class="form-label">Item</label>
                                <input type="text" class="form-control" id="formItem" name="item" required>
                            </div>
                            <div class="mb-3">
                                <label for="formNome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="formNome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="formRetiradaCom" class="form-label">Retirada com</label>
                                <input type="text" class="form-control" id="formRetiradaCom" name="retirada_com">
                            </div>
                            <button type="submit" name="salvar_emprestimo" class="btn btn-success">Salvar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

            const loanModal = document.getElementById('loanModal');
            const searchStep = $('#searchStep');
            const formStep = $('#formStep');
            const searchResult = $('#searchResult');
            const searchDocumentoInput = $('#searchDocumento');
            const formDocumentoInput = $('#formDocumento');

            // Lógica para o botão de consulta
            $('#checkLoanStatusBtn').on('click', function() {
                const documento = searchDocumentoInput.val();
                if (!documento) {
                    searchResult.html('<div class="alert alert-warning">Por favor, digite um documento.</div>');
                    return;
                }

                $.ajax({
                    url: 'verificar_emprestimo.php',
                    type: 'POST',
                    data: { documento: documento },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'bloqueado') {
                            searchResult.html('<div class="alert alert-danger">Este usuário já possui um empréstimo ativo e não pode realizar outro.</div>');
                        } else if (response.status === 'liberado') {
                            searchResult.html('');
                            formDocumentoInput.val(documento); // Passa o documento para o formulário
                            searchStep.hide();
                            formStep.show();
                        } else {
                            searchResult.html('<div class="alert alert-danger">' + (response.message || 'Ocorreu um erro.') + '</div>');
                        }
                    },
                    error: function() {
                        searchResult.html('<div class="alert alert-danger">Erro de comunicação com o servidor.</div>');
                    }
                });
            });

            // Resetar o modal quando ele for fechado
            loanModal.addEventListener('hidden.bs.modal', function () {
                searchStep.show();
                formStep.hide();
                searchDocumentoInput.val('');
                searchResult.html('');
                $('#loanForm')[0].reset(); // Limpa os campos do formulário
            });
        });
    </script>
</body>
</html>
