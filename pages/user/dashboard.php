<?php
/**
 * Dashboard - Usuário
 * 
 * Esta é a página principal após o login do usuário.
 * Implemente aqui:
 * - Informações do usuário
 * - Resumo de atividades
 * - Links para funcionalidades principais
 * - Estatísticas relevantes
 */

// Incluir configurações
require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// Verificar se usuário está logado
if (!usuarioLogado()) {
    redirecionar(SITE_URL . '/pages/user/login.php');
}

// Buscar produtos do banco de dados
$produtos = [];
$total_produtos = 0;
$produtos_baixo_estoque = 0;

try {
    $stmt = $conn->prepare('SELECT * FROM produto ORDER BY dt_criado DESC');
    $stmt->execute();
    $result = $stmt->get_result();
    $produtos = $result->fetch_all(MYSQLI_ASSOC);
    $total_produtos = count($produtos);
    
    // Contar produtos com estoque baixo
    $stmt_estoque = $conn->prepare('SELECT COUNT(*) as total FROM produto WHERE estoque <= limite_min');
    $stmt_estoque->execute();
    $result_estoque = $stmt_estoque->get_result();
    $produtos_baixo_estoque = $result_estoque->fetch_assoc()['total'];
    
} catch (Exception $e) {
    $erro_produtos = "Erro ao carregar produtos: " . $e->getMessage();
}

// Definir título da página
$page_title = 'Dashboard do Usuário';

// Incluir header
include '../../includes/header.php';
?>

<?php
// Mensagens agora são exibidas via modal no footer (evitar imprimir alerts aqui)
?>

<div class="container mt-4">
    <!-- Cabeçalho do dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                    <p class="text-muted mb-0">
                        Bem-vindo, <?php echo $_SESSION['usuario_nome'] ?? $_SESSION['user_name'] ?? 'Usuário'; ?>!
                    </p>
                </div>
                <div>
                    <span class="badge bg-success">Online</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Barra de pesquisa -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="pesquisar.php" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" placeholder="Pesquisar itens..." aria-label="Pesquisar itens">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cards de estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total de Itens</h5>
                            <h3 class="mb-0"><?php echo $total_produtos; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Em Estoque</h5>
                            <h3 class="mb-0"><?php echo $total_produtos - $produtos_baixo_estoque; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Estoque Baixo</h5>
                            <h3 class="mb-0"><?php echo $produtos_baixo_estoque; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Este Mês</h5>
                            <h3 class="mb-0"><?php echo $total_produtos; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Conteúdo principal -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-boxes me-2"></i>Produtos Cadastrados</h5>
                    <span class="badge bg-primary"><?php echo $total_produtos; ?> itens</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($erro_produtos)): ?>
                        <div class="alert alert-danger"><?php echo $erro_produtos; ?></div>
                    <?php elseif (empty($produtos)): ?>
                        <p class="text-muted">Nenhum produto cadastrado.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Limite</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos as $produto): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($produto['imagem'])): ?>
                                                        <img src="../../imgs/<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                                             alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                                                             class="rounded me-2" width="40" height="40" style="object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-box text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($produto['nome']); ?></strong>
                                                        <?php if (!empty($produto['descricao'])): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($produto['descricao']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge <?php echo $produto['estoque'] <= $produto['limite_min'] ? 'bg-warning' : 'bg-success'; ?>">
                                                    <?php echo $produto['estoque']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $produto['limite_min']; ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" data-bs-target="#editarProdutoModal" 
                                                            data-id="<?php echo $produto['id']; ?>"
                                                            data-nome="<?php echo htmlspecialchars($produto['nome']); ?>"
                                                            data-preco="<?php echo $produto['preco']; ?>"
                                                            data-descricao="<?php echo htmlspecialchars($produto['descricao']); ?>"
                                                            data-estoque="<?php echo $produto['estoque']; ?>"
                                                            data-limite="<?php echo $produto['limite_min']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            data-bs-toggle="modal" data-bs-target="#excluirProdutoModal" 
                                                            data-id="<?php echo $produto['id']; ?>"
                                                            data-nome="<?php echo htmlspecialchars($produto['nome']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-cog me-2"></i>Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="produto.php" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Novo Item
                        </a>
                        <a href="relatorios.php" class="btn btn-outline-info">
                            <i class="fas fa-file-export me-2"></i>Relatórios
                        </a>
                        <a href="editar.php" class="btn btn-outline-warning">
                            <i class="fas fa-user-edit me-2"></i>Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Produto -->
<div class="modal fade" id="editarProdutoModal" tabindex="-1" aria-labelledby="editarProdutoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarProdutoModalLabel">Editar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formEditarProduto" method="POST" action="editar_produto.php">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editar_id">
                    <div class="mb-3">
                        <label for="editar_nome" class="form-label">Nome do Produto</label>
                        <input type="text" class="form-control" id="editar_nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_preco" class="form-label">Preço (R$)</label>
                        <input type="number" step="0.01" class="form-control" id="editar_preco" name="preco" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="editar_descricao" name="descricao" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editar_estoque" class="form-label">Estoque</label>
                                <input type="number" class="form-control" id="editar_estoque" name="estoque" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editar_limite" class="form-label">Limite Mínimo</label>
                                <input type="number" class="form-control" id="editar_limite" name="limite_min" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Excluir Produto -->
<div class="modal fade" id="excluirProdutoModal" tabindex="-1" aria-labelledby="excluirProdutoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="excluirProdutoModalLabel">Excluir Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formExcluirProduto" method="POST" action="excluir_produto.php">
                <div class="modal-body">
                    <input type="hidden" name="id" id="excluir_id">
                    <p>Tem certeza que deseja excluir o produto <strong id="excluir_nome"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script para popular os modais com os dados dos produtos
document.addEventListener('DOMContentLoaded', function() {
    // Modal de edição
    const editarModal = document.getElementById('editarProdutoModal');
    editarModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('editar_id').value = button.getAttribute('data-id');
        document.getElementById('editar_nome').value = button.getAttribute('data-nome');
        document.getElementById('editar_preco').value = button.getAttribute('data-preco');
        document.getElementById('editar_descricao').value = button.getAttribute('data-descricao');
        document.getElementById('editar_estoque').value = button.getAttribute('data-estoque');
        document.getElementById('editar_limite').value = button.getAttribute('data-limite');
    });

    // Modal de exclusão
    const excluirModal = document.getElementById('excluirProdutoModal');
    excluirModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('excluir_id').value = button.getAttribute('data-id');
        document.getElementById('excluir_nome').textContent = button.getAttribute('data-nome');
    });
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>