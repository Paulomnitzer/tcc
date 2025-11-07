<?php
require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!usuarioLogado() || !usuarioAdmin()) {
    redirecionar(SITE_URL . '/pages/admin/login.php');
}

$page_title = 'Gerenciar Produtos';
include '../../includes/header.php';

// Processar operações CRUD
$mensagem = '';
$tipoMensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['adicionar'])) {
        // Adicionar produto
        try {
            $nome = sanitizar($_POST['nome'] ?? '');
            $preco = $_POST['preco'] ?? '';
            $descricao = sanitizar($_POST['descricao'] ?? '');
            $categoria = sanitizar($_POST['categoria'] ?? '');
            $estoque = $_POST['estoque'] ?? '';
            $limite_min = $_POST['limite_min'] ?? '';
            $imagem = '';

            // Upload da imagem
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $arquivo = $_FILES['imagem'];
                $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($ext, $permitidas) && $arquivo['size'] <= 1048576) {
                    $dir = '../../imgs/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);

                    $nomeArquivo = uniqid() . '_' . time() . '.' . $ext;
                    $destino = $dir . $nomeArquivo;

                    if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
                        $imagem = $nomeArquivo;
                    }
                }
            }

            // Verificar se produto já existe
            $stmt = $conn->prepare("SELECT id FROM produto WHERE nome = ?");
            $stmt->bind_param("s", $nome);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $mensagem = 'Erro: Já existe um produto com este nome!';
                $tipoMensagem = 'danger';
            } else {
                $stmt = $conn->prepare("INSERT INTO produto (nome, preco, descricao, categoria, estoque, limite_min, imagem) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sdssiis", 
                    $nome,
                    $preco,
                    $descricao,
                    $categoria,
                    $estoque,
                    $limite_min,
                    $imagem
                );
                $stmt->execute();
                $mensagem = 'Produto adicionado com sucesso!';
                $tipoMensagem = 'success';
            }
            $stmt->close();
        } catch (Exception $e) {
            $mensagem = 'Erro ao adicionar produto: ' . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    } elseif (isset($_POST['editar'])) {
        // Editar produto
        try {
            $id = $_POST['id'];
            $nome = sanitizar($_POST['nome'] ?? '');
            $preco = $_POST['preco'] ?? '';
            $descricao = sanitizar($_POST['descricao'] ?? '');
            $categoria = sanitizar($_POST['categoria'] ?? '');
            $estoque = $_POST['estoque'] ?? '';
            $limite_min = $_POST['limite_min'] ?? '';
            $imagem = $_POST['imagem_atual'] ?? '';

            // Upload da nova imagem (se fornecida)
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $arquivo = $_FILES['imagem'];
                $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($ext, $permitidas) && $arquivo['size'] <= 1048576) {
                    $dir = '../../imgs/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);

                    $nomeArquivo = uniqid() . '_' . time() . '.' . $ext;
                    $destino = $dir . $nomeArquivo;

                    if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
                        // Remover imagem antiga se existir
                        if (!empty($imagem) && file_exists($dir . $imagem)) {
                            unlink($dir . $imagem);
                        }
                        $imagem = $nomeArquivo;
                    }
                }
            }

            // Verificar se nome já existe (excluindo o produto atual)
            $stmt = $conn->prepare("SELECT id FROM produto WHERE nome = ? AND id != ?");
            $stmt->bind_param("si", $nome, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $mensagem = 'Erro: Já existe outro produto com este nome!';
                $tipoMensagem = 'danger';
            } else {
                $stmt = $conn->prepare("UPDATE produto SET nome=?, preco=?, descricao=?, categoria=?, estoque=?, limite_min=?, imagem=? WHERE id=?");
                $stmt->bind_param("sdssiisi",
                    $nome,
                    $preco,
                    $descricao,
                    $categoria,
                    $estoque,
                    $limite_min,
                    $imagem,
                    $id
                );
                $stmt->execute();
                $mensagem = 'Produto atualizado com sucesso!';
                $tipoMensagem = 'success';
            }
            $stmt->close();
        } catch (Exception $e) {
            $mensagem = 'Erro ao atualizar produto: ' . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    }
} elseif (isset($_GET['deletar'])) {
    // Deletar produto
    try {
        $produto_id = $_GET['deletar'];
        
        // Buscar imagem para deletar
        $stmt = $conn->prepare("SELECT imagem FROM produto WHERE id = ?");
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $produto = $result->fetch_assoc();
        
        // Deletar produto
        $stmt = $conn->prepare("DELETE FROM produto WHERE id=?");
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        
        // Deletar imagem se existir
        if (!empty($produto['imagem'])) {
            $caminho_imagem = '../../imgs/' . $produto['imagem'];
            if (file_exists($caminho_imagem)) {
                unlink($caminho_imagem);
            }
        }
        
        $mensagem = 'Produto deletado com sucesso!';
        $tipoMensagem = 'success';
        $stmt->close();
    } catch (Exception $e) {
        $mensagem = 'Erro ao deletar produto: ' . $e->getMessage();
        $tipoMensagem = 'danger';
    }
} elseif (isset($_GET['toggle_status'])) {
    // Alternar status ativo/inativo
    try {
        $produto_id = $_GET['toggle_status'];
        
        // Buscar status atual
        $stmt = $conn->prepare("SELECT ativo FROM produto WHERE id = ?");
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $produto = $result->fetch_assoc();
            $novo_status = $produto['ativo'] ? 0 : 1;
            
            // Atualizar status
            $stmt = $conn->prepare("UPDATE produto SET ativo = ? WHERE id = ?");
            $stmt->bind_param("ii", $novo_status, $produto_id);
            $stmt->execute();
            
            $status_text = $novo_status ? 'ativado' : 'inativado';
            $mensagem = "Produto {$status_text} com sucesso!";
            $tipoMensagem = 'success';
        } else {
            $mensagem = 'Produto não encontrado!';
            $tipoMensagem = 'danger';
        }
        $stmt->close();
    } catch (Exception $e) {
        $mensagem = 'Erro ao alterar status do produto: ' . $e->getMessage();
        $tipoMensagem = 'danger';
    }
}

