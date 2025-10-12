<?php
/**
 * Página de Cadastro - Produto
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
$page_title = 'Cadastro de Produto';

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
                    <h4></i>Cadastro de Produto</h4>
                </div>
                <div class="card-body">
                    <!-- Formulário de cadastro -->
                    <form method="POST" action="">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome:</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="preco" class="form-label">Preço:</label>
                                <input type="number" step="0.01" class="form-control" id="preco" name="preco">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="descricao" class="form-label">Descrição:</label>
                                <input type="text" class="form-control" id="descricao" name="descricao">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estoque" class="form-label">Estoque:</label>
                                <input type="number" class="form-control" id="estoque" name="estoque" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="limite" class="form-label">Limite:</label>
                                <input type="number" class="form-control" id="limite" name="limite">
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success">
                                Cadastrar
                            </button>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-secondary">
                                <i class=""></i>Cancelar
                            </button>
                        </div>
                    </form>

                    <hr>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>