<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function checkUser(Request $request)
    {
        $user = Auth::guard('api')->user();
        $domain = 'http://' . $_SERVER['SERVER_NAME'];

        if ($user) {
            return response()->json(['success' => true, 'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'nameOrganization' => $user->name_organization,
                'isJuristic' => $user->is_juristic === 1,
                'inn' => $user->inn,
                'ogrn' => $user->ogrn,
                'address' => $user->address,
                'avatar' => $user->avatar_path? $domain . $user->avatar_path : null,
                'ordersCount' => Order::where('user_id', $user->id)->count(),
            ]], 200);
        }
        return response()->json(['success' => false], 200);
    }

    public function authUser(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        $user = User::where('email', $email)->first();

        if($user !== null && Hash::check($password, $user->password)) {
            $token = Str::random(60);
            User::where('email', $email)->update(['api_token' => $token]);
            $user->remember = null;//for password recovery

            return response()->json(['success' => true, 'token' => 'Bearer ' . $token], 200);
        }

        return response()->json(['success' => false, "error" => ["password" => ['Неверный логин или пароль']]], 200);
    }

    public function logout(Request $request, $user_id)
    {
        $user = User::find($user_id);
        $user->api_token = null;
        $user->save();

        return response()->json(['success' => true, 'data' => 'User logged out'], 200);
    }

    public function testQuery()
    {
        return response()->json(['success' => true, 'data' => 'ok'], 200);
    }
}
