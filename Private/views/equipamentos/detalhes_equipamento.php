<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

// Obter e desencriptar o ID
$idEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEncrypted);

if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: /medcare-inventory-solutions/Private/views/equipamentos/equipamentos.php');
    exit;
}
$erro = '';
$eq = null;
$documentos = [];
$garantias = [];

// Ligar à BD e carregar dados
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $ligacao->prepare("
    SELECT e.*, l.nomeSala, l.servico, l.edificio, l.piso,
               f.nomeFornecedor, f.contactoTelefonico, f.enderecoEmail
        FROM Equipamento e
        LEFT JOIN Localizacao l ON e.idLocalizacao = l.idLocalizacao
        LEFT JOIN Fornecedor f ON e.idFornecedor = f.idFornecedor
        WHERE e.idEquipamento = :id
    ");
    $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmt->execute();
    $equipamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$eq) {
        header('Location: equipamentos.php');
        exit;
    }
    // Documentos associados
    $stmtDocs = $ligacao->prepare("
        SELECT * FROM Documentacao
        WHERE idEquipamento = :id AND ativo = 1
        ORDER BY dataDocumento DESC
    ");
    // Garantias associadas
    $stmtGar = $ligacao->prepare("
        SELECT * FROM Garantia
        WHERE idEquipamento = :id AND ativo = 1
        ORDER BY dataFim DESC
    ");
    $stmtGar->execute([':id' => $idEquipamento]);
    $garantias = $stmtGar->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $erro = 'bd';
}
$ligacao = null;
// Badge de estado
$estadoBadge = '';
if ($eq) {
    $estadoBadge = match ($eq->estado) {
        'operacional' => 'bg-success',
        'manutencao'  => 'bg-warning text-dark',
        'avariado'    => 'bg-danger',
        'inativo'     => 'bg-secondary',
        default       => 'bg-secondary'
    };
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="content-body">
    <?php include '../../includes/sidebar.php'; ?>
    <main class="main-content-wrapper">
        <?php if ($erro === 'bd'): ?>
            <?= mensagem_erro_bd() ?>
        <?php elseif ($eq): ?>
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="fw-bold h2 mb-1 text-dark">
                        <?= htmlspecialchars($eq->nomeEquipamento) ?>
                        <span class="badge <?= $estadoBadge ?> ms-2 fs-6">
                            <?= htmlspecialchars($eq->estado) ?>
                        </span>
                    </h1>
                    <p class="text-muted small mb-0">
                        <?= $eq->codigoInventario ? htmlspecialchars($eq->codigoInventario) . ' · ' : '' ?>
                        <?= htmlspecialchars($eq->categoria ?? '') ?>
                    </p>
                </div>
                <a href="equipamentos.php" class="btn btn-secondary fw-bold px-3 py-2">
                    <i class="fa-solid fa-arrow-left me-2"></i>Voltar
                </a>
            </div>
            <!-- Tabs de navegação -->
            <ul class="nav nav-tabs mb-4" id="tabsEquipamento">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-geral">
                        <i class="fa-solid fa-info-circle me-1"></i>Informação Geral
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-documentos">
                        <i class="fa-solid fa-file-lines me-1"></i>Documentos
                        <?php if (count($documentos) > 0): ?>
                            <span class="badge bg-primary ms-1"><?= count($documentos) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-garantias">
                        <i class="fa-solid fa-file-invoice me-1"></i>Garantias
                        <?php if (count($garantias) > 0): ?>
                            <span class="badge bg-primary ms-1"><?= count($garantias) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <!-- TAB: Informação Geral -->
                <div class="tab-pane fade show active" id="tab-geral">
                    <div class="card-stat border-0 shadow-sm rounded-3 p-4">
                        <div class="row g-4">
                            <!-- Identificação -->
                            <div class="col-12">
                                <p class="secao-form-titulo">Identificação</p>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Código de Inventário</small>
                                        <p class="fw-semibold mb-0"><?= htmlspecialchars($eq->codigoInventario ?? 'N/D') ?></p>
                                    </div>
                                    <div class="col-md-5">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Designação</small>
                                        <p class="fw-bold mb-0"><?= htmlspecialchars($eq->nomeEquipamento) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Categoria</small>
                                        <p class="mb-0"><?= htmlspecialchars($eq->categoria ?? 'N/D') ?></p>
                                    </div>
                                </div>
                            </div>
                            <!-- Dados Técnicos -->
                            <div class="col-12 secao-form">
                                <p class="secao-form-titulo">Dados Técnicos</p>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Marca</small>
                                        <p class="mb-0"><?= htmlspecialchars($eq->marca ?? 'N/D') ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Modelo</small>
                                        <p class="mb-0"><?= htmlspecialchars($eq->modelo ?? 'N/D') ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Número de Série</small>
                                        <p class="mb-0"><?= htmlspecialchars($eq->numeroSerie ?? 'N/D') ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Fabricante</small>
                                        <p class="mb-0"><?= htmlspecialchars($eq->fabricante ?? 'N/D') ?></p>
                                    </div>
                                </div>
                            </div>
                            <!-- Aquisição -->
                            <div class="col-12 secao-form">
                                <p class="secao-form-titulo">Aquisição</p>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Data de Aquisição</small>
                                        <p class="mb-0">
                                            <?= $eq->dataAquisicao ? date('d/m/Y', strtotime($eq->dataAquisicao)) : 'N/D' ?>
                                        </p>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Ano de Fabrico</small>
                                        <p class="mb-0"><?= htmlspecialchars($eq->anoFabrico ?? 'N/D') ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Custo de Aquisição</small>
                                        <p class="mb-0">
                                            <?= $eq->custoAquisicao ? number_format($eq->custoAquisicao, 2, ',', '.') . ' €' : 'N/D' ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Tipo de Entrada</small>
                                        <p class="mb-0"><?= htmlspecialchars(ucfirst($eq->tipoEntrada ?? 'N/D')) ?></p>
                                    </div>
                                </div>
                            </div>
                            <!-- Estado e Localização -->
                            <div class="col-12 secao-form">
                                <p class="secao-form-titulo">Estado e Localização</p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Estado Técnico</small>
                                        <p class="mb-0">
                                            <span class="badge <?= $estadoBadge ?>">
                                                <?= htmlspecialchars($eq->estado) ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Criticidade Clínica</small>
                                        <p class="mb-0"><?= htmlspecialchars($eq->criticidadeClinica ?? 'N/D') ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Localização</small>
                                        <p class="mb-0">
                                            <?php if ($eq->nomeSala): ?>
                                                <?= htmlspecialchars($eq->nomeSala) ?>
                                                <?= $eq->servico ? '<br><small class="text-muted">' . htmlspecialchars($eq->servico) . '</small>' : '' ?>
                                                <?= $eq->edificio ? '<br><small class="text-muted">' . htmlspecialchars($eq->edificio) . ($eq->piso ? ', Piso ' . htmlspecialchars($eq->piso) : '') . '</small>' : '' ?>
                                            <?php else: ?>
                                                N/D
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Fornecedor (só se existir) -->
                            <?php if ($eq->nomeFornecedor): ?>
                                <div class="col-12 secao-form">
                                    <p class="secao-form-titulo">Fornecedor Associado</p>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Entidade</small>
                                            <p class="mb-0"><?= htmlspecialchars($eq->nomeFornecedor) ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Telefone</small>
                                            <p class="mb-0"><?= htmlspecialchars($eq->contactoTelefonico ?? 'N/D') ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Email</small>
                                            <p class="mb-0"><?= htmlspecialchars($eq->enderecoEmail ?? 'N/D') ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <!-- Observações (só se existirem) -->
                            <?php if ($eq->observacoes): ?>
                                <div class="col-12 secao-form">
                                    <p class="secao-form-titulo">Observações</p>
                                    <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($eq->observacoes)) ?></p>
                                </div>
                            <?php endif; ?>
                        </div><!-- /row -->
                        <!-- Botões de ação (só admin, só se ativo) -->
                        <?php if ($_perfil === 'administrador' && $eq->ativo == 1): ?>
                            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                <a href="editar_equipamento.php?id_equipamento=<?= $idEncrypted ?>"
                                    class="btn btn-acao-primaria fw-bold px-4 py-2">
                                    <i class="fa-solid fa-pen me-2"></i>Editar
                                </a>
                                <a href="apagar_equipamento.php?id_equipamento=<?= $idEncrypted ?>"
                                    class="btn btn-danger fw-bold px-4 py-2">
                                    <i class="fa-solid fa-trash me-2"></i>Desativar
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div><!-- /tab-geral -->
                <!-- TAB: Documentos -->
                <div class="tab-pane fade" id="tab-documentos">
                    <div class="card-stat border-0 shadow-sm rounded-3 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Documentação Técnica</h6>
                            <?php if ($_perfil === 'administrador'): ?>
                                <a href="/medcare-inventory-solutions/Private/views/documentacao/inserir_documento.php?id_equipamento=<?= urlencode($idEncrypted) ?>"
                                    class="btn btn-sm btn-acao-primaria">
                                    <i class="fa-solid fa-plus me-1"></i>Adicionar Documento
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($documentos)): ?>
                            <p class="text-muted text-center py-3">Não existem documentos associados a este equipamento.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Nome do Documento</th>
                                            <th>Tipo</th>
                                            <th>Data</th>
                                            <th>Validade</th>
                                            <th class="text-end pe-3">Abrir</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documentos as $doc): ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <strong><?= htmlspecialchars($doc->nomeDocumento) ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                        <?= htmlspecialchars(str_replace('_', ' ', $doc->tipoDocumento)) ?>
                                                    </span>
                                                </td>
                                                <td><?= $doc->dataDocumento ? date('d/m/Y', strtotime($doc->dataDocumento)) : 'N/D' ?></td>
                                                <td>
                                                    <?php if ($doc->dataValidade): ?>
                                                        <?php $diff = (strtotime($doc->dataValidade) - time()) / 86400; ?>
                                                        <span class="<?= $diff < 30 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                                            <?= date('d/m/Y', strtotime($doc->dataValidade)) ?>
                                                            <?= $diff < 0 ? ' <span class="badge bg-danger">Expirado</span>' : '' ?>
                                                        </span>
                                                    <?php else: ?>
                                                        N/D
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <?php if ($doc->nomeFicheiro): ?>
                                                        <a href="/medcare-inventory-solutions/Private/documentos/<?= htmlspecialchars($doc->nomeFicheiro) ?>"
                                                            target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fa-solid fa-file-pdf me-1"></i>Abrir PDF
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Sem ficheiro</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div><!-- /tab-documentos -->
                <!-- TAB: Garantias -->
                <div class="tab-pane fade" id="tab-garantias">
                    <div class="card-stat border-0 shadow-sm rounded-3 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Garantias e Contratos</h6>
                            <?php if ($_perfil === 'administrador'): ?>
                                <a href="/medcare-inventory-solutions/Private/views/garantias/inserir_garantia.php?id_equipamento=<?= urlencode($idEncrypted) ?>"
                                    class="btn btn-sm btn-acao-primaria">
                                    <i class="fa-solid fa-plus me-1"></i>Registar Garantia
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($garantias)): ?>
                            <p class="text-muted text-center py-3">Não existem garantias registadas para este equipamento.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Entidade Responsável</th>
                                            <th>Tipo de Contrato</th>
                                            <th>Início</th>
                                            <th>Fim</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($garantias as $gar): ?>
                                            <?php
                                            $hoje = time();
                                            $fim = strtotime($gar->dataFim);
                                            $diasRestantes = ($fim - $hoje) / 86400;
                                            if ($diasRestantes < 0)
                                                $badgeGar = '<span class="badge bg-danger">Expirada</span>';
                                            elseif ($diasRestantes <= 30)
                                                $badgeGar = '<span class="badge bg-warning text-dark">Expira em ' . round($diasRestantes) . ' dias</span>';
                                            else
                                                $badgeGar = '<span class="badge bg-success">Ativa</span>';
                                            ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <strong><?= htmlspecialchars($gar->entidadeResponsavel ?? 'N/D') ?></strong>
                                                </td>
                                                <td><?= htmlspecialchars($gar->tipoContrato ?? 'N/D') ?></td>
                                                <td><?= date('d/m/Y', strtotime($gar->dataInicio)) ?></td>
                                                <td><?= date('d/m/Y', strtotime($gar->dataFim)) ?></td>
                                                <td><?= $badgeGar ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div><!-- /tab-garantias -->
            </div><!-- /tab-content -->
        <?php endif; ?>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>