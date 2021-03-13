<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RememberController extends Controller
{
    public function sendLink(Request $request)
    {
        $email = $request->email;
        $URI = $request->URI;

        $user = User::where('email', $email)->first();

        if(!$user) {
            return response()->json(['success' => false, 'data' => 'Введенный email не найден'], 200);
        }

        $end_point = Str::random(20);
        $user->remember = $end_point;
        $user->save();

        //send link to email
        $title = "Восстановление пароля";
        $message = "На данный email пришел запрос о восстановлении пароля. Если вы не запрашивали восстановление, то просто проигнорируйте данное сообщение.

        Если Вы запросили восстановление пароля, то пройдите по данной ссылке и следуйте дальнейшим инструкциям.
        Ссылка: " . $URI . "/remember/" . $end_point;
        $to = $email;

        mail($to['email'], 'Новое сообщение express, тема: ' . $title, $message);

        return response()->json(['success' => true, 'data' => $email], 200);
    }

    public function sendNewPassword(Request $request)
    {
        $remember = $request->token;
        $user = User::where('remember', $remember)->get();

        if(count($user) !== 1) {
            return response()->json(['success' => false], 200);
        }

        $email = $user[0]->email;
        $new_password = $token = Str::random(10);
        $user[0]->password = Hash::make($new_password);
        $user[0]->save();

        //send link to email
        $title = "Восстановление пароля";
        $message = "Ваш новый пароль для входа: " . $new_password;
        $to = $email;

        mail($to, 'Новое сообщение express, тема: ' . $title, $message);

        return response()->json(['success' => true], 200);
    }
}
