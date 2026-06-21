CREATE TABLE `Equipamento` (
  `idEquipamento` int PRIMARY KEY AUTO_INCREMENT,
  `nomeEquipamento` varchar(150) NOT NULL,
  `marca` varchar(100),
  `modelo` varchar(100),
  `numeroSerie` varchar(100),
  `fabricante` varchar(100),
  `dataAquisicao` date,
  `anoFabrico` year,
  `custoAquisicao` decimal(10,2),
  `tipoEntrada` varchar(20) NOT NULL DEFAULT 'compra' COMMENT 'compra, doacao, aluguer, emprestimo',
  `categoria` varchar(50),
  `estado` varchar(20) NOT NULL COMMENT 'operacional, manutencao, avariado, inativo',
  `criticidadeClinica` varchar(20) COMMENT 'Baixa, Media, Alta, Suporte de vida',
  `ultimaManutencao` date,
  `proximaManutencao` date,
  `documentacaoStatus` varchar(20),
  `observacoes` text,
  `idFornecedor` int,
  `idLocalizacao` int
);

CREATE TABLE `Fornecedor` (
  `idFornecedor` int PRIMARY KEY AUTO_INCREMENT,
  `nomeFornecedor` varchar(150) NOT NULL,
  `nif` varchar(20),
  `contactoTelefonico` varchar(20),
  `enderecoEmail` varchar(150),
  `morada` varchar(255),
  `website` varchar(255),
  `pessoaContacto` varchar(100),
  `tipoFornecedor` varchar(30) NOT NULL COMMENT 'fabricante, distribuidor, assistencia_tecnica, consumiveis',
  `observacoes` text
);

CREATE TABLE `Localizacao` (
  `idLocalizacao` int PRIMARY KEY AUTO_INCREMENT,
  `nomeSala` varchar(100) NOT NULL,
  `edificio` varchar(100),
  `servico` varchar(100),
  `piso` varchar(20),
  `ativo` tinyint DEFAULT 1
);

CREATE TABLE `Documentacao` (
  `idDocumento` int PRIMARY KEY AUTO_INCREMENT,
  `nomeDocumento` varchar(150) NOT NULL,
  `tipoDocumento` varchar(40) NOT NULL COMMENT 'manual_utilizador, manual_servico, certificado_calibracao, contrato_manutencao, fatura, declaracao_conformidade, relatorio_tecnico',
  `dataDocumento` date,
  `dataValidade` date,
  `nomeFicheiro` varchar(255),
  `idEquipamento` int NOT NULL,
  `idFornecedor` int
);

CREATE TABLE `Garantia` (
  `idGarantia` int PRIMARY KEY AUTO_INCREMENT,
  `dataInicio` date NOT NULL,
  `dataFim` date NOT NULL,
  `temContrato` boolean NOT NULL DEFAULT false,
  `tipoContrato` varchar(100),
  `entidadeResponsavel` varchar(150),
  `periodicidade` varchar(100),
  `observacoes` text,
  `idEquipamento` int NOT NULL,
  `ativo` tinyint DEFAULT 1
);

CREATE TABLE `Utilizador` (
  `idUtilizador` int PRIMARY KEY AUTO_INCREMENT,
  `nomeUtilizador` varchar(100) NOT NULL,
  `username` varchar(50) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `perfil` varchar(20) NOT NULL DEFAULT 'tecnico' COMMENT 'administrador, tecnico'
);

ALTER TABLE `Equipamento` ADD FOREIGN KEY (`idFornecedor`) REFERENCES `Fornecedor` (`idFornecedor`);

ALTER TABLE `Equipamento` ADD FOREIGN KEY (`idLocalizacao`) REFERENCES `Localizacao` (`idLocalizacao`);

ALTER TABLE `Documentacao` ADD FOREIGN KEY (`idEquipamento`) REFERENCES `Equipamento` (`idEquipamento`);

ALTER TABLE `Documentacao` ADD FOREIGN KEY (`idFornecedor`) REFERENCES `Fornecedor` (`idFornecedor`);

ALTER TABLE `Garantia` ADD FOREIGN KEY (`idEquipamento`) REFERENCES `Equipamento` (`idEquipamento`);
