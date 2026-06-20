<?php
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../../config/config.php';
redirect_if_not_logged();
redirect_if_not_admin();
$erros        = [];
$erro_sistema = '';
$sucesso      = false;
$configs      = [];
// Carregar configurações da BD (Ficha 12 — GET)
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $rows = $ligacao->query("SELECT chave, valor FROM Configuracao")->fetchAll(PDO::FETCH_OBJ);
    foreach ($rows as $row) {
        $configs[$row->chave] = $row->valor;
    }
    $ligacao = null;
} catch (PDOException $err) {
    $erro_sistema = "Não foi possível carregar as configurações. Verifique a sua ligação à internet.";
}
// Processar POST (Ficha 13 — bloco de submissão)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = [
        'nome_hospital'      => trim($_POST['nome_hospital']      ?? ''),
        'hero_titulo'        => trim($_POST['hero_titulo']        ?? ''),
        'hero_descricao'     => trim($_POST['hero_descricao']     ?? ''),
        'quem_somos_titulo'  => trim($_POST['quem_somos_titulo']  ?? ''),
        'quem_somos_texto'   => trim($_POST['quem_somos_texto']   ?? ''),
        'telefone'           => trim($_POST['telefone']           ?? ''),
        'email'              => trim($_POST['email']              ?? ''),
        'morada'             => trim($_POST['morada']             ?? ''),
    ];
    // Validação servidor ($erros[])
    if (empty($campos['nome_hospital']))
        $erros[] = "O nome do hospital é obrigatório.";
    if (empty($campos['hero_titulo']))
        $erros[] = "O título da página inicial é obrigatório.";
    if (!empty($campos['email']) && !filter_var($campos['email'], FILTER_VALIDATE_EMAIL))
        $erros[] = "O email introduzido não é válido.";
    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare(
                "INSERT INTO Configuracao (chave, valor)
                 VALUES (:chave, :valor)
                 ON DUPLICATE KEY UPDATE valor = :valor"
            );
            foreach ($campos as $chave => $valor) {
                $stmt->execute([':chave' => $chave, ':valor' => $valor]);
                $configs[$chave] = $valor; // atualizar array local para refletir no form
            }
            $ligacao = null;
            $sucesso = true;
        } catch (PDOException $err) {
            $erro_sistema = "Não foi possível guardar as configurações. Verifique a sua ligação à internet.";
        }
    }
}
// Helper para preencher campos (POST > BD > vazio)
function cfg($key, $configs)
{
    return htmlspecialchars($_POST[$key] ?? $configs[$key] ?? '');
}
?>
<?php include '../../../includes/header.php'; ?>
<?php include '../../../includes/nav.php'; ?>
<div class="content-body">
    <?php include '../../../includes/sidebar.php'; ?>
    <main class="main-content-wrapper">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="fw-bold h2 mb-1 text-dark">
                    <i class="fa-solid fa-globe text-primary me-2"></i>Editar Site Público
                </h1>
                <p class="text-muted small mb-0">
                    Edite os textos e informações visíveis no site público do hospital.
                    Os campos com <span class="text-danger">*</span> são obrigatórios.
                </p>
            </div>
            <a href="/medcare-inventory-solutions/Public/index.php" target="_blank"
                class="btn btn-outline-primary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Ver Site Público
            </a>
        </div>
        <!-- Mensagem de sucesso -->
        <?php if ($sucesso): ?>
            <div class="alert alert-success d-flex align-items-center gap-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div><strong>Configurações guardadas com sucesso!</strong> O site público foi atualizado.</div>
            </div>
        <?php endif; ?>
        <!-- Erros de validação -->
        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger" role="alert">
                <strong><i class="fa-solid fa-circle-exclamation me-2"></i>Foram encontrados erros:</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($erros as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <!-- Erro de sistema -->
        <?php if (!empty($erro_sistema)): ?>
            <div class="alert alert-warning d-flex align-items-center gap-3">
                <i class="fa-solid fa-wifi text-warning fs-4"></i>
                <div><?= htmlspecialchars($erro_sistema) ?></div>
            </div>
        <?php endif; ?>
        <form id="formSitePublico"
            action="editar_site_publico.php"
            method="POST"
            novalidate>
            <div class="card-stat border-0 shadow-sm rounded-3 p-4 mb-4">
                <!-- SECÇÃO: Página Inicial -->
                <p class="secao-form-titulo mt-0">Página Inicial (index.php)</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">
                            Nome do Hospital <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control campo-obrigatorio"
                            name="nome_hospital"
                            placeholder="Ex: MedCare Inventory Solutions"
                            value="<?= cfg('nome_hospital', $configs) ?>">
                        <div class="invalid-feedback">O nome do hospital é obrigatório.</div>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">
                            Título Principal (Hero) <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control campo-obrigatorio"
                            name="hero_titulo"
                            placeholder="Ex: Gestão Eficiente de Inventário Médico"
                            value="<?= cfg('hero_titulo', $configs) ?>">
                        <div class="invalid-feedback">O título é obrigatório.</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Descrição (Hero)</label>
                        <textarea class="form-control" name="hero_descricao" rows="2"
                            placeholder="Texto de apresentação na página inicial..."><?= cfg('hero_descricao', $configs) ?></textarea>
                    </div>
                </div>
                <!-- SECÇÃO: Quem Somos -->
                <p class="secao-form-titulo">Página Quem Somos</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Título</label>
                        <input type="text" class="form-control"
                            name="quem_somos_titulo"
                            placeholder="Ex: Líderes em Engenharia Biomédica"
                            value="<?= cfg('quem_somos_titulo', $configs) ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Texto de Apresentação</label>
                        <textarea class="form-control" name="quem_somos_texto" rows="4"
                            placeholder="Texto sobre o hospital / empresa..."><?= cfg('quem_somos_texto', $configs) ?></textarea>
                    </div>
                </div>
                <!-- SECÇÃO: Contactos -->
                <p class="secao-form-titulo">Informações de Contacto</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Telefone</label>
                        <input type="text" class="form-control"
                            name="telefone"
                            placeholder="Ex: +351 220 000 000"
                            value="<?= cfg('telefone', $configs) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control"
                            name="email"
                            placeholder="Ex: geral@medcare.pt"
                            value="<?= cfg('email', $configs) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Morada</label>
                        <input type="text" class="form-control"
                            name="morada"
                            placeholder="Ex: Rua da Saúde, 1234 — Porto"
                            value="<?= cfg('morada', $configs) ?>">
                    </div>
                </div>
            </div><!-- /card -->
            <div class="d-flex justify-content-end gap-2">
                <a href="/medcare-inventory-solutions/Private/index.php"
                    class="btn btn-outline-secondary px-4 py-2">
                    <i class="fa-solid fa-xmark me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                </button>
            </div>
        </form>
    </main>
</div>
<!-- Validação JS lado cliente (Ficha 13) -->
<script>
    document.getElementById('formSitePublico').addEventListener('submit', function(e) {
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
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    });
    document.querySelectorAll('.campo-obrigatorio').forEach(function(campo) {
        campo.addEventListener('input', function() {
            if (this.value.trim()) this.classList.remove('is-invalid');
        });
    });
</script>
<?php include '../../../includes/footer.php'; ?>