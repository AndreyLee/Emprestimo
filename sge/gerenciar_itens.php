<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_profile'] != 'admin') {
    header("Location: index.php");
    exit();
}

require 'conexao.php';

// Lógica para salvar novo item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['salvar_item'])) {
    $nome = $_POST['nome'];
    $quantidade_total = $_POST['quantidade_total'];

    // A quantidade disponível é igual à total no momento do cadastro
    $quantidade_disponivel = $quantidade_total;

    try {
        $stmt = $pdo->prepare("INSERT INTO itens (nome, quantidade_total, quantidade_disponivel) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $quantidade_total, $quantidade_disponivel]);
        $_SESSION['form_feedback'] = ['status' => 'success', 'message' => 'Item cadastrado com sucesso!'];
    } catch (PDOException $e) {
        $_SESSION['form_feedback'] = ['status' => 'error', 'message' => 'Erro ao cadastrar item.'];
    }
    header("Location: gerenciar_itens.php");
    exit();
}

// Lógica para buscar todos os itens
$itens = $pdo->query("SELECT * FROM itens ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Itens - SGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-primary text-white p-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="mb-0 fs-4">Sistema de Gestão de Empréstimos</h1>
            <a href="logout.php" class="btn btn-danger">Sair</a>
        </div>
    </header>

    <main class="container flex-grow-1 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gerenciamento de Itens</h2>
            <div>
                <a href="index.php" class="btn btn-secondary">Voltar</a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    Adicionar Novo Item
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Itens Cadastrados</h3>
            </div>
            <div class="card-body">
                <table id="itemsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Quantidade Total</th>
                            <th>Quantidade Disponível</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['id']) ?></td>
                                <td><?= htmlspecialchars($item['nome']) ?></td>
                                <td><?= htmlspecialchars($item['quantidade_total']) ?></td>
                                <td><?= htmlspecialchars($item['quantidade_disponivel']) ?></td>
                                <td>
                                    <a href="editar_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <form class="excluir-item-form d-inline" action="excluir_item.php" method="post">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="button" class="btn btn-sm btn-danger excluir-btn">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Adicionar Item -->
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Adicionar Novo Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addItemForm" action="gerenciar_itens.php" method="post">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome do Item</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantidade_total" class="form-label">Quantidade Total</label>
                            <input type="number" class="form-control" id="quantidade_total" name="quantidade_total" min="1" required>
                        </div>
                        <button type="submit" name="salvar_item" class="btn btn-success">Salvar</button>
                    </form>
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
            <?php
            if (isset($_SESSION['form_feedback'])) {
                $status = $_SESSION['form_feedback']['status'];
                $message = $_SESSION['form_feedback']['message'];
                unset($_SESSION['form_feedback']);
                echo "Swal.fire({
                        icon: '{$status}',
                        title: '{$message}',
                        showConfirmButton: false,
                        timer: 1500
                      });";
            }
            ?>
            $('#itemsTable').DataTable();

            // SweetAlert para o botão Excluir
            $('.excluir-btn').on('click', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                Swal.fire({
                    title: 'Você tem certeza?',
                    text: "Esta ação não poderá ser revertida!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                })
            });
        });
    </script>
</body>
</html>
