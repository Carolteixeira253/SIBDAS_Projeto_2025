<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

$idEncrypted = $_GET['id_garantia'] ?? null;
$idGarantia = aes_decrypt($idEncrypted);

if (!$idGarantia || !is_numeric($idGarantia)) {
    header('Location: garantias.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $stmt = $ligacao->prepare("SELECT * FROM Garantia WHERE idGarantia = :id");
    $stmt->bindParam(':id', $idGarantia, PDO::PARAM_INT);
    $stmt->execute();
    $garantia = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$garantia) {
        header('Location: garantias.php');
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
                <h1 class="fw-bold h2 mb-1 text-dark">Detalhes da Garantia</h1>
            </div>
            <a href="garantias.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Equipamento (ID)</label>
                    <p class="fw-bold"><?= htmlspecialchars($garantia['idEquipamento']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Entidade Responsável</label>
                    <p><?= htmlspecialchars($garantia['entidadeResponsavel'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Tipo de Contrato</label>
                    <p><?= htmlspecialchars($garantia['tipoContrato'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Data de Início</label>
                    <p><?= htmlspecialchars($garantia['dataInicio'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Data de Fim</label>
                    <p><?= htmlspecialchars($garantia['dataFim'] ?? 'N/A') ?></p>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <a href="editar_garantia.php?id_garantia=<?= $idEncrypted ?>" class="btn btn-acao-primaria fw-bold px-4 py-2">
                    <i class="fa-solid fa-pen me-2"></i>Editar
                </a>
                <a href="apagar_garantia.php?id_garantia=<?= $idEncrypted ?>" class="btn btn-danger fw-bold px-4 py-2">
                    <i class="fa-solid fa-trash me-2"></i>Apagar
                </a>
                <a href="garantias.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
            </div>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>