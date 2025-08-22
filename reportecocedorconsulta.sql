SELECT
  DATE(d.fecha_hora) AS dia,
  CONCAT(LPAD(HOUR(d.fecha_hora), 2, '0'), ':00') AS hora,
  za.pro_id AS proceso,
  d.usuario_id AS preparador,

  -- COCEDOR 1
  IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_temp_entrada END), 2),'FO') AS c1_temp_entrada,
  IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_temp_salida END) AS c1_temp_salida,
  IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_solidos END) AS c1_solidos,
  IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_agua END) AS c1_agua,
  IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_ntu END) AS c1_ntu,
  IFNULL(ROUND(MAX(CASE WHEN r.cocedor_id = 1 THEN d.param_ph END) AS c1_ph,

  -- COCEDOR 2
  MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_temp_entrada END) AS c2_temp_entrada,
  MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_temp_salida END) AS c2_temp_salida,
  MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_solidos END) AS c2_solidos,
  MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_agua END) AS c2_agua,
  MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_ntu END) AS c2_ntu,
  MAX(CASE WHEN r.cocedor_id = 2 THEN d.param_ph END) AS c2_ph,

  -- COCEDOR 3
  MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_temp_entrada END) AS c3_temp_entrada,
  MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_temp_salida END) AS c3_temp_salida,
  MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_solidos END) AS c3_solidos,
  MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_agua END) AS c3_agua,
  MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_ntu END) AS c3_ntu,
  MAX(CASE WHEN r.cocedor_id = 3 THEN d.param_ph END) AS c3_ph,

  -- COCEDOR 4
  MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_temp_entrada END) AS c4_temp_entrada,
  MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_temp_salida END) AS c4_temp_salida,
  MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_solidos END) AS c4_solidos,
  MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_agua END) AS c4_agua,
  MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_ntu END) AS c4_ntu,
  MAX(CASE WHEN r.cocedor_id = 4 THEN d.param_ph END) AS c4_ph,

  -- COCEDOR 5
  MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_temp_entrada END) AS c5_temp_entrada,
  MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_temp_salida END) AS c5_temp_salida,
  MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_solidos END) AS c5_solidos,
  MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_agua END) AS c5_agua,
  MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_ntu END) AS c5_ntu,
  MAX(CASE WHEN r.cocedor_id = 5 THEN d.param_ph END) AS c5_ph,

  -- COCEDOR 6
  MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_temp_entrada END) AS c6_temp_entrada,
  MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_temp_salida END) AS c6_temp_salida,
  MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_solidos END) AS c6_solidos,
  MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_agua END) AS c6_agua,
  MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_ntu END) AS c6_ntu,
  MAX(CASE WHEN r.cocedor_id = 6 THEN d.param_ph END) AS c6_ph,

  -- COCEDOR 7
  MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_temp_entrada END) AS c7_temp_entrada,
  MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_temp_salida END) AS c7_temp_salida,
  MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_solidos END) AS c7_solidos,
  MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_agua END) AS c7_agua,
  MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_ntu END) AS c7_ntu,
  MAX(CASE WHEN r.cocedor_id = 7 THEN d.param_ph END) AS c7_ph

FROM procesos_cocedores_detalle d
LEFT JOIN procesos_cocedores_relacion r ON r.relacion_id = d.relacion_id
LEFT JOIN zn_procesos_agrupados_detalle za ON za.proceso_agrupado_id = r.proceso_agrupado_id

GROUP BY DATE(d.fecha_hora), HOUR(d.fecha_hora), za.pro_id, d.usuario_id
ORDER BY dia, hora;