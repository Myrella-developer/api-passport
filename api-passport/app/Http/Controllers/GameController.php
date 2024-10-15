<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    public function playGame(Request $request) {
        $user = $request->user();

        $dado1 = rand(1, 6);
        $dado2 = rand(1, 6);
        $resultado = ($dado1 + $dado2) === 7 ? 'win' : 'lose';

        $user->games()->create([
            'dado1' => $dado1,
            'dado2' => $dado2,
            'resultado' => $resultado,
            'created_at' => now(),
        ]);

        return response()->json([
            "status" => true,
            "message" => "Game played succesfully",
            "data" => [
                'dado1' => $dado1,
                'dado2' => $dado2,
                'resultado' => $resultado,
            ]
        ]);
    }
}

