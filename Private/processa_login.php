<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/funcoes.php';
start_session();

// SEGURANÇA: só aceita POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: /sibdas/1231343/medcare-inventory-solutions/Public/login.php');
    return;
}

// RECOLHA DOS DADOS
$username = isset($_POST['text_username']) ? trim($_POST['text_username']) : '';
$password = isset($_POST['text_password']) ? $_POST['text_password'] : '';

// VALIDAÇÃO
$validation_errors = [];

if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
    $validation_errors[] = 'O username tem que ser um email válido.';
}
if (strlen($username) < 5 || strlen($username) > 50) {
    $validation_errors[] = 'O username deve ter entre 5 e 50 caracteres.';
}
if (strlen($password) < 6 || strlen($password) > 12) {
    $validation_errors[] = 'A password deve ter entre 6 e 12 caracteres.';
}

if (!empty($validation_errors)) {
    $_SESSION['validation_errors'] = $validation_errors;
    header('Location: /sibdas/1231343/medcare-inventory-solutions/Public/login.php');
    return;
}

// LIGAÇÃO REAL À BD
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Procurar utilizador pelo username
    $stmt = $ligacao->prepare("SELECT * FROM Utilizador WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $utilizador = $stmt->fetch(PDO::FETCH_OBJ);

    // Verificar se existe e se a password está correta
    if (!$utilizador || !password_verify($password, $utilizador->password)) {
        $_SESSION['server_error'] = 'Login inválido. Verifique as suas credenciais.';
        registar_log('ERRO', 'Tentativa de login falhada para: ' . $username);
        header('Location: /sibdas/1231343/medcare-inventory-solutions/Public/login.php');
        return;
    }

    // LOGIN BEM-SUCEDIDO
    $_SESSION['utilizador'] = $utilizador->username;
    $_SESSION['nome'] = $utilizador->nomeUtilizador;
    $_SESSION['perfil'] = $utilizador->perfil;
    registar_log('LOGIN', 'Login efectuado por ' . $utilizador->username);

    $ligacao = null;

    header('Location: /sibdas/1231343/medcare-inventory-solutions/Private/index.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['server_error'] = 'Erro ao ligar à base de dados.';
    header('Location: /sibdas/1231343/medcare-inventory-solutions/Public/login.php');
    return;
}
