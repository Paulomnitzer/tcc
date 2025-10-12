<?php
/**
 * Página de Cadastro - Usuário
 * 
 * Esta página permite que novos usuários se cadastrem no sistema.
 * Implemente aqui:
 * - Formulário de cadastro
 * - Validação de dados
 * - Inserção no banco de dados
 * - Confirmação de cadastro
 */

// Incluir configurações
require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// Definir título da página
$page_title = 'Cadastro de Usuário';

// Processar formulário de cadastro (implementar lógica aqui)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // TODO: Implementar validação de cadastro
    // $nome = sanitizar($_POST['nome']);
    // $email = sanitizar($_POST['email']);
    // $senha = $_POST['senha'];
    // $confirmar_senha = $_POST['confirmar_senha'];
    
    // TODO: Validar dados
    // TODO: Verificar se email já existe
    // TODO: Inserir usuário no banco de dados
    // TODO: Enviar email de confirmação (opcional)
}

// Incluir header
include '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4><i class="fas fa-user-plus me-2"></i>Cadastro de Usuário</h4>
                </div>
                <div class="card-body">
                    <!-- Formulário de cadastro -->
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome Completo:</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone:</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="data_nascimento" class="form-label">Data de Nascimento:</label>
                                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="senha" class="form-label">Senha:</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                                <div class="form-text">Mínimo 8 caracteres</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirmar_senha" class="form-label">Confirmar Senha:</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="termos" name="termos" required>
                            <label class="form-check-label" for="termos">
                                Aceito os <a href="#" target="_blank">termos de uso</a> e 
                                <a href="#" target="_blank">política de privacidade</a>
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus me-2"></i>Cadastrar
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <!-- Link para login -->
                    <div class="text-center">
                        <p class="mb-0">
                            Já tem conta? 
                            <a href="login.php" class="text-decoration-none">
                                <i class="fas fa-sign-in-alt me-1"></i>Faça login
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
