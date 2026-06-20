<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

$erros = [];
$erro_sistema = '';
$equipamentos = [];

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $equipamentos = $ligacao->query("SELECT idEquipamento, nomeEquipamento, codigoInventario FROM Equipamento WHERE ativo = 1 ORDER BY nomeEquipamento")->fetchAll(PDO::FETCH_OBJ);
    $ligacao = null;
} catch (PDOException $err) {
    $erro_sistema = "Não foi possível carregar os equipamentos.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idEquipamento = $_POST["id_equipamento"] ?? "";
    $entidade      = trim($_POST["entidade_responsavel"] ?? "");
    $tipoContrato  = trim($_POST["tipo_contrato"] ?? "");
    $dataInicio    = $_POST["data_inicio"] ?? "";
    $dataFim       = $_POST["data_fim"] ?? "";

    if (empty($idEquipamento)) $erros[] = "O Equipamento é obrigatório.";
    if (empty($dataInicio))    $erros[] = "A Data de Início é obrigatória.";
    if (empty($dataFim))       $erros[] = "A Data de Fim é obrigatória.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER, DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("INSERT INTO Garantia (idEquipamento, entidadeResponsavel, tipoContrato, dataInicio, dataFim) VALUES (:idEquipamento, :entidade, :tipoContrato, :dataInicio, :dataFim)");
            $stmt->execute([
                ':idEquipamento' => $idEquipamento,
                ':entidade'      => $entidade ?: null,
                ':tipoContrato'  => $tipoContrato ?: null,
                ':dataInicio'    => $dataInicio,
                ':dataFim'       => $dataFim
            ]);
            $ligacao = null;
            header('Location: garantias.php?sucesso=inserido');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao guardar: " . $err->getMessage();
        }
    }
}
?>
<?php include '../../includes/header.php'; ?>
<div class="app-viewport">
<?php include '../../includes/nav.php'; ?>
<div class="content-body">
<?php include '../../includes/sidebar.php'; ?>

<main class="main-content-wrapper">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="fw-bold h2 mb-1">Nova Garantia / Contrato</h1>
            <p class="text-muted small mb-0">Preencha os campos obrigatórios <span class="text-danger">*</span>.</p>
        </div>
        <a href="garantias.php" class="btn btn-outline-secondary px-3 py-2">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <?php if (!empty($erros)): ?>
        <div class="alert alert-danger mb-4">
            <strong><i class="fa-solid fa-circle-exclamation me-2"></i>Corrige os seguintes erros:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($erros as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($erro_sistema)): ?>
        <div class="alert alert-warning mb-4">
            <i class="fa-solid fa-wifi me-2"></i><?= htmlspecialchars($erro_sistema) ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <form action="" method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
                <select class="form-select" name="id_equipamento" required>
                    <option value="">-- Selecionar Equipamento --</option>
                    <?php foreach ($equipamentos as $eq): ?>
                        <option value="<?= $eq->idEquipamento ?>"
                            <?= (($_POST['id_equipamento'] ?? '') == $eq->idEquipamento) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($eq->nomeEquipamento) ?>
                            <?php if ($eq->codigoInventario): ?>
                                (<?= htmlspecialchars($eq->codigoInventario) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Entidade Responsável</label>
                <input type="text" class="form-control" name="entidade_responsavel"
                    placeholder="Ex: Siemens Healthineers"
                    value="<?= htmlspecialchars($_POST['entidade_responsavel'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Tipo de Contrato</label>
                <select class="form-select" name="tipo_contrato">
                    <option value="">-- Selecionar --</option>
                    <?php foreach (['garantia' => 'Garantia', 'manutencao' => 'Contrato de Manutenção', 'calibracao' => 'Contrato de Calibração', 'outro' => 'Outro'] as $val => $label):
                        $sel = (($_POST['tipo_contrato'] ?? '') == $val) ? 'selected' : '';
                    ?>
                        <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Data de Início <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="data_inicio"
                        value="<?= htmlspecialchars($_POST['data_inicio'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Data de Fim <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="data_fim"
                        value="<?= htmlspecialchars($_POST['data_fim'] ?? '') ?>" required>
                </div>
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
</div>
<?php include '../../includes/footer.php'; ?>