<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;

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
            $data = $this->request->getPost([
                'nombre_banco',
                'tipo_cuenta',
                'numero_cuenta',
                'titular',
                'activo'
            ]);

            $logo = $this->request->getFile('logo');
            if ($logo && $logo->isValid() && !$logo->hasMoved()) {
                $newName = $logo->getRandomName();
                $logo->move('uploads/bancos/', $newName);
                $data['logo'] = $newName;
            }

            if ($this->bankModel->insert($data) === false) {
                return redirectView(
                    'admin/settings/config',
                    $this->bankModel->errors(),
                    [['Revisa los campos del formulario', 'warning', 'top-end']],
                    $this->request->getPost(), // ✅ Datos para repoblar
                    'create'                   // ✅ Acción para abrir modal correcto
                );
            }

            return redirectView(
                'admin/settings/config',
                null,
                [['Banco creado exitosamente', 'success', 'top-end']]
            );

        } catch (\Exception $e) {
            log_message('error', 'Error en BankController::create: ' . $e->getMessage());
            return redirectView(
                'admin/settings/config',
                'Ocurrió un error inesperado al crear el banco.',
                [['Error del servidor', 'error', 'top-end']]
            );
        }
    }

    public function update(int $id)
    {
        try {
            // Verificar que el banco existe
            $bank = $this->bankModel->find($id);
            if (!$bank) {
                return redirectView(
                    'admin/settings/config',
                    null,
                    [['Banco no encontrado', 'error', 'top-end']]
                );
            }

            $data = $this->request->getPost([
                'nombre_banco',
                'tipo_cuenta',
                'numero_cuenta',
                'titular',
                'activo'
            ]);

            // Manejo del logo — solo actualizar si se subió uno nuevo
            $logo = $this->request->getFile('logo');
            if ($logo && $logo->isValid() && !$logo->hasMoved()) {
                // Eliminar logo anterior si existe
                if (!empty($bank['logo'])) {
                    $oldPath = FCPATH . 'uploads/bancos/' . $bank['logo'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $newName = $logo->getRandomName();
                $logo->move('uploads/bancos/', $newName);
                $data['logo'] = $newName;
            }

            if ($this->bankModel->update($id, $data) === false) {
                return redirectView(
                    'admin/settings/config',
                    $this->bankModel->errors(),
                    [['Revisa los campos del formulario', 'warning', 'top-end']],
                    array_merge($this->request->getPost(), ['id' => $id]),
                    'edit'
                );
            }

            return redirectView(
                'admin/settings/config',
                null,
                [['Banco actualizado exitosamente', 'success', 'top-end']]
            );

        } catch (\Exception $e) {
            log_message('error', 'BankController::update - ' . $e->getMessage());
            return redirectView(
                'admin/settings/config',
                'Ocurrió un error inesperado al actualizar el banco.',
                [['Error del servidor', 'error', 'top-end']]
            );
        }
    }

    public function delete(int $id)
    {
        try {
            $bank = $this->bankModel->find($id);
            if (!$bank) {
                return redirectView(
                    'admin/settings/config',
                    null,
                    [['Banco no encontrado', 'error', 'top-end']]
                );
            }

            if ($this->bankModel->delete($id) === false) {
                return redirectView(
                    'admin/settings/config',
                    null,
                    [['No se pudo eliminar el banco', 'error', 'top-end']]
                );
            }

            // Eliminar logo del servidor si existe
            if (!empty($bank['logo'])) {
                $logoPath = FCPATH . 'uploads/bancos/' . $bank['logo'];
                if (file_exists($logoPath)) {
                    unlink($logoPath);
                }
            }

            return redirectView(
                'admin/settings/config',
                null,
                [['Banco eliminado exitosamente', 'success', 'top-end']]
            );

        } catch (\Exception $e) {
            log_message('error', 'BankController::delete - ' . $e->getMessage());
            return redirectView(
                'admin/settings/config',
                'Ocurrió un error inesperado al eliminar el banco.',
                [['Error del servidor', 'error', 'top-end']]
            );
        }
    }
}
