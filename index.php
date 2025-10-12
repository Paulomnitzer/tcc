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
require_once 'config/database.php';

// Incluir header comum
include 'includes/header.php';

?>

<main class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="jumbotron">
                <h1 class="display-4">Bem-vindo ao Sistema TCC</h1>
                <p class="lead">Pagina Inicial</p>
                <hr class="my-4">
                <p>Use os links abaixo para navegar pelo sistema:</p>
                
                <!-- Links de navegação - implementar conforme necessário -->
                <div class="btn-group" role="group">
                    <a class="btn btn-primary btn-lg" href="pages/user/login.php" role="button">Área do Usuário</a>
                    <a class="btn btn-secondary btn-lg" href="pages/admin/login.php" role="button">Área Administrativa</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Incluir footer comum
include 'includes/footer.php';
?>
