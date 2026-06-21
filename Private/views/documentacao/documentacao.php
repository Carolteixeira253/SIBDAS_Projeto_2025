<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

$erro = '';
$resultados = [];

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultados = $ligacao->query("
        SELECT d.*, e.nomeEquipamento, e.codigoInventario
        FROM Documentacao d
        LEFT JOIN Equipamento e ON d.idEquipamento = e.idEquipamento
        WHERE d.ativo = 1
        ORDER BY d.dataDocumento DESC
    ")->fetchAll(PDO::FETCH_OBJ);
    $totalGeral   = count($resultados);
    $totalValidos = count(array_filter($resultados, fn($d) => !$d->dataValidade || strtotime($d->dataValidade) > time()));
    $totalExpirados = count(array_filter($resultados, fn($d) => $d->dataValidade && strtotime($d->dataValidade) <= time()));
} catch (PDOException $err) {
    $erro = 'bd';
}
$ligacao = null;
?>
<?php include '../../includes/header.php'; ?>
<div class="app-viewport">
    <?php include '../../includes/nav.php'; ?>
    <div class="app-viewport">
        <div class="content-body">
            <?php include '../../includes/sidebar.php'; ?>

            <main class="main-content-wrapper">

                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="fw-bold h2 mb-1">Documentação Técnica</h1>
                        <p class="text-muted small mb-0">Repositório de manuais, certificados e contratos de manutenção.</p>
                    </div>
                    <?php if ($_perfil === 'administrador'): ?>
                        <a href="inserir_documento.php" class="btn btn-acao-primaria fw-bold px-3 py-2">
                            <i class="fa-solid fa-plus me-2"></i>Adicionar
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($erro === 'bd'): ?>
                    <?= mensagem_erro_bd() ?>
                <?php else: ?>

                    <!-- Contadores -->
                    <div class="d-flex align-items-center gap-4 mb-3 px-1" style="font-size:0.875rem;">
                        <span><strong style="color:#0f172a;"><?= $totalGeral ?></strong> <span class="text-muted">Total</span></span>
                        <span class="text-muted">·</span>
                        <span><strong style="color:#15803d;"><?= $totalValidos ?></strong> <span class="text-muted">Válidos</span></span>
                        <span class="text-muted">·</span>
                        <span><strong style="color:#b91c1c;"><?= $totalExpirados ?></strong> <span class="text-muted">Expirados</span></span>
                    </div>

                    <!-- Filtros -->
                    <div class="card border-0 shadow-sm rounded-3 mb-3 px-4 py-3">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="position-relative flex-grow-1" style="min-width:200px; max-width:320px;">
                                <i class="fa-solid fa-magnifying-glass position-absolute"
                                    style="left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:0.85rem;"></i>
                                <input type="text" id="filtro-texto" class="form-control ps-4"
                                    placeholder="Pesquisar documento ou equipamento..."
                                    style="border-color:#d0e1fd; border-radius:0.5rem;">
                            </div>
                            <select id="filtro-tipo" class="form-select" style="width:auto; border-color:#d0e1fd; border-radius:0.5rem;">
                                <option value="">Tipo</option>
                                <option value="manual_utilizador">Manual Utilizador</option>
                                <option value="manual_servico">Manual Serviço</option>
                                <option value="certificado_calibracao">Certificado Calibração</option>
                                <option value="contrato_manutencao">Contrato Manutenção</option>
                                <option value="relatorio_tecnico">Relatório Técnico</option>
                                <option value="declaracao_conformidade">Declaração Conformidade</option>
                                <option value="fatura">Fatura</option>
                            </select>
                            <button id="limpar-filtros" class="btn btn-outline-secondary btn-sm" style="border-radius:0.5rem;">
                                <i class="fa-solid fa-xmark me-1"></i>Limpar
                            </button>
                        </div>
                    </div>

                    <!-- Tabela -->
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Documento</th>
                                        <th>Tipo</th>
                                        <th>Equipamento</th>
                                        <th>Data</th>
                                        <th>Validade</th>
                                        <th>PDF</th>
                                        <th class="text-end pe-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="corpo-tabela">
                                    <?php if (count($resultados) == 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <i class="fa-solid fa-inbox fs-3 d-block mb-2 opacity-25"></i>
                                                Não existem documentos registados.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($resultados as $doc):
                                            $expirado = $doc->dataValidade && strtotime($doc->dataValidade) < time();
                                            $expiraBreve = $doc->dataValidade && !$expirado && (strtotime($doc->dataValidade) - time()) / 86400 <= 30;
                                        ?>
                                            <tr data-texto="<?= strtolower(htmlspecialchars($doc->nomeDocumento . ' ' . ($doc->nomeEquipamento ?? ''))) ?>"
                                                data-tipo="<?= htmlspecialchars($doc->tipoDocumento ?? '') ?>">
                                                <td class="ps-4">
                                                    <strong style="color:#0f172a;"><?= htmlspecialchars($doc->nomeDocumento) ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $doc->tipoDocumento ?? 'N/D'))) ?>
                                                    </span>
                                                </td>
                                                <td style="font-size:0.85rem; color:#64748b;">
                                                    <?php if ($doc->nomeEquipamento): ?>
                                                        <a href="../equipamentos/detalhes_equipamento.php?id_equipamento=<?= aes_encrypt($doc->idEquipamento) ?>" class="text-primary">
                                                            <?= htmlspecialchars($doc->codigoInventario ?? '') ?>
                                                            <?= htmlspecialchars($doc->nomeEquipamento) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/D</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="font-size:0.85rem; color:#64748b;">
                                                    <?= $doc->dataDocumento ? date('d/m/Y', strtotime($doc->dataDocumento)) : 'N/D' ?>
                                                </td>
                                                <td>
                                                    <?php if ($doc->dataValidade): ?>
                                                        <span class="<?= $expirado ? 'text-danger fw-bold' : ($expiraBreve ? 'text-warning fw-bold' : 'text-muted') ?>" style="font-size:0.85rem;">
                                                            <?= date('d/m/Y', strtotime($doc->dataValidade)) ?>
                                                            <?php if ($expirado): ?>
                                                                <span class="badge bg-danger ms-1">Expirado</span>
                                                            <?php elseif ($expiraBreve): ?>
                                                                <span class="badge bg-warning text-dark ms-1">Expira em breve</span>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Sem validade</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($doc->nomeFicheiro): ?>
                                                        <a href="/sibdas/1231343/medcare-inventory-solutions/Private/documentos/<?= htmlspecialchars($doc->nomeFicheiro) ?>"
                                                            target="_blank" class="btn btn-sm btn-outline-danger" title="Abrir PDF">
                                                            <i class="fa-solid fa-file-pdf me-1"></i>PDF
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="detalhes_documento.php?id_documento=<?= aes_encrypt($doc->idDocumento) ?>"
                                                        class="btn btn-sm btn-tabela-ver me-1" title="Ver detalhes">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <?php if ($_perfil === 'administrador'): ?>
                                                        <a href="editar_documento.php?id_documento=<?= aes_encrypt($doc->idDocumento) ?>"
                                                            class="btn btn-sm btn-tabela-editar me-1" title="Editar">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </a>
                                                        <a href="apagar_documento.php?id_documento=<?= aes_encrypt($doc->idDocumento) ?>"
                                                            class="btn btn-sm btn-tabela-apagar" title="Apagar">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top"
                            style="background:#f8fafc; font-size:0.82rem; color:#64748b;">
                            <span id="contador-resultados"></span>
                            <div id="paginacao" class="d-flex align-items-center"></div>
                        </div>
                    </div>

                <?php endif; ?>

            </main>
        </div>
    </div>

    <script>
        const rows = document.querySelectorAll('#corpo-tabela tr[data-texto]');
        const inTexto = document.getElementById('filtro-texto');
        const inTipo = document.getElementById('filtro-tipo');
        const btnLimp = document.getElementById('limpar-filtros');
        const contador = document.getElementById('contador-resultados');
        const paginacao = document.getElementById('paginacao');

        const POR_PAGINA = 10;
        let paginaAtual = 1;
        let rowsFiltradas = [];

        function filtrar() {
            const txt = inTexto.value.toLowerCase().trim();
            const tipo = inTipo.value;
            rowsFiltradas = [];
            rows.forEach(r => {
                const ok = (!txt || r.dataset.texto.includes(txt)) &&
                    (!tipo || r.dataset.tipo === tipo);
                r.style.display = 'none';
                if (ok) rowsFiltradas.push(r);
            });
            paginaAtual = 1;
            renderPagina();
        }

        function renderPagina() {
            const total = rowsFiltradas.length;
            const totalPaginas = Math.ceil(total / POR_PAGINA) || 1;
            rows.forEach(r => r.style.display = 'none');
            rowsFiltradas.slice((paginaAtual - 1) * POR_PAGINA, paginaAtual * POR_PAGINA).forEach(r => r.style.display = '');
            contador.textContent = total + ' documento(s) encontrado(s) · Página ' + paginaAtual + ' de ' + totalPaginas;
            paginacao.innerHTML = '';
            if (totalPaginas <= 1) return;
            const btn = (txt, pg, disabled, active) => {
                const b = document.createElement('button');
                b.textContent = txt;
                b.className = 'btn btn-sm mx-1 ' + (active ? 'btn-acao-primaria' : 'btn-outline-secondary');
                b.disabled = disabled;
                b.onclick = () => {
                    paginaAtual = pg;
                    renderPagina();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                };
                return b;
            };
            paginacao.appendChild(btn('«', 1, paginaAtual === 1, false));
            paginacao.appendChild(btn('‹', paginaAtual - 1, paginaAtual === 1, false));
            for (let i = 1; i <= totalPaginas; i++) {
                if (i === 1 || i === totalPaginas || Math.abs(i - paginaAtual) <= 1)
                    paginacao.appendChild(btn(i, i, false, i === paginaAtual));
                else if (Math.abs(i - paginaAtual) === 2) {
                    const s = document.createElement('span');
                    s.textContent = '...';
                    s.className = 'mx-1 text-muted align-self-center';
                    paginacao.appendChild(s);
                }
            }
            paginacao.appendChild(btn('›', paginaAtual + 1, paginaAtual === totalPaginas, false));
            paginacao.appendChild(btn('»', totalPaginas, paginaAtual === totalPaginas, false));
        }

        inTexto.addEventListener('input', filtrar);
        inTipo.addEventListener('change', filtrar);
        btnLimp.addEventListener('click', () => {
            inTexto.value = inTipo.value = '';
            filtrar();
        });
        filtrar();
    </script>

    <?php include '../../includes/footer.php'; ?>