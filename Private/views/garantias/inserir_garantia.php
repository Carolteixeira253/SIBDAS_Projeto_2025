<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idEquipamento = $_POST["id_equipamento"] ?? "";
    $entidade = $_POST["entidade_responsavel"] ?? "";
    $tipoContrato = $_POST["tipo_contrato"] ?? "";
    $dataInicio = $_POST["data_inicio"] ?? "";
    $dataFim = $_POST["data_fim"] ?? "";

    $erros = [];
    if (empty($idEquipamento)) $erros[] = "O Equipamento é obrigatório.";
    if (empty($dataInicio)) $erros[] = "A Data de Início é obrigatória.";
    if (empty($dataFim)) $erros[] = "A Data de Fim é obrigatória.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("INSERT INTO Garantia (idEquipamento, entidadeResponsavel, tipoContrato, dataInicio, dataFim) VALUES (:idEquipamento, :entidade, :tipoContrato, :dataInicio, :dataFim)");
            $stmt->execute([
                ':idEquipamento' => $idEquipamento,
                ':entidade' => $entidade ?: null,
                ':tipoContrato' => $tipoContrato ?: null,
                ':dataInicio' => $dataInicio,
                ':dataFim' => $dataFim
            ]);
            $ligacao = null;
            header('Location: garantias.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao guardar: " . $err->getMessage();
        }
    }
}
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="content-body">
    <?php include '../../includes/sidebar.php'; ?>
    <main class="main-content-wrapper">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="fw-bold h2 mb-1 text-dark">Nova Garantia</h1>
                <p class="text-muted small mb-0">Preencha os dados da nova garantia.</p>
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
            <?php if (!empty($erro_sistema)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <form action="#" method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold">ID do Equipamento</label>
                    <input type="number" class="form-control" name="id_equipamento" value="<?= $_POST['id_equipamento'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Entidade Responsável</label>
                    <input type="text" class="form-control" name="entidade_responsavel" value="<?= $_POST['entidade_responsavel'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipo de Contrato</label>
                    <input type="text" class="form-control" name="tipo_contrato" value="<?= $_POST['tipo_contrato'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Data de Início</label>
                    <input type="date" class="form-control" name="data_inicio" value="<?= $_POST['data_inicio'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Data de Fim</label>
                    <input type="date" class="form-control" name="data_fim" value="<?= $_POST['data_fim'] ?? '' ?>" required>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Garantia
                    </button>
                    <a href="garantias.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>