<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

$idEncrypted = $_GET['id_localizacao'] ?? null;
$id = aes_decrypt($idEncrypted);

if (!$id || !is_numeric($id)) {
    header('Location: localizacoes.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $stmt = $ligacao->prepare("SELECT nomeSala, piso FROM Localizacao WHERE idLocalizacao = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $localizacao = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$localizacao) {
        header('Location: localizacoes.php');
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
                <h1 class="fw-bold h2 mb-1 text-dark">Remover Localização</h1>
            </div>
            <a href="localizacoes.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4 text-center">
            <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
            <h4 class="fw-bold"><?= htmlspecialchars($localizacao['nomeSala']) ?></h4>
            <p class="text-muted"><strong>Piso:</strong> <?= htmlspecialchars($localizacao['piso']) ?></p>
            <p class="text-danger fw-bold">Tem a certeza que pretende remover esta localização?</p>
            <div class="d-flex gap-2 justify-content-center mt-3">
                <a href="confirmar_apagar_localizacao.php?id_localizacao=<?= urlencode($idEncrypted) ?>" class="btn btn-danger fw-bold px-4 py-2">
                    <i class="fa-solid fa-check me-2"></i>Sim
                </a>
                <a href="localizacoes.php" class="btn btn-secondary px-4 py-2">
                    <i class="fa-solid fa-times me-2"></i>Não
                </a>
            </div>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>