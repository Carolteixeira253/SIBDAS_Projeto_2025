<?php
require_once __DIR__ . '/../config/config.php';
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
    $configs = [];
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - MedCare Inventory Solutions</title>

    <link rel="stylesheet" href="assets/fontawesome/all.min.css">
    <link rel="stylesheet" href="assets/css/1231343.css">
</head>

<body>
    <header>
        <nav class="bng-navbar">
            <div class="logo-area">
                <img src="assets/img/logo.png" alt="MedCare Logo">
                <h3>MedCare</h3>
            </div>

            <div class="container-navegacao">
                <a href="index.php">Início</a>
                <a href="quem_somos.php">Quem Somos</a>
                <a href="equipamentos.php">Equipamentos</a>
                <a href="servicos.php">Serviços</a>
                <a href="contacto.php">Contacto</a>
            </div>

            <div class="nav-cliente">
                <a href="login.php"><i class="fas fa-user"></i> Entrar</a>
            </div>
        </nav>
    </header>
    <main>
        <section class="srv-seccao">
            <div class="srv-intro">
                <span class="subtitulo-clinico">Soluções Biomédicas</span>
                <h1>Os Nossos Serviços</h1>
                <p>Garantimos a máxima segurança e continuidade operacional dos ativos de saúde através de processos
                    rigorosos de controlo.</p>
            </div>

            <div class="srv-grid">

                <div class="srv-card">
                    <div class="srv-imagem-placeholder">
                        <img src="assets/img/calibracao.jpg" alt="Calibração">
                    </div>

                    <div class="srv-card-header">
                        <div class="srv-icon-wrapper"><i class="fas fa-calculator"></i></div>
                        <h4>Calibração & Metrologia</h4>
                    </div>
                    <p>Ensaios quantitativos e calibração de sensores biomédicos de acordo com os padrões regulamentares
                        internacionais.</p>
                </div>

                <div class="srv-card">
                    <div class="srv-imagem-placeholder">
                        <img src="assets/img/manutencao.png" alt="Manutenção">
                    </div>

                    <div class="srv-card-header">
                        <div class="srv-icon-wrapper"><i class="fas fa-shield-virus"></i></div>
                        <h4>Manutenção Preventiva</h4>
                    </div>
                    <p>Planos sistemáticos de vistorias técnicas para mitigar riscos de falha mecânica ou eletrónica em
                        blocos cirúrgicos.</p>
                </div>

                <div class="srv-card">
                    <div class="srv-imagem-placeholder">
                       <img src="assets/img/auditoria_inventario.webp" alt="Auditoria_inventario">
                    </div>

                    <div class="srv-card-header">
                        <div class="srv-icon-wrapper"><i class="fas fa-clipboard-check"></i></div>
                        <h4>Auditoria de Inventário</h4>
                    </div>
                    <p>Mapeamento e identificação digital de todo o parque tecnológico hospitalar para controlo de
                        localização em tempo real.</p>
                </div>

                <div class="srv-card">
                    <div class="srv-imagem-placeholder">
                        <img src="assets/img/formacao_tecnica.jpeg" alt="Formação Técnica">
                    </div>

                    <div class="srv-card-header">
                        <div class="srv-icon-wrapper"><i class="fas fa-chalkboard-teacher"></i></div>
                        <h4>Formação Técnica</h4>
                    </div>
                    <p>Capacitação de equipas de saúde e clínicos para a correta manipulação operacional dos
                        dispositivos de suporte de vida.</p>
                </div>

            </div>
        </section>
    </main>

    <footer class="footer-container">
        <div class="footer-section">
            <p>&copy; 2026 <?= htmlspecialchars($configs['nome_hospital'] ?? 'MedCare Inventory Solutions') ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>

</html>