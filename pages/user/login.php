<?php
/**
 * Página de Login - Usuário
 * 
 * Esta página permite que usuários comuns façam login no sistema.
 * Implemente aqui:
 * - Formulário de login
 * - Validação de credenciais
 * - Redirecionamento após login
 * - Links para recuperação de senha e cadastro
 */

// Incluir configurações
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';



// Definir título da página
$page_title = 'Login de Usuário';

// Processar formulário de login (implementar lógica aqui)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if($email == "teste@t.com" && $senha == "123"){
    // Simular criação de sessão
    $_SESSION['user_id'] = 1; // ID do usuário
    $_SESSION['user_name'] = "Usuário Teste"; // Nome do usuário
    $_SESSION['user_email'] = $email; // Email do usuário
    $_SESSION['user_role'] = 'user'; // Papel do usuário
    $_SESSION['usuario_logado'] = true; // Marcar como logado

    // Redirecionar para área do usuário
    header('Location: ../../pages/user/dashboard.php');
    exit();
} else if($email != '' || $senha != ''){
    echo "<script>alert('Credenciais inválidas. Tente novamente.');</script>";
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
