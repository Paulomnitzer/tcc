<?php
/**
 * Arquivo principal do projeto TCC
 * 
 * Este arquivo serve como ponto de entrada principal do sistema.
 * Aqui você pode implementar:
 * - Roteamento básico
 * - Verificação de autenticação
 * - Redirecionamento para páginas específicas
 * - Carregamento de configurações globais
 */

// Incluir arquivo de configuração
require_once 'config/config.php';

// Incluir arquivo de conexão com banco de dados
require_once 'config/db.php';


// Incluir header comum
include 'includes/header.php';

?>

<main class="container py-4" style="background: linear-gradient(180deg, #ffffff 0%, #f7f7f7 100%);">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 rounded">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <img src="<?php echo SITE_URL; ?>/assets/images/youstockageindex.png" 
                            alt="Logo da Empresa Youstockage" 
                            class="img-fluid empresa-logo"
                            style="max-width: 150px;">
                    </div>

                    <h1 class="h2 fw-bold mb-2" style="color: #2c3e50;">Bem-vindo ao Youstockage</h1>

                    <p class="small text-muted mb-3">Sistema de Gestão de Estoque Inteligente</p>

                    <div class="row justify-content-center">
                        <div class="col-10">
                            <div class="d-grid gap-2">
                                <a class="btn btn-primary btn-md py-2" href="pages/user/login.php">
                                    <i class="fas fa-user me-2"></i>Área do Usuário
                                </a>
                                <a class="btn btn-outline-secondary btn-md py-2" href="pages/admin/login.php">
                                    <i class="fas fa-lock me-2"></i>Área Administrativa
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-muted small mb-0">
                            © 2025 Youstockage
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<?php
// Incluir footer comum
include 'includes/footer.php';
?>
