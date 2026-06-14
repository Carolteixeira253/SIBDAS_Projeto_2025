<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

// Obter e desencriptar o ID
$idEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEncrypted);

if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: /medcare-inventory-solutions/Private/views/equipamentos/equipamentos.php');
    exit;
}

// Ligar à BD e carregar dados
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->prepare("SELECT * FROM Equipamento WHERE idEquipamento = :id");
    $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmt->execute();
    $equipamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipamento) {
        header('Location: /medcare-inventory-solutions/Private/views/equipamentos/equipamentos.php');
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
                <h1 class="fw-bold h2 mb-1 text-dark">
                    Detalhes do Equipamento
                    <?php
                    $badgeClass = match($equipamento['estado']) {
                        'operacional' => 'bg-success',
                        'manutencao' => 'bg-warning text-dark',
                        'avariado' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge <?= $badgeClass ?> ms-2"><?= htmlspecialchars($equipamento['estado']) ?></span>
                </h1>
                <p class="text-muted small mb-0">Informação detalhada do equipamento médico.</p>
            </div>
            <a href="equipamentos.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Nome do Equipamento</label>
                    <p class="fw-bold"><?= htmlspecialchars($equipamento['nomeEquipamento']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Categoria</label>
                    <p><?= htmlspecialchars($equipamento['categoria']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Estado</label>
                    <p><?= htmlspecialchars($equipamento['estado']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Criticidade Clínica</label>
                    <p><?= htmlspecialchars($equipamento['criticidadeClinica'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">Localização</label>
                    <p><?= htmlspecialchars($equipamento['idLocalizacao'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold text-uppercase">ID</label>
                    <p>#<?= str_pad($equipamento['idEquipamento'], 3, '0', STR_PAD_LEFT) ?></p>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="editar_equipamento.php?id_equipamento=<?= $idEncrypted ?>" class="btn btn-acao-primaria fw-bold px-4 py-2">
                    <i class="fa-solid fa-pen me-2"></i>Editar
                </a>
                <a href="apagar_equipamento.php?id_equipamento=<?= $idEncrypted ?>" class="btn btn-danger fw-bold px-4 py-2">
                    <i class="fa-solid fa-trash me-2"></i>Apagar
                </a>
                <a href="equipamentos.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
            </div>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>