<?php
/**
 * Página de Pesquisa - Produtos
 * 
 * Esta página permite pesquisar produtos no sistema.
 */

// Incluir configurações
require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// Verificar se usuário está logado
if (!usuarioLogado()) {
    redirecionar(SITE_URL . '/pages/user/login.php');
}

// Definir título da página
$page_title = 'Pesquisar Produtos';

// Obter termo de pesquisa
$termo_pesquisa = sanitizar($_GET['q'] ?? '');
$resultados = [];
$total_resultados = 0;

// Buscar produtos se houver termo de pesquisa
if (!empty($termo_pesquisa)) {
    try {
        // Buscar somente produtos ativos
        $stmt = $conn->prepare(''
            SELECT * FROM produto 
            WHERE (nome LIKE ? OR descricao LIKE ?) AND ativo = 1
            ORDER BY 
                CASE 
                    WHEN nome LIKE ? THEN 1 
                    WHEN descricao LIKE ? THEN 2 
                    ELSE 3 
                END,
                nome ASC
        '');
        
        $termo_like = "%$termo_pesquisa%";
        $stmt->bind_param('ssss', $termo_like, $termo_like, $termo_like, $termo_like);
        $stmt->execute();
        $result = $stmt->get_result();
        $resultados = $result->fetch_all(MYSQLI_ASSOC);
        $total_resultados = count($resultados);
        
    } catch (Exception $e) {
        $erro_pesquisa = "Erro ao pesquisar produtos: " . $e->getMessage();
    }
}

// Incluir header
include '../../includes/header.php';
?>

<div class="container mt-4">
    <!-- Cabeçalho -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-search me-2"></i>Pesquisar Produtos</h2>
                    <p class="text-muted mb-0">
                        Encontre produtos no sistema
                    </p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de pesquisa -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="pesquisar.php">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" 
                                   value="<?php echo htmlspecialchars($termo_pesquisa); ?>" 
                                   placeholder="Digite o nome ou descrição do produto..." 
                                   aria-label="Pesquisar produtos">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Pesquisar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        <?php if (!empty($termo_pesquisa)): ?>
                            Resultados da pesquisa para "<?php echo htmlspecialchars($termo_pesquisa); ?>"
                        <?php else: ?>
                            Resultados da Pesquisa
                        <?php endif; ?>
                    </h5>
                    <?php if (!empty($termo_pesquisa)): ?>
                        <span class="badge bg-primary"><?php echo $total_resultados; ?> resultado(s)</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($erro_pesquisa)): ?>
                        <div class="alert alert-danger"><?php echo $erro_pesquisa; ?></div>
                    <?php elseif (empty($termo_pesquisa)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <p>Digite um termo de pesquisa para encontrar produtos.</p>
                        </div>
                    <?php elseif (empty($resultados)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <p>Nenhum produto encontrado para "<?php echo htmlspecialchars($termo_pesquisa); ?>".</p>
                            <p>Tente usar termos diferentes ou verifique a ortografia.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Limite</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultados as $produto): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($produto['imagem'])): ?>
                                                        <img src="../../imgs/<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                                             alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                                                             class="rounded me-3" width="50" height="50" style="object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                                                             style="width: 50px; height: 50px;">
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
                                                <?php if ($produto['estoque'] <= $produto['limite_min']): ?>
                                                    <span class="badge bg-warning">Estoque Baixo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Normal</span>
                                                <?php endif; ?>
                                            </td>
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
    </div>
</div>

<!-- Modal Editar Produto (mesmo do dashboard) -->
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

<!-- Modal Excluir Produto (mesmo do dashboard) -->
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