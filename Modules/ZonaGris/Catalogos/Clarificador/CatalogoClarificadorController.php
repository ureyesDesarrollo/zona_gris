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
        $this->json($this->catalogoClarificador->find($id));
    }

    public function changeStatus()
    {
        $data = Request::input();
        $this->json($this->catalogoClarificador->changeStatus($data));
    }
}