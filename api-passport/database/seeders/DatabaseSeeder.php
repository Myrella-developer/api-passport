<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        Permission::create(['name' => 'manage players']);
        Permission::create(['name' => 'play games']);

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo('manage players');

        $player = Role::create(['name' => 'player']);
        $player->givePermissionTo('play games');

        $user = User::find(1);
        $user->assignRole('admin');


    }
}
