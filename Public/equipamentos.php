<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipamentos - MedCare Inventory Solutions</title>

    <link rel="stylesheet" href="assets/fontawesome/all.min.css">
    <link rel="stylesheet" href="assets/css/1231343.css">
</head>

<body>

    <!--CABEÇALHO e NAVBAR-->
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
    <!-- GRELHA DE EQUIPAMENTOS -->
    <main>
        <section class="eq-seccao">
            <div class="eq-intro">
                <span class="subtitulo-clinico">Inventário Hospitalar</span>
                <h1>Gestão Avançada de Dispositivos Críticos</h1>
                <p>Monitorize o estado de calibração, localização e ciclos de manutenção dos equipamentos da sua unidade
                    de saúde.</p>
            </div>

            <!-- Grelha de Cartões (Grid) -->
            <div class="eq-grid">
                <!-- EQUIPAMENTO 1-->
                <div class="eq-card">
                    <div class="eq-status operacional">● Operacional</div>

                    <div class="eq-imagem-placeholder">
                        <img src="assets/img/monitor.webp" alt="Monitores">
                    </div>


                    <div class="eq-card-header">
                        <div class="eq-icon-wrapper"><i class="fas fa-heartbeat"></i></div>
                        <h4>Monitores Multiparamétricos</h4>
                    </div>
                    <p>Controlo de sinais vitais, calibração de sensores rítmicos e integração de dados em tempo real
                        com a central clínica.</p>
                </div>

                <!-- EQUIPAMENTO 2-->
                <div class="eq-card">
                    <div class="eq-status operacional">● Operacional</div>

                    <div class="eq-imagem-placeholder">
                        <img src="assets/img/ventilador.jpg" alt="Ventiladores">
                    </div>


                    <div class="eq-card-header">
                        <div class="eq-icon-wrapper"><i class="fas fa-wind"></i></div>
                        <h4>Ventiladores Volumétricos</h4>
                    </div>
                    <p>Sistemas críticos de suporte de vida com monitorização preventiva de pressão, fluxo e desgaste de
                        válvulas expiratórias.</p>
                </div>

                <!-- EQUIPAMENTO 3-->
                <div class="eq-card">
                    <div class="eq-status manutencao">● Operacional </div>

                    <div class="eq-imagem-placeholder">
                        <img src="assets/img/desfibrilhador.jpg" alt="Desfibrilhadores">
                    </div>


                    <div class="eq-card-header">
                        <div class="eq-icon-wrapper"><i class="fas fa-bolt"></i></div>
                        <h4>Desfibrilhadores Externos (DEA)</h4>
                    </div>
                    <p>Rastreio automático de carga de baterias, validade das pás adesivas e testes internos de descarga
                        de alta energia.</p>
                </div>
                <!-- EQUIPAMENTO 4-->
                <div class="eq-card">
                    <div class="eq-status operacional">● Operacional</div>

                    <div class="eq-imagem-placeholder">
                        <img src="assets/img/bomba_infusao.png" alt="Bomba de Infusao">
                    </div>


                    <div class="eq-card-header">
                        <div class="eq-icon-wrapper"><i class="fas fa-syringe"></i></div>
                        <h4>Bombas de Infusão Contínua</h4>
                    </div>
                    <p>Calibração micrométrica de fluxo volumétrico para administração rigorosa de terapêuticas e
                        fármacos endovenosos.</p>
                </div>

            </div>
        </section>
    </main>

    <footer class="footer-container">
        <div class="footer-section">
            <p>&copy; 2026 MedCare Inventory Solutions. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>

</html>