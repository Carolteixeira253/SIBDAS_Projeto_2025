<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
redirect_if_not_admin();
require_once __DIR__ . '/../../includes/validacoes.php';

// Desencriptar ID
$idEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEncrypted);
if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: equipamentos.php');
    exit;
}
$erros        = [];
$erro_sistema = '';
$localizacoes = [];
$fornecedores = [];
$equipamento  = null;

// Processar POST (Ficha 13 — bloco de submissão)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoInventario = trim($_POST['codigo_inventario'] ?? '');
    $nome             = trim($_POST['nome_equipamento'] ?? '');
    $categoria        = $_POST['categoria_equipamento'] ?? '';
    $marca            = trim($_POST['marca'] ?? '');
    $modelo           = trim($_POST['modelo'] ?? '');
    $numeroSerie      = trim($_POST['numero_serie'] ?? '');
    $fabricante       = trim($_POST['fabricante'] ?? '');
    $dataAquisicao    = $_POST['data_aquisicao'] ?? '';
    $anoFabrico       = $_POST['ano_fabrico'] ?? '';
    $custoAquisicao   = $_POST['custo_aquisicao'] ?? '';
    $tipoEntrada      = $_POST['tipo_entrada'] ?? 'compra';
    $idLocalizacao    = $_POST['localizacao_equipamento'] ?? '';
    $idFornecedor     = $_POST['fornecedor_equipamento'] ?? '';
    $estado           = $_POST['estado_equipamento'] ?? '';
    $criticidade      = $_POST['criticidade_equipamento'] ?? '';
    $observacoes      = trim($_POST['observacoes'] ?? '');
    // Validação servidor (Ficha 13 — $erros[])
    $erros = validar_nome_equipamento($nome);
    if (empty($categoria)) $erros[] = "A Categoria Prática é obrigatória.";
    if (empty($estado))    $erros[] = "O Estado Técnico é obrigatório.";
    if (!empty($dataAquisicao)) {
        $erros = array_merge($erros, validar_data($dataAquisicao, 'Data de Aquisição'));
    }
    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("
                UPDATE Equipamento SET
                    codigoInventario   = :codigo,
                    nomeEquipamento    = :nome,
                    categoria          = :categoria,
                    marca              = :marca,
                    modelo             = :modelo,
                    numeroSerie        = :serie,
                    fabricante         = :fabricante,
                    dataAquisicao      = :dataAquisicao,
                    anoFabrico         = :anoFabrico,
                    custoAquisicao     = :custo,
                    tipoEntrada        = :tipoEntrada,
                    idLocalizacao      = :localizacao,
                    idFornecedor       = :fornecedor,
                    estado             = :estado,
                    criticidadeClinica = :criticidade,
                    observacoes        = :observacoes
                WHERE idEquipamento = :id
            ");
            $stmt->execute([
                ':codigo'        => $codigoInventario ?: null,
                ':nome'          => $nome,
                ':categoria'     => $categoria,
                ':marca'         => $marca ?: null,
                ':modelo'        => $modelo ?: null,
                ':serie'         => $numeroSerie ?: null,
                ':fabricante'    => $fabricante ?: null,
                ':dataAquisicao' => !empty($dataAquisicao) ? $dataAquisicao : null,
                ':anoFabrico'    => !empty($anoFabrico) ? $anoFabrico : null,
                ':custo'         => !empty($custoAquisicao) ? $custoAquisicao : null,
                ':tipoEntrada'   => $tipoEntrada,
                ':localizacao'   => !empty($idLocalizacao) ? $idLocalizacao : null,
                ':fornecedor'    => !empty($idFornecedor) ? $idFornecedor : null,
                ':estado'        => $estado,
                ':criticidade'   => $criticidade ?: null,
                ':observacoes'   => $observacoes ?: null,
                ':id'            => $idEquipamento,
            ]);
            $ligacao = null;
            header('Location: equipamentos.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Não foi possível atualizar o equipamento. Verifique a sua ligação à internet e tente novamente.";
        }
    }
}
// Carregar dados do equipamento + dropdowns (Ficha 13)
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $ligacao->prepare("SELECT * FROM Equipamento WHERE idEquipamento = :id AND ativo = 1");
    $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmt->execute();
    $equipamento = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$equipamento) {
        header('Location: equipamentos.php');
        exit;
    }
    $localizacoes = $ligacao->query("SELECT idLocalizacao, nomeSala, servico FROM Localizacao WHERE ativo = 1 ORDER BY nomeSala")->fetchAll(PDO::FETCH_OBJ);
    $fornecedores = $ligacao->query("SELECT idFornecedor, nomeFornecedor, tipoFornecedor FROM Fornecedor WHERE ativo = 1 ORDER BY nomeFornecedor")->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $err) {
    $erro_sistema = "Não foi possível carregar os dados. Verifique a sua ligação à internet.";
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
                <h1 class="fw-bold h2 mb-1 text-dark">
                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Editar Equipamento
                </h1>
                <p class="text-muted small mb-0">
                    Atualize os dados do equipamento.
                    Os campos com <span class="text-danger">*</span> são obrigatórios.
                </p>
            </div>
            <a href="equipamentos.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
        <div class="card-stat border-0 shadow-sm rounded-3 p-4">
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
            <?php if (!empty($erro_sistema)): ?>
                <div class="alert alert-warning d-flex align-items-center gap-3">
                    <i class="fa-solid fa-wifi text-warning fs-4"></i>
                    <div><?= htmlspecialchars($erro_sistema) ?></div>
                </div>
            <?php endif; ?>
            <?php if ($equipamento): ?>
                <form id="formEditarEquipamento"
                    action="editar_equipamento.php?id_equipamento=<?= htmlspecialchars($idEncrypted) ?>"
                    method="POST" novalidate>
                    <!-- SECÇÃO 1: Identificação -->
                    <p class="secao-form-titulo mt-0">Identificação</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Código de Inventário</label>
                            <input type="text" class="form-control" name="codigo_inventario"
                                placeholder="Ex: EQ-VENT-001"
                                value="<?= htmlspecialchars($_POST['codigo_inventario'] ?? $equipamento->codigoInventario ?? '') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">
                                Designação do Equipamento <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control campo-obrigatorio"
                                name="nome_equipamento"
                                placeholder="Ex: Ventilador Pulmonar Neonatal"
                                value="<?= htmlspecialchars($_POST['nome_equipamento'] ?? $equipamento->nomeEquipamento ?? '') ?>">
                            <div class="invalid-feedback">A designação é obrigatória.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Categoria Prática <span class="text-danger">*</span>
                            </label>
                            <select class="form-select campo-obrigatorio" name="categoria_equipamento">
                                <option value="">Escolha uma opção...</option>
                                <?php
                                $categorias = [
                                    'Ventilação',
                                    'Imagem',
                                    'Monitorização',
                                    'Diagnóstico',
                                    'Suporte de Vida',
                                    'Terapia',
                                    'Laboratório',
                                    'Esterilização',
                                    'Transporte',
                                    'Reabilitação'
                                ];
                                $catAtual = $_POST['categoria_equipamento'] ?? $equipamento->categoria ?? '';
                                foreach ($categorias as $cat):
                                    $sel = ($catAtual === $cat) ? 'selected' : '';
                                ?>
                                    <option value="<?= $cat ?>" <?= $sel ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione uma categoria.</div>
                        </div>
                    </div>
                    <!-- SECÇÃO 2: Dados Técnicos -->
                    <p class="secao-form-titulo">Dados Técnicos</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Marca</label>
                            <input type="text" class="form-control" name="marca"
                                placeholder="Ex: Puritan Bennett"
                                value="<?= htmlspecialchars($_POST['marca'] ?? $equipamento->marca ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Modelo</label>
                            <input type="text" class="form-control" name="modelo"
                                placeholder="Ex: PB980"
                                value="<?= htmlspecialchars($_POST['modelo'] ?? $equipamento->modelo ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Número de Série (S/N)</label>
                            <input type="text" class="form-control" name="numero_serie"
                                placeholder="Ex: SN-9948-XYZ"
                                value="<?= htmlspecialchars($_POST['numero_serie'] ?? $equipamento->numeroSerie ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Fabricante Oficial</label>
                            <input type="text" class="form-control" name="fabricante"
                                placeholder="Ex: Medtronic"
                                value="<?= htmlspecialchars($_POST['fabricante'] ?? $equipamento->fabricante ?? '') ?>">
                        </div>
                    </div>
                    <!-- SECÇÃO 3: Aquisição -->
                    <p class="secao-form-titulo">Aquisição</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Data de Aquisição</label>
                            <input type="date" class="form-control" name="data_aquisicao"
                                value="<?= htmlspecialchars($_POST['data_aquisicao'] ?? $equipamento->dataAquisicao ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Ano de Fabrico</label>
                            <input type="number" class="form-control" name="ano_fabrico"
                                min="1900" max="<?= date('Y') ?>"
                                value="<?= htmlspecialchars($_POST['ano_fabrico'] ?? $equipamento->anoFabrico ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Custo de Aquisição (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="custo_aquisicao"
                                placeholder="0.00"
                                value="<?= htmlspecialchars($_POST['custo_aquisicao'] ?? $equipamento->custoAquisicao ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo de Entrada</label>
                            <select class="form-select" name="tipo_entrada">
                                <?php
                                $tipos = [
                                    'compra' => 'Compra',
                                    'doacao' => 'Doação',
                                    'aluguer' => 'Aluguer',
                                    'emprestimo' => 'Empréstimo'
                                ];
                                $tipoAtual = $_POST['tipo_entrada'] ?? $equipamento->tipoEntrada ?? 'compra';
                                foreach ($tipos as $val => $label):
                                    $sel = ($tipoAtual === $val) ? 'selected' : '';
                                ?>
                                    <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- SECÇÃO 4: Localização, Estado e Fornecedor -->
                    <p class="secao-form-titulo">Localização, Estado e Fornecedor</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Localização Hospitalar</label>
                            <select class="form-select" name="localizacao_equipamento">
                                <option value="">Selecione a Sala/Serviço...</option>
                                <?php
                                $locAtual = $_POST['localizacao_equipamento'] ?? $equipamento->idLocalizacao ?? '';
                                foreach ($localizacoes as $loc):
                                    $lbl = htmlspecialchars($loc->nomeSala);
                                    if ($loc->servico) $lbl .= ' — ' . htmlspecialchars($loc->servico);
                                    $sel = ($locAtual == $loc->idLocalizacao) ? 'selected' : '';
                                ?>
                                    <option value="<?= $loc->idLocalizacao ?>" <?= $sel ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Estado Técnico Atual <span class="text-danger">*</span>
                            </label>
                            <select class="form-select campo-obrigatorio" name="estado_equipamento">
                                <option value="">Escolha...</option>
                                <?php
                                $estadoAtual = $_POST['estado_equipamento'] ?? $equipamento->estado ?? '';
                                $estados = [
                                    'operacional' => 'Operacional',
                                    'manutencao' => 'Em Manutenção',
                                    'avariado' => 'Avariado',
                                    'inativo' => 'Inativo'
                                ];
                                foreach ($estados as $val => $label):
                                    $sel = ($estadoAtual === $val) ? 'selected' : '';
                                ?>
                                    <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione um estado.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Grau de Criticidade</label>
                            <select class="form-select" name="criticidade_equipamento">
                                <option value="">Escolha...</option>
                                <?php
                                $critAtual = $_POST['criticidade_equipamento'] ?? $equipamento->criticidadeClinica ?? '';
                                $crits = [
                                    'Suporte de vida' => 'Suporte de Vida',
                                    'Alta' => 'Alta',
                                    'Media' => 'Média',
                                    'Baixa' => 'Baixa'
                                ];
                                foreach ($crits as $val => $label):
                                    $sel = ($critAtual === $val) ? 'selected' : '';
                                ?>
                                    <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Fornecedor -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Fornecedor Associado</label>
                            <select class="form-select" name="fornecedor_equipamento">
                                <option value="">Sem fornecedor associado</option>
                                <?php
                                $fornAtual = $_POST['fornecedor_equipamento'] ?? $equipamento->idFornecedor ?? '';
                                foreach ($fornecedores as $f):
                                    $lbl = htmlspecialchars($f->nomeFornecedor);
                                    if ($f->tipoFornecedor) $lbl .= ' (' . htmlspecialchars(ucwords(str_replace('_', ' ', $f->tipoFornecedor))) . ')';
                                    $sel = ($fornAtual == $f->idFornecedor) ? 'selected' : '';
                                ?>
                                    <option value="<?= $f->idFornecedor ?>" <?= $sel ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Observações -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Observações Técnicas / Notas Adicionais</label>
                        <textarea class="form-control" name="observacoes" rows="3"
                            placeholder="Detalhes de software, acessórios incluídos ou restrições especiais..."><?= htmlspecialchars($_POST['observacoes'] ?? $equipamento->observacoes ?? '') ?></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="equipamentos.php" class="btn btn-outline-secondary px-4 py-2">
                            <i class="fa-solid fa-xmark me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>
<!-- Validação JavaScript lado cliente (Ficha 13) -->
<script>
    document.getElementById('formEditarEquipamento').addEventListener('submit', function(e) {
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
        campo.addEventListener('change', function() {
            if (this.value.trim()) this.classList.remove('is-invalid');
        });
    });
</script>
<?php include '../../includes/footer.php'; ?>