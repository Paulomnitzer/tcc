<?php
/**
 * Dashboard Administrativo
 */
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

// Buscar estatísticas do sistema
try {
    // Total de usuários
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $row = $result->fetch_assoc();
    $totalUsuarios = $row['total'];
    
    // Total de produtos
    $result = $conn->query("SELECT COUNT(*) as total FROM produto");
    $row = $result->fetch_assoc();
    $totalProdutos = $row['total'];
    
    // Produtos com estoque baixo
    $result = $conn->query("SELECT COUNT(*) as total FROM produto WHERE estoque <= limite_min");
    $row = $result->fetch_assoc();
    $produtosEstoqueBaixo = $row['total'];
    
    // Usuários cadastrados este mês
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $row = $result->fetch_assoc();
    $usuariosEsteMes = $row['total'];
    
} catch (Exception $e) {
    $totalUsuarios = $totalProdutos = $produtosEstoqueBaixo = $usuariosEsteMes = 0;
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}

$page_title = 'Painel Administrativo';
include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <!-- Cabeçalho do painel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-cogs me-2"></i>Painel Administrativo</h2>
                    <p class="text-muted mb-0">
                        Bem-vindo, <?php echo $_SESSION['usuario_nome'] ?? 'Administrador'; ?>!
                    </p>
                </div>
                <div>
                    <span class="badge bg-danger">Admin</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cards de estatísticas principais -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total de Usuários</h5>
                            <h3 class="mb-0"><?php echo $totalUsuarios; ?></h3>
                            <small>+<?php echo $usuariosEsteMes; ?> este mês</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total de Produtos</h5>
                            <h3 class="mb-0"><?php echo $totalProdutos; ?></h3>
                            <small>Registros ativos</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-database fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Estoque Baixo</h5>
                            <h3 class="mb-0"><?php echo $produtosEstoqueBaixo; ?></h3>
                            <small>Requer atenção</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Sistema</h5>
                            <h3 class="mb-0">Online</h3>
                            <small>Funcionando</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-server fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Conteúdo principal -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-chart-area me-2"></i>Produtos com Estoque Baixo</h5>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $result = $conn->query("SELECT nome, estoque, limite_min FROM produto WHERE estoque <= limite_min ORDER BY estoque ASC LIMIT 10");
                        
                        if ($result && $result->num_rows > 0) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-sm">';
                            echo '<thead><tr><th>Produto</th><th>Estoque Atual</th><th>Limite Mínimo</th><th>Status</th></tr></thead>';
                            echo '<tbody>';
                            while ($produto = $result->fetch_assoc()) {
                                $status = $produto['estoque'] == 0 ? 'bg-danger' : 'bg-warning';
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($produto['nome']) . '</td>';
                                echo '<td>' . $produto['estoque'] . '</td>';
                                echo '<td>' . $produto['limite_min'] . '</td>';
                                echo '<td><span class="badge ' . $status . '">' . ($produto['estoque'] == 0 ? 'Esgotado' : 'Estoque Baixo') . '</span></td>';
                                echo '</tr>';
                            }
                            echo '</tbody></table></div>';
                        } else {
                            echo '<p class="text-success"><i class="fas fa-check-circle me-2"></i>Nenhum produto com estoque baixo.</p>';
                        }
                    } catch (Exception $e) {
                        echo '<p class="text-muted">Erro ao carregar dados.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-tools me-2"></i>Ferramentas Administrativas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="usuarios.php" class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i>Gerenciar Usuários
                        </a>
                        <a href="produtos.php" class="btn btn-outline-success">
                            <i class="fas fa-box me-2"></i>Gerenciar Produtos
                        </a>
                        <a href="configuracoes.php" class="btn btn-outline-secondary">
                            <i class="fas fa-cog me-2"></i>Configurações
                        </a>
                        <a href="relatorios.php" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar me-2"></i>Relatórios
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle me-2"></i>Informações do Sistema</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Versão:</strong></td>
                            <td><?php echo SITE_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Ambiente:</strong></td>
                            <td><?php echo ENVIRONMENT; ?></td>
                        </tr>
                        <tr>
                            <td><strong>PHP:</strong></td>
                            <td><?php echo PHP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Banco:</strong></td>
                            <td>Conectado</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Fechar conexão
$conn->close();
include '../../includes/footer.php'; 
?>