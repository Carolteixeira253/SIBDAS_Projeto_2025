<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('Location: garantias.php');
    exit;
}

$idEncrypted = $_GET['id_garantia'] ?? null;
$idGarantia = aes_decrypt($idEncrypted);

if (!$idGarantia || !is_numeric($idGarantia)) {
    header('Location: garantias.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entidade = $_POST['entidade_responsavel'] ?? '';
    $tipoContrato = $_POST['tipo_contrato'] ?? '';
    $dataInicio = $_POST['data_inicio'] ?? '';
    $dataFim = $_POST['data_fim'] ?? '';

    $erros = [];
    if (empty($dataInicio)) $erros[] = "A Data de Início é obrigatória.";
    if (empty($dataFim)) $erros[] = "A Data de Fim é obrigatória.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $stmt = $ligacao->prepare("UPDATE Garantia SET entidadeResponsavel = :entidade, tipoContrato = :tipoContrato, dataInicio = :dataInicio, dataFim = :dataFim WHERE idGarantia = :id");
            $stmt->execute([
                ':entidade' => $entidade ?: null,
                ':tipoContrato' => $tipoContrato ?: null,
                ':dataInicio' => $dataInicio,
                ':dataFim' => $dataFim,
                ':id' => $idGarantia
            ]);
            $ligacao = null;
            header('Location: garantias.php');
            exit;
        } catch (PDOException $err) {
            $erro = "Erro ao atualizar: " . $err->getMessage();
        }
    }
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
    $garantia = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$garantia) {
        header('Location: garantias.php');
        exit;
    }
} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $garantia = null;
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
                <h1 class="fw-bold h2 mb-1 text-dark">Editar Garantia</h1>
            </div>
            <a href="garantias.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4">
            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form action="editar_garantia.php?id_garantia=<?= $idEncrypted ?>" method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Entidade Responsável</label>
                    <input type="text" class="form-control" name="entidade_responsavel" value="<?= htmlspecialchars($garantia->entidadeResponsavel ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipo de Contrato</label>
                    <input type="text" class="form-control" name="tipo_contrato" value="<?= htmlspecialchars($garantia->tipoContrato ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Data de Início</label>
                    <input type="date" class="form-control" name="data_inicio" value="<?= htmlspecialchars($garantia->dataInicio ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Data de Fim</label>
                    <input type="date" class="form-control" name="data_fim" value="<?= htmlspecialchars($garantia->dataFim ?? '') ?>" required>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                    </button>
                    <a href="garantias.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>