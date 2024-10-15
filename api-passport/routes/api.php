<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\GameController;

//Open Routes
Route::post("register", [AuthController::class, "register"]);
Route::post("login", [AuthController::class, "login"]);

//Protected Routes
Route::group([
    "middleware" => ["auth:api"]
], function(){
    Route::post('/play', [GameController::class, 'playGame']);
    Route::put('/players/{id}', [GameController::class, 'updatePlayerName']);
    Route::post('/players/{id}/games', [GameController::class, 'rollDices']);
    Route::get('/players/{id}/games', [GameController::class, 'getPlayerGames']);
    Route::delete('/players/{id}/games', [GameController::class, 'deletePlayerGames']);
    Route::get("logout", [AuthController::class, "logout"]);
});
Route::group([
    "middleware" => ["auth:api", "role:admin"]
], function(){

        // Ruta para obtener el listado de todos los jugadores/as y su porcentaje medio de éxitos
        Route::get('/players', [AuthController::class, 'showAllPlayers']); // GET /players

        // Ruta para obtener el listado de jugadas de un jugador/a específico (acceso para todos los usuarios)
        Route::get('/players/{id}/games', [GameController::class, 'getPlayerGames']); // GET /players/{id}/games

        // Ruta para obtener el ranking medio de todos los jugadores/as
        Route::get('/players/ranking', [AuthController::class, 'getAverageRanking']); // GET /players/ranking

        // Ruta para obtener el jugador/a con peor porcentaje de éxito
        Route::get('/players/ranking/loser', [AuthController::class, 'getLoser']); // GET /players/ranking/loser

        // Ruta para obtener el jugador/a con mejor porcentaje de éxito
        Route::get('/players/ranking/winner', [AuthController::class, 'getWinner']); // GET /players/ranking/winner
});
// Route::get('/user', function(Request $request) {
//     return $request->user();
// })->middleware('auth:api');



