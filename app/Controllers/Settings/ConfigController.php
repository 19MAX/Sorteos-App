<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ConfigController extends BaseController
{
    protected $bankModel;

    public function __construct()
    {
        $this->bankModel = new \App\Models\BancosModel();
    }

    public function index()
    {

        $data = [
            'data_banks' => $this->bankModel->findAll()
        ];
        return view('settings/config', $data);
    }
}
