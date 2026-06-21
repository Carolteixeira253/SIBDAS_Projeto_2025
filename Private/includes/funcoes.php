<?php
require_once __DIR__ . '/../../config/config.php';
// Inicia a sessão se ainda não estiver iniciada
function start_session()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Verifica se a sessão do utilizador está ativa
function check_session()
{
    return isset($_SESSION['utilizador']);
}

// Redireciona automaticamente se não houver sessão iniciada
function redirect_if_not_logged($redirect_to = '/medcare-inventory-solutions/Public/login.php')
{
    start_session();
    if (!check_session()) {
        header("Location: $redirect_to");
        exit;
    }
}

// Termina a sessão e redireciona
function logout_and_redirect($redirect_to = '/medcare-inventory-solutions/Public/login.php')
{
    start_session();
    session_unset();
    session_destroy();
    header("Location: $redirect_to");
    exit;
}
function is_admin()
{
    start_session();
    return isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'administrador';
}
function redirect_if_not_admin($redirect_to = '/medcare-inventory-solutions/Private/index.php')
{
    start_session();
    if (!is_admin()) {
        header("Location: $redirect_to");
        exit;
    }
}

function aes_encrypt($value)
{
    return bin2hex(openssl_encrypt(
        $value,
        OPENSSL_METHOD,
        OPENSSL_KEY,
        OPENSSL_RAW_DATA,
        OPENSSL_IV
    ));
}

function aes_decrypt($value)
{
    if (!is_string($value) || strlen($value) % 2 !== 0) return false;
    return openssl_decrypt(
        hex2bin($value),
        OPENSSL_METHOD,
        OPENSSL_KEY,
        OPENSSL_RAW_DATA,
        OPENSSL_IV
    );
}
function mensagem_erro_bd()
{
    return '<div class="alert alert-warning d-flex align-items-center gap-3" role="alert">
        <i class="fa-solid fa-wifi text-warning fs-4"></i>
        <div>
            <strong>Sem ligação à base de dados.</strong><br>
            <small>Não foi possível carregar os dados. Verifique a sua ligação à internet e tente novamente.</small>
        </div>
    </div>';
}
function registar_log($tipo, $detalhe)
{
    $pasta = __DIR__ . '/../../logs';

    if (!is_dir($pasta)) {
        mkdir($pasta, 0755, true);
    }
    start_session();
    $utilizador = $_SESSION['utilizador'] ?? 'sistema';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    $linha = '[' . date('Y-m-d H:i:s') . '] | ' . strtoupper($tipo) . ' | ' . $utilizador . ' | ' . $ip . ' | ' . $detalhe . PHP_EOL;
    file_put_contents($pasta . '/sistema.log', $linha, FILE_APPEND | LOCK_EX);
}
