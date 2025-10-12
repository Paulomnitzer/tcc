<?php
/**
 * Funções Auxiliares do Sistema
 * 
 * Este arquivo contém funções reutilizáveis em todo o sistema.
 * Implemente aqui:
 * - Funções de validação
 * - Funções de formatação
 * - Funções de segurança
 * - Outras funções utilitárias
 */

/**
 * Função para sanitizar dados de entrada
 * 
 * @param string $data Dados a serem sanitizados
 * @return string Dados sanitizados
 */
function sanitizar($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Função para validar email
 * 
 * @param string $email Email a ser validado
 * @return bool True se válido, false caso contrário
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Função para gerar hash de senha
 * 
 * @param string $senha Senha em texto plano
 * @return string Hash da senha
 */
function hashSenha($senha) {
    return password_hash($senha, PASSWORD_DEFAULT);
}

/**
 * Função para verificar senha
 * 
 * @param string $senha Senha em texto plano
 * @param string $hash Hash armazenado
 * @return bool True se a senha confere, false caso contrário
 */
function verificarSenha($senha, $hash) {
    return password_verify($senha, $hash);
}

/**
 * Função para redirecionar
 * 
 * @param string $url URL de destino
 */
function redirecionar($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Função para verificar se usuário está logado
 * 
 * @return bool True se logado, false caso contrário
 */
function usuarioLogado() {
    return isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true;
}

/**
 * Função para verificar se usuário é administrador
 * 
 * @return bool True se é admin, false caso contrário
 */
function usuarioAdmin() {
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin';
}

/**
 * Função para formatar data brasileira
 * 
 * @param string $data Data no formato Y-m-d
 * @return string Data no formato d/m/Y
 */
function formatarDataBR($data) {
    return date('d/m/Y', strtotime($data));
}

/**
 * Função para formatar data e hora brasileira
 * 
 * @param string $dataHora Data/hora no formato Y-m-d H:i:s
 * @return string Data/hora no formato d/m/Y H:i
 */
function formatarDataHoraBR($dataHora) {
    return date('d/m/Y H:i', strtotime($dataHora));
}

/**
 * Função para exibir mensagens de alerta
 * 
 * @param string $mensagem Mensagem a ser exibida
 * @param string $tipo Tipo do alerta (success, danger, warning, info)
 */
function exibirAlerta($mensagem, $tipo = 'info') {
    echo "<div class='alert alert-{$tipo} alert-dismissible fade show' role='alert'>";
    echo $mensagem;
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
    echo "</div>";
}

/**
 * Função para debug (apenas em desenvolvimento)
 * 
 * @param mixed $variavel Variável a ser debugada
 */
function debug($variavel) {
    if (ENVIRONMENT == 'development') {
        echo "<pre>";
        var_dump($variavel);
        echo "</pre>";
    }
}

?>
