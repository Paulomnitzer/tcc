<?php
/**
 * Arquivo de Configuração Principal
 * 
 * Este arquivo contém as configurações globais do sistema.
 * Defina aqui:
 * - Constantes do sistema
 * - Configurações de ambiente
 * - Caminhos de arquivos
 * - Configurações de sessão
 * - Outras configurações gerais
 */

// Iniciar sessão se ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações do sistema
define('SITE_NAME', 'Sistema TCC');
define('SITE_URL', 'http://localhost/tcc');
define('SITE_VERSION', '1.0.0');

// Configurações de ambiente
define('ENVIRONMENT', 'development'); // development, production

// Configurações de erro (ajustar conforme ambiente)
if (ENVIRONMENT == 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configurações de caminhos
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PAGES_PATH', ROOT_PATH . '/pages');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Configurações de upload (se necessário)
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Outras configurações podem ser adicionadas aqui conforme necessário

?>
