<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
redirect_if_not_admin();
require_once __DIR__ . '/../../includes/validacoes.php';

$erros = [];
$erro_sistema = '';
$localizacoes = [];
$fornecedores = [];

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $localizacoes = $ligacao->query("SELECT idLocalizacao, nomeSala, servico FROM Localizacao ORDER BY nomeSala")->fetchAll(PDO::FETCH_OBJ);
    $fornecedores = $ligacao->query("SELECT idFornecedor, nomeFornecedor, tipoFornecedor FROM Fornecedor ORDER BY nomeFornecedor")->fetchAll(PDO::FETCH_OBJ);
    $ligacao = null;
} catch (PDOException $err) {
    $erro_sistema = "Não foi possível carregar os dados auxiliares.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigoInventario = trim($_POST["codigo_inventario"] ?? "");
    $nome             = trim($_POST["nome_equipamento"] ?? "");
    $categoria        = $_POST["categoria_equipamento"] ?? "";
    $marca            = trim($_POST["marca"] ?? "");
    $modelo           = trim($_POST["modelo"] ?? "");
    $numeroSerie      = trim($_POST["numero_serie"] ?? "");
    $fabricante       = trim($_POST["fabricante"] ?? "");
    $dataAquisicao    = $_POST["data_aquisicao"] ?? "";
    $anoFabrico       = $_POST["ano_fabrico"] ?? "";
    $custoAquisicao   = $_POST["custo_aquisicao"] ?? "";
    $tipoEntrada      = $_POST["tipo_entrada"] ?? "compra";
    $idLocalizacao    = $_POST["localizacao_equipamento"] ?? "";
    $idFornecedor     = $_POST["fornecedor_equipamento"] ?? "";
    $estado           = $_POST["estado_equipamento"] ?? "";
    $criticidade      = $_POST["criticidade_equipamento"] ?? "";
    $observacoes      = trim($_POST["observacoes"] ?? "");


    $erros = validar_nome_equipamento($nome);
    if (empty($numeroSerie))    $erros[] = "O Número de Série é obrigatório.";
    if (empty($modelo))         $erros[] = "O Modelo é obrigatório.";
    if (empty($fabricante))     $erros[] = "O Fabricante é obrigatório.";
    if (empty($custoAquisicao)) $erros[] = "O Custo de Aquisição é obrigatório.";
    if (empty($idLocalizacao))  $erros[] = "A Localização é obrigatória.";
    if (empty($idFornecedor))   $erros[] = "O Fornecedor é obrigatório.";
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
                INSERT INTO Equipamento
                    (codigoInventario, nomeEquipamento, categoria, marca, modelo, numeroSerie, fabricante,
                     dataAquisicao, anoFabrico, custoAquisicao, tipoEntrada, idLocalizacao, idFornecedor,
                     estado, criticidadeClinica, observacoes, ativo)
                VALUES
                    (:codigo, :nome, :categoria, :marca, :modelo, :serie, :fabricante,
                     :dataAquisicao, :anoFabrico, :custo, :tipoEntrada, :localizacao, :fornecedor,
                     :estado, :criticidade, :observacoes, 1)
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
            ]);
            $ligacao = null;
            header('Location: equipamentos.php?sucesso=inserido');
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
                    <h1 class="fw-bold h2 mb-1">Novo Equipamento</h1>
                    <p class="text-muted small mb-0">
                        Preencha os campos obrigatórios <span class="text-danger">*</span> para registar o equipamento.
                    </p>
                </div>
                <a href="equipamentos.php" class="btn btn-outline-secondary px-3 py-2">
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

            <form id="formInserirEquipamento" action="" method="POST" novalidate>
                <div class="row g-4">

                    <!-- COLUNA ESQUERDA -->
                    <div class="col-12 col-lg-8">

                        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                            <p class="secao-form-titulo mt-0">Identificação</p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Código de Inventário</label>
                                    <input type="text" class="form-control" name="codigo_inventario"
                                        placeholder="Ex: EQ-001"
                                        value="<?= htmlspecialchars($_POST['codigo_inventario'] ?? '') ?>">
                                    <div class="form-text">Deixe em branco para gerar automaticamente.</div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Designação <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control campo-obrigatorio" id="nome_equipamento" name="nome_equipamento"
                                            placeholder="Ex: Ventilador Pulmonar Neonatal"
                                            value="<?= htmlspecialchars($_POST['nome_equipamento'] ?? '') ?>">
                                        <button type="button" class="btn btn-outline-primary" id="btn-autopreenchimento" title="Auto-preencher campos">
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">A designação é obrigatória.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Categoria <span class="text-danger">*</span></label>
                                    <select class="form-select campo-obrigatorio" name="categoria_equipamento">
                                        <option value="">Selecione...</option>
                                        <?php foreach (
                                            [
                                                'Monitorização',
                                                'Suporte de vida',
                                                'Terapia',
                                                'Diagnóstico',
                                                'Laboratório',
                                                'Esterilização',
                                                'Reabilitação',
                                                'Imagiologia',
                                                'Ventilação',
                                                'Imagem',
                                                'Outro'
                                            ] as $cat
                                        ):
                                            $sel = (($_POST['categoria_equipamento'] ?? '') == $cat) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $cat ?>" <?= $sel ?>><?= $cat ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Selecione uma categoria.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tipo de Entrada</label>
                                    <select class="form-select" name="tipo_entrada">
                                        <?php foreach (['compra' => 'Compra', 'doacao' => 'Doação', 'aluguer' => 'Aluguer', 'emprestimo' => 'Empréstimo'] as $val => $label):
                                            $sel = (($_POST['tipo_entrada'] ?? 'compra') == $val) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                            <p class="secao-form-titulo mt-0">Dados Técnicos</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Marca</label>
                                    <input type="text" class="form-control" name="marca"
                                        placeholder="Ex: Philips"
                                        value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Modelo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control campo-obrigatorio" name="modelo"
                                        placeholder="Ex: IntelliVue MP5"
                                        value="<?= htmlspecialchars($_POST['modelo'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Número de Série <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control campo-obrigatorio" name="numero_serie"
                                        placeholder="Ex: SN-9948-XYZ"
                                        value="<?= htmlspecialchars($_POST['numero_serie'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Fabricante <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control campo-obrigatorio" name="fabricante"
                                        placeholder="Ex: Medtronic"
                                        value="<?= htmlspecialchars($_POST['fabricante'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                            <p class="secao-form-titulo mt-0">Aquisição</p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Data de Aquisição</label>
                                    <input type="date" class="form-control" name="data_aquisicao"
                                        value="<?= htmlspecialchars($_POST['data_aquisicao'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Ano de Fabrico</label>
                                    <input type="number" class="form-control" name="ano_fabrico"
                                        placeholder="<?= date('Y') ?>" min="1900" max="<?= date('Y') ?>"
                                        value="<?= htmlspecialchars($_POST['ano_fabrico'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Custo de Aquisição (€) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control campo-obrigatorio" name="custo_aquisicao"
                                        value="<?= htmlspecialchars($_POST['custo_aquisicao'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                            <p class="secao-form-titulo mt-0">Observações</p>
                            <textarea class="form-control" name="observacoes" rows="3"
                                placeholder="Detalhes técnicos, acessórios incluídos, restrições especiais..."><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                        </div>

                    </div>

                    <!-- COLUNA DIREITA -->
                    <div class="col-12 col-lg-4">

                        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                            <p class="secao-form-titulo mt-0">Estado Clínico</p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Estado Técnico <span class="text-danger">*</span></label>
                                <select class="form-select campo-obrigatorio" name="estado_equipamento">
                                    <option value="">Selecione...</option>
                                    <?php foreach (['operacional' => 'Operacional', 'manutencao' => 'Em Manutenção', 'avariado' => 'Avariado', 'inativo' => 'Inativo'] as $val => $label):
                                        $sel = (($_POST['estado_equipamento'] ?? '') == $val) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Selecione um estado.</div>
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Criticidade Clínica <span class="text-danger">*</span></label>
                                <select class="form-select campo-obrigatorio" name="criticidade_equipamento">
                                    <option value="">Selecione...</option>
                                    <?php foreach (['Suporte de vida' => '🔴 Suporte de Vida', 'Alta' => '🟠 Alta', 'Media' => '🟡 Média', 'Baixa' => '🟢 Baixa'] as $val => $label):
                                        $sel = (($_POST['criticidade_equipamento'] ?? '') == $val) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Selecione a criticidade.</div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                            <p class="secao-form-titulo mt-0">Localização <span class="text-danger">*</span></p>
                            <select class="form-select campo-obrigatorio" name="localizacao_equipamento">
                                <option value="">Sem localização atribuída</option>
                                <?php foreach ($localizacoes as $loc):
                                    $lbl = htmlspecialchars($loc->nomeSala);
                                    if ($loc->servico) $lbl .= ' — ' . htmlspecialchars($loc->servico);
                                    $sel = (($_POST['localizacao_equipamento'] ?? '') == $loc->idLocalizacao) ? 'selected' : '';
                                ?>
                                    <option value="<?= $loc->idLocalizacao ?>" <?= $sel ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                            <p class="secao-form-titulo mt-0">Fornecedor <span class="text-danger">*</span></p>
                            <select class="form-select campo-obrigatorio" name="fornecedor_equipamento">
                                <option value="">Sem fornecedor associado</option>
                                <?php foreach ($fornecedores as $f):
                                    $lbl = htmlspecialchars($f->nomeFornecedor);
                                    if ($f->tipoFornecedor) $lbl .= ' (' . htmlspecialchars(ucwords(str_replace('_', ' ', $f->tipoFornecedor))) . ')';
                                    $sel = (($_POST['fornecedor_equipamento'] ?? '') == $f->idFornecedor) ? 'selected' : '';
                                ?>
                                    <option value="<?= $f->idFornecedor ?>" <?= $sel ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-acao-primaria fw-bold py-2">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Gravar Equipamento
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
    document.getElementById('formInserirEquipamento').addEventListener('submit', function(e) {
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
        campo.addEventListener('change', function() {
            if (this.value.trim()) this.classList.remove('is-invalid');
        });
    });

    const sugestoes = {
    'ventilador': { marca: 'Dräger', modelo: 'Evita V500', fabricante: 'Dräger', categoria: 'Ventilação', criticidade: 'Suporte de vida', estado: 'operacional' },
    'monitor': { marca: 'Philips', modelo: 'IntelliVue MP5', fabricante: 'Philips', categoria: 'Monitorização', criticidade: 'Alta', estado: 'operacional' },
    'desfibrilhador': { marca: 'Zoll', modelo: 'R Series', fabricante: 'Zoll', categoria: 'Suporte de vida', criticidade: 'Suporte de vida', estado: 'operacional' },
    'bomba': { marca: 'B. Braun', modelo: 'Infusomat Space', fabricante: 'B. Braun', categoria: 'Terapia', criticidade: 'Media', estado: 'operacional' },
    'ecógrafo': { marca: 'GE Healthcare', modelo: 'Vscan Air', fabricante: 'GE Healthcare', categoria: 'Diagnóstico', criticidade: 'Alta', estado: 'operacional' },
    'autoclave': { marca: 'Getinge', modelo: 'GSS67H', fabricante: 'Getinge', categoria: 'Esterilização', criticidade: 'Media', estado: 'operacional' },
    'oxímetro': { marca: 'Nonin', modelo: '9590', fabricante: 'Nonin', categoria: 'Monitorização', criticidade: 'Alta', estado: 'operacional' },
    'eletrocardiógrafo': { marca: 'Schiller', modelo: 'AT-102', fabricante: 'Schiller', categoria: 'Diagnóstico', criticidade: 'Media', estado: 'operacional' },
    'microscópio': { marca: 'Zeiss', modelo: 'OPMI Vario', fabricante: 'Zeiss', categoria: 'Diagnóstico', criticidade: 'Alta', estado: 'operacional' },
    'incubadora': { marca: 'Dräger', modelo: 'Isolette 8000', fabricante: 'Dräger', categoria: 'Outro', criticidade: 'Suporte de vida', estado: 'operacional' },
    'laser': { marca: 'Lumenis', modelo: 'VersaPulse', fabricante: 'Lumenis', categoria: 'Terapia', criticidade: 'Alta', estado: 'operacional' },
    'endoscópio': { marca: 'Olympus', modelo: 'GIF-H290', fabricante: 'Olympus', categoria: 'Diagnóstico', criticidade: 'Alta', estado: 'operacional' },
    'pacemaker': { marca: 'Medtronic', modelo: '5392', fabricante: 'Medtronic', categoria: 'Suporte de vida', criticidade: 'Suporte de vida', estado: 'operacional' },
    'hemodialisador': { marca: 'Fresenius', modelo: '5008S', fabricante: 'Fresenius', categoria: 'Terapia', criticidade: 'Suporte de vida', estado: 'operacional' },
    'balança': { marca: 'SECA', modelo: '376', fabricante: 'SECA', categoria: 'Diagnóstico', criticidade: 'Baixa', estado: 'operacional' },
    'tensiómetro': { marca: 'Omron', modelo: 'M6 Comfort', fabricante: 'Omron', categoria: 'Monitorização', criticidade: 'Media', estado: 'operacional' },
    'cama': { marca: 'Hill-Rom', modelo: 'VersaCare', fabricante: 'Hill-Rom', categoria: 'Outro', criticidade: 'Baixa', estado: 'operacional' },
};

    document.getElementById('btn-autopreenchimento').addEventListener('click', function() {
        const nome = document.getElementById('nome_equipamento').value.toLowerCase().trim();
        if (!nome) {
            alert('Escreve primeiro o nome do equipamento!');
            return;
        }
        let sugestao = null;
        for (const [chave, dados] of Object.entries(sugestoes)) {
            if (nome.includes(chave)) {
                sugestao = dados;
                break;
            }
        }
        if (!sugestao) {
            alert('Não encontrei sugestões. Preenche manualmente.');
            return;
        }
        const preencher = (selector, valor) => {
            const el = document.querySelector(selector);
            if (el && !el.value) el.value = valor;
        };
        preencher('[name="marca"]', sugestao.marca);
        preencher('[name="fabricante"]', sugestao.fabricante);
        preencher('[name="modelo"]', sugestao.modelo || '');
        preencher('[name="estado_equipamento"]', sugestao.estado);
        preencher('[name="criticidade_equipamento"]', sugestao.criticidade);
        preencher('[name="categoria_equipamento"]', sugestao.categoria);

        this.innerHTML = '<i class="fa-solid fa-check"></i>';
        this.classList.replace('btn-outline-primary', 'btn-success');
        setTimeout(() => {
            this.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i>';
            this.classList.replace('btn-success', 'btn-outline-primary');
        }, 2000);
    });
</script>

<?php include '../../includes/footer.php'; ?>