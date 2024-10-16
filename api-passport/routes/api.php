<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GameController;

// Open Routes
Route::post("register", [AuthController::class, "register"]);
Route::post("login", [AuthController::class, "login"]);

// Protected Routes
Route::group(["middleware" => ["auth:api"]], function() {
    // Ruta para jugar un juego
    Route::post('/players/{id}/play', [GameController::class, 'playGame']); // ruta para jugar --Funciona

    // Ruta para mostrar los juegos de un jugador
    Route::get('/players/{id}/games', [GameController::class, 'showGames']); // mostra todos los juegos --Funciona

    // Ruta para eliminar todos los juegos de un jugador
    Route::delete('/players/{id}/games', [GameController::class, 'deleteGames']); //

    Route::get("logout", [AuthController::class, "logout"]);// funciona

    //Ruta para cambiar el nombre del jugador
    Route::put('/players/{id}', [AuthController::class, 'updatePlayer']); //funciona
});

// Rutas administradas por el middleware de admin
Route::group(["middleware" => ["auth:api", "role:admin"]], function() {
    Route::get('/players', [AuthController::class, 'showAllPlayers']); // funciona y solo admin
    Route::get('/players/ranking', [AuthController::class, 'getAverageRanking']); // funciona y solo admin
    Route::get('/players/ranking/loser', [AuthController::class, 'getLoser']); // funciona y solo admin
    Route::get('/players/ranking/winner', [AuthController::class, 'getWinner']); // funciona y solo admin
});
