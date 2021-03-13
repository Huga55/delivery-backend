<?php

namespace App\Http\Controllers\lk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function changeData(Request $request)
    {
        $id = Auth::guard('api')->user()->id;

        User::where('id', $id)->update([
            'address' => $request->address,
            'inn' => $request->inn,
            'name' => $request->name,
            'name_organization' => $request->name_organization,
            'ogrn' => $request->ogrn,
            'phone' => $request->phone,
        ]);

        $new_password = $request->password;
        if($new_password) {
            User::where('id', $id)->update([
                'password' => Hash::make($new_password),
            ]);
        }

        return response()->json(['success' => true], 200);
    }

    public function changeAvatar(Request $request)
    {
        $id = Auth::guard('api')->user()->id;

        $root_path = $_SERVER['DOCUMENT_ROOT'];
        $domain = 'https://' . $_SERVER['SERVER_NAME'];
        if ($request->hasFile('img')) {
            $user = User::find($id);
            //delete old avatar
            if($user->avatar_path) {
                unlink($root_path . $user->avatar_path);
                $user->update(['avatar_path' => null]);
            }

            //get random name, becouse cache
            $random = Str::random(5);

            //save file on the server
            $file = $request->file('img');
            $destinationPath = 'files/users/' . $id . '/avatar/';
            $filename = $random . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);

            //get path for client
            $path_server = '/public/' . $destinationPath . $filename;

            $user->update(['avatar_path' => $path_server]);

            return response()->json(['success' => true, 'data' => $domain . $path_server], 200);
        }

        return response()->json(['success' => false], 200);
    }

    public function logout()
    {
        $id = Auth::guard('api')->user()->id;

        User::where('id', $id)->update(['api_token' => null]);

        return response()->json(['success' => true], 200);
    }
}
