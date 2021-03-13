<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function check(Request $request)
    {
        $key = $request->header('api-key');
        if(isset($key) && $key && $key !== "null") {
            $user = Admin::where('api_key', $key)->first();
            if(isset($user)) {
                $id = $user['id'];
                $name = $user['name'];
                $email = $user['email'];
                $position = $user['position'];
                $status = $user['status'];
                if(!$status) {
                    return response()->json(['success' => false, 'error' => 'Status is false, user do not have reasons.'], 200);
                }

                $data = [
                    'id' => $id,
                    'name' => $name,
                    'email' => $email,
                    'position' => $position,
                    'status' => $status,
                ];

                return response()->json(['success' => true, 'data' => $data], 200);
            }
        }
        return response()->json(['success' => false], 200);
    }

    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        $user = Admin::where('email', $email)->first();

        if($user !== null && Hash::check($password, $user->password)) {
            $token = Str::random(60);
            Admin::where('email', $email)->update(['api_key' => $token]);
            //$user->remember = null;//for password recovery

            return response()->json(['success' => true, 'token' => $token], 200);
        }

        return response()->json(['success' => false, "error" => ["password" => ['Неверный логин или пароль']]], 200);
    }

    public function register(Reqeust $request)
    {
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    public function logout(Request $request)
    {
        $user_id = $request->user_id;
        $user = Admin::find($user_id);
        $user->api_key = null;
        $user->save();

        return response()->json(['success' => true, 'data' => 'User logged out'], 200);
    }
}
