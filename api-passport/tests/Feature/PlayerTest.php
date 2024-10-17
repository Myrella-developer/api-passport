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

        $this->player = User::factory()->create([
            'name' => 'Player',
            'email' => 'player@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->player->assignRole($playerRole);
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

        $this->assertCount(3, User::all());


        $user = User::where('email', 'newplayer@example.com')->first();
        $this->assertEquals($user->name, 'NewPlayer');
        $this->assertEquals($user->email, 'newplayer@example.com');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_playerLogin(){
        $user = User::factory()->create([
            'email' => 'newplayer@example.com',
            'password' => bcrypt('password'),
        ]);


        $response = $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'password',
        ]);


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

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '123456',
        ]);

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

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'invalid Email value',
        ]);

    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_namePlayer(){
        $user = User::factory()->create([
            'name' => 'OldName',
        ]);
        $token = $user->createToken('UserToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token])->putJson('/api/players/' . $user->id, [
            'name' => 'New Name',
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'New Name is New Name, Change Completed',
        ]);

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_duplicatedNamePlayer(){
        $user1 = User::factory()->create([
            'name' => 'Player One',
        ]);
        $user2 = User::factory()->create([
            'name' => 'Player Two',
        ]);

        $token = $user1->createToken('UserToken')->accessToken;
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->putJson('/api/players/' . $user1->id, [
            'name' => 'Player Two',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'The name is already in use. Please choose another one.',
        ]);

    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_nameToAnonymous(){
        $user = User::factory()->create([
            'name' => 'OldName'
        ]);

        $token = $user->createToken('UserToken')->accessToken;
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->putJson('/api/players/' . $user->id, [
            'name' => '',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('anónimo', $user->fresh()->name);

    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_forbidden_anotherPlayerName(){
        $user1 = User::factory()->create([
            'name' => 'Player One',
        ]);
        $user2 = User::factory()->create([
            'name' => 'Player Two',
        ]);

        $token = $user1->createToken('UserToken')->accessToken;
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->putJson('/api/players/' . $user2->id, [
            'name' => 'New Name',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => "You cannot modify another user's name.",
        ]);

    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_admin_login(){
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt($password = 'adminpassword'),
        ]);
        $admin->assignRole('admin');

        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => $password,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'token',
        ]);
        $this->assertNotEmpty($response->json('token'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_admin_login_withWrongPassword(){
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('adminpassword'),
        ]);
        $admin->assignRole('admin');

        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Password didn't match",
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_admin_login_withWrongEmail(){
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('adminpassword'),
        ]);
        $admin->assignRole('admin');

        $response = $this->postJson('/api/login', [
            'email' => 'wrong_admin@test.com',
            'password' => 'adminpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'invalid Email value',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_adminShow_allPlayers(){
        $admin = User::factory()->create()->assignRole('admin');
        User::factory()->count(3)->create(['rol' => 'player']);

        $response = $this->actingAs($admin, 'api')->getJson('/api/players');

        $response->assertStatus(200);
        $response->assertJsonCount(6);
    }

//     #[\PHPUnit\Framework\Attributes\Test]
// public function test_adminShow_noPlayersFound(){
//     User::query()->delete();
//     $admin = User::factory()->create()->assignRole('admin'); // Crea un administrador

//     $response = $this->actingAs($admin, 'api')->getJson('/api/players'); // Llama al endpoint

//     $response->assertStatus(200);
//     $response->assertJson([
//         'message' => 'No players found',
//     ]);
// }

}




