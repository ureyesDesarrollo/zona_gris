SELECT DATE(d.fecha_hora) AS dia, 
CONCAT(LPAD(HOUR(d.fecha_hora), 2, '0'), ':00') AS hora, 
za.pro_id AS proceso, 
u1.usu_nombre AS operador, 
u2.usu_nombre AS supervisor, 
-- COCEDOR 1 
IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_temp_entrada END), 2), 'FO') AS c1_temp_entrada, 
IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_temp_salida END), 2), 'FO') AS c1_temp_salida, 
IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_solidos END), 2), 'FO') AS c1_solidos, 
IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_agua END), 2), 'FO') AS c1_agua, 
IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_ntu END), 2), 'FO') AS c1_ntu, 
IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_ph END), 2), 'FO') AS c1_ph,
 -- COCEDOR 2 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_temp_entrada END), 2), 'FO') AS c2_temp_entrada, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_temp_salida END), 2), 'FO') AS c2_temp_salida, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_solidos END), 2), 'FO') AS c2_solidos, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_agua END), 2), 'FO') AS c2_agua, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_ntu END), 2), 'FO') AS c2_ntu,
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_ph END), 2), 'FO') AS c2_ph, 
 -- COCEDOR 3
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_temp_entrada END), 2), 'FO') AS c3_temp_entrada, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_temp_salida END), 2), 'FO') AS c3_temp_salida, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_solidos END), 2), 'FO') AS c3_solidos, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_agua END), 2), 'FO') AS c3_agua, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_ntu END), 2), 'FO') AS c3_ntu, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_ph END), 2), 'FO') AS c3_ph, 
 -- COCEDOR 4 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_temp_entrada END), 2), 'FO') AS c4_temp_entrada, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_temp_salida END), 2), 'FO') AS c4_temp_salida, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_solidos END), 2), 'FO') AS c4_solidos, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_agua END), 2), 'FO') AS c4_agua, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_ntu END), 2), 'FO') AS c4_ntu, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_ph END), 2), 'FO') AS c4_ph, 
 -- COCEDOR 5 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_temp_entrada END), 2), 'FO') AS c5_temp_entrada, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_temp_salida END), 2), 'FO') AS c5_temp_salida, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_solidos END), 2), 'FO') AS c5_solidos, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_agua END), 2), 'FO') AS c5_agua, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_ntu END), 2), 'FO') AS c5_ntu, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_ph END), 2), 'FO') AS c5_ph, 
 -- COCEDOR 6 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_temp_entrada END), 2), 'FO') AS c6_temp_entrada, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_temp_salida END), 2), 'FO') AS c6_temp_salida, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_solidos END), 2), 'FO') AS c6_solidos, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_agua END), 2), 'FO') AS c6_agua, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_ntu END), 2), 'FO') AS c6_ntu, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_ph END), 2), 'FO') AS c6_ph, 
 -- COCEDOR 7 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_temp_entrada END), 2), 'FO') AS c7_temp_entrada, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_temp_salida END), 2), 'FO') AS c7_temp_salida, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_solidos END), 2), 'FO') AS c7_solidos, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_agua END), 2), 'FO') AS c7_agua, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_ntu END), 2), 'FO') AS c7_ntu, 
 IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_ph END), 2), 'FO') AS c7_ph 
 FROM procesos_cocedores_detalle d 
 LEFT JOIN procesos_cocedores_relacion r ON r.relacion_id = d.relacion_id 
 LEFT JOIN zn_procesos_agrupados_detalle za ON za.proceso_agrupado_id = r.proceso_agrupado_id 
 INNER JOIN usuarios u1 ON u1.usu_id = d.usuario_id 
 LEFT JOIN usuarios u2 ON u2.usu_id = d.supervisor_id 
 GROUP BY DATE(d.fecha_hora), HOUR(d.fecha_hora), za.pro_id
 ORDER BY dia, hora;