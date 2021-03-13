<?php

namespace App\Http\Controllers;

use App\Http\Controllers\query\QueryController;
use Illuminate\Http\Request;
use App\Http\Controllers;
use \Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
    	//физ или юрид
    	$type_of_user = $request->input('is_juristic') === "true";

        //правила валидации
        if($type_of_user) {
            $rules = [
                'name' => 'required',
                'phone' => 'required',//нужен isphone
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'name_organization' => 'required',
                'inn' => 'required',
                'ogrn' => 'required',
                'address' => 'required',
            ];
            //перечисление инпутов для валидации из POST запроса
            $input = $request->only('name', 'phone','email', 'password', 'name_organization' , 'inn', 'ogrn', 'address');
        }else {
            $rules = [
                'name' => 'required',
                'phone' => 'required',//нужен isphone
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
            ];
            //перечисление инпутов для валидации из POST запроса
            $input = $request->only('name', 'phone','email', 'password');
        }

        $messages = [
            'required'    => 'Заполните все обязательные поля.',
            'email'    => 'Email введен неверно.',
            'email.unique' => 'Данный email уже используется.',
            'phone.unique' => 'Данный телефон уже используется.',
        ];

        //валидация
        $validator = Validator::make($input, $rules, $messages);

        //если валидацию не прошел, то выводим список ошибок по name и текст ошибок
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()], 200);
        }

        //валидация пройдена, регистрация успешна
        if($type_of_user) {
            User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'name_organization' => $request->name_organization,
                'inn' => $request->inn,
                'ogrn' => $request->ogrn,
                'address' => $request->address,
                'is_juristic' => $type_of_user,
            ]);
        }else {
            User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_juristic' => $type_of_user,
            ]);
        }

        return response()->json(['success' => true], 200);
    }

    public function getNameOrganization($inn)
    {
        $query = new QueryController();
        $data = $query->getNameOrganization($inn);
        if(!isset($data->error)) {
            return response()->json(['success' => true, 'data' => $data], 200);
        }else {
            return response()->json(['success' => false, 'data' => $data], 200);
        }
    }
}
