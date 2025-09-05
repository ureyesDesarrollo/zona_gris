<?php

namespace Modules\Alertas\Teams;

use Modules\Alertas\Teams\CrearAdaptiveCard;
use App\Helpers\Logger;

class EnviarAlertaTeams
{
    public static function enviarAlerta(array $destino, array $datos, array $facts)
    {
        $respTipo = $destino['responsable_tipo'] ?? 'general';

        // Elegir plantilla según tipo de destinatario
        switch ($respTipo) {
            case 'supervisores':
                $adaptiveCard = CrearAdaptiveCard::crearAdaptiveCardGeneral($datos, $facts);
                break;
            case 'jefe':
            case 'gerencia':
                $adaptiveCard = CrearAdaptiveCard::crearAdaptiveCardSupervisorValidacion($datos, $facts);
                break;
            default:
                $adaptiveCard = CrearAdaptiveCard::crearAdaptiveCardGeneral($datos, $facts);
                break;
        }

        $mensaje = [
            'destino' => $destino,
            'mensaje' => $adaptiveCard
        ];

        Logger::info('🚀 Enviando alerta a Teams, {payload}', [
            'payload' => json_encode($mensaje, JSON_UNESCAPED_UNICODE)
        ]);

        $webhookUrl = 'https://default28a9eec67db54994a8e16ca14948f6.b7.environment.api.powerplatform.com:443/powerautomate/automations/direct/workflows/0f69e8b16a87481c8752b4149937532b/triggers/manual/paths/invoke?api-version=1&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=rI6Z5Jo0pL71wgKn3dwJZSdXmF3GOyodE0t1gCq9VZ0';

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mensaje));
        curl_setopt($ch, CURLOPT_CAINFO, 'C:/wamp64/cacert.pem');

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            Logger::error('Error al enviar alerta a Teams, {error}', ['error' => $error]);
            return "Error al enviar mensaje: $error";
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            Logger::info('Alerta enviada correctamente');
            return $response;
        } else {
            Logger::error('Error al enviar alerta, {httpCode}, {response}', ['httpCode' => $httpCode, 'response' => $response]);
            return "Error HTTP $httpCode: $response";
        }
    }
}
