<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

// Registar o evento no log
registar_log('exportar', 'Exportação CSV de equipamentos pelo utilizador ' . ($_SESSION['utilizador'] ?? 'desconhecido'));

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->query("
        SELECT
            e.codigoInventario      AS 'Código',
            e.nomeEquipamento       AS 'Equipamento',
            e.marca                 AS 'Marca',
            e.modelo                AS 'Modelo',
            e.numeroSerie           AS 'Nº Série',
            e.fabricante            AS 'Fabricante',
            e.categoria             AS 'Categoria',
            e.estado                AS 'Estado',
            e.criticidadeClinica    AS 'Criticidade',
            e.tipoEntrada           AS 'Tipo Entrada',
            e.dataAquisicao         AS 'Data Aquisição',
            e.anoFabrico            AS 'Ano Fabrico',
            e.custoAquisicao        AS 'Custo (€)',
            l.nomeSala              AS 'Sala',
            l.servico               AS 'Serviço',
            l.edificio              AS 'Edifício',
            l.piso                  AS 'Piso',
            f.nomeFornecedor        AS 'Fornecedor',
            e.observacoes           AS 'Observações'
        FROM Equipamento e
        LEFT JOIN Localizacao l ON e.idLocalizacao = l.idLocalizacao
        LEFT JOIN Fornecedor  f ON e.idFornecedor  = f.idFornecedor
        WHERE e.ativo = 1
        ORDER BY e.idEquipamento ASC
    ");
    $equipamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ligacao = null;

} catch (PDOException $e) {
    die('Erro ao exportar: ' . $e->getMessage());
}

// Headers HTTP que forçam o download do ficheiro
$nome = 'medcare_equipamentos_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nome . '"');

$saida = fopen('php://output', 'w');

// BOM UTF-8 — garante que o Excel abre com acentos correctos
fprintf($saida, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Cabeçalhos das colunas
if (!empty($equipamentos)) {
    fputcsv($saida, array_keys($equipamentos[0]), ';');
}

// Uma linha por equipamento
foreach ($equipamentos as $eq) {
    fputcsv($saida, $eq, ';');
}

fclose($saida);
exit;