<?php
/**
 * Página para editar produto
 */

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// Verificar se usuário está logado
if (!usuarioLogado()) {
    redirecionar(SITE_URL . '/pages/user/login.php');
}

$page_title = 'Editar Produto';

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $nome = sanitizar($_POST['nome'] ?? '');
    $preco = $_POST['preco'] ?? '';
    $descricao = sanitizar($_POST['descricao'] ?? '');
    $estoque = $_POST['estoque'] ?? '';
    $limite_min = $_POST['limite_min'] ?? '';

    $erros = [];
    
    // Validações
    if (empty($id) || empty($nome) || empty($estoque) || empty($limite_min)) {
        $erros[] = 'Preencha todos os campos obrigatórios.';
    }
    
    if (!empty($preco) && !is_numeric($preco)) {
        $erros[] = 'Preço deve ser um valor numérico.';
    }
    
    if (!is_numeric($estoque)) {
        $erros[] = 'Estoque deve ser um valor numérico.';
    }
    
    if (!is_numeric($limite_min)) {
        $erros[] = 'Limite mínimo deve ser um valor numérico.';
    }

    // Se não houver erros, atualizar produto
    if (empty($erros)) {
        try {
            $stmt = $conn->prepare('UPDATE produto SET nome = ?, preco = ?, descricao = ?, estoque = ?, limite_min = ? WHERE id = ?');
            $stmt->bind_param('sdsiii', $nome, $preco, $descricao, $estoque, $limite_min, $id);
            
            if ($stmt->execute()) {
                $_SESSION['sucesso'] = 'Produto atualizado com sucesso!';
            } else {
                $erros[] = 'Erro ao atualizar produto: ' . $stmt->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            $erros[] = 'Erro ao atualizar produto: ' . $e->getMessage();
        }
    }

    // Se houver erros, armazenar para exibir no dashboard
    if (!empty($erros)) {
        $_SESSION['erros'] = $erros;
    }
    
    // Redirecionar de volta para o dashboard
    redirecionar(SITE_URL . '/pages/user/dashboard.php');
    exit;
} else {
    // Se não for POST, redirecionar
    redirecionar(SITE_URL . '/pages/user/dashboard.php');
}
?>