<?php

namespace App\Http\Controllers;

use App\Http\Controllers\query\QueryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function sendMessage(Request $request)
    {
        $result = $this->validateInpts($request);

        if(!$result['success']) {
            return response()->json(['success' => false, 'error' => $result['message']], 200);
        }

        $name = $request->input('name');
        $phone = $request->input('phone');
        $title = $request->input('title');
        $message = $request->input('message');
        $to = 'test@mail.ru';

        mail($to, 'Новое сообщение express, тема: ' . $title,
            'Пользователь имя: ' . $name . ' телефон: ' . $phone .
            '. Сообщение: ' . $message);

        return response()->json(['success' => true], 200);
    }

    public function getCaptcha()
    {
        $query = new QueryController;
        $key = $query->getCaptchaKey();

        return response()->json(['success' => true, 'data' => ['img' => captcha_src(), 'key' => $key] ], 200);
    }

    private function validateInpts($request)
    {
        $rules = [
            'name' => 'required',
            'phone' => 'required',//нужен isphone
            'title' => 'required',
            'message' => 'required',
            'captcha' => 'required|captcha_api:' . $request->input('key') . ',default',
        ];

        $input = $request->only('name', 'phone','title', 'message', 'captcha' );

        $messages = [
            'required' => 'Заполните все обязательные поля.',
            'captcha_api' => 'Код с картинки введен неверно.',
        ];

        //валидация
        $validator = Validator::make($input, $rules, $messages);

        //если валидацию не прошел, то выводим список ошибок по name и текст ошибок
        if ($validator->fails()) {
            return ['success' => false, 'message' => $validator->messages()];
        }
        return ['success' => true];
    }
}
