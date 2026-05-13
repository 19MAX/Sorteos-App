<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $adminPassword = password_hash(env('SEEDER_ADMIN_PASSWORD'), PASSWORD_DEFAULT);

        $adminData = [
            'username' => env('SEEDER_ADMIN_USERNAME'),
            'password' => $adminPassword,
            'nombre' => env('SEEDER_ADMIN_NAME'),
            'email' => env('SEEDER_ADMIN_EMAIL'),
            'is_superadmin' => 1,
            'last_login' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $builder = $this->db->table('admins');
        $builder->insert($adminData);

    }
}