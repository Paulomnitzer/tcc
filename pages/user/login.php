<?php
// Incluir configurações e funções
require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// Iniciar sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if ($email != '' && $senha != '') {
        // Consulta usando mysqli
        $stmt = $conn->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();

        if ($usuario && $usuario['senha'] === $senha) { // senha em texto puro
            session_start();
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nome'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_role'] = 'user';
            $_SESSION['usuario_logado'] = true;

            header('Location: ../../pages/user/dashboard.php');
            exit();
        } else {
            $erro = 'Credenciais inválidas. Tente novamente.';
        }
    } else {
        $erro = 'Preencha todos os campos.';
    }
}


// Incluir header
include '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4><i class="fas fa-user me-2"></i>Login de Usuário</h4>
                </div>
                <div class="card-body">
                    <?php if(!empty($erro)): ?>
                        <div class="alert alert-danger"><?= $erro ?></div>
                    <?php endif; ?>

                    <!-- Formulário de login -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha:</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="lembrar" name="lembrar">
                            <label class="form-check-label" for="lembrar">Lembrar-me</label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Entrar
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <!-- Links adicionais -->
                    <div class="text-center">
                        <p class="mb-2">
                            <a href="esqueci_senha.php" class="text-decoration-none">
                                <i class="fas fa-key me-1"></i>Esqueci minha senha
                            </a>
                        </p>
                        <p class="mb-0">
                            Não tem conta? 
                            <a href="cadastro.php" class="text-decoration-none">
                                <i class="fas fa-user-plus me-1"></i>Cadastre-se
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>
