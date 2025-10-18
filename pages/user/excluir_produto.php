<?php
/**
 * Página para excluir produto
 */

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// Verificar se usuário está logado
if (!usuarioLogado()) {
    redirecionar(SITE_URL . '/pages/user/login.php');
}

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    
    $erros = [];
    
    // Validações
    if (empty($id)) {
        $erros[] = 'ID do produto não informado.';
    }

    // Se não houver erros, excluir produto
    if (empty($erros)) {
        try {
            // Buscar informações do produto para excluir a imagem
            $stmt_select = $conn->prepare('SELECT imagem FROM produto WHERE id = ?');
            $stmt_select->bind_param('i', $id);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            $produto = $result->fetch_assoc();
            $stmt_select->close();
            
            // Excluir produto do banco
            $stmt_delete = $conn->prepare('DELETE FROM produto WHERE id = ?');
            $stmt_delete->bind_param('i', $id);
            
            if ($stmt_delete->execute()) {
                // Se excluiu com sucesso, excluir a imagem também
                if (!empty($produto['imagem'])) {
                    $caminho_imagem = '../../imgs/' . $produto['imagem'];
                    if (file_exists($caminho_imagem)) {
                        unlink($caminho_imagem);
                    }
                }
                $_SESSION['sucesso'] = 'Produto excluído com sucesso!';
            } else {
                $erros[] = 'Erro ao excluir produto: ' . $stmt_delete->error;
            }
            $stmt_delete->close();
        } catch (Exception $e) {
            $erros[] = 'Erro ao excluir produto: ' . $e->getMessage();
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