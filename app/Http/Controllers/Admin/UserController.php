<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getAll(Request $request)
    {
        $countNeed = $request->countNeed;
        $currentPage = $request->currentPage;
        $searchFilter = $request->searchFilter;
        $nameFilter = $request->name;
        $dateCreateFilter = $request->dateCreate;

        $user_collection = User::where('id', '>', 0);

        if(!$user_collection || $user_collection->count() === 0) {
            return response()->json(['success' => true, 'data' => null, 'count' => 0], 200);
        }

        if($searchFilter && trim($searchFilter)) {
            $search_user = trim($searchFilter);

            $user_id = User::where('name', 'LIKE', '%' . $search_user . '%')
                ->orWhere('phone', 'LIKE', '%' . $search_user . '%')
                ->orWhere('email', 'LIKE', '%' . $search_user . '%')
                ->orWhere('name_organization', 'LIKE', '%' . $search_user . '%')
                ->orWhere('inn', 'LIKE', '%' . $search_user . '%')
                ->orWhere('ogrn', 'LIKE', '%' . $search_user . '%')
                ->orWhere('address', 'LIKE', '%' . $search_user . '%')->get();

            $result_of_search = [];
            foreach ($user_id as $user) {
                $result_of_search[] = $user['id'];
            }

            $user_collection = User::where('id', $result_of_search);
        }

        if($nameFilter || $dateCreateFilter) {
            if($nameFilter) {
                $user_collection = $user_collection->orderBy('name', $nameFilter);
            }
            if($dateCreateFilter) {
                $user_collection = $user_collection->orderBy('created_at', $nameFilter);
            }
        }
        $users = $user_collection->get();

        $count = $users->count();

        if($count === 0) {
            return response()->json(['success' => true, 'data' => null, 'count' => $count], 200);
        }

        //if currentPage  = 1, then start from 0
        if($currentPage === 1) {
            $from = 0;
        }else {
            //or from first row of current page
            $from = $countNeed * ($currentPage - 1);
        }
        //before final row
        $to = $from + $countNeed;

        $data = [];

        for($i = $from, $j = 0; $i < $users->count() && $i < $to; $i++, $j++) {
            $data[$j]['id'] = $users[$i]['id'];
            $data[$j]['name'] = $users[$i]['name'];
            $data[$j]['type'] = $users[$i]['is_juristic']? 'Юр. лицо' : 'Физ. лицо';
            $data[$j]['name_organization'] = $users[$i]['name_organization'];
            $data[$j]['inn'] = $users[$i]['inn'];
            $data[$j]['ogrn'] = $users[$i]['ogrn'];
            $data[$j]['address'] = $users[$i]['address'];
            $data[$j]['phone'] = $users[$i]['phone'];
        }

        return response()->json(['success' => true, 'data' => ['users' => $data, 'count' => $count]], 200);
    }

    public function getOne($id)
    {
        $user_id = $id;

        $user = User::find($user_id);

        $data['id'] = $user['id'];
        $data['name'] = $user['name'];
        $data['type'] = $user['is_juristic']? 'Юр. лицо' : 'Физ. лицо';
        $data['name_organization'] = $user['name_organization'];
        $data['inn'] = $user['inn'];
        $data['ogrn'] = $user['ogrn'];
        $data['address'] = $user['address'];
        $data['phone'] = $user['phone'];

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    public function delete()
    {

    }
}
