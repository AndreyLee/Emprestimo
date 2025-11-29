<?php
session_start();
require 'conexao.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($nome) || empty($senha)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE nome = ?");
        $stmt->execute([$nome]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_profile'] = $user['perfil'];
            header("Location: index.php");
            exit();
        } else {
            $error = 'Usuário ou senha inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-primary text-white text-center p-3">
        <h1>Sistema de Gestão de Empréstimos</h1>
    </header>

    <main class="container d-flex flex-grow-1 justify-content-center align-items-center">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Login</h3>
                    <form action="login.php" method="post">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-light text-center p-3 mt-auto">
        <p>© 2025 SGE - Sistema de Gestão de Equipamentos</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Erro de Login',
            text: '<?= addslashes($error) ?>'
        });
    </script>
    <?php endif; ?>
</body>
</html>
