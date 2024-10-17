<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use Tests\TestCase;

class PlayerTest extends TestCase
{

    use RefreshDatabase;
    protected $admin;
    protected $player;
    protected $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar el cliente de acceso personal para Passport
        $clientRepository = new PassportClientRepository();
        $clientRepository->createPersonalAccessClient(
            null, 'Test Personal Access Client', 'http://localhost:8080'
        );

        $adminRole = Role::create(['name' => 'admin']);
        $playerRole = Role::create(['name' => 'player']);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'rol' => 'player',
        ]);
        $this->admin->assignRole($adminRole);


        $this->adminToken = $this->admin->createToken('AdminToken')->accessToken;

        // Crear usuario player
        $this->player = User::factory()->create([
            'name' => 'Player',
            'email' => 'player@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->player->assignRole($playerRole); // Asigna el rol player
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_make_newPlayer(){
        $response = $this->postJson('/api/register', [
            'name' => 'NewPlayer',
            'email' => 'newplayer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        //dd($response->getContent());
        $response->assertStatus(201)
        ->assertJson([
            'status' => true,
            'message' => 'User registered successfully',
            'data' => []
        ]);
        // Verifica el número total de usuarios
    $this->assertCount(3, User::all());

    // Busca el nuevo usuario por su correo electrónico
    $user = User::where('email', 'newplayer@example.com')->first();
    // Verifica que el usuario creado tiene los datos correctos
    $this->assertEquals($user->name, 'NewPlayer');
    $this->assertEquals($user->email, 'newplayer@example.com');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_playerLogin(){
        $user = User::factory()->create([
        'email' => 'newplayer@example.com',
        'password' => bcrypt('password'),
    ]);

    // Intentar iniciar sesión
    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password', // Usar la contraseña original
    ]);

    // Verificar que la respuesta sea correcta
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'message',
        'token',
    ]);
    $this->assertNotEmpty($response->json('token'));

    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_playerLogin_withWrongPassword(){
        $user = User::factory()->create([
            'email' => 'newplayer@example.com',
            'password' => bcrypt('password'),
        ]);

        // Intentar iniciar sesión
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '123456',
        ]);

        // Verificar que la respuesta sea correcta
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Password didn't match",
        ]);

    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_playerLogin_withWrongEmail(){
        $user = User::factory()->create([
            'email' => 'newplayer@example.com',
            'password' => bcrypt('password'),
        ]);

        // Intentar iniciar sesión
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@email.com',
            'password' => 'password',
        ]);

        // Verificar que la respuesta sea correcta
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'invalid Email value',
        ]);

    }
}




