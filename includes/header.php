<?php
// Garantir sessão ativa e tratar logout antes de qualquer saída HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tratamento seguro de logout via POST (botão com name="sair")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sair'])) {
    // Limpar variáveis de sessão
    $_SESSION = [];

    // Remover cookie de sessão se existia
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    // Destruir sessão e redirecionar para a página inicial
    session_unset();
    session_destroy();

    // Usar SITE_URL se definido, caso contrário redireciona para /index.php
    if (defined('SITE_URL')) {
        header('Location: ' . SITE_URL . '/index.php');
    } else {
        header('Location: /index.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS customizado -->
    <link href="<?php echo SITE_URL; ?>/css/style.css" rel="stylesheet">
    
    <!-- CSS adicional específico da página (se definido) -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?php echo SITE_URL . '/css/' . $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #8a551bff;">
        <div class="container">
         <a class="navbar-brand" href="<?php echo SITE_URL; ?>/pages/user/dashboard.php">
    <i class="fas fa-graduation-cap me-2"></i>
    <?php echo SITE_NAME; ?>
        </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- Links de navegação - implementar conforme necessário -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>">Início</a>
                    </li>
                    <!-- Adicionar mais links conforme necessário -->
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['usuario_logado'])): ?>
                        <!-- Menu para usuário logado -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo $_SESSION['usuario_nome'] ?? $_SESSION['user_name'] ?? 'Usuário'; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="post" action="" class="m-0">
                                        <button type="submit" name="sair" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Sair
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Menu para usuário não logado -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/user/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Container principal -->
    <div class="main-content">
