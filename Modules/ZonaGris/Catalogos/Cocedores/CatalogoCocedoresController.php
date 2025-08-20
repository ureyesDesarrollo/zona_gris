<?php

namespace Modules\ZonaGris\Catalogos\Cocedores;

use App\BaseController;
use App\Database;
use App\Helpers\Request;
use App\Helpers\Validator;

class CatalogoCocedoresController extends BaseController
{
    private $CatalogoCocedoresModel;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->CatalogoCocedoresModel = new CatalogoCocedores($db);
    }

    public function index()
    {
        try {
            $result = $this->CatalogoCocedoresModel->all();
            $this->json($result, 200);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $result = $this->CatalogoCocedoresModel->find($id);
            $this->json($result, 200);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], $e->getCode() ?: 404);
        }
    }

    public function store()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['nombre']);

        if ($validator->fails()) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 422);
            return;
        }

        if ($this->CatalogoCocedoresModel->existsByName($data['nombre'])) {
            $this->json(['success' => false, 'error' => 'Ya existe un cocedor con este nombre']);
            return;
        }

        try {
            $id = $this->CatalogoCocedoresModel->create($data);
            $this->json(['success' => true, 'id' => $id], 201);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $this->json(['success' => false, 'error' => $e->getMessage()], $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function update($id)
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['nombre']);

        if ($validator->fails()) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 422);
            return;
        }

        if ($this->CatalogoCocedoresModel->existsByName($data['nombre'])) {
            $this->json(['success' => false, 'error' => 'Ya existe un cocedor con este nombre']);
            return;
        }


        try {
            $ok = $this->CatalogoCocedoresModel->update($id, $data);
            $this->json($ok);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $this->json(['success' => false, 'error' => $e->getMessage()], $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    // PUT /api/zonagris/catalogos/cocedor/{id}/estatus
    public function changeStatus($id)
    {
        $data = Request::input();
        $estatus = $data['estatus'] ?? null;

        if (!$estatus) {
            $this->json(['success' => false, 'error' => 'Estatus requerido'], 400);
            return;
        }

        try {
            $ok = $this->CatalogoCocedoresModel->changeStatus($id, $estatus);
            $this->json($ok);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
