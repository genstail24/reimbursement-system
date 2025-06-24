<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Manager One',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ],
            [
                'name' => 'Manager Two',
                'email' => 'managerTwo@example.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ],
            [
                'name' => 'Employee One',
                'email' => 'employee1@example.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
            ],
            [
                'name' => 'Employee Two',
                'email' => 'employee2@example.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
            ],
            [
                'name' => 'Employee Three',
                'email' => 'employee3@example.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => $data['password'],
                ]
            );

            $user->assignRole($data['role']);
        }
    }
}
