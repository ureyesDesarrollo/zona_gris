<?php

namespace Modules\Alertas\Jobs;
use Modules\ZonaGris\Funciones\Cocedores\Cocedores;
use Modules\Alertas\Teams\EnviarAlertaTeams;
use App\Helpers\Logger;

class AlertasValidacion {
    public static function verificarValidacionesPendientes() {
        Logger::info('Verificando validaciones pendientes');
        $registros = Cocedores::obtenerRegistrosSinValidar();
        Logger::info("Registros sin validar: {count}", ['count' => count($registros)]);
        foreach ($registros as $registro) {
            $minutos = self::minutosDesde($registro['fecha_hora']);
            Logger::info("Verificando validación: {minutos} minutos", ['minutos' => $minutos]);
            if ($minutos >= 15 && !$registro['alerta_15_enviada']) {
                EnviarAlertaTeams::enviarAlerta([
                    'tipo' => 'chat_user',
                    'responsable_tipo' => 'jefe',
                    'id' => 'desarrollo@progel.com.mx'
                ], [
                    'titulo' => '🚨 Validación pendiente 15 minutos',
                    'fecha' => date('Y-m-d H:i:s')
                ], [
                    ['titulo' => 'Cocedor', 'valor' => $registro['cocedor_id']],
                    ['titulo' => 'Hora registro', 'valor' => $registro['fecha_hora']]
                ]);

                Cocedores::marcarAlerta($registro['detalle_id'], 'alerta_15_enviada');
            }

            if ($minutos >= 30 && !$registro['alerta_30_enviada']) {
                EnviarAlertaTeams::enviarAlerta([
                    'tipo' => 'chat_user',
                    'responsable_tipo' => 'gerencia',
                    'id' => 'desarrollo@progel.com.mx'
                ], [
                    'titulo' => '🚨 Validación pendiente 30 minutos',
                    'fecha' => date('Y-m-d H:i:s')
                ], [
                    ['titulo' => 'Cocedor', 'valor' => $registro['cocedor_id']],
                    ['titulo' => 'Hora registro', 'valor' => $registro['fecha_hora']]
                ]);

                Cocedores::marcarAlerta($registro['detalle_id'], 'alerta_30_enviada');
            }
        }
    }

    private static function minutosDesde(string $fecha): float {
        return (time() - strtotime($fecha)) / 60;
    }
}
