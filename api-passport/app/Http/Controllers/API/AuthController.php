<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use function Laravel\Prompts\password;

class AuthController extends Controller
{
    const WINNED_GAME = 7;

    public function register(Request $request){

        $request->validate([
            "name" => "nullable|string",
            "email" => "required|string|email|unique:users",
            "password" => "required|confirmed",
        ]);

        $name = $request->name ?? 'anonimo';
        if($name !== 'anonimo') {
            $request->validate([
                'name' => 'unique:users,name',
            ]);
        }

       $user = User::create([
            "name" => $name,
            "email" => $request->email,
            "password" => bcrypt($request->password),
            "registered_at" => Carbon::now(),
        ]);

        $user->assignRole('player');
        return response()->json([
            "status" => true,
            "message" => "User registered successfully",
            "data" => []
        ],201);
    }

    public function login (Request $request){
        $request->validate([
            "email" => "required|email|string",
            "password" => "required"
        ]);

        $user = User::where("email", $request->email)->first();
        if(!empty($user)){

            if(Hash::check($request->password, $user->password)){

              $token = $user->createToken('myToken')->accessToken;
                return response()->json([
                    "status" => true,
                    "message" => "Login succesful",
                    "token" => $token,
                    "data" => []
                ],200);
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "Password didn't match",
                    "data" => []
                ],401);
            }
        }else{
            return response()->json([
                "status" => false,
                "message" => "invalid Email value",
                "data" => []
            ],401);
        }
    }


     public function logout()
     {
         $token = auth()->user()->token();
         $token->revoke();
         return response()->json([
             "status" => true,
             "message" => "User logged out successfully",
         ],200);
     }

     public function showAllPlayers(Request $request) {
        if ($request->user()->hasRole('admin')) {
            $users = User::all();

            if ($users->isEmpty()) {
                return response()->json([
                    'message' => 'No players found'
                ], 200);
            }

            return response()->json($users, 200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Access to this information is not allowed"
            ], 403);
        }
    }


    public function getAverageRanking(Request $request){
        if ($request->user()->hasRole('admin')) {
            $players = User::has('games')->get();
            if($players->isEmpty()){
                return response()->json([
                    'status' => false,
                    'message' => 'No players found'
                ], 404);
            }
                $averageRanking = $players->map(function ($player) {
                $totalGames = $player->games->count();
                $wonGames = $player->games->filter(function ($game) {
                    return ($game->dado1 + $game->dado2) === self::WINNED_GAME;
                })->count();
                $winPercentage = $totalGames > 0 ? ($wonGames / $totalGames) * 100 : 0;

                return $winPercentage;
            });

            $averageOfAverage = $averageRanking->avg();

            return response()->json([
                "status" => true,
                "average_ranking" => $averageOfAverage,
            ], 200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Access to this information is not allowed"
            ], 403);
        }
    }

    public function getLoser(Request $request) {
        if ($request->user()->hasRole('admin')) {
            $players = User::has('games')->get();

            if ($players->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No players found'
                ], 404);
            }
            if ($players->count() === 1) {
                return response()->json([
                    'status' => true,
                    'message' => 'No hay otros jugadores para comparar.',
                    'loser' => $players->first(),
                ], 200);
            }

            $loser = $players->map(function ($player) {
                $totalGames = $player->games->count();
                $wonGames = $player->games->filter(function ($game) {
                    return ($game->dado1 + $game->dado2) === self::WINNED_GAME;
                })->count();

                $winPercentage = $totalGames > 0 ? ($wonGames / $totalGames) * 100 : 0;

                return [
                    'player' => $player,
                    'winPercentage' => $winPercentage
                ];
            })->sortBy('winPercentage')->first();

            return response()->json([
                "status" => true,
                "loser" => $loser,
            ], 200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Access to this information is not allowed"
            ], 403);
        }
    }

    public function getWinner(Request $request) {
        if ($request->user()->hasRole('admin')) {
            $players = User::has('games')->get();

            if ($players->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No players found'
                ], 404);
            }

            if ($players->count() === 1) {
                return response()->json([
                    'status' => true,
                    'message' => 'No hay otros jugadores para comparar.',
                    'winner' => $players->first(),
                ], 200);
            }


            $winner = $players->map(function ($player) {
                $totalGames = $player->games->count();
                $wonGames = $player->games->filter(function ($game) {
                    return ($game->dado1 + $game->dado2) === self::WINNED_GAME;
                })->count();
                $winPercentage = $totalGames > 0 ? ($wonGames / $totalGames) * 100 : 0;

                return [
                    'player' => $player,
                    'winPercentage' => $winPercentage,
                ];
            })->sortByDesc('winPercentage')->first();

            return response()->json([
                'status' => true,
                'winner' => $winner,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Access to this information is not allowed'
            ], 403);
        }
    }

    public function updatePlayer(Request $request, string $id) {
        $userToUpdate = User::findOrFail($id);
        $authUser = $request->user();

        if ($authUser->id !== $userToUpdate->id) {
            return response()->json([
                "message" => "You cannot modify another user's name."
            ], 403);
        }

        $request->validate([
            'name' => 'nullable|string',
        ]);

        $newName = empty($request->name) ? 'anónimo' : $request->name;

        if($newName !== 'anónimo') {
            $existingUser = User::where('name', $newName)->first();
            if ($existingUser && $existingUser->id !== $userToUpdate->id) {
                return response()->json([
                    'message' => 'The name is already in use. Please choose another one.'
                ], 400);
            }
            $request->validate([
                'name' => 'unique:users,name',
            ]);
        }

        $userToUpdate->name = $newName;
        $userToUpdate->save();

        return response()->json([
            'message' => 'New Name is ' . $newName . ', Change Completed'
        ], 200);
    }


}

