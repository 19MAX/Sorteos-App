<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ParticipantModel;
use App\Models\TransactionModel;
use App\Helpers\DataTablesHelper;

class ParticipantController extends BaseController
{
    protected $participantModel;
    protected $transactionModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->transactionModel = new TransactionModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Participantes',
        ];
        return view('admin/participants/index', $data);
    }

    public function data()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso denegado']);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('participants p')
            ->select('p.id, p.codigo, p.nombres, p.apellidos, p.full_name, p.email, p.cedula, p.telefono, p.verificado, p.created_at')
            ->select('(SELECT COUNT(*) FROM transactions t WHERE t.participant_id = p.id) as transaction_count', false)
            ->select('(SELECT COUNT(*) FROM tickets tk WHERE tk.participant_id = p.id) as ticket_count', false);

        $columns = [
            'p.id',
            'p.codigo',
            'p.nombres',
            'p.apellidos',
            'p.full_name',
            'p.email',
            'p.cedula',
            'p.telefono',
            'p.verificado',
            'p.created_at',
            'transaction_count',
            'ticket_count'
        ];

        $searchable = ['p.codigo', 'p.nombres', 'p.apellidos', 'p.email', 'p.cedula', 'p.telefono'];

        return $this->response->setJSON(
            DataTablesHelper::response($this->request, $builder, $columns, $searchable)
        );
    }

    public function buscar()
    {
        $cedula = $this->request->getGet('cedula') ?? '';

        if (empty($cedula)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Cédula no proporcionada'
            ]);
        }

        $existingParticipant = $this->participantModel->findByCedula($cedula);
        if ($existingParticipant) {
            return $this->response->setJSON([
                'status'  => 'exists',
                'message' => 'Ya existe un participante con esta cédula',
                'data'    => $existingParticipant
            ]);
        }

        $apiService = new \App\Services\ApiPrivadaService();
        $apiData = $apiService->getDataUser($cedula);

        if ($apiData) {
            return $this->response->setJSON([
                'status'  => 'success',
                'data'    => $apiData
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'not_found',
            'message' => 'No se encontraron datos para esta cédula'
        ]);
    }

    public function create()
    {
        $data = [
            'title' => 'Nuevo Participante',
        ];
        return view('admin/participants/create', $data);
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to(url_to('admin.participants.index'));
        }

        $rules = [
            'nombres'  => 'required|max_length[100]',
            'apellidos'=> 'required|max_length[100]',
            'email'    => 'required|valid_email|max_length[100]',
            'cedula'   => 'required|max_length[20]',
            'telefono' => 'required|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Datos inválidos',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $existingParticipant = $this->participantModel->findByCedula($this->request->getPost('cedula'));
        if ($existingParticipant) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Ya existe un participante con esta cédula',
                'errors'  => ['cedula' => 'Esta cédula ya está registrada']
            ]);
        }

        $nombres = $this->request->getPost('nombres');
        $apellidos = $this->request->getPost('apellidos');

        $data = [
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'full_name' => trim($nombres . ' ' . $apellidos),
            'email'     => $this->request->getPost('email'),
            'cedula'    => $this->request->getPost('cedula'),
            'telefono'  => $this->request->getPost('telefono'),
            'verificado'=> $this->request->getPost('verificado') ? 1 : 0,
        ];

        $saved = $this->participantModel->save($data);

        if (!$saved) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Error al guardar el participante'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Participante creado exitosamente',
            'id'      => $this->participantModel->getInsertID()
        ]);
    }

    public function edit($id)
    {
        $participant = $this->participantModel->find($id);

        if (!$participant) {
            return redirect()->to(url_to('admin.participants.index'))
                ->with('error', 'Participante no encontrado');
        }

        $data = [
            'title'       => 'Editar Participante',
            'participant' => $participant,
        ];
        return view('admin/participants/edit', $data);
    }

    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to(url_to('admin.participants.index'));
        }

        $participant = $this->participantModel->find($id);

        if (!$participant) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Participante no encontrado'
            ]);
        }

        $rules = [
            'nombres'  => 'required|max_length[100]',
            'apellidos'=> 'required|max_length[100]',
            'email'    => 'required|valid_email|max_length[100]',
            'cedula'   => "required|max_length[20]|is_unique[participants.cedula,id,{$id}]",
            'telefono' => 'required|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Datos inválidos',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $nombres = $this->request->getPost('nombres');
        $apellidos = $this->request->getPost('apellidos');

        $data = [
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'full_name' => trim($nombres . ' ' . $apellidos),
            'email'     => $this->request->getPost('email'),
            'cedula'    => $this->request->getPost('cedula'),
            'telefono'  => $this->request->getPost('telefono'),
            'verificado'=> $this->request->getPost('verificado') ? 1 : 0,
        ];

        $updated = $this->participantModel->update($id, $data);

        if (!$updated) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Error al actualizar el participante'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Participante actualizado exitosamente'
        ]);
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to(url_to('admin.participants.index'));
        }

        $participant = $this->participantModel->find($id);

        if (!$participant) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Participante no encontrado'
            ]);
        }

        $transactionCount = $this->transactionModel->where('participant_id', $id)->countAllResults();

        if ($transactionCount > 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "No se puede eliminar el participante porque tiene {$transactionCount} transacción(es) asociada(s)"
            ]);
        }

        $this->participantModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Participante eliminado exitosamente'
        ]);
    }
}