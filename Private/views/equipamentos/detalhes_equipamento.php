<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

// Desencriptar ID (Ficha 13)
$idEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEncrypted);

if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: equipamentos.php');
    exit;
}

$erro    = '';
$eq      = null;
$documentos = [];
$garantias  = [];

// 1. Carregar equipamento principal
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->prepare("
        SELECT e.*,
               l.nomeSala, l.servico, l.edificio, l.piso,
               f.nomeFornecedor, f.contactoTelefonico, f.enderecoEmail
        FROM Equipamento e
        LEFT JOIN Localizacao l ON e.idLocalizacao = l.idLocalizacao
        LEFT JOIN Fornecedor  f ON e.idFornecedor  = f.idFornecedor
        WHERE e.idEquipamento = :id
    ");
    $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmt->execute();
    $eq = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$eq) {
        header('Location: equipamentos.php');
        exit;
    }
    $stmtDocs = $ligacao->prepare("
        SELECT * FROM Documentacao
        WHERE idEquipamento = :id AND ativo = 1
        ORDER BY dataDocumento DESC
    ");
    $stmtDocs->execute([':id' => $idEquipamento]);
    $documentos = $stmtDocs->fetchAll(PDO::FETCH_OBJ);

    $stmtGar = $ligacao->prepare("
        SELECT * FROM Garantia
        WHERE idEquipamento = :id AND ativo = 1
        ORDER BY dataFim DESC
    ");
    $stmtGar->execute([':id' => $idEquipamento]);
    $garantias = $stmtGar->fetchAll(PDO::FETCH_OBJ);

    $ligacao = null;
} catch (PDOException $err) {
    $erro = 'bd';
}

$badgeEstado = match ($eq->estado ?? '') {
    'operacional' => 'badge-estado-operacional',
    'manutencao'  => 'badge-estado-manutencao',
    'avariado'    => 'badge-estado-avariado',
    default       => 'badge-estado-inativo',
};
$badgeCrit = match ($eq->criticidadeClinica ?? '') {
    'Suporte de vida' => 'badge-crit-vida',
    'Alta'            => 'badge-crit-alta',
    'Media'           => 'badge-crit-media',
    default           => 'badge-crit-baixa',
};
?>
<?php include '../../includes/header.php'; ?>
<div class="app-viewport">
<?php include '../../includes/nav.php'; ?>
<div class="content-body">
<?php include '../../includes/sidebar.php'; ?>

<main class="main-content-wrapper">

    <?php if ($erro === 'bd'): ?>
        <?= mensagem_erro_bd() ?>
    <?php elseif ($eq): ?>

    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="fw-bold h2 mb-1">
                <?= htmlspecialchars($eq->nomeEquipamento) ?>
                <span class="badge <?= $badgeEstado ?> ms-2 fs-6"><?= htmlspecialchars($eq->estado) ?></span>
            </h1>
            <p class="text-muted small mb-0">
                <?= $eq->codigoInventario ? htmlspecialchars($eq->codigoInventario) . ' · ' : '' ?>
                <?= htmlspecialchars($eq->categoria ?? '') ?>
            </p>
        </div>
        <a href="equipamentos.php" class="btn btn-outline-secondary px-3 py-2">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
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
            <div class="row g-4">

                <!-- COLUNA ESQUERDA -->
                <div class="col-12 col-lg-8">

                    <!-- Identificação -->
                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <p class="secao-form-titulo mt-0">Identificação</p>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Código</small>
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
                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <p class="secao-form-titulo mt-0">Dados Técnicos</p>
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
                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <p class="secao-form-titulo mt-0">Aquisição</p>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Data de Aquisição</small>
                                <p class="mb-0"><?= $eq->dataAquisicao ? date('d/m/Y', strtotime($eq->dataAquisicao)) : 'N/D' ?></p>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Ano Fabrico</small>
                                <p class="mb-0"><?= htmlspecialchars($eq->anoFabrico ?? 'N/D') ?></p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Custo</small>
                                <p class="mb-0"><?= $eq->custoAquisicao ? number_format($eq->custoAquisicao, 2, ',', '.') . ' €' : 'N/D' ?></p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Tipo de Entrada</small>
                                <p class="mb-0"><?= htmlspecialchars(ucfirst($eq->tipoEntrada ?? 'N/D')) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Fornecedor Associado -->
                    <?php if ($eq->nomeFornecedor): ?>
                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="secao-form-titulo mt-0 mb-0">Fornecedor Associado</p>
                            <a href="../fornecedores/detalhes_fornecedor.php?id_fornecedor=<?= aes_encrypt($eq->idFornecedor) ?>"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ver Fornecedor
                            </a>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Entidade</small>
                                <p class="fw-semibold mb-0"><?= htmlspecialchars($eq->nomeFornecedor) ?></p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Telefone</small>
                                <p class="mb-0">
                                    <a href="tel:<?= htmlspecialchars($eq->contactoTelefonico ?? '') ?>" class="text-dark">
                                        <?= htmlspecialchars($eq->contactoTelefonico ?? 'N/D') ?>
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Email</small>
                                <p class="mb-0">
                                    <a href="mailto:<?= htmlspecialchars($eq->enderecoEmail ?? '') ?>" class="text-dark">
                                        <?= htmlspecialchars($eq->enderecoEmail ?? 'N/D') ?>
                                    </a>
                                </p>
                            </div>
                            <?php if (!empty($eq->website)): ?>
                            <div class="col-md-4">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Website</small>
                                <p class="mb-0">
                                    <a href="https://<?= htmlspecialchars($eq->website) ?>" target="_blank" class="text-primary">
                                        <?= htmlspecialchars($eq->website) ?>
                                    </a>
                                </p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($eq->pessoaContacto)): ?>
                            <div class="col-md-4">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Pessoa de Contacto</small>
                                <p class="mb-0"><?= htmlspecialchars($eq->pessoaContacto) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($eq->observacoes): ?>
                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <p class="secao-form-titulo mt-0">Observações</p>
                        <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($eq->observacoes)) ?></p>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- COLUNA DIREITA -->
                <div class="col-12 col-lg-4">

                    <!-- Estado Clínico -->
                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <p class="secao-form-titulo mt-0">Estado Clínico</p>
                        <div class="mb-3">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Estado Técnico</small>
                            <p class="mb-0 mt-1">
                                <span class="badge <?= $badgeEstado ?> fs-6"><?= htmlspecialchars($eq->estado) ?></span>
                            </p>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Criticidade Clínica</small>
                            <p class="mb-0 mt-1">
                                <span class="badge <?= $badgeCrit ?> fs-6"><?= htmlspecialchars($eq->criticidadeClinica ?? 'N/D') ?></span>
                            </p>
                        </div>
                    </div>

                    <!-- Localização -->
                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <p class="secao-form-titulo mt-0">Localização</p>
                        <?php if ($eq->nomeSala): ?>
                            <p class="fw-bold mb-1"><?= htmlspecialchars($eq->nomeSala) ?></p>
                            <?php if ($eq->servico): ?>
                                <p class="text-muted small mb-1">
                                    <i class="fa-solid fa-briefcase-medical me-1"></i><?= htmlspecialchars($eq->servico) ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($eq->edificio): ?>
                                <p class="text-muted small mb-0">
                                    <i class="fa-solid fa-building me-1"></i><?= htmlspecialchars($eq->edificio) ?>
                                    <?= $eq->piso ? ', Piso ' . htmlspecialchars($eq->piso) : '' ?>
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted small mb-0">Sem localização atribuída.</p>
                            <?php if ($_perfil === 'administrador'): ?>
                                <a href="editar_equipamento.php?id_equipamento=<?= htmlspecialchars($idEncrypted) ?>" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fa-solid fa-plus me-1"></i>Atribuir Localização
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Ações -->
                    <?php if ($_perfil === 'administrador' && $eq->ativo == 1): ?>
                    <div class="d-grid gap-2">
                        <a href="editar_equipamento.php?id_equipamento=<?= htmlspecialchars($idEncrypted) ?>"
                            class="btn btn-acao-primaria fw-bold py-2">
                            <i class="fa-solid fa-pen me-2"></i>Editar Equipamento
                        </a>
                        <a href="apagar_equipamento.php?id_equipamento=<?= htmlspecialchars($idEncrypted) ?>"
                            class="btn btn-danger py-2">
                            <i class="fa-solid fa-trash me-2"></i>Desativar
                        </a>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- TAB: Documentos -->
        <div class="tab-pane fade" id="tab-documentos">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Documentação Técnica</h6>
                    <?php if ($_perfil === 'administrador'): ?>
                        <a href="../documentacao/inserir_documento.php?id_equipamento=<?= urlencode($idEncrypted) ?>"
                            class="btn btn-sm btn-acao-primaria">
                            <i class="fa-solid fa-plus me-1"></i>Adicionar Documento
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (empty($documentos)): ?>
                    <p class="text-muted text-center py-4">
                        <i class="fa-solid fa-file-circle-xmark d-block fs-3 mb-2 opacity-25"></i>
                        Não existem documentos associados a este equipamento.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Nome</th>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th>Validade</th>
                                    <th class="text-end pe-3">Abrir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documentos as $doc): ?>
                                    <tr>
                                        <td class="ps-3"><strong><?= htmlspecialchars($doc->nomeDocumento) ?></strong></td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                <?= htmlspecialchars(str_replace('_', ' ', $doc->tipoDocumento)) ?>
                                            </span>
                                        </td>
                                        <td><?= $doc->dataDocumento ? date('d/m/Y', strtotime($doc->dataDocumento)) : 'N/D' ?></td>
                                        <td>
                                            <?php if ($doc->dataValidade):
                                                $diff = (strtotime($doc->dataValidade) - time()) / 86400; ?>
                                                <span class="<?= $diff < 30 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                                    <?= date('d/m/Y', strtotime($doc->dataValidade)) ?>
                                                    <?= $diff < 0 ? ' <span class="badge bg-danger">Expirado</span>' : '' ?>
                                                </span>
                                            <?php else: ?>N/D<?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <?php if ($doc->nomeFicheiro): ?>
                                                <a href="/sibdas/1231343/medcare-inventory-solutions/Private/documentos/<?= htmlspecialchars($doc->nomeFicheiro) ?>"
                                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-file-pdf me-1"></i>Abrir
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
        </div>

        <!-- TAB: Garantias -->
        <div class="tab-pane fade" id="tab-garantias">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Garantias e Contratos</h6>
                    <?php if ($_perfil === 'administrador'): ?>
                        <a href="../garantias/inserir_garantia.php?id_equipamento=<?= urlencode($idEncrypted) ?>"
                            class="btn btn-sm btn-acao-primaria">
                            <i class="fa-solid fa-plus me-1"></i>Registar Garantia
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (empty($garantias)): ?>
                    <p class="text-muted text-center py-4">
                        <i class="fa-solid fa-file-circle-xmark d-block fs-3 mb-2 opacity-25"></i>
                        Não existem garantias registadas para este equipamento.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Entidade Responsável</th>
                                    <th>Tipo de Contrato</th>
                                    <th>Periodicidade</th>
                                    <th>Início</th>
                                    <th>Fim</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($garantias as $gar):
                                    $diasRestantes = (strtotime($gar->dataFim) - time()) / 86400;
                                    if ($diasRestantes < 0)
                                        $bgGar = '<span class="badge bg-danger">Expirada</span>';
                                    elseif ($diasRestantes <= 30)
                                        $bgGar = '<span class="badge bg-warning text-dark">Expira em ' . round($diasRestantes) . ' dias</span>';
                                    else
                                        $bgGar = '<span class="badge bg-success">Ativa</span>';
                                ?>
                                    <tr>
                                        <td class="ps-3"><strong><?= htmlspecialchars($gar->entidadeResponsavel ?? 'N/D') ?></strong></td>
                                        <td><?= htmlspecialchars($gar->tipoContrato ?? 'Sem contrato') ?></td>
                                        <td><?= htmlspecialchars($gar->periodicidade ?? 'N/D') ?></td>
                                        <td><?= date('d/m/Y', strtotime($gar->dataInicio)) ?></td>
                                        <td><?= date('d/m/Y', strtotime($gar->dataFim)) ?></td>
                                        <td><?= $bgGar ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /tab-content -->

    <?php endif; ?>

</main>
</div>
</div>

<?php include '../../includes/footer.php'; ?>