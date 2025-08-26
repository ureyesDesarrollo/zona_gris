<?php
namespace Modules\Alertas\Teams;

use Modules\Alertas\Teams\CrearAdaptiveCard;

class EnviarAlertaTeams {
    public static function enviarAlerta(array $datos, array $facts) {
        $adaptiveCard = CrearAdaptiveCard::crearAdaptiveCard($datos, $facts);
        $jsonCard = json_encode($adaptiveCard);
        $mensaje = [
            'mensaje' => $adaptiveCard
        ];

        $webhookUrl = 'https://prod-169.westus.logic.azure.com:443/workflows/0f69e8b16a87481c8752b4149937532b/triggers/manual/paths/invoke?api-version=2016-06-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=baQvqqYE36R546pHRL5qNnT9NYSb5xtVPvOeihf52zQ';

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
            return "Error al enviar mensaje: $error";
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $response;
        } else {
            return "Error HTTP $httpCode: $response";
        }
    }
}
