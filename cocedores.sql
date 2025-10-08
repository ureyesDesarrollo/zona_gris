-- --------------- COCEDORES -----------------------------------------------------------

CREATE TABLE `zn_procesos_agrupados` (
    `proceso_agrupado_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador del proceso agrupado',
    `descripcion` varchar(255) DEFAULT NULL COMMENT 'Motivo o razón de la agrupación de procesos',
    `usuario_id` int DEFAULT NULL COMMENT 'Usuario responsable de la agrupación',
    PRIMARY KEY (`proceso_agrupado_id`),
    KEY `idx_zn_procesos_agrupados_usuario_id` (`usuario_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Registra agrupaciones de procesos según criterios definidos por el usuario';

CREATE TABLE `zn_procesos_agrupados_detalle` (
    `agrupado_detalle_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del detalle de agrupación',
    `proceso_agrupado_id` int NOT NULL COMMENT 'Referencia al proceso agrupado principal',
    `pro_id` int NOT NULL COMMENT 'Identificador del proceso involucrado en la agrupación',
    PRIMARY KEY (`agrupado_detalle_id`),
    KEY `idx_zn_procesos_agrupados_detalle_proceso_agrupado_id` (`proceso_agrupado_id`),
    KEY `idx_zn_procesos_agrupados_detalle_pro_id` (`pro_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Detalles de los procesos individuales dentro de una agrupación';

CREATE TABLE `cocedores` (
    `cocedor_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del cocedor',
    `nombre` varchar(100) NOT NULL COMMENT 'Nombre o clave del cocedor',
    `capacidad` DECIMAL(5,2) DEFAULT NULL COMMENT 'Capacidad máxima del cocedor (en kg, litros, etc)',
    `estatus` enum(
        'ACTIVO',
        'INACTIVO',
        'MANTENIMIENTO',
        'OCUPADO'
    ) DEFAULT 'ACTIVO' COMMENT 'Estatus actual del cocedor',
    `observaciones` varchar(255) DEFAULT NULL COMMENT 'Observaciones relevantes del cocedor',
    PRIMARY KEY (`cocedor_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Catálogo de cocedores disponibles en planta';

CREATE TABLE `procesos_cocedores_relacion` (
    `relacion_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único de la relación cocedor-proceso',
    `proceso_agrupado_id` int NOT NULL COMMENT 'Referencia al proceso agrupado',
    `cocedor_id` int NOT NULL COMMENT 'Referencia al cocedor involucrado',
    `fecha_inicio` datetime DEFAULT NULL COMMENT 'Fecha y hora en que inicia la relación',
    `fecha_fin` datetime DEFAULT NULL COMMENT 'Fecha y hora en que finaliza la relación',
    PRIMARY KEY (`relacion_id`),
    KEY `idx_procesos_cocedores_relacion_proceso_agrupado_id` (`proceso_agrupado_id`),
    KEY `idx_procesos_cocedores_relacion_cocedor_id` (`cocedor_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Relación entre agrupaciones de procesos y cocedores';

CREATE TABLE `procesos_cocedores_detalle` (
    `detalle_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del detalle del proceso en el cocedor',
    `relacion_id` int NOT NULL COMMENT 'Referencia a la relación cocedor-proceso',
    `fecha_hora` datetime NOT NULL COMMENT 'Fecha y hora del registro del detalle',
    `usuario_id` int NOT NULL COMMENT 'Usuario responsable del registro',
    `responsable_tipo` enum(
        'OPERADOR',
        'CONTROL DE PROCESOS'
    ) NOT NULL COMMENT 'Tipo de responsable que realiza el registro',
    `tipo_registro` enum('OPERACION', 'REINICIO') NOT NULL COMMENT 'Tipo de registro realizado',
    `param_agua` DECIMAL(5,2) DEFAULT NULL COMMENT 'Parámetro: cantidad de agua',
    `param_temp_entrada` DECIMAL(5,2) DEFAULT NULL COMMENT 'Parámetro: temperatura de entrada',
    `param_temp_salida` DECIMAL(5,2) DEFAULT NULL COMMENT 'Parámetro: temperatura de salida',
    `param_solidos` DECIMAL(5,2) DEFAULT NULL COMMENT 'Parámetro: sólidos totales',
    `param_ph` DECIMAL(5,2) DEFAULT NULL COMMENT 'Parámetro: pH de la muestra',
    `param_ntu` DECIMAL(5,2) DEFAULT NULL COMMENT 'Parámetro: NTU (turbidez)',
    `muestra_tomada` INT DEFAULT NULL COMMENT 'Indica si se tomó muestra (TRUE/FALSE)',
    `observaciones` text COMMENT 'Observaciones adicionales del registro',
    `agitacion` ENUM('SI,NO') DEFAULT NULL COMMENT 'Estado de la agitación',
    `desengrasador` ENUM('SI,NO') DEFAULT NULL COMMENT 'Estado del desengrasador',
    `supervisor_validado` INT DEFAULT '0' COMMENT '1=validado por supervisor, 0=no',
    `supervisor_id` int DEFAULT NULL COMMENT 'Usuario supervisor que valida',
    `fecha_validacion` datetime DEFAULT NULL COMMENT 'Fecha y hora de validación',
    PRIMARY KEY (`detalle_id`),
    KEY `idx_procesos_cocedores_detalle_relacion_id` (`relacion_id`),
    KEY `idx_procesos_cocedores_detalle_usuario_id` (`usuario_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Registros detallados de la operación y control en los cocedores';

CREATE TABLE `paros_cocedores` (
    `paro_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del paro de cocedor',
    `cocedor_id` int NOT NULL COMMENT 'Referencia al cocedor involucrado',
    `fecha_inicio` datetime NOT NULL COMMENT 'Fecha y hora en que inicia el paro',
    `fecha_fin` datetime DEFAULT NULL COMMENT 'Fecha y hora en que finaliza el paro',
    `motivo` text NOT NULL COMMENT 'Motivo del paro',
    `usuario_id` int NOT NULL COMMENT 'Usuario responsable de registrar el paro',
    `observaciones` text COMMENT 'Observaciones adicionales sobre el paro',
    `usuario_activa` int DEFAULT NULL COMMENT 'Usuario que reactivó el cocedor',
    PRIMARY KEY (`paro_id`),
    KEY `idx_paros_cocedores_usuario_id` (`usuario_id`),
    KEY `idx_paros_cocedores_fecha_inicio` (`fecha_inicio`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Registro de paros (detenciones) ocurridos en cocedores y sus motivos';

-- Relación entre agrupaciones y usuario
ALTER TABLE zn_procesos_agrupados
  ADD CONSTRAINT fk_procesos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(usu_id);

-- Detalle de agrupación → agrupado + proceso
ALTER TABLE zn_procesos_agrupados_detalle
  ADD CONSTRAINT fk_detalle_agrupado FOREIGN KEY (proceso_agrupado_id) REFERENCES zn_procesos_agrupados(proceso_agrupado_id),
  ADD CONSTRAINT fk_detalle_proceso FOREIGN KEY (pro_id) REFERENCES procesos(pro_id);

-- Relación cocedor-proceso agrupado
ALTER TABLE procesos_cocedores_relacion
  ADD CONSTRAINT fk_relacion_agrupado FOREIGN KEY (proceso_agrupado_id) REFERENCES zn_procesos_agrupados(proceso_agrupado_id),
  ADD CONSTRAINT fk_relacion_cocedor FOREIGN KEY (cocedor_id) REFERENCES cocedores(cocedor_id);

-- Detalle hora a hora
ALTER TABLE procesos_cocedores_detalle
  ADD CONSTRAINT fk_detalle_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(usu_id),
  ADD CONSTRAINT fk_detalle_supervisor FOREIGN KEY (supervisor_id) REFERENCES usuarios(usu_id);

  -- Paros
ALTER TABLE paros_cocedores
  ADD CONSTRAINT fk_paros_cocedor FOREIGN KEY (cocedor_id) REFERENCES cocedores(cocedor_id),
  ADD CONSTRAINT fk_paros_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(usu_id),
  ADD CONSTRAINT fk_paros_activa FOREIGN KEY (usuario_activa) REFERENCES usuarios(usu_id);

ALTER TABLE procesos_cocedores_detalle
ADD COLUMN supervisor_observaciones TEXT,
ADD COLUMN alerta_15_enviada INT NOT NULL DEFAULT 0,
ADD COLUMN alerta_30_enviada INT NOT NULL DEFAULT 0;

-- Reportes por fecha y cocedor
CREATE INDEX idx_detalle_fecha ON procesos_cocedores_detalle(fecha_hora);
CREATE INDEX idx_relacion_fecha ON procesos_cocedores_relacion(fecha_inicio, fecha_fin);
CREATE INDEX idx_paros_fecha ON paros_cocedores(fecha_inicio, fecha_fin);

-- Validaciones
CREATE INDEX idx_validacion ON procesos_cocedores_detalle(supervisor_validado, alerta_15_enviada, alerta_30_enviada);

ALTER TABLE procesos_cocedores_detalle
ADD COLUMN creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;


INSERT INTO `bd_sis_preparacion`.`cocedores` (`cocedor_id`,`nombre`, `capacidad`, `estatus`) VALUES (1,'Cocedor 1', '170', 'ACTIVO');
INSERT INTO `bd_sis_preparacion`.`cocedores` (`cocedor_id`,`nombre`, `capacidad`, `estatus`) VALUES (2,'Cocedor 2', '170', 'ACTIVO');
INSERT INTO `bd_sis_preparacion`.`cocedores` (`cocedor_id`,`nombre`, `capacidad`, `estatus`) VALUES (3,'Cocedor 3', '170', 'ACTIVO');
INSERT INTO `bd_sis_preparacion`.`cocedores` (`cocedor_id`,`nombre`, `capacidad`, `estatus`) VALUES (4,'Cocedor 4', '170', 'ACTIVO');
INSERT INTO `bd_sis_preparacion`.`cocedores` (`cocedor_id`,`nombre`, `capacidad`, `estatus`) VALUES (5,'Cocedor 5', '170', 'ACTIVO');
INSERT INTO `bd_sis_preparacion`.`cocedores` (`cocedor_id`,`nombre`, `capacidad`, `estatus`) VALUES (6,'Cocedor 6', '190', 'ACTIVO');
INSERT INTO `bd_sis_preparacion`.`cocedores` (`cocedor_id`,`nombre`, `capacidad`, `estatus`) VALUES (7,'Cocedor 7', '190', 'ACTIVO');
INSERT INTO `bd_sis_preparacion`.`bitacora_modulos` (`bm_descripcion`) VALUES ('Cocedores');
-- INSERT INTO `bd_sis_preparacion`.`usuarios_permisos` (`bm_id`, `up_id`, `upe_agregar`, `upe_borrar`, `upe_editar`, `upe_listar`) VALUES ('51', '25', '1', '1', '1', '1');
UPDATE `bd_sis_preparacion`.`usuarios_permisos` SET `up_id` = '25' WHERE (`upe_id` = '495');

SELECT * FROM zn_procesos_agrupados;