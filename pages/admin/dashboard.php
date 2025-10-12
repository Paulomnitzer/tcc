<?php
/**
 * Dashboard Administrativo
 * 
 * Esta é a página principal do painel administrativo.
 * Implemente aqui:
 * - Estatísticas gerais do sistema
 * - Gráficos e relatórios
 * - Links para funcionalidades administrativas
 * - Monitoramento do sistema
 */

// Incluir configurações
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Verificar se usuário está logado e é administrador
if (!usuarioLogado() || !usuarioAdmin()) {
    redirecionar(SITE_URL . '/pages/admin/login.php');
}

// Definir título da página
$page_title = 'Painel Administrativo';

// TODO: Buscar estatísticas do sistema do banco de dados

// Incluir header
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
                            <h3 class="mb-0">0</h3>
                            <small>+0% este mês</small>
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
                            <h5 class="card-title">Registros Ativos</h5>
                            <h3 class="mb-0">0</h3>
                            <small>+0% este mês</small>
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
                            <h5 class="card-title">Pendências</h5>
                            <h3 class="mb-0">0</h3>
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
                    <h5><i class="fas fa-chart-area me-2"></i>Estatísticas do Sistema</h5>
                </div>
                <div class="card-body">
                    <!-- TODO: Implementar gráficos de estatísticas -->
                    <p class="text-muted">Gráficos de estatísticas serão implementados aqui.</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Atividades Recentes</h5>
                </div>
                <div class="card-body">
                    <!-- TODO: Implementar lista de atividades recentes -->
                    <p class="text-muted">Log de atividades recentes do sistema.</p>
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
                        <a href="configuracoes.php" class="btn btn-outline-secondary">
                            <i class="fas fa-cog me-2"></i>Configurações
                        </a>
                        <a href="relatorios.php" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar me-2"></i>Relatórios
                        </a>
                        <a href="backup.php" class="btn btn-outline-warning">
                            <i class="fas fa-download me-2"></i>Backup
                        </a>
                        <a href="logs.php" class="btn btn-outline-danger">
                            <i class="fas fa-file-alt me-2"></i>Logs do Sistema
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
                            <td><strong>Último Backup:</strong></td>
                            <td>Nunca</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>
