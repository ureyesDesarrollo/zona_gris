-- ==========================================
-- TABLA PRINCIPAL DE CLARIFICADORES
-- ==========================================
CREATE TABLE `clarificadores` (
    `clarificador_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del clarificador',
    `nombre` varchar(100) NOT NULL COMMENT 'Nombre del clarificador',
    `estatus` enum('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO' COMMENT 'Estado del clarificador',
    `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
    PRIMARY KEY (`clarificador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Catálogo de clarificadores disponibles';

-- ==========================================
-- RELACIÓN ENTRE PROCESOS AGRUPADOS Y CLARIFICADORES
-- ==========================================
CREATE TABLE `procesos_clarificador_relacion` (
    `relacion_id` int NOT NULL AUTO_INCREMENT,
    `clarificador_id` int NOT NULL,
    `proceso_agrupado_id` int NOT NULL,
    `fecha_inicio` datetime DEFAULT NULL,
    `fecha_fin` datetime DEFAULT NULL,
    PRIMARY KEY (`relacion_id`),
    KEY `idx_proc_clari_relac_clarificador_id` (`clarificador_id`),
    KEY `idx_proc_clari_relac_proc_agrupado_id` (`proceso_agrupado_id`),
    CONSTRAINT `fk_proc_clari_relac_clarificador` FOREIGN KEY (`clarificador_id`) REFERENCES `clarificadores` (`clarificador_id`),
    CONSTRAINT `fk_proc_clari_relac_proc_agrupado` FOREIGN KEY (`proceso_agrupado_id`) REFERENCES `zn_procesos_agrupados` (`proceso_agrupado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Relación procesos agrupados ↔ clarificadores';

-- ==========================================
-- DETALLE DE PARÁMETROS CADA MEDIA HORA
-- ==========================================
CREATE TABLE `procesos_clarificador_detalle` (
    `detalle_id` int NOT NULL AUTO_INCREMENT,
    `relacion_id` int NOT NULL,
    `usuario_id` int NOT NULL,
    `responsable_tipo` enum('OPERADOR', 'CONTROL DE PROCESOS') NOT NULL,
    `param_solidos_entrada` decimal(10,2) NOT NULL,
    `param_flujo_salida` decimal(10,2) NOT NULL,
    `param_ntu_entrada` decimal(10,2) NOT NULL,
    `param_ntu_salida` decimal(10,2) NOT NULL,
    `param_ph_entrada` decimal(10,2) NOT NULL,
    `param_ph_electrodo` decimal(10,2) NOT NULL,
    `param_ph_control` decimal(10,2) NOT NULL,
    `param_dosificacion_polimero` decimal(10,2) NOT NULL,
    `tanque` int DEFAULT NULL,
    `tanque_hora_inicio` DATETIME DEFAULT NULL,
    `tanque_hora_fin` DATETIME DEFAULT NULL,
    `param_presion` decimal(10,2) NOT NULL,
    `param_entrada_aire` decimal(10,2) NOT NULL,
    `param_varometro` decimal(10,2) NOT NULL,
    `param_nivel_nata` decimal(10,2) NOT NULL,
    `param_filtro_1` decimal(10,2) NOT NULL,
    `param_filtro_2` decimal(10,2) NOT NULL,
    `param_filtro_3` decimal(10,2) NOT NULL,
    `param_filtro_4` decimal(10,2) NOT NULL,
    `param_filtro_5` decimal(10,2) NOT NULL,
    `cambio_filtro` int NOT NULL DEFAULT 0,
    `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `supervisor_validado` INT NOT NULL,
    `supervisor_id` INT NULL,
    `control_procesos_validado` INT NULL,
    `control_procesos_id` INT NULL,
    PRIMARY KEY (`detalle_id`),
    KEY `idx_proc_clari_detalle_relacion_id` (`relacion_id`),
    KEY `idx_proc_clari_detalle_usuario_id` (`usuario_id`),
    CONSTRAINT `fk_proc_clari_detalle_relacion` FOREIGN KEY (`relacion_id`) REFERENCES `procesos_clarificador_relacion` (`relacion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Parámetros registrados cada media hora en clarificadores';


-- ==========================================
-- APLICACIÓN DE QUÍMICOS (INDEPENDIENTE DE LA RELACIÓN ACTIVA)
-- ==========================================
CREATE TABLE `aplicacion_quimicos_clarificador` (
    `aplicacion_id` int NOT NULL AUTO_INCREMENT,
    `clarificador_id` int NOT NULL,
    `quimico_lote` varchar(100) NOT NULL COMMENT 'Lote del quimico aplicado',
    `cantidad` decimal(10,2) NOT NULL,
    `unidad_medida` varchar(20) NOT NULL COMMENT 'ml, L, kg, etc.',
    `usuario_id` int NOT NULL,
    `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `observaciones` text,
    PRIMARY KEY (`aplicacion_id`),
    KEY `idx_aplicacion_clarificador_id` (`clarificador_id`),
    KEY `idx_aplicacion_usuario_id` (`usuario_id`),
    KEY `idx_aplicacion_fecha_hora` (`fecha_hora`),
    CONSTRAINT `fk_aplicacion_clarificador` FOREIGN KEY (`clarificador_id`) REFERENCES `clarificadores` (`clarificador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Registro de aplicación de químicos en clarificadores';


-- ==========================================
-- PAROS DE CLARIFICADORES
-- ==========================================
CREATE TABLE `paros_clarificador` (
    `paro_id` int NOT NULL AUTO_INCREMENT,
    `relacion_id` int NOT NULL,
    `clarificador_id` int NOT NULL,
    `fecha_inicio` datetime NOT NULL,
    `fecha_fin` datetime DEFAULT NULL,
    `motivo` text NOT NULL,
    `usuario_id` int NOT NULL,
    `observaciones` text,
    `usuario_activa` int DEFAULT NULL COMMENT 'Usuario que reactivó',
    PRIMARY KEY (`paro_id`),
    KEY `idx_paros_relacion_id` (`relacion_id`),
    KEY `idx_paros_clarificador_id` (`clarificador_id`),
    KEY `idx_paros_usuario_id` (`usuario_id`),
    KEY `idx_paros_fecha_inicio` (`fecha_inicio`),
    CONSTRAINT `fk_paros_relacion` FOREIGN KEY (`relacion_id`) REFERENCES `procesos_clarificador_relacion` (`relacion_id`),
    CONSTRAINT `fk_paros_clarificador` FOREIGN KEY (`clarificador_id`) REFERENCES `clarificadores` (`clarificador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Registro de paros en clarificadores';

INSERT INTO `bd_sis_preparacion`.`clarificadores` (`nombre`) VALUES ('Clarificador 1');
