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
    //POST [name, email, passworđ]
    public function register(Request $request){
        //Validation
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
        //Create user
       $user = User::create([
            "name" => $name,
            "email" => $request->email,
            "password" => bcrypt($request->password),
            "registered_at" => Carbon::now(),
        ]);

        $user->assignRole('player');
        return response()->json([
            "status" => true,
            "message" => "User registered succesfully",
            "data" => []
        ]);
    }
     //POST [email, password]
    public function login (Request $request){
        $request->validate([
            "email" => "required|email|string",
            "password" => "required"
        ]);
        // User object
        $user = User::where("email", $request->email)->first();
        if(!empty($user)){
            // User exists
            if(Hash::check($request->password, $user->password)){
                //Password matched
              $token = $user->createToken('myToken')->accessToken;
                return response()->json([
                    "status" => true,
                    "message" => "Login succesful",
                    "token" => $token,
                    "data" => []
                ]);
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "Password didn't match",
                    "data" => []
                ]);
            }
        }else{
            return response()->json([
                "status" => false,
                "message" => "invalid Email value",
                "data" => []
            ]);
        }
    }

     //GET [Auth: Token]
     public function logout()
     {
         $token = auth()->user()->token();
         $token->revoke();
         return response()->json([
             "status" => true,
             "message" => "User logged out successfully",
         ]);
     }

     public function showAllPlayers(Request $request) {
        if ($request->user()->hasRole('admin')) {
            $users = User::all();

            if ($users->isEmpty()) {
                return response()->json(['message' => 'No players found'], 200);
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
                return response()->json(['status' => false, 'message' => 'No players found'], 404);
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
                return response()->json(['status' => false, 'message' => 'No players found'], 404);
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
            })->sortBy('winPercentage')->first(); // Ordenar por porcentaje de victorias

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
}

