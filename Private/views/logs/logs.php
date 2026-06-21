<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
redirect_if_not_admin();

$ficheiroLog = __DIR__ . '/../../../logs/sistema.log';
$linhas = [];
$erro = '';

if (file_exists($ficheiroLog)) {
    $conteudo = file($ficheiroLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $linhas = array_reverse($conteudo); // mais recentes primeiro
} else {
    $erro = 'Ficheiro de log não encontrado.';
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
                    <h1 class="fw-bold h2 mb-1">Logs do Sistema</h1>
                    <p class="text-muted small mb-0">Registo de eventos e acções dos utilizadores.</p>
                </div>
                <span class="badge bg-primary fs-6"><?= count($linhas) ?> eventos</span>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($erro) ?>
                </div>
            <?php elseif (empty($linhas)): ?>
                <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
                    <i class="fa-solid fa-inbox fs-1 text-muted opacity-25 mb-3"></i>
                    <p class="text-muted">Não existem eventos registados ainda.</p>
                </div>
            <?php else: ?>

                <!-- Filtro -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="position-relative flex-grow-1" style="max-width:320px;">
                            <i class="fa-solid fa-magnifying-glass position-absolute"
                                style="left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:0.85rem;"></i>
                            <input type="text" id="filtro-log" class="form-control ps-4"
                                placeholder="Pesquisar nos logs..."
                                style="border-color:#d0e1fd; border-radius:0.5rem;">
                        </div>
                        <button onclick="document.getElementById('filtro-log').value=''; filtrarLogs();"
                            class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-xmark me-1"></i>Limpar
                        </button>
                    </div>
                </div>

                <!-- Tabela de logs -->
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Data/Hora</th>
                                    <th>Tipo</th>
                                    <th>Utilizador</th>
                                    <th>IP</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody id="corpo-logs">
                                <?php foreach ($linhas as $linha):
                                    // Formato: [2026-06-20 14:25:31] | LOGIN | admin@medcare.pt | 127.0.0.1 | Login efectuado
                                    $partes = explode(' | ', $linha);
                                    $data   = isset($partes[0]) ? trim($partes[0], '[]') : 'N/D';
                                    $tipo   = isset($partes[1]) ? trim($partes[1]) : 'N/D';
                                    $user   = isset($partes[2]) ? trim($partes[2]) : 'N/D';
                                    $ip     = isset($partes[3]) ? trim($partes[3]) : 'N/D';
                                    $desc   = isset($partes[4]) ? trim($partes[4]) : 'N/D';

                                    $badgeTipo = match (strtolower($tipo)) {
                                        'login'    => 'bg-success',
                                        'logout'   => 'bg-secondary',
                                        'exportar' => 'bg-primary',
                                        'erro'     => 'bg-danger',
                                        default    => 'bg-info',
                                    };
                                ?>
                                    <tr data-texto="<?= strtolower(htmlspecialchars($linha)) ?>">
                                        <td class="ps-4" style="font-size:0.82rem; color:#64748b; white-space:nowrap;">
                                            <?= htmlspecialchars($data) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $badgeTipo ?>"><?= htmlspecialchars($tipo) ?></span>
                                        </td>
                                        <td style="font-size:0.85rem;"><?= htmlspecialchars($user) ?></td>
                                        <td style="font-size:0.82rem; color:#64748b;"><?= htmlspecialchars($ip) ?></td>
                                        <td style="font-size:0.85rem; color:#475569;"><?= htmlspecialchars($desc) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-2 border-top" style="background:#f8fafc; font-size:0.82rem; color:#64748b;">
                        <span id="contador-logs"><?= count($linhas) ?> evento(s) encontrado(s)</span>
                    </div>
                </div>

            <?php endif; ?>

        </main>
    </div>
</div>

<script>
    function filtrarLogs() {
        const txt = document.getElementById('filtro-log').value.toLowerCase();
        const rows = document.querySelectorAll('#corpo-logs tr');
        let n = 0;
        rows.forEach(r => {
            const ok = !txt || r.dataset.texto.includes(txt);
            r.style.display = ok ? '' : 'none';
            if (ok) n++;
        });
        document.getElementById('contador-logs').textContent = n + ' evento(s) encontrado(s)';
    }
    document.getElementById('filtro-log').addEventListener('input', filtrarLogs);
</script>

<?php include '../../includes/footer.php'; ?>