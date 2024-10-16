<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        Permission::firstOrCreate(['name' => 'manage players']);
        Permission::firstOrCreate(['name' => 'play games']);

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo('manage players');

        $playerRole = Role::firstOrCreate(['name' => 'player']);
        $playerRole->givePermissionTo('play games');

        // Crear el usuario administrador
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->assignRole('admin');

        // Crear varios usuarios jugadores
        $playersData = [
            ['email' => 'player1@example.com', 'name' => 'Player One'],
            ['email' => 'player2@example.com', 'name' => 'Player Two'],
            ['email' => 'player3@example.com', 'name' => 'Player Three'],
            ['email' => 'player4@example.com', 'name' => 'Player Four'],
            ['email' => 'player5@example.com', 'name' => 'Player Five'],
        ];

        foreach ($playersData as $playerData) {
            $playerUser = User::firstOrCreate(
                ['email' => $playerData['email']],
                [
                    'name' => $playerData['name'],
                    'password' => Hash::make('password'), // Contraseña por defecto
                ]
            );
            $playerUser->assignRole('player');
        }
    }
}
