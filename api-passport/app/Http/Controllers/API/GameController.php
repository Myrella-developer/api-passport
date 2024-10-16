<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    const WINNED_GAME = 7;

    // Show all games of the player
    public function showGames(Request $request, string $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userId = $request->user()->id;

        if ($userId !== $user->id) {
            abort(403, 'You cannot view other games');
        }

        $games = $user->games;

        if ($games->isEmpty()) {
            return response()->json(['message' => 'No games recorded'], 200);
        }

        $totalGames = $games->count();
        $wonGames = $games->filter(function ($game) {
            return ($game->dado1 + $game->dado2) === self::WINNED_GAME;
        })->count();
        $winPercentage = $totalGames > 0 ? ($wonGames / $totalGames) * 100 : 0;

        $formattedGames = $games->map(function ($game, $index) {
            return [
                'Game number' => $index + 1,
                'Dado 1' => $game->dado1,
                'Dado 2' => $game->dado2,
                'Resultado' => $game->resultado ? 'Win' : 'Lose',
            ];
        });

        return response()->json([
            'Win percentage' => $winPercentage . "%",
            'Games played' => $formattedGames
        ], 200);
    }

    // Play a game
    public function playGame(Request $request, string $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userId = $request->user()->id;

        if ($userId !== $user->id) {
            abort(403, 'You not allowed to play this game');
        }

        $dado1 = rand(1, 6);
        $dado2 = rand(1, 6);
        $resultado = ($dado1 + $dado2) === self::WINNED_GAME;

        Game::create([
            'user_id' => $userId,
            'dado1' => $dado1,
            'dado2' => $dado2,
            'resultado' => $resultado,
        ]);

        return response()->json([
            'message' => $resultado ? 'You win! Your dice values:' : 'You lose. Your dice values:',
            'Dado 1' => $dado1,
            'Dado 2' => $dado2,
            'Total' => $dado1 + $dado2,
        ], 200);
    }

    // Delete all games of the player
    public function deleteGames(Request $request, string $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userId = $request->user()->id;

        if ($userId !== $user->id) {
            abort(403, 'You cannot delete other players\' games');
        }

        $games = $user->games;

        if ($games->isEmpty()) {
            return response()->json(['message' => 'No games to delete'], 200);
        }

        $deletedCount = $user->games()->delete();
        return response()->json(['message' => "{$deletedCount} games deleted."], 200);
    }
}

