<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ConfigController extends BaseController
{
    protected $settingsModel;
    protected $bankModel;

    public function __construct()
    {
        $this->settingsModel = new \App\Models\SettingsModel();
        $this->bankModel = new \App\Models\BancosModel();
        helper(['form', 'response']);
    }

    public function index()
    {

        $data = [
            'data_banks' => $this->bankModel->findAll(),
            'settings' => $this->settingsModel->first()
        ];
        return view('settings/config', $data);
    }

    public function save()
    {
        try {

            $data = $this->request->getPost([
                'nombre_producto',
                'descripcion_producto',
                'boletos_minimos',
                'total_boletos',
                'precio_boleto'
            ]);

            // Checkbox
            $data['sorteo_activo'] = $this->request->getPost('sorteo_activo') ? 1 : 0;

            // Usuario (opcional)
            $data['updated_by'] = session()->get('user_id') ?? 1;

            // Imagen
            $img = $this->request->getFile('imagen_producto');

            if ($img && $img->isValid() && !$img->hasMoved()) {
                $newName = $img->getRandomName();
                $img->move('uploads/productos/', $newName);
                $data['imagen_producto'] = $newName;
            }

            // Obtener registro actual (solo 1 config)
            $settings = $this->settingsModel->first();

            if ($settings) {
                // UPDATE
                if ($this->settingsModel->update($settings['id'], $data) === false) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'errors' => $this->settingsModel->errors()
                    ]);
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Configuración actualizada'
                ]);
            } else {
                // INSERT
                if ($this->settingsModel->insert($data) === false) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'errors' => $this->settingsModel->errors()
                    ]);
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Configuración creada'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'SettingsController::save - ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del servidor'
            ]);
        }
    }
}
