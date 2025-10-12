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
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Verificar se usuário está logado
if (!usuarioLogado()) {
    redirecionar(SITE_URL . '/pages/user/login.php');
}

// Definir título da página
$page_title = 'Dashboard do Usuário';

// TODO: Buscar dados do usuário e estatísticas do banco de dados

// Incluir header
include '../../includes/header.php';
?>

<div class="container mt-4">
    <!-- Cabeçalho do dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                    <p class="text-muted mb-0">
                        Bem-vindo, <?php echo $_SESSION['usuario_nome'] ?? 'Usuário'; ?>!
                    </p>
                </div>
                <div>
                    <span class="badge bg-success">Online</span>
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
                            <h3 class="mb-0">0</h3>
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
                            <h5 class="card-title">Concluídos</h5>
                            <h3 class="mb-0">0</h3>
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
                            <h5 class="card-title">Pendentes</h5>
                            <h3 class="mb-0">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
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
                            <h3 class="mb-0">0</h3>
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
                <div class="card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Atividades Recentes</h5>
                </div>
                <div class="card-body">
                    <!-- TODO: Implementar lista de atividades recentes -->
                    <p class="text-muted">Nenhuma atividade recente encontrada.</p>
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
                <!-- Botão que abre o modal -->
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#novoItemModal">
                    <i class="fas fa-plus me-2"></i>Novo Item
                </button>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#pesquisaItemModal">
                    <i class="fas fa-search me-2"></i>Pesquisar
                </button>
                <a href="#" class="btn btn-outline-info">
                    <i class="fas fa-file-export me-2"></i>Relatórios
                </a>
                <a href="perfil.php" class="btn btn-outline-warning">
                    <i class="fas fa-user-edit me-2"></i>Editar Perfil
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="novoItemModal" tabindex="-1" aria-labelledby="novoItemModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="novoItemModalLabel">Adicionar Novo Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="nomeItem" class="form-label">Nome do Item</label>
            <input type="text" class="form-control" id="nomeItem" placeholder="Digite o nome">
          </div>
          <div class="mb-3">
            <label for="quantidadeItem" class="form-label">Quantidade</label>
            <input type="number" class="form-control" id="quantidadeItem" placeholder="0">
          </div>
          <div class="mb-3">
            <label for="precoItem" class="form-label">Preço</label>
            <input type="number" class="form-control" id="precoItem" placeholder="0.00" step="0.01">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary">Salvar Item</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="pesquisaItemModal" tabindex="-1" aria-labelledby="pesquisaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pesquisaModalLabel">Pesquisar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="valorPesquisa" class="form-label">Pesquisar Item</label>
            <input type="text" class="form-control" id="valorPesquisa" placeholder="Pesquise: ">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary">Pesquisar</button>
      </div>
    </div>
  </div>
</div>


<?php
// Incluir footer
include '../../includes/footer.php';
?>
