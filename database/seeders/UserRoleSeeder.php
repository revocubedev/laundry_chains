<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'admin',
            ],
            [
                'name' => 'staff'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
