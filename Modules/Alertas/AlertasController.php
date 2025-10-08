<?php
namespace Modules\Alertas;

use Modules\Alertas\Teams\EnviarAlertaTeams;
use App\BaseController;
use App\Helpers\Logger;
use App\Helpers\Request;

class AlertasController extends BaseController {
    public function enviarAlerta() {
        $data = Request::input();
        if (empty($data)) {
            return $this->json(['error' => 'No se proporcionaron datos'], 400);
        }
        
        Logger::info('Enviando alerta a Teams {datos}', ['datos' => $data['titulo']]);
        
        $destino = [
            'tipo' => 'chat_user',
            'id' => 'desarrollo@progel.com.mx'
        ];

        $datos = [
            'titulo' => $data['titulo'],
            'fecha' => $data['fecha']
        ];

        Logger::info('Enviando alerta a Teams {fecha}, {titulo}, {facts}', ['fecha' => $datos['fecha'], 'titulo' => $datos['titulo'], 'facts' => $data['facts']]);
        
        $facts = $data['facts'];

        $result = EnviarAlertaTeams::enviarAlerta($destino, $datos, $facts);
        return $this->json(['result' => $result], 200);
    }
}
