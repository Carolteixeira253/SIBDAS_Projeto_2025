<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
redirect_if_not_admin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeSala = trim($_POST["nome_sala"] ?? "");
    $edificio = trim($_POST["edificio"] ?? "");
    $servico  = trim($_POST["servico"] ?? "");
    $piso     = trim($_POST["piso"] ?? "");

    $erros = [];
    if (empty($nomeSala)) $erros[] = "O Nome da Sala é obrigatório.";
    if (empty($piso))     $erros[] = "O Piso é obrigatório.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER, DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("INSERT INTO Localizacao (nomeSala, edificio, servico, piso) VALUES (:nomeSala, :edificio, :servico, :piso)");
            $stmt->execute([
                ':nomeSala' => $nomeSala,
                ':edificio' => $edificio ?: null,
                ':servico'  => $servico ?: null,
                ':piso'     => $piso
            ]);
            $ligacao = null;
            header('Location: localizacoes.php?sucesso=inserido');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Não foi possível guardar a localização. Tente novamente.";
        }
    }
}
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>
<div class="app-viewport">
<div class="content-body">
<?php include '../../includes/sidebar.php'; ?>

<main class="main-content-wrapper">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="fw-bold h2 mb-1">Nova Localização</h1>
            <p class="text-muted small mb-0">
                Preencha os campos obrigatórios <span class="text-danger">*</span> para registar a localização.
            </p>
        </div>
        <a href="localizacoes.php" class="btn btn-outline-secondary px-3 py-2">
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

    <form id="formInserirLocalizacao" action="" method="POST" novalidate>
        <div class="row g-4">

            <!-- COLUNA ESQUERDA -->
            <div class="col-12 col-lg-8">

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Identificação</p>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nome da Sala <span class="text-danger">*</span></label>
                            <input type="text" class="form-control campo-obrigatorio" name="nome_sala"
                                placeholder="Ex: Sala de Emergência 1"
                                value="<?= htmlspecialchars($_POST['nome_sala'] ?? '') ?>">
                            <div class="invalid-feedback">O nome da sala é obrigatório.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Piso <span class="text-danger">*</span></label>
                            <input type="text" class="form-control campo-obrigatorio" name="piso"
                                placeholder="Ex: 0, 1, -1"
                                value="<?= htmlspecialchars($_POST['piso'] ?? '') ?>">
                            <div class="invalid-feedback">O piso é obrigatório.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Edifício</label>
                            <input type="text" class="form-control" name="edificio"
                                placeholder="Ex: Edifício Principal"
                                value="<?= htmlspecialchars($_POST['edificio'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Serviço</label>
                            <input type="text" class="form-control" name="servico"
                                placeholder="Ex: Urgências"
                                value="<?= htmlspecialchars($_POST['servico'] ?? '') ?>">
                        </div>
                    </div>
                </div>

            </div>

            <!-- COLUNA DIREITA -->
            <div class="col-12 col-lg-4">

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Resumo</p>
                    <p class="text-muted small mb-0">
                        Preencha os dados da localização hospitalar.
                        Os campos marcados com <span class="text-danger">*</span> são obrigatórios.
                    </p>
                    <hr>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Nome da sala</li>
                        <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Piso</li>
                        <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Edifício (opcional)</li>
                        <li><i class="fa-solid fa-check text-success me-2"></i>Serviço (opcional)</li>
                    </ul>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-acao-primaria fw-bold py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Localização
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
document.getElementById('formInserirLocalizacao').addEventListener('submit', function(e) {
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
document.querySelectorAll('.campo-obrigatorio').forEach(function(campo) {
    campo.addEventListener('change', function() {
        if (this.value.trim()) this.classList.remove('is-invalid');
    });
});
// Auto-sugestão baseada no serviço
const sugestoesServico = {
    'urgências':              { edificio: 'Edifício Principal', piso: '0' },
    'unidade de cuidados intensivos': { edificio: 'Edifício Principal', piso: '1' },
    'bloco operatório':       { edificio: 'Edifício Cirurgia', piso: '2' },
    'recobro':                { edificio: 'Edifício Cirurgia', piso: '2' },
    'pediatria':              { edificio: 'Edifício Pediátrico', piso: '1' },
    'neonatologia':           { edificio: 'Edifício Pediátrico', piso: '2' },
    'cardiologia':            { edificio: 'Edifício Ambulatório', piso: '1' },
    'ortopedia':              { edificio: 'Edifício Ambulatório', piso: '2' },
    'medicina física':        { edificio: 'Edifício Reabilitação', piso: '0' },
    'patologia clínica':      { edificio: 'Edifício Laboratórios', piso: '0' },
    'microbiologia':          { edificio: 'Edifício Laboratórios', piso: '0' },
    'esterilização':          { edificio: 'Edifício Serviços', piso: '-1' },
    'radiologia':             { edificio: 'Edifício Imagiologia', piso: '0' },
    'tomografia':             { edificio: 'Edifício Imagiologia', piso: '0' },
    'imagiologia':            { edificio: 'Edifício Imagiologia', piso: '0' },
    'gastrenterologia':       { edificio: 'Edifício Ambulatório', piso: '1' },
    'neurologia':             { edificio: 'Edifício Ambulatório', piso: '3' },
    'farmácia':               { edificio: 'Edifício Principal', piso: '0' },
    'obstetrícia':            { edificio: 'Edifício Maternidade', piso: '1' },
    'medicina interna':       { edificio: 'Edifício Principal', piso: '3' },
    'pneumologia':            { edificio: 'Edifício Ambulatório', piso: '2' },
    'nefrologia':             { edificio: 'Edifício Ambulatório', piso: '0' },
    'oncologia':              { edificio: 'Edifício Oncologia', piso: '1' },
    'psiquiatria':            { edificio: 'Edifício Psiquiatria', piso: '1' },
    'oftalmologia':           { edificio: 'Edifício Cirurgia', piso: '2' },
    'urologia':               { edificio: 'Edifício Cirurgia', piso: '2' },
    'anatomia patológica':    { edificio: 'Edifício Laboratórios', piso: '-1' },
    'imunohemoterapia':       { edificio: 'Edifício Laboratórios', piso: '0' },
};

const inputServico  = document.querySelector('[name="servico"]');
const inputEdificio = document.querySelector('[name="edificio"]');
const inputPiso     = document.querySelector('[name="piso"]');

inputServico.addEventListener('blur', function() {
    const servico = this.value.toLowerCase().trim();
    if (!servico) return;

    let sugestao = null;
    for (const [chave, dados] of Object.entries(sugestoesServico)) {
        if (servico.includes(chave)) { sugestao = dados; break; }
    }
    if (!sugestao) return;

    if (!inputEdificio.value) {
        inputEdificio.value = sugestao.edificio;
        inputEdificio.style.borderColor = '#0a478a';
        setTimeout(() => inputEdificio.style.borderColor = '', 2000);
    }
    if (!inputPiso.value) {
        inputPiso.value = sugestao.piso;
        inputPiso.style.borderColor = '#0a478a';
        setTimeout(() => inputPiso.style.borderColor = '', 2000);
    }
});
</script>

<?php include '../../includes/footer.php'; ?>