<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

if (!usuarioLogado() || !usuarioAdmin()) {
    redirecionar(SITE_URL . '/pages/admin/login.php');
}

// Conectar ao banco
$host = "localhost";
$user = "root";
$pass = "";
$db   = "banco";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$page_title = 'Gerenciar Usuários';
include '../../includes/header.php';

// Processar operações CRUD
$mensagem = '';
$tipoMensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['adicionar'])) {
        // Adicionar usuário
        try {
            // Verificar se email já existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $_POST['email']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $mensagem = 'Erro: Este email já está cadastrado!';
                $tipoMensagem = 'danger';
            } else {
                $senhaHash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, dt_nascimento, senha, tipo) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", 
                    $_POST['nome'],
                    $_POST['email'],
                    $_POST['telefone'],
                    $_POST['dt_nascimento'],
                    $senhaHash,
                    $_POST['tipo']
                );
                $stmt->execute();
                $mensagem = 'Usuário adicionado com sucesso!';
                $tipoMensagem = 'success';
            }
            $stmt->close();
        } catch (Exception $e) {
            $mensagem = 'Erro ao adicionar usuário: ' . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    } elseif (isset($_POST['editar'])) {
        // Editar usuário
        try {
            // Verificar se email já existe (excluindo o usuário atual)
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $_POST['email'], $_POST['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $mensagem = 'Erro: Este email já está cadastrado por outro usuário!';
                $tipoMensagem = 'danger';
            } else {
                if (!empty($_POST['senha'])) {
                    $senhaHash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE usuarios SET nome=?, email=?, telefone=?, dt_nascimento=?, senha=?, tipo=? WHERE id=?");
                    $stmt->bind_param("ssssssi",
                        $_POST['nome'],
                        $_POST['email'],
                        $_POST['telefone'],
                        $_POST['dt_nascimento'],
                        $senhaHash,
                        $_POST['tipo'],
                        $_POST['id']
                    );
                } else {
                    $stmt = $conn->prepare("UPDATE usuarios SET nome=?, email=?, telefone=?, dt_nascimento=?, tipo=? WHERE id=?");
                    $stmt->bind_param("sssssi",
                        $_POST['nome'],
                        $_POST['email'],
                        $_POST['telefone'],
                        $_POST['dt_nascimento'],
                        $_POST['tipo'],
                        $_POST['id']
                    );
                }
                $stmt->execute();
                $mensagem = 'Usuário atualizado com sucesso!';
                $tipoMensagem = 'success';
            }
            $stmt->close();
        } catch (Exception $e) {
            $mensagem = 'Erro ao atualizar usuário: ' . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    }
} elseif (isset($_GET['deletar'])) {
    // Não permitir deletar o próprio usuário
    if ($_GET['deletar'] != $_SESSION['usuario_id']) {
        try {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id=?");
            $stmt->bind_param("i", $_GET['deletar']);
            $stmt->execute();
            $mensagem = 'Usuário deletado com sucesso!';
            $tipoMensagem = 'success';
            $stmt->close();
        } catch (Exception $e) {
            $mensagem = 'Erro ao deletar usuário: ' . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    } else {
        $mensagem = 'Você não pode deletar seu próprio usuário!';
        $tipoMensagem = 'danger';
    }
}

// Buscar usuários com paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Construir query base
$whereConditions = [];
$params = [];
$types = '';

// Filtros
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $whereConditions[] = "(nome LIKE ? OR email LIKE ?)";
    $busca = "%" . $_GET['busca'] . "%";
    $params[] = $busca;
    $params[] = $busca;
    $types .= 'ss';
}

if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
    $whereConditions[] = "tipo = ?";
    $params[] = $_GET['tipo'];
    $types .= 's';
}

// Ordenação
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'novos';
switch ($ordenar) {
    case 'antigos':
        $orderBy = "created_at ASC";
        break;
    case 'nome':
        $orderBy = "nome ASC";
        break;
    default:
        $orderBy = "created_at DESC";
        break;
}

// Construir query WHERE
$whereSQL = '';
if (!empty($whereConditions)) {
    $whereSQL = "WHERE " . implode(" AND ", $whereConditions);
}

try {
    // Total de usuários para paginação
    $sqlCount = "SELECT COUNT(*) as total FROM usuarios $whereSQL";
    if (!empty($whereConditions)) {
        $stmt = $conn->prepare($sqlCount);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sqlCount);
    }
    $row = $result->fetch_assoc();
    $totalUsuarios = $row['total'];
    $totalPaginas = ceil($totalUsuarios / $limite);

    // Buscar usuários
    $sql = "SELECT id, nome, email, telefone, dt_nascimento, tipo, created_at 
            FROM usuarios 
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
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    $stmt->close();
    
} catch (Exception $e) {
    $usuarios = [];
    $totalPaginas = 1;
    $mensagem = 'Erro ao carregar usuários: ' . $e->getMessage();
    $tipoMensagem = 'danger';
}

