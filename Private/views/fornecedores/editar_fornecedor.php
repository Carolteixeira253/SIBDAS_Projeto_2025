<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
redirect_if_not_admin();

$idEncrypted   = $_GET['id_fornecedor'] ?? null;
$idFornecedor  = aes_decrypt($idEncrypted);
if (!$idFornecedor || !is_numeric($idFornecedor)) {
    header('Location: fornecedores.php');
    exit;
}

$erros        = [];
$erro_sistema = '';
$fornecedor   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeFornecedor     = trim($_POST['nome_fornecedor'] ?? '');
    $nif                = trim($_POST['nif'] ?? '');
    $contactoTelefonico = trim($_POST['contacto_telefonico'] ?? '');
    $enderecoEmail      = trim($_POST['endereco_email'] ?? '');
    $morada             = trim($_POST['morada'] ?? '');
    $website            = trim($_POST['website'] ?? '');
    $pessoaContacto     = trim($_POST['pessoa_contacto'] ?? '');
    $tipoFornecedor     = $_POST['tipo_fornecedor'] ?? '';
    $observacoes        = trim($_POST['observacoes'] ?? '');

    if (empty($nomeFornecedor))     $erros[] = "O Nome é obrigatório.";
    if (empty($nif))                $erros[] = "O NIF é obrigatório.";
    if (empty($contactoTelefonico)) $erros[] = "O Contacto Telefónico é obrigatório.";
    if (empty($enderecoEmail))      $erros[] = "O Email é obrigatório.";
    if (empty($tipoFornecedor))     $erros[] = "O Tipo de Fornecedor é obrigatório.";
    if (empty($pessoaContacto))     $erros[] = "A Pessoa de Contacto é obrigatória.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER, DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("
                UPDATE Fornecedor SET
                    nomeFornecedor     = :nome,
                    nif                = :nif,
                    contactoTelefonico = :tel,
                    enderecoEmail      = :email,
                    morada             = :morada,
                    website            = :website,
                    pessoaContacto     = :pessoa,
                    tipoFornecedor     = :tipo,
                    observacoes        = :obs
                WHERE idFornecedor = :id
            ");
            $stmt->execute([
                ':nome'    => $nomeFornecedor,
                ':nif'     => $nif,
                ':tel'     => $contactoTelefonico,
                ':email'   => $enderecoEmail,
                ':morada'  => $morada ?: null,
                ':website' => $website ?: null,
                ':pessoa'  => $pessoaContacto,
                ':tipo'    => $tipoFornecedor,
                ':obs'     => $observacoes ?: null,
                ':id'      => $idFornecedor,
            ]);
            $ligacao = null;
            header('Location: fornecedores.php?sucesso=editado');
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
    $stmt = $ligacao->prepare("SELECT * FROM Fornecedor WHERE idFornecedor = :id AND ativo = 1");
    $stmt->bindParam(':id', $idFornecedor, PDO::PARAM_INT);
    $stmt->execute();
    $fornecedor = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$fornecedor) {
        header('Location: fornecedores.php');
        exit;
    }
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
            <h1 class="fw-bold h2 mb-1">Editar Fornecedor</h1>
            <p class="text-muted small mb-0">
                Actualize os dados do fornecedor. Campos obrigatórios <span class="text-danger">*</span>
            </p>
        </div>
        <a href="fornecedores.php" class="btn btn-outline-secondary px-3 py-2">
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

    <?php if ($fornecedor): ?>
    <form id="formEditarFornecedor"
          action="editar_fornecedor.php?id_fornecedor=<?= htmlspecialchars($idEncrypted) ?>"
          method="POST" novalidate>

        <div class="row g-4">

            <!-- COLUNA ESQUERDA -->
            <div class="col-12 col-lg-8">

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Identificação</p>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nome da Entidade <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control campo-obrigatorio" id="nome_fornecedor" name="nome_fornecedor"
                                    placeholder="Ex: Philips Healthcare Portugal"
                                    value="<?= val('nome_fornecedor', $fornecedor->nomeFornecedor) ?>">
                                <button type="button" class="btn btn-outline-primary" id="btn-autopreenchimento" title="Auto-preencher campos">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">O nome é obrigatório.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">NIF <span class="text-danger">*</span></label>
                            <input type="text" class="form-control campo-obrigatorio" name="nif"
                                placeholder="Ex: 500123456" maxlength="9"
                                value="<?= val('nif', $fornecedor->nif) ?>">
                            <div class="invalid-feedback">O NIF é obrigatório.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo de Fornecedor <span class="text-danger">*</span></label>
                            <select class="form-select campo-obrigatorio" name="tipo_fornecedor">
                                <option value="">Selecione...</option>
                                <?php foreach ([
                                    'fabricante'          => 'Fabricante',
                                    'distribuidor'        => 'Distribuidor',
                                    'assistencia_tecnica' => 'Assistência Técnica',
                                    'consumiveis'         => 'Consumíveis',
                                ] as $v => $l): ?>
                                    <option value="<?= $v ?>" <?= sel('tipo_fornecedor', $fornecedor->tipoFornecedor, $v) ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione o tipo.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Website</label>
                            <input type="text" class="form-control" name="website"
                                placeholder="Ex: www.philips.pt"
                                value="<?= val('website', $fornecedor->website) ?>">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Contactos</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control campo-obrigatorio" name="contacto_telefonico"
                                placeholder="Ex: +351 21 000 0000"
                                value="<?= val('contacto_telefonico', $fornecedor->contactoTelefonico) ?>">
                            <div class="invalid-feedback">O telefone é obrigatório.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control campo-obrigatorio" name="endereco_email"
                                placeholder="Ex: geral@empresa.pt"
                                value="<?= val('endereco_email', $fornecedor->enderecoEmail) ?>">
                            <div class="invalid-feedback">O email é obrigatório.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pessoa de Contacto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control campo-obrigatorio" name="pessoa_contacto"
                                placeholder="Ex: João Silva"
                                value="<?= val('pessoa_contacto', $fornecedor->pessoaContacto) ?>">
                            <div class="invalid-feedback">A pessoa de contacto é obrigatória.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Morada</label>
                            <input type="text" class="form-control" name="morada"
                                placeholder="Ex: Rua da Saúde, 1 — Lisboa"
                                value="<?= val('morada', $fornecedor->morada) ?>">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Observações</p>
                    <textarea class="form-control" name="observacoes" rows="3"
                        placeholder="Notas adicionais..."><?= val('observacoes', $fornecedor->observacoes) ?></textarea>
                </div>

            </div>

            <!-- COLUNA DIREITA -->
            <div class="col-12 col-lg-4">

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Fornecedor Actual</p>
                    <p class="fw-bold mb-1"><?= htmlspecialchars($fornecedor->nomeFornecedor) ?></p>
                    <p class="text-muted small mb-1">NIF: <?= htmlspecialchars($fornecedor->nif ?? 'N/D') ?></p>
                    <p class="text-muted small mb-0"><?= htmlspecialchars($fornecedor->enderecoEmail ?? 'N/D') ?></p>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-acao-primaria fw-bold py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                    </button>
                    <a href="fornecedores.php" class="btn btn-outline-secondary py-2">
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
document.getElementById('formEditarFornecedor').addEventListener('submit', function(e) {
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

// Varinha mágica — auto-preenchimento por nome do fornecedor
const sugestoesFornecedor = {
    'philips':      { tipo: 'fabricante', website: 'www.philips.pt', telefone: '+351 21 413 5000' },
    'dräger':       { tipo: 'fabricante', website: 'www.draeger.com/pt', telefone: '+351 22 608 0300' },
    'draeger':      { tipo: 'fabricante', website: 'www.draeger.com/pt', telefone: '+351 22 608 0300' },
    'ge healthcare':{ tipo: 'fabricante', website: 'www.gehealthcare.com', telefone: '+351 21 423 0000' },
    'siemens':      { tipo: 'fabricante', website: 'www.siemens-healthineers.pt', telefone: '+351 21 722 5000' },
    'medtronic':    { tipo: 'fabricante', website: 'www.medtronic.pt', telefone: '+351 21 318 5200' },
    'b. braun':     { tipo: 'fabricante', website: 'www.bbraun.pt', telefone: '+351 21 318 9300' },
    'fresenius':    { tipo: 'fabricante', website: 'www.freseniusmedicalcare.pt', telefone: '+351 21 424 5500' },
    'olympus':      { tipo: 'fabricante', website: 'www.olympus-europa.com/pt', telefone: '+351 21 315 6400' },
    'stryker':      { tipo: 'fabricante', website: 'www.stryker.pt', telefone: '+351 21 413 9800' },
    'zeiss':        { tipo: 'fabricante', website: 'www.zeiss.pt', telefone: '+351 21 000 4444' },
    'roche':        { tipo: 'fabricante', website: 'www.roche.pt', telefone: '+351 21 425 7000' },
    'distribuidor': { tipo: 'distribuidor', website: '', telefone: '' },
    'assistencia':  { tipo: 'assistencia_tecnica', website: '', telefone: '' },
    'techmed':      { tipo: 'assistencia_tecnica', website: 'www.techmed.pt', telefone: '+351 22 933 4400' },
};

document.getElementById('btn-autopreenchimento').addEventListener('click', function() {
    const nome = document.getElementById('nome_fornecedor').value.toLowerCase().trim();
    if (!nome) {
        alert('Escreve primeiro o nome do fornecedor!');
        return;
    }

    let sugestao = null;
    for (const [chave, dados] of Object.entries(sugestoesFornecedor)) {
        if (nome.includes(chave)) { sugestao = dados; break; }
    }

    if (!sugestao) {
        alert('Não encontrei sugestões. Preenche manualmente.');
        return;
    }

    const preencher = (selector, valor) => {
        const el = document.querySelector(selector);
        if (el && !el.value) el.value = valor;
    };

    preencher('[name="tipo_fornecedor"]', sugestao.tipo);
    preencher('[name="website"]', sugestao.website);
    preencher('[name="contacto_telefonico"]', sugestao.telefone);

    this.innerHTML = '<i class="fa-solid fa-check"></i>';
    this.classList.replace('btn-outline-primary', 'btn-success');
    setTimeout(() => {
        this.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i>';
        this.classList.replace('btn-success', 'btn-outline-primary');
    }, 2000);
});
</script>

<?php include '../../includes/footer.php'; ?>