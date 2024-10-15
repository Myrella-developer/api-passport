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
                return response()->json(['message' => 'No hay jugadores registrados'], 200);
            }

            return response()->json($users, 200);
        } else {
            return response()->json([
                "message" => "Access to this information is not allowed"
            ], 403);
        }
    }

}
