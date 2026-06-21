<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
redirect_if_not_admin();

$idEncrypted = $_GET['id_garantia'] ?? null;
$idGarantia  = aes_decrypt($idEncrypted);
if (!$idGarantia || !is_numeric($idGarantia)) {
    header('Location: garantias.php');
    exit;
}

$erros        = [];
$erro_sistema = '';
$garantia     = null;
$equipamentos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEquipamento       = $_POST['id_equipamento'] ?? '';
    $dataInicio          = $_POST['data_inicio'] ?? '';
    $dataFim             = $_POST['data_fim'] ?? '';
    $temContrato         = isset($_POST['tem_contrato']) ? 1 : 0;
    $tipoContrato        = trim($_POST['tipo_contrato'] ?? '');
    $entidadeResponsavel = trim($_POST['entidade_responsavel'] ?? '');
    $periodicidade       = trim($_POST['periodicidade'] ?? '');
    $observacoes         = trim($_POST['observacoes'] ?? '');

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
                UPDATE Garantia SET
                    idEquipamento       = :idEquip,
                    dataInicio          = :dataInicio,
                    dataFim             = :dataFim,
                    temContrato         = :temContrato,
                    tipoContrato        = :tipoContrato,
                    entidadeResponsavel = :entidade,
                    periodicidade       = :periodicidade,
                    observacoes         = :obs
                WHERE idGarantia = :id
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
                ':id'            => $idGarantia,
            ]);
            $ligacao = null;
            header('Location: garantias.php?sucesso=editado');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao guardar alterações.";
        }
    }
}

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $ligacao->prepare("SELECT * FROM Garantia WHERE idGarantia = :id AND ativo = 1");
    $stmt->bindParam(':id', $idGarantia, PDO::PARAM_INT);
    $stmt->execute();
    $garantia = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$garantia) {
        header('Location: garantias.php');
        exit;
    }
    $equipamentos = $ligacao->query("
        SELECT idEquipamento, nomeEquipamento, codigoInventario
        FROM Equipamento WHERE ativo = 1
        ORDER BY nomeEquipamento
    ")->fetchAll(PDO::FETCH_OBJ);
    $ligacao = null;
} catch (PDOException $err) {
    $erro_sistema = "Não foi possível carregar os dados.";
}

function val($campo, $bd) {
    return htmlspecialchars($_POST[$campo] ?? $bd ?? '');
}
function sel($campo, $bd, $valor) {
    $atual = $_POST[$campo] ?? $bd ?? '';
    return $atual == $valor ? 'selected' : '';
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
            <h1 class="fw-bold h2 mb-1">Editar Garantia</h1>
            <p class="text-muted small mb-0">
                Actualize os dados da garantia. Campos obrigatórios <span class="text-danger">*</span>
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

    <?php if ($garantia): ?>
    <form id="formEditarGarantia"
          action="editar_garantia.php?id_garantia=<?= htmlspecialchars($idEncrypted) ?>"
          method="POST" novalidate>

        <div class="row g-4">

            <div class="col-12 col-lg-8">

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Equipamento e Cobertura</p>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
                            <select class="form-select campo-obrigatorio" name="id_equipamento">
                                <option value="">Selecione...</option>
                                <?php foreach ($equipamentos as $eq):
                                    $lbl = htmlspecialchars($eq->nomeEquipamento);
                                    if ($eq->codigoInventario) $lbl = '[' . htmlspecialchars($eq->codigoInventario) . '] ' . $lbl;
                                ?>
                                    <option value="<?= $eq->idEquipamento ?>" <?= sel('id_equipamento', $garantia->idEquipamento, $eq->idEquipamento) ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Data de Início <span class="text-danger">*</span></label>
                            <input type="date" class="form-control campo-obrigatorio" name="data_inicio"
                                value="<?= val('data_inicio', $garantia->dataInicio) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Data de Fim <span class="text-danger">*</span></label>
                            <input type="date" class="form-control campo-obrigatorio" name="data_fim"
                                value="<?= val('data_fim', $garantia->dataFim) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Entidade Responsável <span class="text-danger">*</span></label>
                            <input type="text" class="form-control campo-obrigatorio" name="entidade_responsavel"
                                value="<?= val('entidade_responsavel', $garantia->entidadeResponsavel) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Periodicidade</label>
                            <select class="form-select" name="periodicidade">
                                <option value="">Sem periodicidade</option>
                                <?php foreach (['Mensal','Trimestral','Semestral','Anual'] as $p): ?>
                                    <option value="<?= $p ?>" <?= sel('periodicidade', $garantia->periodicidade, $p) ?>><?= $p ?></option>
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
                                    <?= (($_POST['tem_contrato'] ?? $garantia->temContrato) == 1) ? 'checked' : '' ?>>
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
                                ] as $v => $l): ?>
                                    <option value="<?= $v ?>" <?= sel('tipo_contrato', $garantia->tipoContrato, $v) ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Observações</p>
                    <textarea class="form-control" name="observacoes" rows="3"><?= val('observacoes', $garantia->observacoes) ?></textarea>
                </div>

            </div>

            <div class="col-12 col-lg-4">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-acao-primaria fw-bold py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                    </button>
                    <a href="garantias.php" class="btn btn-outline-secondary py-2">
                        <i class="fa-solid fa-xmark me-2"></i>Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>

</main>
</div>
</div>

<script>
document.getElementById('formEditarGarantia').addEventListener('submit', function(e) {
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