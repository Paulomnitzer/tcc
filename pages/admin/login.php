<?php
/**
 * Página de Login - Administrador
 * 
 * Esta página permite que administradores façam login no sistema.
 * Implemente aqui:
 * - Formulário de login administrativo
 * - Validação de credenciais de admin
 * - Redirecionamento para painel administrativo
 * - Segurança adicional (se necessário)
 */

// Incluir configurações
require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// Definir título da página
$page_title = 'Login Administrativo';

// Processar formulário de login (implementar lógica aqui)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if($email == "admin@admin" && $senha == "admin"){
    // Simular criação de sessão
    $_SESSION['user_id'] = 1; // ID do usuário
    $_SESSION['user_name'] = "Usuário Admin Teste"; // Nome do usuário
    $_SESSION['user_email'] = $email; // Email do usuário
    $_SESSION['user_role'] = 'user'; // Papel do usuário
    $_SESSION['usuario_logado'] = true; // Marcar como logado
    $_SESSION['usuario_tipo'] = "admin"; // Marcar como logado

    // Redirecionar para área do usuário
    header('Location: ../../pages/admin/dashboard.php');
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
            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white text-center">
                    <h4><i class="fas fa-shield-alt me-2"></i>Acesso Administrativo</h4>
                </div>
                <div class="card-body">
                    <!-- Aviso de segurança -->
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Área Restrita:</strong> Acesso apenas para administradores autorizados.
                    </div>
                    
                    <!-- Formulário de login -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Administrativo:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha:</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="confirmar_admin" name="confirmar_admin" required>
                                <label class="form-check-label" for="confirmar_admin">
                                    Confirmo que sou um administrador autorizado
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-sign-in-alt me-2"></i>Acessar Painel
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <!-- Links adicionaissss-->
                    <div class="text-center">
                        <p class="mb-2">

                        </p>
                        <p class="mb-0">
                            <a href="../../index.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Voltar ao site
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
