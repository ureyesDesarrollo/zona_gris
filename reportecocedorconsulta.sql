SELECT
  DATE(d.fecha_hora) AS dia,
  CONCAT(LPAD(HOUR(d.fecha_hora), 2, '0'), ':00') AS hora,
  za.pro_id AS proceso,
  d.usuario_id AS preparador,

  -- COCEDOR 1
  CASE WHEN r.cocedor_id = 1 THEN d.param_temp_entrada END AS c1_temp_entrada,
  CASE WHEN r.cocedor_id = 1 THEN d.param_temp_salida END AS c1_temp_salida,
  CASE WHEN r.cocedor_id = 1 THEN d.param_solidos END AS c1_solidos,
  CASE WHEN r.cocedor_id = 1 THEN d.param_agua END AS c1_agua,
  CASE WHEN r.cocedor_id = 1 THEN d.param_ntu END AS c1_ntu,
  CASE WHEN r.cocedor_id = 1 THEN d.param_ph END AS c1_ph,

  -- COCEDOR 2
  AVG(CASE WHEN r.cocedor_id = 2 THEN d.param_temp_entrada END) AS c2_temp_entrada,
  AVG(CASE WHEN r.cocedor_id = 2 THEN d.param_temp_salida END) AS c2_temp_salida,
  AVG(CASE WHEN r.cocedor_id = 2 THEN d.param_solidos END) AS c2_solidos,
  AVG(CASE WHEN r.cocedor_id = 2 THEN d.param_agua END) AS c2_agua,
  AVG(CASE WHEN r.cocedor_id = 2 THEN d.param_ntu END) AS c2_ntu,
  AVG(CASE WHEN r.cocedor_id = 2 THEN d.param_ph END) AS c2_ph,

  -- Repite para cocedor 3 al 7...

  AVG(CASE WHEN r.cocedor_id = 7 THEN d.param_temp_entrada END) AS c7_temp_entrada,
  AVG(CASE WHEN r.cocedor_id = 7 THEN d.param_temp_salida END) AS c7_temp_salida,
  AVG(CASE WHEN r.cocedor_id = 7 THEN d.param_solidos END) AS c7_solidos,
  AVG(CASE WHEN r.cocedor_id = 7 THEN d.param_agua END) AS c7_agua,
  AVG(CASE WHEN r.cocedor_id = 7 THEN d.param_ntu END) AS c7_ntu,
  AVG(CASE WHEN r.cocedor_id = 7 THEN d.param_ph END) AS c7_ph

FROM procesos_cocedores_detalle d
JOIN procesos_cocedores_relacion r ON r.relacion_id = d.relacion_id
JOIN zn_procesos_agrupados_detalle za ON za.proceso_agrupado_id = r.proceso_agrupado_id

GROUP BY DATE(d.fecha_hora), HOUR(d.fecha_hora), za.pro_id, d.usuario_id
ORDER BY dia, hora;


SELECT * FROM procesos_cocedores_detalle;