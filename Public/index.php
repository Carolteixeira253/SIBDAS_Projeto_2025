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
    // Se não conseguir ligar usa valores por defeito
    $configs = [
        'nome_hospital'  => 'MedCare Inventory Solutions',
        'hero_titulo'    => 'Gestão Eficiente de Inventário Médico',
        'hero_descricao' => 'Otimize o controlo, manutenção e rastreabilidade de todos os equipamentos clínicos da sua unidade de saúde.',
    ];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($configs['nome_hospital'] ?? 'MedCare Inventory Solutions') ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
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
        <section class="hero">
            <div class="hero-texto">
                <h1><?= htmlspecialchars($configs['hero_titulo'] ?? 'Gestão Eficiente de Inventário Médico') ?></h1>
                <p><?= htmlspecialchars($configs['hero_descricao'] ?? '') ?></p>
                <div class="hero-botoes">
                    <a href="contacto.php" class="btn-azul">Solicitar Demonstração</a>
                    <a href="servicos.php" class="btn-transparente">Saber Mais</a>
                </div>
            </div>
            <div class="hero-imagem">
                <img src="assets/img/gestao_inventario.png" alt="Gestão de Inventário Médico">
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