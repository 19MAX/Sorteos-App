<?php

namespace App\Controllers\Home;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\BancosModel;

class HomeController extends BaseController
{
    public function index()
    {
        $data = [
            'titulo'      => "Samsung Galaxy S25 Ultra",
            'descripcion' => "512GB, Titanio Plateado. Nuevo en caja.",
            'imagen'      => "xyz789.jpg",
            'carrusel'    => ["c1.jpg", "c2.jpg", "c3.jpg"],
            'precio'      => 3.00,
            'moneda'      => "USD",
            'porcentaje'  => 0
        ];

        return view('home/index', $data);
    }

    public function comprar()
    {
        $bancosModel = new BancosModel();
        $data = [
            'titulo'      => "Samsung Galaxy S25 Ultra",
            'precio'      => 3.00,
            'moneda'      => "USD",
            'bancos'      => $bancosModel->where('activo', 1)->findAll()
        ];

        return view('home/comprar', $data);
    }

    public function misBoletos()
    {
        return view('home/mis-boletos');
    }
}
