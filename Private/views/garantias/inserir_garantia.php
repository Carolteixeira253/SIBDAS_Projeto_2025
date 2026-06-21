<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
redirect_if_not_admin();

$erros = [];
$erro_sistema = '';
$equipamentos = [];

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $equipamentos = $ligacao->query("
        SELECT idEquipamento, nomeEquipamento, codigoInventario
        FROM Equipamento WHERE ativo = 1
        ORDER BY nomeEquipamento
    ")->fetchAll(PDO::FETCH_OBJ);
    $ligacao = null;
} catch (PDOException $err) {
    $erro_sistema = "Não foi possível carregar os equipamentos.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idEquipamento       = $_POST["id_equipamento"] ?? "";
    $dataInicio          = $_POST["data_inicio"] ?? "";
    $dataFim             = $_POST["data_fim"] ?? "";
    $temContrato         = isset($_POST["tem_contrato"]) ? 1 : 0;
    $tipoContrato        = trim($_POST["tipo_contrato"] ?? "");
    $entidadeResponsavel = trim($_POST["entidade_responsavel"] ?? "");
    $periodicidade       = trim($_POST["periodicidade"] ?? "");
    $observacoes         = trim($_POST["observacoes"] ?? "");

    if (empty($idEquipamento))       $erros[] = "O Equipamento é obrigatório.";
    if (empty($dataInicio))          $erros[] = "A Data de Início é obrigatória.";
    if (empty($dataFim))             $erros[] = "A Data de Fim é obrigatória.";
    if (empty($entidadeResponsavel)) $erros[] = "A Entidade Responsável é obrigatória.";
    if (!empty($dataInicio) && !empty($dataFim) && $dataFim < $dataInicio)
        $erros[] = "A Data de Fim não pode ser anterior à Data de Início.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER, DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("
                INSERT INTO Garantia
                    (idEquipamento, dataInicio, dataFim, temContrato, tipoContrato, entidadeResponsavel, periodicidade, observacoes)
                VALUES
                    (:idEquip, :dataInicio, :dataFim, :temContrato, :tipoContrato, :entidade, :periodicidade, :obs)
            ");
            $stmt->execute([
                ':idEquip'       => $idEquipamento,
                ':dataInicio'    => $dataInicio,
                ':dataFim'       => $dataFim,
                ':temContrato'   => $temContrato,
                ':tipoContrato'  => $tipoContrato ?: null,
                ':entidade'      => $entidadeResponsavel,
                ':periodicidade' => $periodicidade ?: null,
                ':obs'           => $observacoes ?: null,
            ]);
            $ligacao = null;
            header('Location: garantias.php?sucesso=inserido');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Não foi possível guardar a garantia. Tente novamente.";
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
            <h1 class="fw-bold h2 mb-1">Nova Garantia</h1>
            <p class="text-muted small mb-0">
                Preencha os campos obrigatórios <span class="text-danger">*</span> para registar a garantia.
            </p>
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

    <form id="formInserirGarantia" action="" method="POST" novalidate>
        <div class="row g-4">

            <!-- COLUNA ESQUERDA -->
            <div class="col-12 col-lg-8">

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Equipamento e Cobertura</p>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
                            <select class="form-select campo-obrigatorio" name="id_equipamento">
                                <option value="">Selecione o equipamento...</option>
                                <?php foreach ($equipamentos as $eq):
                                    $lbl = htmlspecialchars($eq->nomeEquipamento);
                                    if ($eq->codigoInventario) $lbl = '[' . htmlspecialchars($eq->codigoInventario) . '] ' . $lbl;
                                    $sel = (($_POST['id_equipamento'] ?? '') == $eq->idEquipamento) ? 'selected' : '';
                                ?>
                                    <option value="<?= $eq->idEquipamento ?>" <?= $sel ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione um equipamento.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Data de Início <span class="text-danger">*</span></label>
                            <input type="date" class="form-control campo-obrigatorio" name="data_inicio"
                                value="<?= htmlspecialchars($_POST['data_inicio'] ?? '') ?>">
                            <div class="invalid-feedback">A data de início é obrigatória.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Data de Fim <span class="text-danger">*</span></label>
                            <input type="date" class="form-control campo-obrigatorio" name="data_fim"
                                value="<?= htmlspecialchars($_POST['data_fim'] ?? '') ?>">
                            <div class="invalid-feedback">A data de fim é obrigatória.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Entidade Responsável <span class="text-danger">*</span></label>
                            <input type="text" class="form-control campo-obrigatorio" name="entidade_responsavel"
                                placeholder="Ex: Philips Healthcare Portugal"
                                value="<?= htmlspecialchars($_POST['entidade_responsavel'] ?? '') ?>">
                            <div class="invalid-feedback">A entidade é obrigatória.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Periodicidade</label>
                            <select class="form-select" name="periodicidade">
                                <option value="">Sem periodicidade</option>
                                <?php foreach (['Mensal','Trimestral','Semestral','Anual'] as $p):
                                    $sel = (($_POST['periodicidade'] ?? '') == $p) ? 'selected' : '';
                                ?>
                                    <option value="<?= $p ?>" <?= $sel ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Contrato</p>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="tem_contrato" id="tem_contrato"
                                    <?= isset($_POST['tem_contrato']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="tem_contrato">
                                    Tem contrato de manutenção associado
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Tipo de Contrato</label>
                            <select class="form-select" name="tipo_contrato">
                                <option value="">Sem contrato</option>
                                <?php foreach ([
                                    'Manutenção Preventiva' => 'Manutenção Preventiva',
                                    'Manutenção Preventiva e Correctiva' => 'Manutenção Preventiva e Correctiva',
                                    'Full Service' => 'Full Service',
                                    'Garantia Alargada' => 'Garantia Alargada',
                                ] as $v => $l):
                                    $sel = (($_POST['tipo_contrato'] ?? '') == $v) ? 'selected' : '';
                                ?>
                                    <option value="<?= $v ?>" <?= $sel ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Observações</p>
                    <textarea class="form-control" name="observacoes" rows="3"
                        placeholder="Notas adicionais sobre a garantia..."><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                </div>

            </div>

            <!-- COLUNA DIREITA -->
            <div class="col-12 col-lg-4">

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Resumo</p>
                    <p class="text-muted small mb-0">
                        Registe a garantia ou contrato de manutenção associado ao equipamento.
                    </p>
                    <hr>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Equipamento</li>
                        <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Data de início</li>
                        <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Data de fim</li>
                        <li><i class="fa-solid fa-check text-success me-2"></i>Entidade responsável</li>
                    </ul>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-acao-primaria fw-bold py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Garantia
                    </button>
                    <button type="reset" class="btn btn-outline-secondary py-2">
                        <i class="fa-solid fa-rotate-left me-2"></i>Limpar Campos
                    </button>
                </div>

            </div>
        </div>
    </form>

</main>
</div>
</div>

<script>
document.getElementById('formInserirGarantia').addEventListener('submit', function(e) {
    let valido = true;
    document.querySelectorAll('.campo-obrigatorio').forEach(function(campo) {
        campo.classList.remove('is-invalid');
        if (!campo.value.trim()) {
            campo.classList.add('is-invalid');
            valido = false;
        }
    });
    if (!valido) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>