// Buscar usuário para edição
$usuarioEdicao = null;
if (isset($_GET['editar'])) {
    try {
        $stmt = $conn->prepare("SELECT id, nome, email, telefone, dt_nascimento, tipo FROM usuarios WHERE id=?");
        $stmt->bind_param("i", $_GET['editar']);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuarioEdicao = $result->fetch_assoc();
        $stmt->close();
    } catch (Exception $e) {
        $mensagem = 'Erro ao carregar usuário: ' . $e->getMessage();
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
}
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users me-2"></i>Gerenciar Usuários</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                    <i class="fas fa-plus me-2"></i>Novo Usuário
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
                        <div class="col-md-4">
                            <label for="busca" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="busca" name="busca" 
                                   value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>" 
                                   placeholder="Nome ou email...">
                        </div>
                        <div class="col-md-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-control" id="tipo" name="tipo">
                                <option value="">Todos</option>
                                <option value="user" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'user') ? 'selected' : ''; ?>>Usuário</option>
                                <option value="admin" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="ordenar" class="form-label">Ordenar por</label>
                            <select class="form-control" id="ordenar" name="ordenar">
                                <option value="novos" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'novos') ? 'selected' : ''; ?>>Mais Recentes</option>
                                <option value="antigos" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'antigos') ? 'selected' : ''; ?>>Mais Antigos</option>
                                <option value="nome" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'nome') ? 'selected' : ''; ?>>Nome A-Z</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary w-100 me-2">
                                <i class="fas fa-search me-2"></i>Filtrar
                            </button>
                            <a href="usuarios.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Lista de Usuários (<?php echo $totalUsuarios; ?> total)</h5>
                </div>
                <div class="card-body">
                    <?php if (count($usuarios) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>Data Nasc.</th>
                                        <th>Tipo</th>
                                        <th>Cadastrado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?php echo $usuario['id']; ?></td>
                                            <td><?php echo $usuario['nome']; ?></td>
                                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['telefone'] ?? 'N/A'); ?></td>
                                            <td><?php echo $usuario['dt_nascimento'] ? date('d/m/Y', strtotime($usuario['dt_nascimento'])) : 'N/A'; ?></td>
                                            <td>
                                                <span class="badge <?php echo $usuario['tipo'] == 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                                    <?php echo ucfirst($usuario['tipo']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($usuario['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="?editar=<?php echo $usuario['id']; ?>" 
                                                       class="btn btn-warning" 
                                                       data-bs-toggle="modal" 
                                                       data-bs-target="#modalUsuario"
                                                       title="Editar usuário">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($usuario['id']): ?>
                                                        <a href="?deletar=<?php echo $usuario['id']; ?>" 
                                                           class="btn btn-danger" 
                                                           onclick="return confirm('Tem certeza que deseja deletar o usuário <?php echo htmlspecialchars($usuario['nome']); ?>? Esta ação não pode ser desfeita.')"
                                                           title="Deletar usuário">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-secondary" disabled title="Não é possível deletar seu próprio usuário">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
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
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum usuário encontrado</h5>
                            <p class="text-muted">Clique no botão "Novo Usuário" para adicionar o primeiro usuário.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Adicionar/Editar Usuário -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="formUsuario">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioLabel">
                        <?php if ($usuarioEdicao): ?>
                            <i class="fas fa-edit me-2"></i>Editar Usuário: <?php echo htmlspecialchars($usuarioEdicao['nome']); ?>
                        <?php else: ?>
                            <i class="fas fa-plus me-2"></i>Adicionar Novo Usuário
                        <?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($usuarioEdicao): ?>
                        <input type="hidden" name="id" value="<?php echo $usuarioEdicao['id']; ?>">
                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Editando usuário ID: <?php echo $usuarioEdicao['id']; ?>
                            </small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-2"></i>
                                Preencha os dados para criar um novo usuário
                            </small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo $usuarioEdicao ? htmlspecialchars($usuarioEdicao['nome']) : ''; ?>" 
                                       required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $usuarioEdicao ? htmlspecialchars($usuarioEdicao['email']) : ''; ?>" 
                                       required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="telefone" name="telefone" 
                                       value="<?php echo $usuarioEdicao ? htmlspecialchars($usuarioEdicao['telefone']) : ''; ?>" 
                                       maxlength="20" placeholder="(11) 99999-9999">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dt_nascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" id="dt_nascimento" name="dt_nascimento" 
                                       value="<?php echo $usuarioEdicao ? $usuarioEdicao['dt_nascimento'] : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="senha" class="form-label">
                                    <?php echo $usuarioEdicao ? 'Nova Senha' : 'Senha *'; ?>
                                    <?php if ($usuarioEdicao): ?>
                                        <small class="text-muted">(deixe em branco para manter a atual)</small>
                                    <?php endif; ?>
                                </label>
                                <input type="password" class="form-control" id="senha" name="senha" 
                                       <?php echo $usuarioEdicao ? '' : 'required'; ?>
                                       minlength="6" placeholder="Mínimo 6 caracteres">
                                <div class="form-text">
                                    <?php if ($usuarioEdicao): ?>
                                        Preencha apenas se desejar alterar a senha
                                    <?php else: ?>
                                        A senha deve ter pelo menos 6 caracteres
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo de Usuário *</label>
                                <select class="form-control" id="tipo" name="tipo" required>
                                    <option value="user" <?php echo ($usuarioEdicao && $usuarioEdicao['tipo'] == 'user') ? 'selected' : ''; ?>>Usuário</option>
                                    <option value="admin" <?php echo ($usuarioEdicao && $usuarioEdicao['tipo'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                                <?php if ($usuarioEdicao && $usuarioEdicao['id'] == $_SESSION['usuario_id']): ?>
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Cuidado ao alterar seu próprio tipo de usuário
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($usuarioEdicao): ?>
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-2"></i>
                                Usuário cadastrado em: <?php 
                                $stmt = $conn->prepare("SELECT created_at, updated_at FROM usuarios WHERE id = ?");
                                $stmt->bind_param("i", $usuarioEdicao['id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $dadosUsuario = $result->fetch_assoc();
                                echo date('d/m/Y H:i', strtotime($dadosUsuario['created_at']));
                                ?> | 
                                Última atualização: <?php echo date('d/m/Y H:i', strtotime($dadosUsuario['updated_at'])); ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" name="<?php echo $usuarioEdicao ? 'editar' : 'adicionar'; ?>" 
                            class="btn <?php echo $usuarioEdicao ? 'btn-warning' : 'btn-primary'; ?>">
                        <i class="fas fa-save me-2"></i>
                        <?php echo $usuarioEdicao ? 'Atualizar Usuário' : 'Criar Usuário'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script para manipulação do modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalUsuario = document.getElementById('modalUsuario');
    const formUsuario = document.getElementById('formUsuario');
    
    modalUsuario.addEventListener('hidden.bs.modal', function () {
        // Limpar o formulário quando o modal fechar (apenas se não estiver editando)
        if (!<?php echo $usuarioEdicao ? 'true' : 'false'; ?>) {
            formUsuario.reset();
        }
        // Remover parâmetro de edição da URL
        if (window.location.search.includes('editar=')) {
            const url = new URL(window.location);
            url.searchParams.delete('editar');
            window.history.replaceState({}, document.title, url.toString());
        }
    });

    // Se estiver editando, preencher o modal automaticamente
    <?php if (isset($_GET['editar']) && $usuarioEdicao): ?>
        var modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
        modal.show();
    <?php endif; ?>

    // Máscara para telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            
            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else if (value.length > 0) {
                value = value.replace(/^(\d{0,2})/, '($1');
            }
            
            e.target.value = value;
        });
    }

    // Validação do formulário
    formUsuario.addEventListener('submit', function(e) {
        const senha = document.getElementById('senha').value;
        const isEditando = <?php echo $usuarioEdicao ? 'true' : 'false'; ?>;
        
        if (!isEditando && senha.length < 6) {
            e.preventDefault();
            alert('A senha deve ter pelo menos 6 caracteres!');
            document.getElementById('senha').focus();
        }
        
        // Validação de email básica
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Por favor, insira um email válido!');
            document.getElementById('email').focus();
        }
    });
    
    // Gerar senha aleatória (opcional)
    const gerarSenhaBtn = document.createElement('button');
    gerarSenhaBtn.type = 'button';
    gerarSenhaBtn.className = 'btn btn-outline-secondary btn-sm mt-1';
    gerarSenhaBtn.innerHTML = '<i class="fas fa-key me-1"></i>Gerar Senha';
    gerarSenhaBtn.onclick = function() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
        let senha = '';
        for (let i = 0; i < 10; i++) {
            senha += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('senha').value = senha;
    };
    
    const senhaGroup = document.querySelector('.mb-3 label[for="senha"]').parentNode;
    senhaGroup.appendChild(gerarSenhaBtn);
});
</script>

<?php 
// Fechar conexão
$conn->close();
include '../../includes/footer.php'; 
?>