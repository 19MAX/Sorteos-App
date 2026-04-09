<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class BankController extends BaseController
{

    protected $bankModel;

    public function __construct()
    {
        $this->bankModel = new \App\Models\BancosModel();
        helper(['form', 'response']);
    }


    public function create()
    {
        try {

            // Obtener datos del formulario (nombres correctos)
            $data = $this->request->getPost([
                'nombre_banco',
                'tipo_cuenta',
                'numero_cuenta',
                'titular',
                'activo'
            ]);

            // Manejo del logo
            $logo = $this->request->getFile('logo');

            if ($logo && $logo->isValid() && !$logo->hasMoved()) {
                $newName = $logo->getRandomName();
                $logo->move('uploads/bancos/', $newName);
                $data['logo'] = $newName;
            }

            // Insertar en DB
            if ($this->bankModel->insert($data) === false) {

                $errores = implode('<br>', $this->bankModel->errors());

                return redirectView('admin/settings/config', $errores, [['Errores de validación', 'error']], $data);

            }

            return redirectView('admin/settings/config', null, [['Banco creado exitosamente', 'success', 'top-end']], null);

        } catch (\Exception $e) {

            log_message('error', 'Error en BankController::create: ' . $e->getMessage());


            return redirectView('admin/settings/config', 'Ocurrió un error al crear el banco', [['Error al crear banco', 'error', 'top-end']], null);
        }
    }
}
