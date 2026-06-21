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
    <title>Quem Somos - MedCare Inventory Solutions</title>

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
        <section class="quem-somos-container">
            <div class="quem-somos-conteudo">
                <span class="subtitulo-clinico">A Nossa Missão</span>
                <h1><?= htmlspecialchars($configs['sobre_titulo'] ?? 'Líderes em Engenharia Biomédica e Gestão de Dispositivos Médicos') ?></h1>
                <p><?= htmlspecialchars($configs['sobre_texto'] ?? 'Fundada com o objetivo de elevar os padrões de segurança e eficiência hospitalar.') ?></p>
                <p>Apoiamos hospitais, clínicas e unidades de saúde na transição para uma gestão digital inteligente,
                    garantindo que cada ventilador, monitor ou desfibrilhador esteja perfeitamente operacional,
                    calibrado e em total conformidade com as normas internacionais de saúde.</p>

                <div class="valores-grid">
                    <div class="valor-item">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Segurança Máxima</h4>
                        <p>Rastreabilidade total de cada dispositivo médico.</p>
                    </div>
                    <div class="valor-item">
                        <i class="fas fa-sync-alt"></i>
                        <h4>Manutenção Inteligente</h4>
                        <p>Alertas automáticos para calibrações e vistorias.</p>
                    </div>
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