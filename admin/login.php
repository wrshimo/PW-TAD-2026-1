<?php
require_once '../conexao.php';
require_once '../includes/layout.php';
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Busca segura com Prepared Statements
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :u");
    $stmt->execute([':u' => $usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        // SEGURANÇA: Regenera o ID para evitar Session Hijacking
        session_regenerate_id(true);
        
        $_SESSION['logado'] = true;
        $_SESSION['admin_nome'] = $user['nome'];
        $_SESSION['admin_id'] = $user['id'];
        
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['flash'] = ['msg' => 'Usuário ou senha incorretos!', 'tipo' => 'danger'];
        header('Location: login.php');
        exit;
    }
}

render_head("Loja do Shimo - Login");
render_header("admin")
?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-4">Acesso Administrativo</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Usuário</label>
                                <input type="text" name="usuario" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <input type="password" name="senha" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
render_footer();