// Buscar produtos com paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Construir query base
$whereConditions = [];
$params = [];
$types = '';

// Filtros
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $whereConditions[] = "(nome LIKE ? OR descricao LIKE ? OR categoria LIKE ?)";
    $busca = "%" . $_GET['busca'] . "%";
    $params[] = $busca;
    $params[] = $busca;
    $params[] = $busca;
    $types .= 'sss';
}

if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $whereConditions[] = "categoria LIKE ?";
    $params[] = "%" . $_GET['categoria'] . "%";
    $types .= 's';
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $whereConditions[] = "ativo = ?";
    $params[] = $_GET['status'];
    $types .= 'i';
}

// Filtro de estoque baixo
if (isset($_GET['estoque_baixo']) && $_GET['estoque_baixo'] == '1') {
    $whereConditions[] = "estoque <= limite_min";
}

// Ordenação
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'novos';
switch ($ordenar) {
    case 'antigos':
        $orderBy = "dt_criado ASC";
        break;
    case 'nome':
        $orderBy = "nome ASC";
        break;
    case 'preco_asc':
        $orderBy = "preco ASC";
        break;
    case 'preco_desc':
        $orderBy = "preco DESC";
        break;
    case 'estoque':
        $orderBy = "estoque ASC";
        break;
    default:
        $orderBy = "dt_criado DESC";
        break;
}

// Construir query WHERE
$whereSQL = '';
if (!empty($whereConditions)) {
    $whereSQL = "WHERE " . implode(" AND ", $whereConditions);
}

try {
    // Total de produtos para paginação
    $sqlCount = "SELECT COUNT(*) as total FROM produto $whereSQL";
    if (!empty($whereConditions)) {
        $stmt = $conn->prepare($sqlCount);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sqlCount);
    }
    $row = $result->fetch_assoc();
    $totalProdutos = $row['total'];
    $totalPaginas = ceil($totalProdutos / $limite);

    // Buscar produtos
    $sql = "SELECT id, nome, descricao, categoria, preco, estoque, limite_min, imagem, ativo, dt_criado 
            FROM produto 
            $whereSQL 
            ORDER BY $orderBy 
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($whereConditions)) {
        $types .= 'ii';
        $params[] = $limite;
        $params[] = $offset;
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param("ii", $limite, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    $stmt->close();
    
} catch (Exception $e) {
    $produtos = [];
    $totalPaginas = 1;
    $mensagem = 'Erro ao carregar produtos: ' . $e->getMessage();
    $tipoMensagem = 'danger';
}

// Buscar categorias únicas para o filtro
$categorias = [];
try {
    $result = $conn->query("SELECT DISTINCT categoria FROM produto WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria");
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row['categoria'];
    }
} catch (Exception $e) {
    // Ignora erro de categorias
}

