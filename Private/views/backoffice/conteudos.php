<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
redirect_if_not_admin();

$erros = [];
$sucesso = false;
$configs = [];

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $rows = $ligacao->query("SELECT chave, valor FROM Configuracao")->fetchAll(PDO::FETCH_OBJ);
    foreach ($rows as $row) {
        $configs[$row->chave] = $row->valor;
    }
    $ligacao = null;
} catch (PDOException $err) {
    $erros[] = "Não foi possível carregar as configurações.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = [
        'nome_hospital'     => trim($_POST['nome_hospital']     ?? ''),
        'hero_titulo'       => trim($_POST['hero_titulo']       ?? ''),
        'hero_descricao'    => trim($_POST['hero_descricao']    ?? ''),
        'sobre_titulo'      => trim($_POST['sobre_titulo']      ?? ''),
        'sobre_texto'       => trim($_POST['sobre_texto']       ?? ''),
        'contacto_email'    => trim($_POST['contacto_email']    ?? ''),
        'contacto_telefone' => trim($_POST['contacto_telefone'] ?? ''),
        'contacto_morada'   => trim($_POST['contacto_morada']   ?? ''),
    ];

    if (empty($campos['nome_hospital'])) $erros[] = "O Nome do Hospital é obrigatório.";
    if (empty($campos['hero_titulo']))   $erros[] = "O Título principal é obrigatório.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER, DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("UPDATE Configuracao SET valor = :valor WHERE chave = :chave");
            foreach ($campos as $chave => $valor) {
                $stmt->execute([':chave' => $chave, ':valor' => $valor]);
            }
            $ligacao = null;
            $configs = $campos;
            $sucesso = true;
        } catch (PDOException $err) {
            $erros[] = "Erro ao guardar as configurações.";
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
            <h1 class="fw-bold h2 mb-1">Editar Site Público</h1>
            <p class="text-muted small mb-0">Actualize os conteúdos apresentados na área pública do sistema.</p>
        </div>
        <a href="/medcare-inventory-solutions/Public/index.php" target="_blank" class="btn btn-outline-secondary px-3 py-2">
            <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Ver Site Público
        </a>
    </div>

    <?php if ($sucesso): ?>
        <div class="alert alert-success mb-4">
            <i class="fa-solid fa-circle-check me-2"></i>Configurações guardadas com sucesso!
        </div>
    <?php endif; ?>

    <?php if (!empty($erros)): ?>
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">
                <?php foreach ($erros as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
        <div class="row g-4">

            <div class="col-12 col-lg-8">

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Identificação</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nome do Hospital <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nome_hospital"
                            value="<?= htmlspecialchars($configs['nome_hospital'] ?? '') ?>">
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Página Inicial</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título Principal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="hero_titulo"
                            value="<?= htmlspecialchars($configs['hero_titulo'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descrição</label>
                        <textarea class="form-control" name="hero_descricao" rows="3"><?= htmlspecialchars($configs['hero_descricao'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Secção Sobre</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título</label>
                        <input type="text" class="form-control" name="sobre_titulo"
                            value="<?= htmlspecialchars($configs['sobre_titulo'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Texto</label>
                        <textarea class="form-control" name="sobre_texto" rows="4"><?= htmlspecialchars($configs['sobre_texto'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Contactos</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" name="contacto_email"
                                value="<?= htmlspecialchars($configs['contacto_email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefone</label>
                            <input type="text" class="form-control" name="contacto_telefone"
                                value="<?= htmlspecialchars($configs['contacto_telefone'] ?? '') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Morada</label>
                            <input type="text" class="form-control" name="contacto_morada"
                                value="<?= htmlspecialchars($configs['contacto_morada'] ?? '') ?>">
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Informação</p>
                    <p class="text-muted small mb-0">
                        As alterações aqui efectuadas serão reflectidas imediatamente na área pública do site.
                    </p>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-acao-primaria fw-bold py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                    </button>
                    <a href="/medcare-inventory-solutions/Public/index.php" target="_blank"
                        class="btn btn-outline-secondary py-2">
                        <i class="fa-solid fa-eye me-2"></i>Pré-visualizar Site
                    </a>
                </div>
            </div>

        </div>
    </form>

</main>
</div>
</div>

<?php include '../../includes/footer.php'; ?>