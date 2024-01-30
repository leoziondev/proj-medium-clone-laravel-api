<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $payload = $request->validate([
            "name"      => "required|min:2|max:60",
            "email"     => "required|email|unique:users,email",
            "password"  => "required|min:6|max:50|confirmed"
        ]);

        try {
            $payload["password"] = Hash::make($payload["password"]);
            User::create($payload);
            return ["status" => 200, "message" => "Account created successfully!"];
        } catch (\Exception $err) {
            Log::info("register_err =>" . $err->getMessage());
            return response()->json(["message" => "Somenthing went wrong. Pls try again"], 500);
        }
    }

    public function login(Request $request)
    {
        $payload = $request->validate([
            "email"     => "required|email",
            "password"  => "required"
        ]);

        try {
            $user = User::where("email", $payload["email"])->first();
            if ($user) {
                // Check password
                if (!Hash::check($payload["password"], $user->password)) {
                    return response()->json(["status" => 401, "message" => "Invalid credentials"], 401);
                }

                // Generate token
                $token = $user->createToken("web")->plainTextToken;
                $authRes = array_merge($user->toArray(), ["token" => $token]);
                return ["status" => 200, "message" => "Logged in successfully!", "user" => $authRes];
            }

            return ["status" => 401, "message" => "No user found with this email."];
        } catch (\Exception $err) {
            Log::info("login_err =>" . $err->getMessage());
            return response()->json(["message" => "Somenthing went wrong. Pls try again"], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return ["status" => 200, "message" => "Logged out successfully!"];
        } catch (\Exception $err) {
            Log::info("logout_err =>" . $err->getMessage());
            return response()->json(["message" => "Somenthing went wrong. Pls try again"], 500);
        }
    }
}
