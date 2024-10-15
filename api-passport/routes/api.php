<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
//Open Routes
Route::post("register", [AuthController::class, "register"]);
Route::post("login", [AuthController::class, "login"]);
//Protected Routes
Route::group([
    "middleware" => ["auth:api"]
], function(){
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


