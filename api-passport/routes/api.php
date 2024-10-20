<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GameController;


Route::post("register", [AuthController::class, "register"]);
Route::post("login", [AuthController::class, "login"]);

Route::group(["middleware" => ["auth:api"]], function() {
    Route::post('/players/{id}/play', [GameController::class, 'playGame']);
    Route::get('/players/{id}/games', [GameController::class, 'showGames']);
    Route::delete('/players/{id}/games', [GameController::class, 'deleteGames']);
    Route::get("logout", [AuthController::class, "logout"]);
    Route::put('/players/{id}', [AuthController::class, 'updatePlayer']);
});

Route::group(["middleware" => ["auth:api", "role:admin"]], function() {
    Route::get('/players', [AuthController::class, 'showAllPlayers']);
    Route::get('/players/ranking', [AuthController::class, 'getAverageRanking']);
    Route::get('/players/ranking/loser', [AuthController::class, 'getLoser']);
    Route::get('/players/ranking/winner', [AuthController::class, 'getWinner']);
});

