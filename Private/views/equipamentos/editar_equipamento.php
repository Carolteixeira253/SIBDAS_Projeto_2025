<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
require_once __DIR__ . '/../../includes/validacoes.php';

// Permitir apenas GET e POST
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('Location: /medcare-inventory-solutions/Public/login.php');
    exit;
}

// Obter e desencriptar o ID
$idEquipamentoEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEquipamentoEncrypted);

if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: /medcare-inventory-solutions/Private/views/equipamentos/equipamentos.php');
    exit;
}

// Processar o POST (guardar alterações)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_equipamento'] ?? '';
    $categoria = $_POST['categoria_equipamento'] ?? '';
    $estado = $_POST['estado_equipamento'] ?? '';
    $criticidade = $_POST['criticidade_equipamento'] ?? '';

    $erros = [];

    $nome = trim($nome);
    $erros = validar_nome($nome);

    if (empty($categoria)) $erros[] = "A Categoria é obrigatória.";
    if (empty($estado)) $erros[] = "O Estado é obrigatório.";
    if (empty($criticidade)) $erros[] = "A Criticidade é obrigatória.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $ligacao->prepare("UPDATE Equipamento SET 
                nomeEquipamento = :nome,
                categoria = :categoria,
                estado = :estado,
                criticidadeClinica = :criticidade
                WHERE idEquipamento = :id");

            $stmt->execute([
                ':nome' => $nome,
                ':categoria' => $categoria,
                ':estado' => $estado,
                ':criticidade' => $criticidade,
                ':id' => $idEquipamento
            ]);

            $ligacao = null;

            header('Location: equipamentos.php');
            exit;
        } catch (PDOException $err) {
            $erro = "Erro ao atualizar: " . $err->getMessage();
        }
    }
}

// Ligar à BD e carregar dados do equipamento
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->prepare("SELECT * FROM Equipamento WHERE idEquipamento = :id");
    $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmt->execute();
    $equipamento = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$equipamento) {
        header('Location: /medcare-inventory-solutions/Private/views/equipamentos/equipamentos.php');
        exit;
    }
} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $equipamento = null;
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
                <h1 class="fw-bold h2 mb-1 text-dark">Editar Equipamento</h1>
                <p class="text-muted small mb-0">Atualizar os dados do equipamento.</p>
            </div>
            <a href="equipamentos.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4">

            <!-- Área de erros de validação -->
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
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Erro:</strong> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form action="editar_equipamento.php?id_equipamento=<?= $idEquipamentoEncrypted ?>" method="POST" novalidate>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome do Equipamento</label>
                    <input type="text" class="form-control" name="nome_equipamento"
                        value="<?= htmlspecialchars($equipamento->nomeEquipamento) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Categoria</label>
                    <select class="form-select" name="categoria_equipamento" required>
                        <option value="">-- Selecionar --</option>
                        <option value="Ventilação" <?= $equipamento->categoria == 'Ventilação' ? 'selected' : '' ?>>Ventilação</option>
                        <option value="Imagem" <?= $equipamento->categoria == 'Imagem' ? 'selected' : '' ?>>Imagem</option>
                        <option value="Monitorização" <?= $equipamento->categoria == 'Monitorização' ? 'selected' : '' ?>>Monitorização</option>
                        <option value="Diagnóstico" <?= $equipamento->categoria == 'Diagnóstico' ? 'selected' : '' ?>>Diagnóstico</option>
                        <option value="Suporte de Vida" <?= $equipamento->categoria == 'Suporte de Vida' ? 'selected' : '' ?>>Suporte de Vida</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Estado</label>
                    <select class="form-select" name="estado_equipamento" required>
                        <option value="">-- Selecionar --</option>
                        <option value="operacional" <?= $equipamento->estado == 'operacional' ? 'selected' : '' ?>>Operacional</option>
                        <option value="manutencao" <?= $equipamento->estado == 'manutencao' ? 'selected' : '' ?>>Manutenção</option>
                        <option value="avariado" <?= $equipamento->estado == 'avariado' ? 'selected' : '' ?>>Avariado</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Criticidade Clínica</label>
                    <select class="form-select" name="criticidade_equipamento" required>
                        <option value="">-- Selecionar --</option>
                        <option value="Alta" <?= $equipamento->criticidadeClinica == 'Alta' ? 'selected' : '' ?>>Alta</option>
                        <option value="Media" <?= $equipamento->criticidadeClinica == 'Media' ? 'selected' : '' ?>>Média</option>
                        <option value="Baixa" <?= $equipamento->criticidadeClinica == 'Baixa' ? 'selected' : '' ?>>Baixa</option>
                    </select>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                    </button>
                    <a href="equipamentos.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                </div>

            </form>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>