<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

$idEncrypted = $_GET['id_fornecedor'] ?? null;
$id = aes_decrypt($idEncrypted);

if (!$id || !is_numeric($id)) {
    header('Location: fornecedores.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $ligacao->prepare("UPDATE Fornecedor SET ativo = 0 WHERE idFornecedor = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('Location: fornecedores.php');
    exit;
} catch (PDOException $e) {
    echo "<p class='text-danger'>Não foi possível processar o pedido. Tente novamente.</p>";
    exit;
}
?>