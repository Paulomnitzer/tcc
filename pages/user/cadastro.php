<?php
/**
 * Página de Cadastro - Usuário
 */
require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$page_title = 'Cadastro de Usuário';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar dados
    $nome = sanitizar($_POST['nome'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $telefone = sanitizar($_POST['telefone'] ?? '');
    $data_nascimento = sanitizar($_POST['data_nascimento'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $termos = isset($_POST['termos']);

    $erros = [];
    
    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erros[] = 'Preencha todos os campos obrigatórios.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido.';
    }
    
    if ($senha !== $confirmar_senha) {
        $erros[] = 'As senhas não coincidem.';
    }
    
    if (strlen($senha) < 8) {
        $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
    }
    
    if (!$termos) {
        $erros[] = 'Você deve aceitar os termos de uso.';
    }

    // Verificar se email já existe
    if (empty($erros)) {
        $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $erros[] = 'Este email já está cadastrado.';
        }
        $stmt->close();
    }

    // Se não houver erros, inserir usuário
    if (empty($erros)) {
        // ✅ Senha sem hash (conforme solicitado)
        $stmt = $conn->prepare('INSERT INTO usuarios (nome, email, telefone, dt_nascimento, senha) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $nome, $email, $telefone, $data_nascimento, $senha);
        
        if ($stmt->execute()) {
            $sucesso = 'Cadastro realizado com sucesso!';
            // Limpar formulário após sucesso
            $nome = $email = $telefone = $data_nascimento = '';
        } else {
            $erros[] = 'Erro ao cadastrar usuário: ' . $stmt->error;
        }
        $stmt->close();
    }
}

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
                    
                    <!-- ✅ Exibir mensagens de erro/sucesso -->
                    <?php if (!empty($erros)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($erros as $erro): ?>
                                    <li><?php echo htmlspecialchars($erro); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($sucesso)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($sucesso); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome Completo:</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo htmlspecialchars($nome ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone:</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" 
                                       value="<?php echo htmlspecialchars($telefone ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="data_nascimento" class="form-label">Data de Nascimento:</label>
                                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" 
                                       value="<?php echo htmlspecialchars($data_nascimento ?? ''); ?>">
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
                            <input type="checkbox" class="form-check-input" id="termos" name="termos" 
                                   <?php echo (isset($_POST['termos']) ? 'checked' : ''); ?> required>
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
include '../../includes/footer.php';
?>