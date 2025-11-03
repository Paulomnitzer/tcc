<?php
/**
 * Relatórios - Usuário
 * 
 * Página para visualização de relatórios e estatísticas
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

if (!usuarioLogado()) {
    redirecionar(SITE_URL . '/pages/user/login.php');
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

// Buscar dados para relatórios
$dados_relatorios = [];
$total_produtos = 0;
$produtos_baixo_estoque = 0;
$produtos_sem_estoque = 0;
$categorias_count = [];
$produtos_mais_vendidos = [];

try {
    // Total de produtos
    $result = $conn->query("SELECT COUNT(*) as total FROM produto");
    $total_produtos = $result->fetch_assoc()['total'];

    // Produtos com estoque baixo
    $result = $conn->query("SELECT COUNT(*) as total FROM produto WHERE estoque <= limite_min AND estoque > 0");
    $produtos_baixo_estoque = $result->fetch_assoc()['total'];

    // Produtos sem estoque
    $result = $conn->query("SELECT COUNT(*) as total FROM produto WHERE estoque = 0");
    $produtos_sem_estoque = $result->fetch_assoc()['total'];

    // Produtos por categoria (se houver tabela de categorias)
    $result = $conn->query("
        SELECT categoria, COUNT(*) as total 
        FROM produto 
        WHERE categoria IS NOT NULL AND categoria != '' 
        GROUP BY categoria 
        ORDER BY total DESC 
        LIMIT 10
    ");
    while ($row = $result->fetch_assoc()) {
        $categorias_count[] = $row;
    }

    // Produtos mais vendidos (se houver tabela de vendas)
    $result = $conn->query("
        SELECT nome, estoque, preco 
        FROM produto 
        ORDER BY estoque DESC 
        LIMIT 5
    ");
    while ($row = $result->fetch_assoc()) {
        $produtos_mais_vendidos[] = $row;
    }

    // Estatísticas mensais (últimos 6 meses)
    $result = $conn->query("
        SELECT 
            DATE_FORMAT(dt_criado, '%Y-%m') as mes,
            COUNT(*) as total_produtos,
            SUM(CASE WHEN estoque <= limite_min THEN 1 ELSE 0 END) as estoque_baixo
        FROM produto 
        WHERE dt_criado >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(dt_criado, '%Y-%m')
        ORDER BY mes DESC
    ");
    $estatisticas_mensais = [];
    while ($row = $result->fetch_assoc()) {
        $estatisticas_mensais[] = $row;
    }

} catch (Exception $e) {
    $erro_relatorios = "Erro ao carregar dados para relatórios: " . $e->getMessage();
}

$page_title = 'Relatórios';
include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <!-- Cabeçalho -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-chart-bar me-2"></i>Relatórios</h2>
                    <p class="text-muted mb-0">
                        Visualize estatísticas e relatórios do sistema
                    </p>
                </div>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimir
                    </button>
                    <button class="btn btn-outline-success" id="btnExportarPDF">
                        <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($erro_relatorios)): ?>
        <div class="alert alert-danger">
            <?php echo $erro_relatorios; ?>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="data_inicio" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                        </div>
                        <div class="col-md-3">
                            <label for="data_fim" class="form-label">Data Fim</label>
                            <input type="date" class="form-control" id="data_fim" name="data_fim">
                        </div>
                        <div class="col-md-3">
                            <label for="categoria" class="form-label">Categoria</label>
                            <select class="form-control" id="categoria" name="categoria">
                                <option value="">Todas as categorias</option>
                                <?php foreach ($categorias_count as $categoria): ?>
                                    <option value="<?php echo htmlspecialchars($categoria['categoria']); ?>">
                                        <?php echo htmlspecialchars($categoria['categoria']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 me-2">
                                <i class="fas fa-search me-2"></i>Filtrar
                            </button>
                            <a href="relatorios.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Produtos</h5>
                            <h3 class="mb-0"><?php echo $total_produtos; ?></h3>
                            <small>Cadastrados no sistema</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-boxes fa-2x"></i>
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
                            <h5 class="card-title">Em Estoque</h5>
                            <h3 class="mb-0"><?php echo $total_produtos - $produtos_baixo_estoque - $produtos_sem_estoque; ?></h3>
                            <small>Estoque normal</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
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
                            <h3 class="mb-0"><?php echo $produtos_baixo_estoque; ?></h3>
                            <small>Reabastecimento necessário</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Sem Estoque</h5>
                            <h3 class="mb-0"><?php echo $produtos_sem_estoque; ?></h3>
                            <small>Produtos esgotados</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos e Tabelas -->
    <div class="row">
        <!-- Estatísticas Mensais -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Estatísticas Mensais (Últimos 6 meses)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mês/Ano</th>
                                    <th>Total Produtos</th>
                                    <th>Estoque Baixo</th>
                                    <th>% Estoque Baixo</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($estatisticas_mensais)): ?>
                                    <?php foreach ($estatisticas_mensais as $estatistica): ?>
                                        <?php 
                                        $percentual = $estatistica['total_produtos'] > 0 ? 
                                            round(($estatistica['estoque_baixo'] / $estatistica['total_produtos']) * 100, 2) : 0;
                                        $status_class = $percentual > 30 ? 'danger' : ($percentual > 15 ? 'warning' : 'success');
                                        ?>
                                        <tr>
                                            <td><strong><?php echo date('m/Y', strtotime($estatistica['mes'] . '-01')); ?></strong></td>
                                            <td><?php echo $estatistica['total_produtos']; ?></td>
                                            <td><?php echo $estatistica['estoque_baixo']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <?php echo $percentual; ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($percentual > 30): ?>
                                                    <span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Crítico</span>
                                                <?php elseif ($percentual > 15): ?>
                                                    <span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Atenção</span>
                                                <?php else: ?>
                                                    <span class="text-success"><i class="fas fa-check-circle me-1"></i>Normal</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                            <p>Nenhum dado disponível para os últimos 6 meses</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produtos por Categoria -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Produtos por Categoria</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($categorias_count)): ?>
                        <div class="list-group">
                            <?php foreach ($categorias_count as $categoria): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?php echo htmlspecialchars($categoria['categoria']); ?></span>
                                    <span class="badge bg-primary rounded-pill"><?php echo $categoria['total']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-tags fa-3x mb-3"></i>
                            <p>Nenhuma categoria cadastrada</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Produtos Mais Vendidos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Produtos com Maior Estoque</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($produtos_mais_vendidos)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Posição</th>
                                        <th>Produto</th>
                                        <th>Estoque</th>
                                        <th>Preço</th>
                                        <th>Valor Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos_mais_vendidos as $index => $produto): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?php echo $index == 0 ? 'warning' : ($index < 3 ? 'info' : 'secondary'); ?>">
                                                    #<?php echo $index + 1; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($produto['nome']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $produto['estoque']; ?></span>
                                            </td>
                                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                            <td>
                                                <strong>R$ <?php echo number_format($produto['estoque'] * $produto['preco'], 2, ',', '.'); ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-trophy fa-3x mb-3"></i>
                            <p>Nenhum produto disponível</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Relatório de Estoque Crítico -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Alerta - Estoque Crítico</h5>
                </div>
                <div class="card-body">
                    <?php
                    $result = $conn->query("
                        SELECT nome, estoque, limite_min, preco 
                        FROM produto 
                        WHERE estoque <= limite_min 
                        ORDER BY estoque ASC 
                        LIMIT 10
                    ");
                    $estoque_critico = [];
                    while ($row = $result->fetch_assoc()) {
                        $estoque_critico[] = $row;
                    }
                    ?>
                    
                    <?php if (!empty($estoque_critico)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Atenção!</strong> Existem <?php echo count($estoque_critico); ?> produtos com estoque crítico que necessitam de reabastecimento.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-warning">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Estoque Atual</th>
                                        <th>Limite Mínimo</th>
                                        <th>Preço</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estoque_critico as $produto): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($produto['nome']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-<?php echo $produto['estoque'] == 0 ? 'danger' : 'warning'; ?>">
                                                    <?php echo $produto['estoque']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $produto['limite_min']; ?></td>
                                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                            <td>
                                                <?php if ($produto['estoque'] == 0): ?>
                                                    <span class="text-danger"><i class="fas fa-times-circle me-1"></i>Esgotado</span>
                                                <?php else: ?>
                                                    <span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Crítico</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Excelente!</strong> Nenhum produto com estoque crítico no momento.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumo Executivo -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Resumo Executivo</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Pontos Positivos:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Total de <?php echo $total_produtos; ?> produtos cadastrados</li>
                                <li><i class="fas fa-check text-success me-2"></i><?php echo $total_produtos - $produtos_baixo_estoque - $produtos_sem_estoque; ?> produtos com estoque normal</li>
                                <?php if (empty($estoque_critico)): ?>
                                    <li><i class="fas fa-check text-success me-2"></i>Nenhum produto com estoque crítico</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Recomendações:</h6>
                            <ul class="list-unstyled">
                                <?php if ($produtos_baixo_estoque > 0): ?>
                                    <li><i class="fas fa-exclamation text-warning me-2"></i>Reabastecer <?php echo $produtos_baixo_estoque; ?> produtos com estoque baixo</li>
                                <?php endif; ?>
                                <?php if ($produtos_sem_estoque > 0): ?>
                                    <li><i class="fas fa-times text-danger me-2"></i>Repor <?php echo $produtos_sem_estoque; ?> produtos esgotados</li>
                                <?php endif; ?>
                                <li><i class="fas fa-chart-line text-info me-2"></i>Monitorar tendências mensais</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Exportar para PDF
    document.getElementById('btnExportarPDF').addEventListener('click', function() {
        alert('Funcionalidade de exportação PDF será implementada em breve!');
        // Aqui você pode integrar com bibliotecas como jsPDF ou fazer requisição para o servidor
    });

    // Aplicar classes de impressão
    const style = document.createElement('style');
    style.innerHTML = `
        @media print {
            .btn { display: none !important; }
            .card-header { background: #f8f9fa !important; color: #000 !important; }
            .table-dark { background: #000 !important; color: #fff !important; }
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php 
// Fechar conexão
$conn->close();
include '../../includes/footer.php'; 
?>