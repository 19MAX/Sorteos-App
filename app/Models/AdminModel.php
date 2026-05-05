<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'password', 'nombre', 'email', 'is_superadmin', 'last_login'];

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected function setPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyLogin(string $username, string $password): ?array
    {
        $admin = $this->where('username', $username)->first();

        if (!$admin) {
            return null;
        }

        if (!password_verify($password, $admin['password'])) {
            return null;
        }

        $this->update($admin['id'], ['last_login' => date('Y-m-d H:i:s')]);

        unset($admin['password']);

        return $admin;
    }
}