<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Game;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    protected $player;
    protected $playerToken;
    protected $otherPlayer;
    protected $game;

    protected function setUp(): void {
        parent::setUp();
        $clientRepository = new PassportClientRepository();
        $clientRepository->createPersonalAccessClient(
            null, 'Test Personal Access Client', 'http://localhost:8080'
        );

        $this->player = User::factory()->create();
        $this->playerToken = $this->player->createToken('PlayerToken')->accessToken;
        $this->otherPlayer = User::factory()->create();


    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_playerCanPlay(){

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->playerToken",
        ])->postJson("api/players/{$this->player->id}/play");

        $response->assertStatus(200)->assertJsonStructure([
            'message', 'Dado 1', 'Dado 2', 'Total'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_cannotplay_OtherplayerGame(){

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->playerToken",
        ])->postJson("api/players/{$this->otherPlayer->id}/play");

        $response->assertStatus(403)->assertJson([
            'message'=> 'You not allowed to play this game',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_player_showGames(){

        Game::create([
            'user_id' => $this->player->id,
            'dado1' => 6,
            'dado2' => 1,
            'resultado' => 'Win',
        ]);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->playerToken",
        ])->getJson("api/players/{$this->player->id}/games");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'Win percentage',
                     'Games played' => [
                         '*' => ['Game number', 'Dado 1', 'Dado 2', 'Resultado']
                     ]
                 ])
                 ->assertJson(['Win percentage' => '100%']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_player_canDeleteGame(){
        Game::create([
            'user_id' => $this->player->id,
            'dado1' => 3,
            'dado2' => 4,
            'resultado' => 'Win',
        ]);
        Game::create([
            'user_id' => $this->player->id,
            'dado1' => 4,
            'dado2' => 6,
            'resultado' => 'lose',
        ]);

        $this->assertCount(2, $this->player->games);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->playerToken",
        ])->deleteJson("api/players/{$this->player->id}/games");

        $response->assertStatus(200)->assertJson([
            'message' => '2 games deleted.'
        ]);
        $this->player = User::find($this->player->id);
        $this->assertCount(0, $this->player->games);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_player_cannotDelete_otherPlayersGame(){
        Game::create([
            'user_id' => $this->otherPlayer->id,
            'dado1' => 3,
            'dado2' => 4,
            'resultado' => 'Win'
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->playerToken",
        ])->deleteJson("api/players/{$this->otherPlayer->id}/games");

        $response->assertStatus(403)->assertJson([
            'message' => "You cannot delete other players' games"
        ]);
    }
}
