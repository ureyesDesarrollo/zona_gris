<?php

namespace Modules\ZonaGris\Catalogos\Clarificador;

use App\BaseController;
use App\Database;
use App\Helpers\Request;

class CatalogoClarificadorController extends BaseController
{
    private $catalogoClarificador;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->catalogoClarificador = new CatalogoClarificador($db);
    }

    public function index()
    {
        $this->json($this->catalogoClarificador->all());
    }

    public function show($id)
    {
        $clarificador = $this->catalogoClarificador->find($id);

        if (empty($clarificador)) {
            return $this->json(['error' => 'Clarificador no encontrado'], 404);
        }

        return $this->json($clarificador, 200);
    }

    public function changeStatus()
    {
        $data = Request::input();
        $this->json($this->catalogoClarificador->changeStatus($data));
    }
}
