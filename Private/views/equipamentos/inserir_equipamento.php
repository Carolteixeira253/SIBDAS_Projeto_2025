<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

// Verificar se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recolher dados
    $nome = $_POST["nome_equipamento"] ?? "";
    $categoria = $_POST["categoria_equipamento"] ?? "";
    $estado = $_POST["estado_equipamento"] ?? "";
    $localizacao = $_POST["localizacao_equipamento"] ?? "";
    $criticidade = $_POST["criticidade_equipamento"] ?? "";

    // 2. Validar os dados
    $erros = [];

    $nome = trim($nome);
    if (empty($nome)) {
        $erros[] = "O campo Nome é obrigatório.";
    } elseif (preg_match('/\d/', $nome)) {
        $erros[] = "O campo Nome não pode conter números.";
    }

    if (empty($categoria)) {
        $erros[] = "A Categoria é obrigatória.";
    }

    if (empty($estado)) {
        $erros[] = "O Estado é obrigatório.";
    }
    // 3. Normalizar dados
    $nome = ucwords(strtolower($nome)); // Ex: "ventilador pulmonar" → "Ventilador Pulmonar"
    $categoria = ucfirst(strtolower($categoria)); // Ex: "VENTILAÇÃO" → "Ventilação"
    // 4. Se não houver erros, guardar na BD
    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "INSERT INTO Equipamento (nomeEquipamento, categoria, estado, idLocalizacao, criticidadeClinica) 
                    VALUES (:nome, :categoria, :estado, :localizacao, :criticidade)";

            $stmt = $ligacao->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':categoria' => $categoria,
                ':estado' => $estado,
                ':localizacao' => $localizacao ?: null,
                ':criticidade' => $criticidade
            ]);

            $ligacao = null;

            // Redireciona para a lista de equipamentos
            header('Location: equipamentos.php');
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
                <h1 class="fw-bold h2 mb-1 text-dark">Novo Equipamento</h1>
                <p class="text-muted small mb-0">Preencha os dados do novo equipamento.</p>
            </div>
            <a href="equipamentos.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4">
            <!-- Área de erros -->
            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Foram encontrados os seguintes erros:</strong>
                    <ul class="mb-0">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Área de erro de sistema (BD) -->
            <?php if (!empty($erro_sistema)): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Erro:</strong> <?= htmlspecialchars($erro_sistema) ?>
                </div>
            <?php endif; ?>
            <form action="#" method="POST" novalidate>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome do Equipamento</label>
                    <input type="text" class="form-control" name="nome_equipamento"
                        placeholder="Ex: Ventilador"
                        value="<?= $_POST['nome_equipamento'] ?? '' ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Categoria</label>
                    <select class="form-select" name="categoria_equipamento" required>
                        <option value="">-- Selecionar --</option>
                        <option value="Ventilação" <?= (($_POST['categoria_equipamento'] ?? '') == 'Ventilação') ? 'selected' : '' ?>>Ventilação</option>
                        <option value="Imagem" <?= (($_POST['categoria_equipamento'] ?? '') == 'Imagem') ? 'selected' : '' ?>>Imagem</option>
                        <option value="Monitorização" <?= (($_POST['categoria_equipamento'] ?? '') == 'Monitorização') ? 'selected' : '' ?>>Monitorização</option>
                        <option value="Diagnóstico" <?= (($_POST['categoria_equipamento'] ?? '') == 'Diagnóstico') ? 'selected' : '' ?>>Diagnóstico</option>
                        <option value="Suporte de Vida" <?= (($_POST['categoria_equipamento'] ?? '') == 'Suporte de Vida') ? 'selected' : '' ?>>Suporte de Vida</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Estado</label>
                    <select class="form-select" name="estado_equipamento" required>
                        <option value="">-- Selecionar --</option>
                        <option value="operacional" <?= (($_POST['estado_equipamento'] ?? '') == 'operacional') ? 'selected' : '' ?>>Operacional</option>
                        <option value="manutencao" <?= (($_POST['estado_equipamento'] ?? '') == 'manutencao') ? 'selected' : '' ?>>Manutenção</option>
                        <option value="avariado" <?= (($_POST['estado_equipamento'] ?? '') == 'avariado') ? 'selected' : '' ?>>Avariado</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Localização (ID)</label>
                    <input type="number" class="form-control" name="localizacao_equipamento"
                        placeholder="Ex: 1"
                        value="<?= $_POST['localizacao_equipamento'] ?? '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Criticidade Clínica</label>
                    <select class="form-select" name="criticidade_equipamento" required>
                        <option value="">-- Selecionar --</option>
                        <option value="Alta" <?= (($_POST['criticidade_equipamento'] ?? '') == 'Alta') ? 'selected' : '' ?>>Alta</option>
                        <option value="Media" <?= (($_POST['criticidade_equipamento'] ?? '') == 'Media') ? 'selected' : '' ?>>Média</option>
                        <option value="Baixa" <?= (($_POST['criticidade_equipamento'] ?? '') == 'Baixa') ? 'selected' : '' ?>>Baixa</option>
                    </select>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Equipamento
                    </button>
                    <a href="equipamentos.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                </div>

            </form>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>