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
    $stmt = $ligacao->prepare("SELECT nomeFornecedor, enderecoEmail FROM Fornecedor WHERE idFornecedor = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$fornecedor) {
        header('Location: fornecedores.php');
        exit;
    }
} catch (PDOException $e) {
    echo "<p class='text-danger'>Não foi possível processar o pedido. Tente novamente.</p>";
    exit;
}
$ligacao = null;
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="content-body">
    <?php include '../../includes/sidebar.php'; ?>
    <main class="main-content-wrapper">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="fw-bold h2 mb-1 text-dark">Remover Fornecedor</h1>
                <p class="text-muted small mb-0">Confirme a remoção do fornecedor.</p>
            </div>
            <a href="fornecedores.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4 text-center">
            <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
            <h4 class="fw-bold"><?= htmlspecialchars($fornecedor['nomeFornecedor']) ?></h4>
            <p class="text-muted"><?= htmlspecialchars($fornecedor['enderecoEmail'] ?? 'N/A') ?></p>
            <p class="text-danger fw-bold">Tem a certeza que pretende desativar este fornecedor?</p>
            <div class="d-flex gap-2 justify-content-center mt-3">
                <a href="confirmar_apagar_fornecedor.php?id_fornecedor=<?= urlencode($idEncrypted) ?>" class="btn btn-danger fw-bold px-4 py-2">
                    <i class="fa-solid fa-check me-2"></i>Sim
                </a>
                <a href="fornecedores.php" class="btn btn-secondary px-4 py-2">
                    <i class="fa-solid fa-times me-2"></i>Não
                </a>
            </div>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>