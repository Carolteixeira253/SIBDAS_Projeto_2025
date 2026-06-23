-- --------------------------------------------------------
-- Anfitrião:                    vsgate-s1.dei.isep.ipp.pt
-- Versão do servidor:           8.0.45 - MySQL Community Server - GPL
-- SO do servidor:               Linux
-- HeidiSQL Versão:              12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- A despejar estrutura da base de dados para db1231343
CREATE DATABASE IF NOT EXISTS `db1231343` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db1231343`;

-- A despejar estrutura para tabela db1231343.Configuracao
CREATE TABLE IF NOT EXISTS `Configuracao` (
  `chave` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `valor` text COLLATE utf8mb4_bin,
  `descricao` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- A despejar dados para tabela db1231343.Configuracao: ~8 rows (aproximadamente)
INSERT INTO `Configuracao` (`chave`, `valor`, `descricao`) VALUES
	('email', 'geral@medcare.pt', 'Email de contacto'),
	('hero_descricao', 'Otimize o controlo, manutenção e rastreabilidade de todos os equipamentos clínicos da sua unidade de saúde.', 'Texto de apresentação da página inicial'),
	('hero_titulo', 'Gestão Eficiente de Inventário Médico', 'Título principal da página inicial'),
	('morada', 'Rua da Saúde, 1234 — Porto', 'Morada do hospital'),
	('nome_hospital', 'MedCare Inventory Solutions', 'Nome exibido no site público'),
	('quem_somos_texto', 'Fundada com o objetivo de elevar os padrões de segurança e eficiência hospitalar, a MedCare desenvolve soluções tecnológicas avançadas para o controlo total de inventários clínicos.', 'Texto da página Quem Somos'),
	('quem_somos_titulo', 'Líderes em Engenharia Biomédica e Gestão de Dispositivos Médicos', 'Título da página Quem Somos'),
	('telefone', '+351 220 000 000', 'Telefone de contacto');

-- A despejar estrutura para tabela db1231343.Documentacao
CREATE TABLE IF NOT EXISTS `Documentacao` (
  `idDocumento` int NOT NULL AUTO_INCREMENT,
  `nomeDocumento` varchar(150) COLLATE utf8mb4_bin NOT NULL,
  `tipoDocumento` enum('manual_utilizador','manual_servico','certificado_calibracao','contrato_manutencao','fatura','declaracao_conformidade','relatorio_tecnico') COLLATE utf8mb4_bin NOT NULL,
  `dataDocumento` date DEFAULT NULL,
  `dataValidade` date DEFAULT NULL,
  `nomeFicheiro` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `idEquipamento` int NOT NULL,
  `idFornecedor` int DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idDocumento`),
  KEY `FK_Doc_Equipamento` (`idEquipamento`),
  KEY `FK_Doc_Fornecedor` (`idFornecedor`),
  CONSTRAINT `FK_Doc_Equipamento` FOREIGN KEY (`idEquipamento`) REFERENCES `Equipamento` (`idEquipamento`),
  CONSTRAINT `FK_Doc_Fornecedor` FOREIGN KEY (`idFornecedor`) REFERENCES `Fornecedor` (`idFornecedor`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- A despejar dados para tabela db1231343.Documentacao: ~46 rows (aproximadamente)
INSERT INTO `Documentacao` (`idDocumento`, `nomeDocumento`, `tipoDocumento`, `dataDocumento`, `dataValidade`, `nomeFicheiro`, `idEquipamento`, `idFornecedor`, `ativo`) VALUES
	(1, 'Manual Utilizador IntelliVue MP5', 'manual_utilizador', '2022-03-15', NULL, 'manual_utilizador.pdf', 1, 1, 1),
	(2, 'Manual Serviço IntelliVue MP5', 'manual_servico', '2022-03-15', NULL, 'manual_servico.pdf', 1, 1, 1),
	(3, 'Certificado Calibração MP5 2024', 'certificado_calibracao', '2024-01-10', '2025-01-10', 'certificado_calibracao.pdf', 1, 1, 1),
	(4, 'Contrato Manutenção Philips 2024', 'contrato_manutencao', '2024-01-01', '2024-12-31', 'contrato_manutencao.pdf', 1, 1, 1),
	(5, 'Manual Utilizador Evita V500', 'manual_utilizador', '2021-06-20', NULL, 'manual_utilizador.pdf', 2, 2, 1),
	(6, 'Manual Serviço Evita V500', 'manual_servico', '2021-06-20', NULL, 'manual_servico.pdf', 2, 2, 1),
	(7, 'Contrato Full Service Dräger 2024', 'contrato_manutencao', '2024-01-01', '2024-12-31', 'contrato_manutencao.pdf', 2, 2, 1),
	(8, 'Relatório Manutenção Preventiva Jan 2024', 'relatorio_tecnico', '2024-01-15', NULL, 'relatorio_avaria.pdf', 2, 2, 1),
	(9, 'Declaração Conformidade Evita V500', 'declaracao_conformidade', '2021-06-20', NULL, NULL, 2, 2, 1),
	(10, 'Manual Utilizador Infusomat Space', 'manual_utilizador', '2020-09-10', NULL, 'manual_utilizador.pdf', 3, 4, 1),
	(11, 'Fatura Aquisição Bomba Infusão', 'fatura', '2020-09-10', NULL, 'fatura_equipamento.pdf', 3, 9, 1),
	(12, 'Certificado Calibração Desfibrilhador 2024', 'certificado_calibracao', '2024-02-01', '2025-02-01', NULL, 4, 8, 1),
	(13, 'Manual Utilizador Zoll R Series', 'manual_utilizador', '2021-01-05', NULL, 'certificado_calibracao.pdf', 4, 11, 1),
	(14, 'Contrato Manutenção Zoll 2024', 'contrato_manutencao', '2024-01-01', '2024-12-31', NULL, 4, 8, 1),
	(15, 'Relatório Inspecção Desfibrilhador 2024', 'relatorio_tecnico', '2024-03-10', NULL, 'contrato_manutencao.pdf', 4, 8, 1),
	(16, 'Manual Utilizador Vscan Air', 'manual_utilizador', '2023-02-28', NULL, 'relatorio_avaria.pdf', 5, 3, 1),
	(17, 'Declaração Conformidade Vscan Air', 'declaracao_conformidade', '2023-02-28', NULL, NULL, 5, 3, 1),
	(18, 'Manual Utilizador AT-102', 'manual_utilizador', '2019-11-12', NULL, NULL, 6, 17, 1),
	(19, 'Certificado Calibração AT-102 2023', 'certificado_calibracao', '2023-11-12', '2024-11-12', 'fatura_equipamento.pdf', 6, 8, 1),
	(20, 'Manual Utilizador GSS67H', 'manual_utilizador', '2020-05-18', NULL, NULL, 7, 18, 1),
	(21, 'Relatório Qualificação Autoclave 2024', 'relatorio_tecnico', '2024-03-01', NULL, NULL, 7, 8, 1),
	(22, 'Declaração Conformidade GSS67H', 'declaracao_conformidade', '2020-05-18', NULL, NULL, 7, 18, 1),
	(23, 'Manual Utilizador Raio-X GE', 'manual_utilizador', '2022-07-14', NULL, NULL, 10, 3, 1),
	(24, 'Declaração Conformidade Raio-X', 'declaracao_conformidade', '2022-07-14', NULL, NULL, 10, 3, 1),
	(25, 'Relatório Avaria Raio-X 2024', 'relatorio_tecnico', '2024-06-01', NULL, 'doc_1781988222_certificado_calibracao.pdf', 10, 8, 1),
	(26, 'Manual Utilizador IntelliVue MX40', 'manual_utilizador', '2023-04-10', NULL, NULL, 11, 1, 1),
	(27, 'Contrato Garantia Philips MX40', 'contrato_manutencao', '2023-04-10', '2026-04-10', NULL, 11, 1, 1),
	(28, 'Declaração Conformidade MX40', 'declaracao_conformidade', '2023-04-10', NULL, NULL, 11, 1, 1),
	(29, 'Manual Utilizador Babylog VN500', 'manual_utilizador', '2021-05-15', NULL, NULL, 14, 2, 1),
	(30, 'Contrato Full Service Babylog', 'contrato_manutencao', '2021-05-15', '2026-05-15', NULL, 14, 2, 1),
	(31, 'Manual Utilizador Vivid E95', 'manual_utilizador', '2022-09-12', NULL, NULL, 23, 3, 1),
	(32, 'Contrato Manutenção GE Vivid E95', 'contrato_manutencao', '2022-09-12', '2025-09-12', NULL, 23, 3, 1),
	(33, 'Manual Utilizador Cios Alpha', 'manual_utilizador', '2022-03-08', NULL, NULL, 26, 5, 1),
	(34, 'Contrato Full Service Siemens', 'contrato_manutencao', '2022-03-08', '2025-03-08', NULL, 26, 5, 1),
	(35, 'Manual Utilizador ABL800', 'manual_utilizador', '2021-10-18', NULL, NULL, 28, 21, 1),
	(36, 'Certificado Calibração ABL800 2024', 'certificado_calibracao', '2024-01-20', '2025-01-20', NULL, 28, 29, 1),
	(37, 'Manual Utilizador Hemodialisador', 'manual_utilizador', '2021-11-11', NULL, NULL, 48, 7, 1),
	(38, 'Contrato Full Service Fresenius 2024', 'contrato_manutencao', '2024-01-01', '2024-12-31', NULL, 48, 7, 1),
	(39, 'Certificado Calibração Hemodialisador', 'certificado_calibracao', '2024-01-15', '2025-01-15', NULL, 48, 29, 1),
	(40, 'Manual Utilizador Microscópio Zeiss', 'manual_utilizador', '2021-01-25', NULL, NULL, 50, 19, 1),
	(41, 'Declaração Conformidade Zeiss OPMI', 'declaracao_conformidade', '2021-01-25', NULL, NULL, 50, 19, 1),
	(42, 'Contrato Full Service Zeiss 2024', 'contrato_manutencao', '2024-01-01', '2026-01-25', NULL, 50, 19, 1),
	(43, 'Manual Utilizador Endoscópio Olympus', 'manual_utilizador', '2021-02-14', NULL, NULL, 45, 10, 1),
	(44, 'Contrato Manutenção Olympus 2024', 'contrato_manutencao', '2024-01-01', '2024-12-31', NULL, 45, 10, 1),
	(45, 'Manual Utilizador Laringoscópio C-MAC', 'manual_utilizador', '2022-10-10', NULL, NULL, 44, 12, 1),
	(46, 'Declaração Conformidade C-MAC', 'declaracao_conformidade', '2022-10-10', NULL, NULL, 44, 12, 1),
	(47, 'contrato_manutencao', 'contrato_manutencao', NULL, NULL, 'doc_1781987890_contrato_manutencao.pdf', 50, NULL, 1),
	(48, 'Manual Instruções', 'manual_servico', NULL, NULL, 'doc_1782034075_manual_servico.pdf', 48, NULL, 1);

-- A despejar estrutura para tabela db1231343.Equipamento
CREATE TABLE IF NOT EXISTS `Equipamento` (
  `idEquipamento` int NOT NULL AUTO_INCREMENT,
  `nomeEquipamento` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `marca` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `numeroSerie` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `fabricante` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `dataAquisicao` date DEFAULT NULL,
  `anoFabrico` year DEFAULT NULL,
  `custoAquisicao` decimal(10,2) DEFAULT NULL,
  `tipoEntrada` enum('compra','doacao','aluguer','emprestimo') COLLATE utf8mb4_bin NOT NULL DEFAULT 'compra',
  `observacoes` text COLLATE utf8mb4_bin,
  `categoria` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_bin NOT NULL DEFAULT 'operacional',
  `criticidadeClinica` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `ultimaManutencao` date DEFAULT NULL,
  `proximaManutencao` date DEFAULT NULL,
  `documentacaoStatus` varchar(20) COLLATE utf8mb4_bin NOT NULL DEFAULT 'Completa',
  `idFornecedor` int DEFAULT NULL,
  `idLocalizacao` int DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `codigoInventario` varchar(50) COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`idEquipamento`),
  KEY `FK_Equipamento_Fornecedor` (`idFornecedor`),
  KEY `FK_Equipamento_Localizacao` (`idLocalizacao`),
  CONSTRAINT `FK_Equipamento_Fornecedor` FOREIGN KEY (`idFornecedor`) REFERENCES `Fornecedor` (`idFornecedor`) ON DELETE SET NULL,
  CONSTRAINT `FK_Equipamento_Localizacao` FOREIGN KEY (`idLocalizacao`) REFERENCES `Localizacao` (`idLocalizacao`) ON DELETE SET NULL,
  CONSTRAINT `CK_Equipamento_criticidade` CHECK ((`criticidadeClinica` in (_utf8mb4'Baixa',_utf8mb4'Media',_utf8mb4'Alta',_utf8mb4'Suporte de vida'))),
  CONSTRAINT `CK_Equipamento_datas` CHECK (((`proximaManutencao` >= `ultimaManutencao`) or (`proximaManutencao` is null) or (`ultimaManutencao` is null))),
  CONSTRAINT `CK_Equipamento_documento` CHECK ((`documentacaoStatus` in (_utf8mb4'Completa',_utf8mb4'Em Falta')))
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- A despejar dados para tabela db1231343.Equipamento: ~51 rows (aproximadamente)
INSERT INTO `Equipamento` (`idEquipamento`, `nomeEquipamento`, `marca`, `modelo`, `numeroSerie`, `fabricante`, `dataAquisicao`, `anoFabrico`, `custoAquisicao`, `tipoEntrada`, `observacoes`, `categoria`, `estado`, `criticidadeClinica`, `ultimaManutencao`, `proximaManutencao`, `documentacaoStatus`, `idFornecedor`, `idLocalizacao`, `ativo`, `codigoInventario`) VALUES
	(1, 'Monitor Multiparamétrico', 'Philips', 'IntelliVue MP5', 'MP5-2022-45873', 'Philips', '2022-03-15', '2022', 12500.00, 'compra', 'Inclui sensor SpO2 e cabo ECG', 'Monitorização', 'operacional', 'Alta', NULL, NULL, 'Completa', 1, 4, 1, 'EQ-MON-001'),
	(2, 'Ventilador Pulmonar', 'Dräger', 'Evita V500', 'EV500-2021-9934', 'Dräger', '2021-06-20', '2021', 45000.00, 'compra', 'Contrato manutenção anual activo', 'Ventilação', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 2, 4, 1, 'EQ-VEN-001'),
	(3, 'Bomba de Infusão', 'B. Braun', 'Infusomat Space', 'INF-2020-88321', 'B. Braun', '2020-09-10', '2020', 3200.00, 'compra', NULL, 'Terapia', 'operacional', 'Media', NULL, NULL, 'Completa', 4, 12, 1, 'EQ-BOM-001'),
	(4, 'Desfibrilhador Manual', 'Zoll', 'R Series', 'ZR-2021-7712', 'Zoll', '2021-01-05', '2021', 18000.00, 'compra', 'Verificação mensal obrigatória', 'Suporte de vida', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 11, 1, 1, 'EQ-DES-001'),
	(5, 'Ecógrafo Portátil', 'GE Healthcare', 'Vscan Air', 'VS-2023-1122', 'GE Healthcare', '2023-02-28', '2023', 8500.00, 'compra', NULL, 'Diagnóstico', 'operacional', 'Alta', NULL, NULL, 'Completa', 3, 18, 1, 'EQ-ECO-001'),
	(6, 'Eletrocardiógrafo 12 Derivações', 'Schiller', 'AT-102', 'AT102-2019-5544', 'Schiller', '2019-11-12', '2019', 4200.00, 'compra', 'Em calibração', 'Diagnóstico', 'manutencao', 'Media', NULL, NULL, 'Completa', 17, 15, 1, 'EQ-ELC-001'),
	(7, 'Autoclave Industrial', 'Getinge', 'GSS67H', 'GSS-2020-3301', 'Getinge', '2020-05-18', '2020', 22000.00, 'compra', NULL, 'Esterilização', 'operacional', 'Media', NULL, NULL, 'Completa', 18, 23, 1, 'EQ-AUT-001'),
	(8, 'Oxímetro de Pulso Portátil', 'Nonin', '9590', 'NIN-2022-7788', 'Nonin', '2022-08-01', '2022', 850.00, 'compra', NULL, 'Monitorização', 'operacional', 'Alta', NULL, NULL, 'Completa', 15, 1, 1, 'EQ-OXI-001'),
	(9, 'Cama Hospitalar Eléctrica', 'Hill-Rom', 'VersaCare', 'HR-2021-4455', 'Hill-Rom', '2021-03-22', '2021', 5500.00, 'compra', NULL, 'Outro', 'operacional', 'Baixa', NULL, NULL, 'Completa', 22, 33, 1, 'EQ-CAM-001'),
	(10, 'Máquina de Raio-X Portátil', 'GE Healthcare', 'Optima XR220amx', 'GE-2022-9966', 'GE Healthcare', '2022-07-14', '2022', 35000.00, 'compra', 'Aguarda peça de substituição', 'Imagiologia', 'avariado', 'Alta', NULL, NULL, 'Completa', 3, 24, 1, 'EQ-RAI-001'),
	(11, 'Monitor Sinais Vitais Neonatal', 'Philips', 'IntelliVue MX40', 'MX40-2023-3344', 'Philips', '2023-04-10', '2023', 9800.00, 'compra', NULL, 'Monitorização', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 1, 14, 1, 'EQ-MON-002'),
	(12, 'Bomba de Seringa', 'B. Braun', 'Perfusor Space', 'PERF-2021-6677', 'B. Braun', '2021-09-30', '2021', 2800.00, 'compra', NULL, 'Terapia', 'operacional', 'Media', NULL, NULL, 'Completa', 4, 4, 1, 'EQ-BOM-002'),
	(13, 'Ventilador de Transporte', 'Dräger', 'Oxylog 3000', 'OX3-2020-1122', 'Dräger', '2020-02-10', '2020', 28000.00, 'compra', NULL, 'Ventilação', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 2, 4, 1, 'EQ-VEN-002'),
	(14, 'Ventilador Neonatal', 'Dräger', 'Babylog VN500', 'BVN-2021-3344', 'Dräger', '2021-05-15', '2021', 52000.00, 'compra', NULL, 'Ventilação', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 2, 14, 1, 'EQ-VEN-003'),
	(15, 'Monitor de Pressão Invasiva', 'Philips', 'IntelliVue MP20', 'MP20-2022-5566', 'Philips', '2022-01-20', '2022', 8900.00, 'compra', NULL, 'Monitorização', 'operacional', 'Alta', NULL, NULL, 'Completa', 1, 4, 1, 'EQ-MON-003'),
	(16, 'Monitor de Glicemia', 'Roche', 'Accu-Chek Inform II', 'ACI-2021-7788', 'Roche', '2021-03-05', '2021', 1200.00, 'compra', NULL, 'Monitorização', 'operacional', 'Media', NULL, NULL, 'Completa', 16, 21, 1, 'EQ-MON-004'),
	(17, 'Monitor de Débito Cardíaco', 'Edwards', 'EV1000', 'EV1-2022-9900', 'Edwards', '2022-06-18', '2022', 22000.00, 'compra', 'Em revisão anual', 'Monitorização', 'manutencao', 'Alta', NULL, NULL, 'Completa', 42, 4, 1, 'EQ-MON-005'),
	(18, 'Desfibrilhador Automático Externo', 'Philips', 'HeartStart FRx', 'HSF-2023-1111', 'Philips', '2023-01-10', '2023', 2500.00, 'compra', NULL, 'Suporte de vida', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 1, 1, 1, 'EQ-DES-002'),
	(19, 'Desfibrilhador Manual Avançado', 'Zoll', 'X Series', 'ZX-2020-2222', 'Zoll', '2020-08-22', '2020', 21000.00, 'compra', NULL, 'Suporte de vida', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 11, 16, 1, 'EQ-DES-003'),
	(20, 'Bomba de Infusão Volumétrica', 'B. Braun', 'Outlook 400ES', 'OL4-2019-3333', 'B. Braun', '2019-04-14', '2019', 2900.00, 'compra', NULL, 'Terapia', 'operacional', 'Media', NULL, NULL, 'Completa', 4, 42, 1, 'EQ-BOM-003'),
	(21, 'Bomba de Nutrição Entérica', 'Fresenius', 'Applix', 'APX-2021-4444', 'Fresenius', '2021-07-30', '2021', 1800.00, 'compra', NULL, 'Terapia', 'operacional', 'Media', NULL, NULL, 'Completa', 7, 4, 1, 'EQ-BOM-004'),
	(22, 'Bomba de PCA', 'B. Braun', 'Perfusor fm', 'PFM-2020-5555', 'B. Braun', '2020-11-05', '2020', 3100.00, 'compra', 'Aguarda substituição', 'Terapia', 'inativo', 'Media', NULL, NULL, 'Completa', 4, 18, 0, 'EQ-BOM-005'),
	(23, 'Ecógrafo Cardiovascular', 'GE Healthcare', 'Vivid E95', 'VE95-2022-6666', 'GE Healthcare', '2022-09-12', '2022', 85000.00, 'compra', NULL, 'Diagnóstico', 'operacional', 'Alta', NULL, NULL, 'Completa', 3, 15, 1, 'EQ-ECO-002'),
	(24, 'Ecógrafo Obstétrico', 'Samsung', 'HS70A', 'HS70-2021-7777', 'Samsung', '2021-12-01', '2021', 32000.00, 'compra', NULL, 'Diagnóstico', 'operacional', 'Alta', NULL, NULL, 'Completa', 43, 31, 1, 'EQ-ECO-003'),
	(25, 'Holter 24 Horas', 'Schiller', 'MT-200', 'MT2-2021-9999', 'Schiller', '2021-08-15', '2021', 4500.00, 'compra', NULL, 'Diagnóstico', 'operacional', 'Media', NULL, NULL, 'Completa', 17, 15, 1, 'EQ-ELC-002'),
	(26, 'Arco Cirúrgico C', 'Siemens', 'Cios Alpha', 'CA-2022-1010', 'Siemens', '2022-03-08', '2022', 120000.00, 'compra', NULL, 'Imagiologia', 'operacional', 'Alta', NULL, NULL, 'Completa', 5, 7, 1, 'EQ-RAI-002'),
	(27, 'Densitómetro Ósseo', 'Hologic', 'Discovery A', 'DA-2020-2020', 'Hologic', '2020-06-25', '2020', 75000.00, 'compra', NULL, 'Imagiologia', 'manutencao', 'Media', NULL, NULL, 'Completa', 28, 24, 1, 'EQ-RAI-003'),
	(28, 'Analisador de Gasometria', 'Radiometer', 'ABL800', 'ABL-2021-3030', 'Radiometer', '2021-10-18', '2021', 28000.00, 'compra', NULL, 'Laboratório', 'operacional', 'Alta', NULL, NULL, 'Completa', 21, 21, 1, 'EQ-ANA-001'),
	(29, 'Analisador Bioquímico', 'Roche', 'Cobas c111', 'CC1-2020-4040', 'Roche', '2020-02-28', '2020', 35000.00, 'compra', NULL, 'Laboratório', 'operacional', 'Media', NULL, NULL, 'Completa', 16, 21, 1, 'EQ-ANA-002'),
	(30, 'Centrífuga Laboratorial', 'Eppendorf', '5810R', 'EP5-2022-5050', 'Eppendorf', '2022-07-07', '2022', 6500.00, 'compra', NULL, 'Laboratório', 'operacional', 'Baixa', NULL, NULL, 'Completa', 25, 21, 1, 'EQ-ANA-003'),
	(31, 'Autoclave de Bancada', 'Tuttnauer', '2540E', 'TUT-2021-6060', 'Tuttnauer', '2021-04-12', '2021', 4800.00, 'compra', NULL, 'Esterilização', 'operacional', 'Media', NULL, NULL, 'Completa', 34, 23, 1, 'EQ-AUT-002'),
	(32, 'Cadeira de Rodas Eléctrica', 'Permobil', 'M3 Corpus', 'PM3-2021-8080', 'Permobil', '2021-06-01', '2021', 12000.00, 'compra', NULL, 'Reabilitação', 'operacional', 'Baixa', NULL, NULL, 'Completa', 31, 19, 1, 'EQ-REA-001'),
	(33, 'Equipamento de Fisioterapia', 'Enraf Nonius', 'Sonopuls 490', 'SN4-2022-1001', 'Enraf Nonius', '2022-02-14', '2022', 3200.00, 'compra', NULL, 'Reabilitação', 'manutencao', 'Baixa', NULL, NULL, 'Completa', 32, 20, 1, 'EQ-REA-002'),
	(34, 'Oxímetro de Mesa', 'Nonin', 'Model 7500', 'NM7-2021-2002', 'Nonin', '2021-11-20', '2021', 1500.00, 'compra', NULL, 'Monitorização', 'operacional', 'Alta', NULL, NULL, 'Completa', 15, 15, 1, 'EQ-OXI-002'),
	(35, 'Cama Pediátrica', 'Stryker', 'Safe-T-Side', 'STS-2022-3003', 'Stryker', '2022-05-05', '2022', 6200.00, 'compra', NULL, 'Outro', 'operacional', 'Baixa', NULL, NULL, 'Completa', 13, 12, 1, 'EQ-CAM-002'),
	(36, 'Mesa de Bloco Operatório', 'Maquet', 'Alphastar', 'ALS-2021-4004', 'Maquet', '2021-08-08', '2021', 28000.00, 'compra', NULL, 'Outro', 'operacional', 'Media', NULL, NULL, 'Completa', 14, 7, 1, 'EQ-CAM-003'),
	(37, 'Aspirador Cirúrgico', 'Medela', 'Dominant 50', 'MD5-2020-5005', 'Medela', '2020-03-20', '2020', 2100.00, 'compra', NULL, 'Terapia', 'operacional', 'Media', NULL, NULL, 'Completa', 37, 7, 1, 'EQ-ASP-001'),
	(38, 'Unidade Electrocirúrgica', 'Valleylab', 'Force FX-C', 'FFX-2022-7007', 'Valleylab', '2022-01-15', '2022', 15000.00, 'compra', NULL, 'Terapia', 'operacional', 'Alta', NULL, NULL, 'Completa', 26, 7, 1, 'EQ-TER-001'),
	(39, 'Laser Cirúrgico', 'Lumenis', 'VersaPulse', 'LVP-2021-8008', 'Lumenis', '2021-04-25', '2021', 65000.00, 'compra', 'Aguarda técnico', 'Terapia', 'avariado', 'Alta', NULL, NULL, 'Completa', 27, 39, 1, 'EQ-TER-002'),
	(40, 'Incubadora Neonatal', 'Dräger', 'Isolette 8000', 'IS8-2022-9009', 'Dräger', '2022-08-30', '2022', 18500.00, 'compra', NULL, 'Outro', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 2, 14, 1, 'EQ-INC-001'),
	(41, 'Incubadora de Transporte', 'Dräger', 'Air-Shields TI500', 'TI5-2021-1011', 'Dräger', '2021-10-10', '2021', 22000.00, 'compra', NULL, 'Outro', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 2, 14, 1, 'EQ-INC-002'),
	(42, 'Balança Pediátrica', 'SECA', '376', 'SEC-2022-3033', 'SECA', '2022-06-06', '2022', 650.00, 'compra', NULL, 'Diagnóstico', 'operacional', 'Baixa', NULL, NULL, 'Completa', 38, 12, 1, 'EQ-BAL-001'),
	(43, 'Tensiómetro Digital', 'Omron', 'M6 Comfort', 'OM6-2023-5055', 'Omron', '2023-03-03', '2023', 280.00, 'compra', NULL, 'Monitorização', 'operacional', 'Media', NULL, NULL, 'Completa', 39, 1, 1, 'EQ-TEN-001'),
	(44, 'Laringoscópio Vídeo', 'Karl Storz', 'C-MAC', 'CMA-2022-7077', 'Karl Storz', '2022-10-10', '2022', 9500.00, 'compra', NULL, 'Diagnóstico', 'operacional', 'Alta', NULL, NULL, 'Completa', 12, 7, 1, 'EQ-LAR-001'),
	(45, 'Endoscópio Digestivo', 'Olympus', 'GIF-H290', 'GIF-2021-8088', 'Olympus', '2021-02-14', '2021', 42000.00, 'compra', NULL, 'Diagnóstico', 'manutencao', 'Alta', NULL, NULL, 'Completa', 10, 28, 1, 'EQ-END-001'),
	(46, 'Mesa Cirúrgica', 'Maquet', 'Betastar', 'BET-2020-9099', 'Maquet', '2020-04-04', '2020', 35000.00, 'compra', NULL, 'Outro', 'operacional', 'Media', NULL, NULL, 'Completa', 14, 7, 1, 'EQ-CIR-001'),
	(47, 'Foco Cirúrgico LED', 'Trumpf', 'TruLight 5000', 'TL5-2022-1100', 'Trumpf', '2022-12-12', '2022', 18000.00, 'compra', NULL, 'Outro', 'operacional', 'Media', NULL, NULL, 'Completa', 23, 7, 1, 'EQ-LUZ-001'),
	(48, 'Hemodialisador', 'Fresenius', '5008S', 'FR5-2021-3300', 'Fresenius', '2021-11-11', '2021', 95000.00, 'compra', NULL, 'Terapia', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 7, 36, 1, 'EQ-HAR-001'),
	(49, 'Pacemaker Externo', 'Medtronic', '5392', 'MD5-2022-4400', 'Medtronic', '2022-02-02', '2022', 8500.00, 'compra', NULL, 'Suporte de vida', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 6, 4, 1, 'EQ-PAC-001'),
	(50, 'Capnógrafo', 'Nellcor', 'N-85', 'NC85-2021-5500', 'Nellcor', '2021-06-30', '2026', 4200.00, 'compra', NULL, 'Monitorização', 'operacional', 'Alta', NULL, NULL, 'Completa', 41, 4, 1, 'EQ-CAP-001'),
	(51, 'Microscópio Cirúrgico', 'Zeiss', 'OPMI Vario', 'ZOP-2021-9900', 'Zeiss', '2021-01-25', '2021', 180000.00, 'compra', 'Uso exclusivo neurocirurgia', 'Diagnóstico', 'operacional', 'Alta', NULL, NULL, 'Completa', 19, 7, 1, 'EQ-MIC-001'),
	(52, 'Ventilador teste', 'Dräger', 'Evita V500', 'SN-DF-7777', 'Dräger', NULL, NULL, 800000.00, 'compra', NULL, 'Imagiologia', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 46, 43, 1, NULL),
	(53, 'Pacemaker', 'Medtronic', '5392', 'SN-DF-7777', 'Medtronic', NULL, '2050', -99.00, 'compra', NULL, 'Suporte de vida', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 30, 9, 0, NULL),
	(54, 'Desfibrilhador Neonatal', 'Zoll', 'R Series', 'SN-DF-7777', 'Zoll', '2021-01-05', '2021', 18000.00, 'compra', NULL, 'Suporte de vida', 'operacional', 'Suporte de vida', NULL, NULL, 'Completa', 48, 12, 1, NULL),
	(55, 'Bomba Insulina', 'B. Braun', 'Infusomat Space', 'SN-DF-7777', 'B. Braun', NULL, '2025', 20000.00, 'compra', NULL, 'Terapia', 'operacional', 'Media', NULL, NULL, 'Completa', 28, 34, 1, NULL);

-- A despejar estrutura para tabela db1231343.Fornecedor
CREATE TABLE IF NOT EXISTS `Fornecedor` (
  `idFornecedor` int NOT NULL AUTO_INCREMENT,
  `nomeFornecedor` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `nif` varchar(20) COLLATE utf8mb4_bin DEFAULT NULL,
  `contactoTelefonico` varchar(20) COLLATE utf8mb4_bin DEFAULT NULL,
  `enderecoEmail` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `morada` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `pessoaContacto` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `tipoFornecedor` enum('fabricante','distribuidor','assistencia_tecnica','consumiveis') COLLATE utf8mb4_bin NOT NULL DEFAULT 'distribuidor',
  `observacoes` text COLLATE utf8mb4_bin,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idFornecedor`),
  UNIQUE KEY `UQ_Fornecedor_email` (`enderecoEmail`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- A despejar dados para tabela db1231343.Fornecedor: ~50 rows (aproximadamente)
INSERT INTO `Fornecedor` (`idFornecedor`, `nomeFornecedor`, `nif`, `contactoTelefonico`, `enderecoEmail`, `morada`, `website`, `pessoaContacto`, `tipoFornecedor`, `observacoes`, `ativo`) VALUES
	(1, 'Philips Healthcare Portugal', '500123456', '+351 21 413 5000', 'healthcare@philips.pt', 'Rua da Quinta do Pinheiro, 5 — Oeiras', 'www.philips.pt/healthcare', 'João Ferreira', 'fabricante', 'Representante oficial em Portugal', 1),
	(2, 'Dräger Portugal Lda', '500234567', '+351 22 608 0300', 'info@draeger.pt', 'Av. da Boavista, 1583 — Porto', 'www.draeger.com/pt', 'Ana Costa', 'fabricante', NULL, 1),
	(3, 'GE Healthcare Portugal', '500345678', '+351 21 423 0000', 'gehealthcare@ge.pt', 'Rua Cidade de Córdova, 2 — Amadora', 'www.gehealthcare.com', 'Carlos Mendes', 'fabricante', NULL, 1),
	(4, 'B. Braun Portugal', '500456789', '+351 21 318 9300', 'info@bbraun.pt', 'Rua Consiglieri Pedroso, 80 — Queluz', 'www.bbraun.pt', 'Maria Santos', 'fabricante', NULL, 1),
	(5, 'Siemens Healthineers Portugal', '500567890', '+351 21 722 5000', 'healthineers@siemens.pt', 'Rua Irmãos Siemens, 1 — Amadora', 'www.siemens-healthineers.pt', 'Pedro Oliveira', 'fabricante', NULL, 1),
	(6, 'Medtronic Portugal', '500678901', '+351 21 318 5200', 'info@medtronic.pt', 'Rua Dr. António Loureiro Borges, 2 — Lisboa', 'www.medtronic.pt', 'Sofia Rodrigues', 'fabricante', NULL, 1),
	(7, 'Fresenius Medical Care Portugal', '500789012', '+351 21 424 5500', 'geral@fresenius.pt', 'Estrada Nacional 249-3, Queluz', 'www.freseniusmedicalcare.pt', 'Rui Fonseca', 'fabricante', NULL, 1),
	(8, 'TechMed Assistência Técnica', '500890123', '+351 22 933 4400', 'suporte@techmed.pt', 'Rua das Clínicas, 10 — Braga', 'www.techmed.pt', 'André Carvalho', 'assistencia_tecnica', 'Cobertura nacional', 1),
	(9, 'MedDistrib Portugal', '500901234', '+351 21 544 3300', 'comercial@meddistrib.pt', 'Parque Industrial do Seixal, Lote 12', 'www.meddistrib.pt', 'Catarina Lopes', 'distribuidor', NULL, 1),
	(10, 'Olympus Portugal', '501012345', '+351 21 315 6400', 'info@olympus.pt', 'Av. D. João II, 35 — Lisboa', 'www.olympus-europa.com/pt', 'Tiago Marques', 'fabricante', NULL, 1),
	(11, 'Zoll Medical Portugal', '501123456', '+351 21 145 0000', 'geral@zoll.pt', 'Rua da Saúde, 45 — Lisboa', 'www.zoll.com', 'Inês Barbosa', 'fabricante', NULL, 1),
	(12, 'Karl Storz Portugal', '501234567', '+351 21 382 6700', 'info@karlstorz.pt', 'Rua Visconde Seabra, 3 — Lisboa', 'www.karlstorz.com', 'Bruno Ferreira', 'fabricante', NULL, 1),
	(13, 'Stryker Portugal', '501345678', '+351 21 413 9800', 'geral@stryker.pt', 'Rua dos Escritórios, 10 — Carnaxide', 'www.stryker.pt', 'Margarida Costa', 'fabricante', NULL, 1),
	(14, 'Maquet Portugal', '501456789', '+351 22 507 8800', 'info@maquet.pt', 'Av. da República, 25 — Porto', 'www.maquet.com', 'Filipe Sousa', 'fabricante', NULL, 1),
	(15, 'Nonin Medical Europe', '501567890', '+351 21 000 1111', 'europe@nonin.com', 'Rua das Flores, 15 — Lisboa', 'www.nonin.com', 'Leonor Pinto', 'fabricante', NULL, 1),
	(16, 'Roche Diagnósticos Portugal', '501678901', '+351 21 425 7000', 'diagnostics@roche.pt', 'Estrada Nacional 249, Amadora', 'www.roche.pt', 'Diogo Santos', 'fabricante', NULL, 1),
	(17, 'Schiller Portugal', '501789012', '+351 21 000 2222', 'info@schiller.pt', 'Rua do Comércio, 10 — Lisboa', 'www.schiller.ch', 'Vera Almeida', 'distribuidor', NULL, 1),
	(18, 'Getinge Portugal', '501890123', '+351 21 000 3333', 'geral@getinge.pt', 'Av. António Augusto de Aguiar, 45 — Lisboa', 'www.getinge.com/pt', 'Nuno Teixeira', 'fabricante', NULL, 1),
	(19, 'Zeiss Portugal', '501901234', '+351 21 000 4444', 'info@zeiss.pt', 'Rua Castilho, 75 — Lisboa', 'www.zeiss.pt', 'Paula Monteiro', 'fabricante', NULL, 1),
	(20, 'MedSupply Consumíveis', '502012345', '+351 22 000 5555', 'geral@medsupply.pt', 'Zona Industrial de Gaia, Lote 5', 'www.medsupply.pt', 'Ricardo Oliveira', 'consumiveis', NULL, 1),
	(21, 'Radiometer Portugal', '502123456', '+351 21 000 6666', 'info@radiometer.pt', 'Av. da Liberdade, 195 — Lisboa', 'www.radiometer.com', 'Susana Ferreira', 'fabricante', NULL, 1),
	(22, 'Hill-Rom Portugal', '502234567', '+351 21 000 7777', 'geral@hillrom.pt', 'Rua da Saúde, 100 — Lisboa', 'www.hillrom.com', 'Marco Silva', 'fabricante', NULL, 1),
	(23, 'Trumpf Medical Portugal', '502345678', '+351 21 000 8888', 'info@trumpf-medical.pt', 'Av. das Nações, 55 — Lisboa', 'www.trumpf-medical.com', 'Helena Costa', 'fabricante', NULL, 1),
	(24, 'DeVilbiss Healthcare', '502456789', '+351 21 000 9999', 'info@devilbiss.pt', 'Rua do Ouro, 30 — Lisboa', 'www.devilbisshealthcare.com', 'António Pereira', 'fabricante', NULL, 1),
	(25, 'Eppendorf Portugal', '502567890', '+351 21 001 0000', 'info@eppendorf.pt', 'Rua da Ciência, 15 — Oeiras', 'www.eppendorf.com/pt', 'Cristina Lopes', 'fabricante', NULL, 1),
	(26, 'Valleylab Portugal', '502678901', '+351 21 001 1111', 'geral@valleylab.pt', 'Rua da Tecnologia, 8 — Lisboa', 'www.covidien.com', 'José Rodrigues', 'fabricante', NULL, 1),
	(27, 'Lumenis Portugal', '502789012', '+351 21 001 2222', 'info@lumenis.pt', 'Av. Fontes Pereira de Melo, 20 — Lisboa', 'www.lumenis.com', 'Ana Matos', 'fabricante', NULL, 1),
	(28, 'Hologic Portugal', '502890123', '+351 21 001 3333', 'geral@hologic.pt', 'Rua Castelo Branco Saraiva, 5 — Lisboa', 'www.hologic.com', 'Cláudia Santos', 'fabricante', NULL, 1),
	(29, 'MedCal Calibração e Manutenção', '502901234', '+351 22 001 4444', 'geral@medcal.pt', 'Rua da Manutenção, 3 — Porto', 'www.medcal.pt', 'Eduardo Faria', 'assistencia_tecnica', NULL, 1),
	(30, 'BioMed Soluções', '503012345', '+351 21 001 5555', 'info@biomed.pt', 'Parque Tecnológico de Lisboa, Lote 3', 'www.biomed.pt', 'Patrícia Neves', 'distribuidor', NULL, 1),
	(31, 'Permobil Portugal', '503123456', '+351 21 001 6666', 'geral@permobil.pt', 'Av. dos Combatentes, 15 — Lisboa', 'www.permobil.com/pt', 'Gonçalo Mendes', 'fabricante', NULL, 1),
	(32, 'Enraf Nonius Portugal', '503234567', '+351 22 001 7777', 'info@enrafnonius.pt', 'Rua de Santa Catarina, 20 — Porto', 'www.enrafnonius.com', 'Teresa Alves', 'fabricante', NULL, 1),
	(33, 'Thermo Fisher Scientific Portugal', '503345678', '+351 21 001 8888', 'geral@thermofisher.pt', 'Rua Cidade de Córdova, 5 — Amadora', 'www.thermofisher.com/pt', 'Sérgio Carvalho', 'fabricante', NULL, 1),
	(34, 'Tuttnauer Portugal', '503456789', '+351 21 001 9999', 'info@tuttnauer.pt', 'Av. da República, 50 — Lisboa', 'www.tuttnauer.com', 'Joana Ribeiro', 'fabricante', NULL, 1),
	(35, 'Hawo Portugal', '503567890', '+351 22 002 0000', 'geral@hawo.pt', 'Rua Industrial, 12 — Matosinhos', 'www.hawo.com', 'Luís Ferreira', 'fabricante', NULL, 1),
	(36, 'Fresenius Kabi Portugal', '503678901', '+351 21 424 5600', 'info@fresenius-kabi.pt', 'Estrada Nacional 249-3 — Queluz', 'www.fresenius-kabi.com/pt', 'Daniela Costa', 'fabricante', NULL, 1),
	(37, 'Medela Portugal', '503789012', '+351 21 002 1111', 'geral@medela.pt', 'Rua das Amoreiras, 8 — Lisboa', 'www.medela.pt', 'Francisco Santos', 'fabricante', NULL, 1),
	(38, 'SECA Portugal', '503890123', '+351 21 002 2222', 'info@seca.pt', 'Av. da Índia, 20 — Lisboa', 'www.seca.com/pt', 'Isabel Marques', 'fabricante', NULL, 1),
	(39, 'Omron Healthcare Portugal', '503901234', '+351 21 002 3333', 'geral@omron.pt', 'Rua Dr. Mário Moutinho, 30 — Lisboa', 'www.omron-healthcare.pt', 'Manuel Rodrigues', 'fabricante', NULL, 1),
	(40, 'Riester Portugal', '504012345', '+351 21 002 4444', 'info@riester.pt', 'Rua das Palmeiras, 10 — Lisboa', 'www.riester.de', 'Filomena Sousa', 'fabricante', NULL, 1),
	(41, 'Nellcor Portugal', '504123456', '+351 21 002 5555', 'geral@nellcor.pt', 'Av. Marginal, 30 — Cascais', 'www.nellcor.com', 'Bernardo Lopes', 'fabricante', NULL, 1),
	(42, 'Edwards Lifesciences Portugal', '504234567', '+351 21 002 6666', 'info@edwards.pt', 'Rua de Entrecampos, 4 — Lisboa', 'www.edwards.com/pt', 'Marta Oliveira', 'fabricante', NULL, 1),
	(43, 'Samsung Medison Portugal', '504345678', '+351 21 002 7777', 'geral@samsungmedison.pt', 'Av. Fontes Pereira de Melo, 50 — Lisboa', 'www.samsungmedison.com', 'Álvaro Costa', 'fabricante', NULL, 1),
	(44, 'Dometic Medical Portugal', '504456789', '+351 21 002 8888', 'info@dometic-medical.pt', 'Rua do Progresso, 5 — Setúbal', 'www.dometic.com', 'Carla Fonseca', 'fabricante', NULL, 1),
	(45, 'MedTech Ibérica', '504567890', '+351 22 002 9999', 'comercial@medtechiberia.pt', 'Av. da Boavista, 3265 — Porto', 'www.medtechiberia.pt', 'Vasco Pereira', 'distribuidor', NULL, 1),
	(46, 'Hospital Supply Lda', '504678901', '+351 21 003 0000', 'geral@hospitalsupply.pt', 'Zona Industrial Alfragide, Lote 8', 'www.hospitalsupply.pt', 'Raquel Sousa', 'consumiveis', NULL, 1),
	(47, 'ProMed Assistência', '504789012', '+351 22 003 1111', 'suporte@promed.pt', 'Rua de Antero de Quental, 5 — Porto', 'www.promed.pt', 'Hélder Santos', 'assistencia_tecnica', NULL, 1),
	(48, 'Instrumentarium Portugal', '504890123', '+351 21 003 2222', 'info@instrumentarium.pt', 'Rua da Imprensa, 10 — Lisboa', 'www.instrumentarium.com', 'Beatriz Ferreira', 'fabricante', NULL, 1),
	(49, 'Mindray Portugal', '504901234', '+351 21 003 3333', 'geral@mindray.pt', 'Av. Columbano Bordalo Pinheiro, 75 — Lisboa', 'www.mindray.com/pt', 'Roberto Martins', 'fabricante', NULL, 1),
	(50, 'Spacelabs Healthcare Portugal', '505012345', '+351 21 003 4444', 'info@spacelabs.pt', 'Rua do Século, 79 — Lisboa', 'www.spacelabs.com', 'Mónica Alves', 'fabricante', NULL, 1),
	(101, 'Philips', '3456799', '+351 21 413 5000', 'philips@info.com', NULL, 'www.philips.pt', 'João Paulo', 'fabricante', NULL, 1);

-- A despejar estrutura para tabela db1231343.Garantia
CREATE TABLE IF NOT EXISTS `Garantia` (
  `idGarantia` int NOT NULL AUTO_INCREMENT,
  `dataInicio` date NOT NULL,
  `dataFim` date NOT NULL,
  `temContrato` tinyint(1) NOT NULL DEFAULT '0',
  `tipoContrato` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `entidadeResponsavel` varchar(150) COLLATE utf8mb4_bin DEFAULT NULL,
  `periodicidade` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_bin,
  `idEquipamento` int NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idGarantia`),
  KEY `FK_Garantia_Equipamento` (`idEquipamento`),
  CONSTRAINT `FK_Garantia_Equipamento` FOREIGN KEY (`idEquipamento`) REFERENCES `Equipamento` (`idEquipamento`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- A despejar dados para tabela db1231343.Garantia: ~50 rows (aproximadamente)
INSERT INTO `Garantia` (`idGarantia`, `dataInicio`, `dataFim`, `temContrato`, `tipoContrato`, `entidadeResponsavel`, `periodicidade`, `observacoes`, `idEquipamento`, `ativo`) VALUES
	(1, '2022-03-15', '2025-03-15', 1, 'Manutenção Preventiva', 'Philips Healthcare Portugal', 'Semestral', 'Inclui peças e mão de obra', 1, 1),
	(2, '2021-06-20', '2024-06-20', 1, 'Full Service', 'Dräger Portugal Lda', 'Anual', 'Contrato expirado — renovação pendente', 2, 1),
	(3, '2020-09-10', '2023-09-10', 0, NULL, 'B. Braun Portugal', NULL, 'Garantia expirada', 3, 1),
	(4, '2021-01-05', '2026-01-05', 1, 'Manutenção Preventiva', 'TechMed Assistência Técnica', 'Trimestral', NULL, 4, 1),
	(5, '2023-02-28', '2026-02-28', 1, 'Garantia Alargada', 'GE Healthcare Portugal', 'Anual', NULL, 5, 1),
	(6, '2019-11-12', '2022-11-12', 0, NULL, 'Schiller Portugal', NULL, 'Garantia expirada', 6, 1),
	(7, '2020-05-18', '2025-05-18', 1, 'Manutenção Preventiva', 'Getinge Portugal', 'Semestral', NULL, 7, 1),
	(8, '2022-08-01', '2025-08-01', 0, NULL, 'Nonin Medical Europe', NULL, NULL, 8, 1),
	(9, '2021-03-22', '2024-03-22', 0, NULL, 'Hill-Rom Portugal', NULL, 'Garantia expirada', 9, 1),
	(10, '2022-07-14', '2025-07-14', 1, 'Full Service', 'TechMed Assistência Técnica', 'Anual', NULL, 10, 1),
	(11, '2023-04-10', '2026-04-10', 1, 'Garantia Alargada', 'Philips Healthcare Portugal', 'Anual', NULL, 11, 1),
	(12, '2021-09-30', '2024-09-30', 1, 'Manutenção Preventiva', 'B. Braun Portugal', 'Semestral', NULL, 12, 1),
	(13, '2020-02-10', '2025-02-10', 1, 'Full Service', 'Dräger Portugal Lda', 'Anual', NULL, 13, 1),
	(14, '2021-05-15', '2026-05-15', 1, 'Full Service', 'Dräger Portugal Lda', 'Semestral', NULL, 14, 1),
	(15, '2022-01-20', '2025-01-20', 1, 'Manutenção Preventiva', 'Philips Healthcare Portugal', 'Anual', NULL, 15, 1),
	(16, '2021-03-05', '2024-03-05', 0, NULL, 'Roche Diagnósticos Portugal', NULL, 'Garantia expirada', 16, 1),
	(17, '2022-06-18', '2025-06-18', 1, 'Manutenção Preventiva', 'TechMed Assistência Técnica', 'Anual', 'Expira em breve', 17, 1),
	(18, '2023-01-10', '2026-01-10', 0, NULL, 'Philips Healthcare Portugal', NULL, NULL, 18, 1),
	(19, '2020-08-22', '2025-08-22', 1, 'Full Service', 'Zoll Medical Portugal', 'Anual', NULL, 19, 1),
	(20, '2019-04-14', '2022-04-14', 1, 'Full Service', 'B. Braun Portugal', NULL, 'Garantia expirada', 20, 1),
	(21, '2021-07-30', '2024-07-30', 1, 'Manutenção Preventiva', 'Fresenius Kabi Portugal', 'Semestral', NULL, 21, 1),
	(22, '2020-11-05', '2023-11-05', 0, NULL, 'B. Braun Portugal', NULL, 'Garantia expirada', 22, 1),
	(23, '2022-09-12', '2025-09-12', 1, 'Full Service', 'TechMed Assistência Técnica', 'Anual', NULL, 23, 1),
	(24, '2021-12-01', '2024-12-01', 1, 'Garantia Alargada', 'Samsung Medison Portugal', 'Anual', NULL, 24, 1),
	(25, '2021-08-15', '2024-08-15', 0, NULL, 'Schiller Portugal', NULL, 'Garantia expirada', 25, 1),
	(26, '2022-03-08', '2025-03-08', 1, 'Full Service', 'Siemens Healthineers Portugal', 'Semestral', NULL, 26, 1),
	(27, '2020-06-25', '2025-06-25', 1, 'Manutenção Preventiva', 'TechMed Assistência Técnica', 'Anual', NULL, 27, 1),
	(28, '2021-10-18', '2024-10-18', 1, 'Manutenção Preventiva', 'Radiometer Portugal', 'Semestral', NULL, 28, 1),
	(29, '2020-02-28', '2023-02-28', 0, NULL, 'Roche Diagnósticos Portugal', NULL, 'Garantia expirada', 29, 1),
	(30, '2022-07-07', '2025-07-07', 0, NULL, 'Eppendorf Portugal', NULL, NULL, 30, 1),
	(31, '2021-04-12', '2024-04-12', 1, 'Manutenção Preventiva', 'Tuttnauer Portugal', 'Anual', NULL, 31, 1),
	(32, '2021-06-01', '2024-06-01', 0, NULL, 'Permobil Portugal', NULL, 'Garantia expirada', 32, 1),
	(33, '2022-02-14', '2025-02-14', 1, 'Manutenção Preventiva', 'TechMed Assistência Técnica', 'Anual', NULL, 33, 1),
	(34, '2021-11-20', '2024-11-20', 0, NULL, 'Nonin Medical Europe', NULL, 'Garantia expirada', 34, 1),
	(35, '2022-05-05', '2025-05-05', 1, 'Garantia Alargada', 'Stryker Portugal', 'Anual', NULL, 35, 1),
	(36, '2021-08-08', '2026-08-08', 1, 'Manutenção Preventiva', 'Maquet Portugal', 'Semestral', NULL, 36, 1),
	(37, '2020-03-20', '2023-03-20', 0, NULL, 'Medela Portugal', NULL, 'Garantia expirada', 37, 1),
	(38, '2022-01-15', '2025-01-15', 1, 'Full Service', 'TechMed Assistência Técnica', 'Anual', NULL, 38, 1),
	(39, '2021-04-25', '2024-04-25', 1, 'Manutenção Preventiva', 'TechMed Assistência Técnica', 'Anual', NULL, 39, 1),
	(40, '2022-08-30', '2025-08-30', 1, 'Full Service', 'Dräger Portugal Lda', 'Semestral', NULL, 40, 1),
	(41, '2021-10-10', '2026-10-10', 1, 'Full Service', 'Dräger Portugal Lda', 'Semestral', NULL, 41, 1),
	(42, '2022-06-06', '2025-06-06', 0, NULL, 'SECA Portugal', NULL, NULL, 42, 1),
	(43, '2023-03-03', '2026-03-03', 0, NULL, 'Omron Healthcare Portugal', NULL, NULL, 43, 1),
	(44, '2022-10-10', '2025-10-10', 1, 'Manutenção Preventiva', 'Karl Storz Portugal', 'Semestral', NULL, 44, 1),
	(45, '2021-02-14', '2024-02-14', 1, 'Full Service', 'Olympus Portugal', 'Anual', NULL, 45, 1),
	(46, '2020-04-04', '2025-04-04', 1, 'Manutenção Preventiva', 'Maquet Portugal', 'Anual', NULL, 46, 1),
	(47, '2022-12-12', '2025-12-12', 1, 'Full Service', 'Trumpf Medical Portugal', 'Semestral', NULL, 47, 1),
	(48, '2021-11-11', '2026-11-11', 1, 'Full Service', 'Fresenius Medical Care Portugal', 'Semestral', NULL, 48, 1),
	(49, '2022-02-02', '2025-02-02', 1, 'Manutenção Preventiva', 'TechMed Assistência Técnica', 'Anual', NULL, 49, 1),
	(50, '2021-06-30', '2024-06-30', 1, 'Manutenção Preventiva', 'TechMed Assistência Técnica', 'Anual', NULL, 50, 1),
	(51, '2025-02-02', '2027-02-02', 1, 'Full Service', 'Philips Medical Systems', 'Trimestral', NULL, 29, 1);

-- A despejar estrutura para tabela db1231343.Localizacao
CREATE TABLE IF NOT EXISTS `Localizacao` (
  `idLocalizacao` int NOT NULL AUTO_INCREMENT,
  `nomeSala` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `edificio` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `servico` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `piso` varchar(10) COLLATE utf8mb4_bin NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idLocalizacao`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- A despejar dados para tabela db1231343.Localizacao: ~50 rows (aproximadamente)
INSERT INTO `Localizacao` (`idLocalizacao`, `nomeSala`, `edificio`, `servico`, `piso`, `ativo`) VALUES
	(1, 'Sala de Emergência 1', 'Edifício Principal', 'Urgências', '0', 1),
	(2, 'Sala de Emergência 2', 'Edifício Principal', 'Urgências', '0', 1),
	(3, 'Sala de Triagem', 'Edifício Principal', 'Urgências', '0', 1),
	(4, 'UCI — Cama 1', 'Edifício Principal', 'Unidade de Cuidados Intensivos', '1', 1),
	(5, 'UCI — Cama 2', 'Edifício Principal', 'Unidade de Cuidados Intensivos', '1', 1),
	(6, 'UCI — Cama 3', 'Edifício Principal', 'Unidade de Cuidados Intensivos', '1', 1),
	(7, 'Bloco Operatório 1', 'Edifício Cirurgia', 'Bloco Operatório', '2', 1),
	(8, 'Bloco Operatório 2', 'Edifício Cirurgia', 'Bloco Operatório', '2', 1),
	(9, 'Bloco Operatório 3', 'Edifício Cirurgia', 'Bloco Operatório', '2', 1),
	(10, 'Recobro A', 'Edifício Cirurgia', 'Recobro', '2', 1),
	(11, 'Recobro B', 'Edifício Cirurgia', 'Recobro', '2', 1),
	(12, 'Enfermaria Pediatria A', 'Edifício Pediátrico', 'Pediatria', '1', 1),
	(13, 'Enfermaria Pediatria B', 'Edifício Pediátrico', 'Pediatria', '1', 1),
	(14, 'UCIN — Neonatologia', 'Edifício Pediátrico', 'Neonatologia', '2', 1),
	(15, 'Consulta Cardiologia 1', 'Edifício Ambulatório', 'Cardiologia', '1', 1),
	(16, 'Consulta Cardiologia 2', 'Edifício Ambulatório', 'Cardiologia', '1', 1),
	(17, 'Sala de Hemodinâmica', 'Edifício Ambulatório', 'Cardiologia', '1', 1),
	(18, 'Consulta Ortopedia', 'Edifício Ambulatório', 'Ortopedia', '2', 1),
	(19, 'Ginásio de Reabilitação', 'Edifício Reabilitação', 'Medicina Física e Reabilitação', '0', 1),
	(20, 'Sala de Fisioterapia', 'Edifício Reabilitação', 'Medicina Física e Reabilitação', '0', 1),
	(21, 'Laboratório Central', 'Edifício Laboratórios', 'Patologia Clínica', '0', 1),
	(22, 'Laboratório Microbiologia', 'Edifício Laboratórios', 'Microbiologia', '0', 1),
	(23, 'Central de Esterilização', 'Edifício Serviços', 'Esterilização', '-1', 1),
	(24, 'Radiologia — Sala 1', 'Edifício Imagiologia', 'Radiologia', '0', 1),
	(25, 'Radiologia — Sala 2', 'Edifício Imagiologia', 'Radiologia', '0', 1),
	(26, 'TAC — Sala Principal', 'Edifício Imagiologia', 'Tomografia', '0', 1),
	(27, 'Ressonância Magnética', 'Edifício Imagiologia', 'Imagiologia', '0', 1),
	(28, 'Endoscopia — Sala 1', 'Edifício Ambulatório', 'Gastrenterologia', '1', 1),
	(29, 'Consulta Neurologia', 'Edifício Ambulatório', 'Neurologia', '3', 1),
	(30, 'Farmácia Hospitalar', 'Edifício Principal', 'Farmácia', '0', 1),
	(31, 'Maternidade — Sala Parto 1', 'Edifício Maternidade', 'Obstetrícia', '1', 1),
	(32, 'Maternidade — Sala Parto 2', 'Edifício Maternidade', 'Obstetrícia', '1', 1),
	(33, 'Enfermaria Medicina Interna A', 'Edifício Principal', 'Medicina Interna', '3', 1),
	(34, 'Enfermaria Medicina Interna B', 'Edifício Principal', 'Medicina Interna', '3', 1),
	(35, 'Consulta Pneumologia', 'Edifício Ambulatório', 'Pneumologia', '2', 1),
	(36, 'Hemodiálise — Posto 1', 'Edifício Ambulatório', 'Nefrologia', '0', 1),
	(37, 'Hemodiálise — Posto 2', 'Edifício Ambulatório', 'Nefrologia', '0', 1),
	(38, 'Dermatologia — Gabinete 1', 'Edifício Ambulatório', 'Dermatologia', '2', 1),
	(39, 'Oftalmologia — Sala Cirurgia', 'Edifício Cirurgia', 'Oftalmologia', '2', 1),
	(40, 'ORL — Gabinete Audiologia', 'Edifício Ambulatório', 'Otorrinolaringologia', '3', 1),
	(41, 'Psiquiatria — Enfermaria', 'Edifício Psiquiatria', 'Psiquiatria', '1', 1),
	(42, 'Oncologia — Sala Quimioterapia', 'Edifício Oncologia', 'Oncologia', '1', 1),
	(43, 'Bloco Obstétrico', 'Edifício Maternidade', 'Obstetrícia', '2', 1),
	(44, 'UCIP Pediátrica', 'Edifício Pediátrico', 'Cuidados Intensivos Pediátricos', '3', 1),
	(45, 'Gastrenterologia — Sala 1', 'Edifício Ambulatório', 'Gastrenterologia', '1', 1),
	(46, 'Urologia — Bloco', 'Edifício Cirurgia', 'Urologia', '2', 1),
	(47, 'Consulta Endocrinologia', 'Edifício Ambulatório', 'Endocrinologia', '2', 1),
	(48, 'Imagiologia Intervencionista', 'Edifício Imagiologia', 'Radiologia de Intervenção', '0', 1),
	(49, 'Banco de Sangue', 'Edifício Laboratórios', 'Imunohemoterapia', '0', 1),
	(50, 'Anatomia Patológica', 'Edifício Laboratórios', 'Anatomia Patológica', '-1', 1);

-- A despejar estrutura para tabela db1231343.Utilizador
CREATE TABLE IF NOT EXISTS `Utilizador` (
  `idUtilizador` int NOT NULL AUTO_INCREMENT,
  `nomeUtilizador` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_bin NOT NULL,
  `perfil` enum('administrador','tecnico') COLLATE utf8mb4_bin NOT NULL DEFAULT 'tecnico',
  PRIMARY KEY (`idUtilizador`),
  UNIQUE KEY `UQ_Utilizador_username` (`username`),
  UNIQUE KEY `UQ_Utilizador_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- A despejar dados para tabela db1231343.Utilizador: ~2 rows (aproximadamente)
INSERT INTO `Utilizador` (`idUtilizador`, `nomeUtilizador`, `username`, `password`, `email`, `perfil`) VALUES
	(1, 'Administrador MedCare', 'admin@medcare.pt', '$2y$10$Ev1lZny2tWx4x49cmldo2OM6MNcS6fotBTbX8Xa23KeLmpXZxQT8a', 'admin@medcare.pt', 'administrador'),
	(2, 'Técnico Biomédico', 'tecnico@medcare.pt', '$2y$10$Ev1lZny2tWx4x49cmldo2OM6MNcS6fotBTbX8Xa23KeLmpXZxQT8a', 'tecnico@medcare.pt', 'tecnico');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
