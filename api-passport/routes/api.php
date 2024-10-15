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
    Route::post('/players/{id}/games', [GameController::class, 'rollDices']);
    Route::get('/players/{id}/games', [GameController::class, 'getPlayerGames']);
    Route::delete('/players/{id}/games', [GameController::class, 'deletePlayerGames']);
    Route::get("logout", [AuthController::class, "logout"]);
});
Route::group([
    "middleware" => ["auth:api", "role:admin"]
], function(){
    Route::get("players", [AuthController::class, "showAllPlayers"]);
});
// Route::get('/user', function(Request $request) {
//     return $request->user();
// })->middleware('auth:api');



