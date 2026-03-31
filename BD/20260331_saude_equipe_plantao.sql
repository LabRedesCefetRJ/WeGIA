USE `wegia`;

-- =====================================================
-- Gestão de Equipe de Plantão - módulo saúde
-- Data: 2026-03-31
-- =====================================================

CREATE TABLE IF NOT EXISTS `saude_equipe_plantao` (
  `id_equipe_plantao` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `descricao` VARCHAR(255) NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `id_usuario_criacao` INT(11) NOT NULL,
  `data_criacao` DATETIME NOT NULL,
  `id_usuario_atualizacao` INT(11) NULL,
  `data_atualizacao` DATETIME NULL,
  PRIMARY KEY (`id_equipe_plantao`),
  UNIQUE KEY `uq_saude_equipe_plantao_nome` (`nome`),
  KEY `idx_saude_equipe_plantao_ativo` (`ativo`),
  KEY `idx_saude_equipe_plantao_usuario_criacao` (`id_usuario_criacao`),
  CONSTRAINT `fk_saude_equipe_plantao_usuario_criacao`
    FOREIGN KEY (`id_usuario_criacao`) REFERENCES `pessoa` (`id_pessoa`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_equipe_plantao_usuario_atualizacao`
    FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `pessoa` (`id_pessoa`)
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `saude_equipe_membro` (
  `id_equipe_membro` INT NOT NULL AUTO_INCREMENT,
  `id_equipe_plantao` INT NOT NULL,
  `id_funcionario` INT(11) NOT NULL,
  `id_usuario_criacao` INT(11) NOT NULL,
  `data_criacao` DATETIME NOT NULL,
  PRIMARY KEY (`id_equipe_membro`),
  UNIQUE KEY `uq_saude_equipe_membro` (`id_equipe_plantao`, `id_funcionario`),
  KEY `idx_saude_equipe_membro_funcionario` (`id_funcionario`),
  CONSTRAINT `fk_saude_equipe_membro_equipe`
    FOREIGN KEY (`id_equipe_plantao`) REFERENCES `saude_equipe_plantao` (`id_equipe_plantao`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_equipe_membro_funcionario`
    FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_equipe_membro_usuario_criacao`
    FOREIGN KEY (`id_usuario_criacao`) REFERENCES `pessoa` (`id_pessoa`)
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `saude_escala_mensal` (
  `id_escala_mensal` INT NOT NULL AUTO_INCREMENT,
  `ano` SMALLINT NOT NULL,
  `mes` TINYINT NOT NULL,
  `observacao` VARCHAR(500) NULL,
  `bloqueada` TINYINT(1) NOT NULL DEFAULT 0,
  `id_usuario_criacao` INT(11) NOT NULL,
  `data_criacao` DATETIME NOT NULL,
  `id_usuario_atualizacao` INT(11) NULL,
  `data_atualizacao` DATETIME NULL,
  PRIMARY KEY (`id_escala_mensal`),
  UNIQUE KEY `uq_saude_escala_mensal_ano_mes` (`ano`, `mes`),
  KEY `idx_saude_escala_mensal_mes_ano` (`mes`, `ano`),
  CONSTRAINT `fk_saude_escala_mensal_usuario_criacao`
    FOREIGN KEY (`id_usuario_criacao`) REFERENCES `pessoa` (`id_pessoa`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_escala_mensal_usuario_atualizacao`
    FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `pessoa` (`id_pessoa`)
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `saude_escala_dia` (
  `id_escala_dia` INT NOT NULL AUTO_INCREMENT,
  `id_escala_mensal` INT NOT NULL,
  `dia` TINYINT NOT NULL,
  `turno` ENUM('DIA', 'NOITE') NOT NULL DEFAULT 'DIA',
  `id_equipe_plantao` INT NOT NULL,
  `observacao` VARCHAR(255) NULL,
  `id_usuario_atualizacao` INT(11) NULL,
  `data_atualizacao` DATETIME NULL,
  PRIMARY KEY (`id_escala_dia`),
  UNIQUE KEY `uq_saude_escala_dia` (`id_escala_mensal`, `dia`, `turno`),
  KEY `idx_saude_escala_dia_equipe` (`id_equipe_plantao`),
  KEY `idx_saude_escala_dia_turno` (`turno`),
  CONSTRAINT `fk_saude_escala_dia_escala_mensal`
    FOREIGN KEY (`id_escala_mensal`) REFERENCES `saude_escala_mensal` (`id_escala_mensal`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_escala_dia_equipe`
    FOREIGN KEY (`id_equipe_plantao`) REFERENCES `saude_equipe_plantao` (`id_equipe_plantao`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_escala_dia_usuario_atualizacao`
    FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `pessoa` (`id_pessoa`)
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `saude_plantao_membro_dia` (
  `id_plantao_membro_dia` INT NOT NULL AUTO_INCREMENT,
  `id_escala_dia` INT NOT NULL,
  `id_funcionario` INT(11) NOT NULL,
  `ajuste` ENUM('ADICIONAR', 'REMOVER') NOT NULL,
  `observacao` VARCHAR(255) NULL,
  `id_usuario_atualizacao` INT(11) NOT NULL,
  `data_atualizacao` DATETIME NOT NULL,
  PRIMARY KEY (`id_plantao_membro_dia`),
  UNIQUE KEY `uq_saude_plantao_membro_dia` (`id_escala_dia`, `id_funcionario`),
  KEY `idx_saude_plantao_membro_dia_funcionario` (`id_funcionario`),
  CONSTRAINT `fk_saude_plantao_membro_dia_escala_dia`
    FOREIGN KEY (`id_escala_dia`) REFERENCES `saude_escala_dia` (`id_escala_dia`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_plantao_membro_dia_funcionario`
    FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_plantao_membro_dia_usuario`
    FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `pessoa` (`id_pessoa`)
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `saude_log_equipe_plantao` (
  `id_log_equipe_plantao` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `data_hora` DATETIME NOT NULL,
  `acao` VARCHAR(80) NOT NULL,
  `descricao` VARCHAR(500) NOT NULL,
  `id_equipe_plantao` INT NULL,
  `id_funcionario` INT(11) NULL,
  `id_escala_mensal` INT NULL,
  `id_escala_dia` INT NULL,
  `dados_json` LONGTEXT NULL,
  PRIMARY KEY (`id_log_equipe_plantao`),
  KEY `idx_saude_log_equipe_plantao_data` (`data_hora`),
  KEY `idx_saude_log_equipe_plantao_acao` (`acao`),
  KEY `idx_saude_log_equipe_plantao_equipe` (`id_equipe_plantao`),
  KEY `idx_saude_log_equipe_plantao_funcionario` (`id_funcionario`),
  KEY `idx_saude_log_equipe_plantao_escala_dia` (`id_escala_dia`),
  CONSTRAINT `fk_saude_log_equipe_plantao_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `pessoa` (`id_pessoa`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_log_equipe_plantao_equipe`
    FOREIGN KEY (`id_equipe_plantao`) REFERENCES `saude_equipe_plantao` (`id_equipe_plantao`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_log_equipe_plantao_funcionario`
    FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_log_equipe_plantao_escala_mensal`
    FOREIGN KEY (`id_escala_mensal`) REFERENCES `saude_escala_mensal` (`id_escala_mensal`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_saude_log_equipe_plantao_escala_dia`
    FOREIGN KEY (`id_escala_dia`) REFERENCES `saude_escala_dia` (`id_escala_dia`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- Integração com intercorrências
-- =====================================================

SET @db_name := DATABASE();

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db_name
        AND TABLE_NAME = 'saude_escala_mensal'
        AND COLUMN_NAME = 'bloqueada'
    ),
    'SELECT 1',
    'ALTER TABLE `saude_escala_mensal` ADD COLUMN `bloqueada` TINYINT(1) NOT NULL DEFAULT 0 AFTER `observacao`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `saude_escala_mensal`
SET `bloqueada` = 0
WHERE `bloqueada` IS NULL;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db_name
        AND TABLE_NAME = 'aviso'
        AND COLUMN_NAME = 'id_saude_equipe_plantao'
    ),
    'SELECT 1',
    'ALTER TABLE `aviso` ADD COLUMN `id_saude_equipe_plantao` INT NULL AFTER `data`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db_name
        AND TABLE_NAME = 'aviso'
        AND COLUMN_NAME = 'id_saude_escala_dia'
    ),
    'SELECT 1',
    'ALTER TABLE `aviso` ADD COLUMN `id_saude_escala_dia` INT NULL AFTER `id_saude_equipe_plantao`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db_name
        AND TABLE_NAME = 'aviso'
        AND COLUMN_NAME = 'data_plantao'
    ),
    'SELECT 1',
    'ALTER TABLE `aviso` ADD COLUMN `data_plantao` DATE NULL AFTER `id_saude_escala_dia`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db_name
        AND TABLE_NAME = 'aviso'
        AND COLUMN_NAME = 'turno_plantao'
    ),
    'SELECT 1',
    'ALTER TABLE `aviso` ADD COLUMN `turno_plantao` ENUM(''DIA'', ''NOITE'') NULL AFTER `data_plantao`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = @db_name
        AND TABLE_NAME = 'aviso'
        AND INDEX_NAME = 'idx_aviso_saude_equipe'
    ),
    'SELECT 1',
    'ALTER TABLE `aviso` ADD INDEX `idx_aviso_saude_equipe` (`id_saude_equipe_plantao`)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = @db_name
        AND TABLE_NAME = 'aviso'
        AND INDEX_NAME = 'idx_aviso_turno_plantao'
    ),
    'SELECT 1',
    'ALTER TABLE `aviso` ADD INDEX `idx_aviso_turno_plantao` (`turno_plantao`)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = @db_name
        AND TABLE_NAME = 'aviso'
        AND INDEX_NAME = 'idx_aviso_saude_escala_dia'
    ),
    'SELECT 1',
    'ALTER TABLE `aviso` ADD INDEX `idx_aviso_saude_escala_dia` (`id_saude_escala_dia`)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = @db_name
        AND TABLE_NAME = 'aviso'
        AND INDEX_NAME = 'idx_aviso_data_plantao'
    ),
    'SELECT 1',
    'ALTER TABLE `aviso` ADD INDEX `idx_aviso_data_plantao` (`data_plantao`)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.REFERENTIAL_CONSTRAINTS
      WHERE CONSTRAINT_SCHEMA = @db_name
        AND CONSTRAINT_NAME = 'fk_aviso_saude_equipe_plantao'
        AND TABLE_NAME = 'aviso'
    ),
    'SELECT 1',
    'ALTER TABLE `aviso` ADD CONSTRAINT `fk_aviso_saude_equipe_plantao` FOREIGN KEY (`id_saude_equipe_plantao`) REFERENCES `saude_equipe_plantao` (`id_equipe_plantao`) ON DELETE SET NULL ON UPDATE CASCADE'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.REFERENTIAL_CONSTRAINTS
      WHERE CONSTRAINT_SCHEMA = @db_name
        AND CONSTRAINT_NAME = 'fk_aviso_saude_escala_dia'
        AND TABLE_NAME = 'aviso'
    ),
    'SELECT 1',
    'ALTER TABLE `aviso` ADD CONSTRAINT `fk_aviso_saude_escala_dia` FOREIGN KEY (`id_saude_escala_dia`) REFERENCES `saude_escala_dia` (`id_escala_dia`) ON DELETE SET NULL ON UPDATE CASCADE'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
