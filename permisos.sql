UPDATE usuarios SET usu_usuario = 'ulises', usu_pwr = '7c94976f5d4567f3eab3a1475134fc5a' WHERE usu_usuario = 'ureyes';

INSERT INTO `bd_sis_preparacion`.`usuarios_perfiles` (`up_nombre`) VALUES ('Operador zona gris');

UPDATE usuarios_perfiles SET up_nombre = TRIM(up_nombre);

INSERT INTO `bd_sis_preparacion`.`usuarios_permisos` (`bm_id`, `up_id`, `upe_agregar`, `upe_borrar`, `upe_editar`, `upe_listar`) VALUES ('51', '25', '1', '1', '1', '1');
INSERT INTO `bd_sis_preparacion`.`usuarios_permisos` (`bm_id`, `up_id`, `upe_agregar`, `upe_borrar`, `upe_editar`, `upe_listar`) VALUES ('52', '25', '1', '1', '1', '1');
INSERT INTO `bd_sis_preparacion`.`usuarios_permisos` (`bm_id`, `up_id`, `upe_agregar`, `upe_borrar`, `upe_editar`, `upe_listar`) VALUES ('51', '29', '1', '1', '1', '1');
INSERT INTO `bd_sis_preparacion`.`usuarios_permisos` (`bm_id`, `up_id`, `upe_agregar`, `upe_borrar`, `upe_editar`, `upe_listar`) VALUES ('52', '29', '1', '1', '1', '1');