// Buscar produto para edição
$produtoEdicao = null;
if (isset($_GET['editar'])) {
    try {
        $stmt = $conn->prepare("SELECT id, nome, descricao, categoria, preco, estoque, limite_min, imagem, ativo FROM produto WHERE id=?");
        $stmt->bind_param("i", $_GET['editar']);
        $stmt->execute();
        $result = $stmt->get_result();
        $produtoEdicao = $result->fetch_assoc();
        $stmt->close();
    } catch (Exception $e) {
        $mensagem = 'Erro ao carregar produto: ' . $e->getMessage();
        $tipoMensagem = 'danger';
    }
}
?>

<style>
.table th {
    border-top: none;
    font-weight: 600;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}

.badge {
    font-size: 0.75em;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.075);
}

.status-ativo {
    color: #198754;
}

.status-inativo {
    color: #dc3545;
}

.estoque-baixo {
    background-color: #fff3cd !important;
}

.imagem-produto {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group {
        display: flex;
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 0.25rem;
    }
    
    .imagem-produto {
        width: 40px;
        height: 40px;
    }
}
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-boxes me-2"></i>Gerenciar Produtos</h2>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalProduto">
                    <i class="fas fa-plus me-2"></i>Novo Produto
                </button>
            </div>

            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipoMensagem; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filtros e Busca -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="busca" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="busca" name="busca" 
                                   value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>" 
                                   placeholder="Nome, descrição ou categoria...">
                        </div>
                        <div class="col-md-2">
                            <label for="categoria" class="form-label">Categoria</label>
                            <select class="form-control" id="categoria" name="categoria">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                                        <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $cat) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : ''; ?>>Ativo</option>
                                <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : ''; ?>>Inativo</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="ordenar" class="form-label">Ordenar por</label>
                            <select class="form-control" id="ordenar" name="ordenar">
                                <option value="novos" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'novos') ? 'selected' : ''; ?>>Mais Recentes</option>
                                <option value="antigos" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'antigos') ? 'selected' : ''; ?>>Mais Antigos</option>
                                <option value="nome" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'nome') ? 'selected' : ''; ?>>Nome A-Z</option>
                                <option value="preco_asc" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'preco_asc') ? 'selected' : ''; ?>>Preço Menor</option>
                                <option value="preco_desc" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'preco_desc') ? 'selected' : ''; ?>>Preço Maior</option>
                                <option value="estoque" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'estoque') ? 'selected' : ''; ?>>Estoque Baixo</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="estoque_baixo" name="estoque_baixo" value="1" 
                                    <?php echo (isset($_GET['estoque_baixo']) && $_GET['estoque_baixo'] == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="estoque_baixo">
                                    Apenas estoque baixo
                                </label>
                            </div>
                            <button type="submit" class="btn btn-outline-primary w-100 me-2">
                                <i class="fas fa-search me-2"></i>Filtrar
                            </button>
                            <a href="produtos.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Lista de Produtos (<?php echo $totalProdutos; ?> total)</h5>
                </div>
                <div class="card-body">
                    <?php if (count($produtos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Imagem</th>
                                        <th>Nome</th>
                                        <th>Categoria</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Limite Mín.</th>
                                        <th>Status</th>
                                        <th>Criado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos as $produto): 
                                        $estoque_baixo = $produto['estoque'] <= $produto['limite_min'];
                                        $classe_linha = $estoque_baixo ? 'estoque-baixo' : '';
                                    ?>
                                        <tr class="<?php echo $classe_linha; ?>">
                                            <td>
                                                <?php if (!empty($produto['imagem'])): ?>
                                                    <img src="../../imgs/<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                                         alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                                                         class="imagem-produto">
                                                <?php else: ?>
                                                    <div class="imagem-produto bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($produto['nome']); ?></div>
                                                <?php if (!empty($produto['descricao'])): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($produto['descricao'], 0, 50)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($produto['categoria'] ?? 'N/A'); ?></td>
                                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="<?php echo $estoque_baixo ? 'text-danger fw-bold' : ''; ?>">
                                                    <?php echo $produto['estoque']; ?>
                                                    <?php if ($estoque_baixo): ?>
                                                        <i class="fas fa-exclamation-triangle text-danger ms-1" title="Estoque baixo!"></i>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $produto['limite_min']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $produto['ativo'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $produto['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($produto['dt_criado'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="?editar=<?php echo $produto['id']; ?>" 
                                                       class="btn btn-warning" 
                                                       data-bs-toggle="modal" 
                                                       data-bs-target="#modalProduto"
                                                       title="Editar produto">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?toggle_status=<?php echo $produto['id']; ?>" 
                                                       class="btn <?php echo $produto['ativo'] ? 'btn-secondary' : 'btn-success'; ?>"
                                                       title="<?php echo $produto['ativo'] ? 'Inativar produto' : 'Ativar produto'; ?>"
                                                       onclick="return confirm('Tem certeza que deseja <?php echo $produto['ativo'] ? 'inativar' : 'ativar'; ?> o produto <?php echo htmlspecialchars($produto['nome']); ?>?')">
                                                        <i class="fas <?php echo $produto['ativo'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </a>
                                                    <a href="?deletar=<?php echo $produto['id']; ?>" 
                                                       class="btn btn-danger" 
                                                       onclick="return confirm('Tem certeza que deseja deletar o produto <?php echo htmlspecialchars($produto['nome']); ?>? Esta ação não pode ser desfeita.')"
                                                       title="Deletar produto">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <?php if ($totalPaginas > 1): ?>
                            <nav aria-label="Navegação de páginas">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagina > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>" aria-label="Anterior">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                        <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($pagina < $totalPaginas): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>" aria-label="Próxima">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum produto encontrado</h5>
                            <p class="text-muted">Clique no botão "Novo Produto" para adicionar o primeiro produto.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Adicionar/Editar Produto -->
<div class="modal fade" id="modalProduto" tabindex="-1" aria-labelledby="modalProdutoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="formProduto" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProdutoLabel">
                        <?php if ($produtoEdicao): ?>
                            <i class="fas fa-edit me-2"></i>Editar Produto: <?php echo htmlspecialchars($produtoEdicao['nome']); ?>
                        <?php else: ?>
                            <i class="fas fa-plus me-2"></i>Adicionar Novo Produto
                        <?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($produtoEdicao): ?>
                        <input type="hidden" name="id" value="<?php echo $produtoEdicao['id']; ?>">
                        <input type="hidden" name="imagem_atual" value="<?php echo htmlspecialchars($produtoEdicao['imagem'] ?? ''); ?>">
                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Editando produto ID: <?php echo $produtoEdicao['id']; ?>
                            </small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-2"></i>
                                Preencha os dados para criar um novo produto
                            </small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Produto *</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo $produtoEdicao ? htmlspecialchars($produtoEdicao['nome']) : ''; ?>" 
                                       required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="preco" class="form-label">Preço (R$) *</label>
                                <input type="number" step="0.01" class="form-control" id="preco" name="preco" 
                                       value="<?php echo $produtoEdicao ? $produtoEdicao['preco'] : ''; ?>" 
                                       required min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoria *</label>
                                <input type="text" class="form-control" id="categoria" name="categoria" 
                                       value="<?php echo $produtoEdicao ? htmlspecialchars($produtoEdicao['categoria']) : ''; ?>" 
                                       required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estoque" class="form-label">Estoque *</label>
                                <input type="number" class="form-control" id="estoque" name="estoque" 
                                       value="<?php echo $produtoEdicao ? $produtoEdicao['estoque'] : ''; ?>" 
                                       required min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="limite_min" class="form-label">Limite Mínimo *</label>
                                <input type="number" class="form-control" id="limite_min" name="limite_min" 
                                       value="<?php echo $produtoEdicao ? $produtoEdicao['limite_min'] : ''; ?>" 
                                       required min="0">
                                <div class="form-text">Estoque mínimo permitido</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="imagem" class="form-label">Imagem do Produto</label>
                                <input type="file" class="form-control" id="imagem" name="imagem" 
                                       accept="image/jpeg, image/png, image/gif, image/webp">
                                <div class="form-text">
                                    Formatos: JPG, JPEG, PNG, GIF, WEBP. Máx: 1MB
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                                          placeholder="Descrição detalhada do produto"><?php echo $produtoEdicao ? htmlspecialchars($produtoEdicao['descricao']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <?php if ($produtoEdicao): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div>
                                        <span class="badge <?php echo $produtoEdicao['ativo'] ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $produtoEdicao['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </div>
                                    <div class="form-text">
                                        Para alterar o status, use o botão de ativar/inativar na lista
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($produtoEdicao['imagem'])): ?>
                                    <label class="form-label">Imagem Atual</label>
                                    <div>
                                        <img src="../../imgs/<?php echo htmlspecialchars($produtoEdicao['imagem']); ?>" 
                                             alt="<?php echo htmlspecialchars($produtoEdicao['nome']); ?>" 
                                             class="imagem-produto" style="width: 100px; height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-2"></i>
                                Produto cadastrado em: <?php 
                                $stmt = $conn->prepare("SELECT dt_criado FROM produto WHERE id = ?");
                                $stmt->bind_param("i", $produtoEdicao['id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $dadosProduto = $result->fetch_assoc();
                                echo date('d/m/Y H:i', strtotime($dadosProduto['dt_criado']));
                                ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" name="<?php echo $produtoEdicao ? 'editar' : 'adicionar'; ?>" 
                            class="btn <?php echo $produtoEdicao ? 'btn-warning' : 'btn-success'; ?>">
                        <i class="fas fa-save me-2"></i>
                        <?php echo $produtoEdicao ? 'Atualizar Produto' : 'Criar Produto'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script para manipulação do modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalProduto = document.getElementById('modalProduto');
    const formProduto = document.getElementById('formProduto');
    
    modalProduto.addEventListener('hidden.bs.modal', function () {
        // Limpar o formulário quando o modal fechar (apenas se não estiver editando)
        if (!<?php echo $produtoEdicao ? 'true' : 'false'; ?>) {
            formProduto.reset();
        }
        // Remover parâmetro de edição da URL
        if (window.location.search.includes('editar=')) {
            const url = new URL(window.location);
            url.searchParams.delete('editar');
            window.history.replaceState({}, document.title, url.toString());
        }
    });

    // Se estiver editando, preencher o modal automaticamente
    <?php if (isset($_GET['editar']) && $produtoEdicao): ?>
        var modal = new bootstrap.Modal(document.getElementById('modalProduto'));
        modal.show();
    <?php endif; ?>

    // Validação do formulário
    formProduto.addEventListener('submit', function(e) {
        const preco = document.getElementById('preco').value;
        const estoque = document.getElementById('estoque').value;
        const limiteMin = document.getElementById('limite_min').value;
        
        if (preco < 0) {
            e.preventDefault();
            alert('O preço não pode ser negativo!');
            document.getElementById('preco').focus();
        }
        
        if (estoque < 0) {
            e.preventDefault();
            alert('O estoque não pode ser negativo!');
            document.getElementById('estoque').focus();
        }
        
        if (limiteMin < 0) {
            e.preventDefault();
            alert('O limite mínimo não pode ser negativo!');
            document.getElementById('limite_min').focus();
        }

        // Validação de imagem
        const imagemInput = document.getElementById('imagem');
        if (imagemInput.files.length > 0) {
            const arquivo = imagemInput.files[0];
            const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const tamanhoMaximo = 1048576; // 1MB
            
            if (!tiposPermitidos.includes(arquivo.type)) {
                e.preventDefault();
                alert('Apenas imagens JPG, JPEG, PNG, GIF e WEBP são permitidas!');
                imagemInput.focus();
            }
            
            if (arquivo.size > tamanhoMaximo) {
                e.preventDefault();
                alert('A imagem deve ter no máximo 1MB!');
                imagemInput.focus();
            }
        }
    });

    // Preview de imagem
    const imagemInput = document.getElementById('imagem');
    if (imagemInput) {
        imagemInput.addEventListener('change', function(e) {
            const arquivo = e.target.files[0];
            if (arquivo) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Criar ou atualizar preview
                    let preview = document.getElementById('imagem-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.id = 'imagem-preview';
                        preview.className = 'mt-2';
                        imagemInput.parentNode.appendChild(preview);
                    }
                    preview.innerHTML = `
                        <label class="form-label">Pré-visualização:</label>
                        <div>
                            <img src="${e.target.result}" class="imagem-produto" style="width: 100px; height: 100px;">
                        </div>
                    `;
                };
                reader.readAsDataURL(arquivo);
            }
        });
    }
});
</script>

<?php 
// Fechar conexão
$conn->close();
include '../../includes/footer.php'; 
?>