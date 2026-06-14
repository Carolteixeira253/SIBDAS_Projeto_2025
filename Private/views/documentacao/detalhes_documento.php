<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

$idEncrypted = $_GET['id_documento'] ?? null;
$idDocumento = aes_decrypt($idEncrypted);

if (!$idDocumento || !is_numeric($idDocumento)) {
    header('Location: documentacao.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $stmt = $ligacao->prepare("SELECT * FROM Documentacao WHERE idDocumento = :id");
    $stmt->bindParam(':id', $idDocumento, PDO::PARAM_INT);
    $stmt->execute();
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$documento) {
        header('Location: documentacao.php');
        exit;
    }
} catch (PDOException $e) {
    echo "<p class='text-danger'>Erro: " . $e->getMessage() . "</p>";
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
                <h1 class="fw-bold h2 mb-1 text-dark">Detalhes do Documento</h1>
            </div>
            <a href="documentacao.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Nome</label>
                    <p class="fw-bold"><?= htmlspecialchars($documento['nomeDocumento']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Tipo</label>
                    <p><?= htmlspecialchars($documento['tipoDocumento'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Ficheiro</label>
                    <p><?= htmlspecialchars($documento['nomeFicheiro'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Data</label>
                    <p><?= htmlspecialchars($documento['dataDocumento'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Validade</label>
                    <p><?= htmlspecialchars($documento['dataValidade'] ?? 'N/A') ?></p>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <a href="editar_documento.php?id_documento=<?= $idEncrypted ?>" class="btn btn-acao-primaria fw-bold px-4 py-2">
                    <i class="fa-solid fa-pen me-2"></i>Editar
                </a>
                <a href="apagar_documento.php?id_documento=<?= $idEncrypted ?>" class="btn btn-danger fw-bold px-4 py-2">
                    <i class="fa-solid fa-trash me-2"></i>Apagar
                </a>
                <a href="documentacao.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
            </div>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>