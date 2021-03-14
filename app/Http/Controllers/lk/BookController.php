<?php

namespace App\Http\Controllers\lk;

use App\Http\Controllers\Controller;
use App\Http\Controllers\query\QueryController;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function createAddress(Request $request)
    {
        $id = Auth::guard('api')->user()->id;

        $organization = Organization::create([
            'user_id' => $id,
            'type' => $request->type_person,
            'name_organization' => $request->name_organization,
            'name' => $request->name,
            'phone_work' => $request->phone_work,
            'phone_mobile' => $request->phone_mobile,
            'phone_more' => $request->phone_more,
            'position' => $request->position,
            'inn' => $request->inn,
        ]);

        //add all addresses of organization
        foreach ($request->addresses as $address) {
            Address::create([
                'organization_id' => $organization->id,
                'city' => $address['city'],
                'street' => $address['street'],
                'home' => $address['home'],
                'corpus' => $address['corpus'],
                'structure' => $address['structure'],
                'house_type' => $address['house_type'],
                'apartment' => $address['apartment'],
            ]);
        }

        return response()->json(['status' => true], 200);
    }

    public function changeAddress(Request $request)
    {
        Organization::where('id', $request->id_organization)
            ->update([
                'type' => $request->type_person,
                'name_organization' => $request->name_organization,
                'name' => $request->name,
                'phone_work' => $request->phone_work,
                'phone_mobile' => $request->phone_mobile,
                'phone_more' => $request->phone_more,
                'position' => $request->position,
                'inn' => $request->inn,
            ]);

        foreach ($request->addresses as $address) {
            Address::where('id', $request->id_address)
                ->update([
                    'city' => $address['city'],
                    'street' => $address['street'],
                    'home' => $address['home'],
                    'corpus' => $address['corpus'],
                    'structure' => $address['structure'],
                    'house_type' => $address['house_type'],
                    'apartment' => $address['apartment'],
                ]);
        }
    }

    public function deleteAddress(Request $request)
    {
        if (Auth::check()) {
            /**
             * После проверки уже можешь получать любое свойство модели
             * пользователя через фасад Auth, например id
             */
            $id = Auth::user()->id;
        }else {
            return response()->json(['success' => false], 200);
        }

        Address::find($request->id_address)->delete();

        $organizations_addresses = Organization::find($request->id_organization)->address()->first();

        //if organization do not have any address, then delete this
        if(is_null($organizations_addresses)) {
            Organization::find($request->id_organization)->delete();
        }

        return response()->json(['success' => true, 'data' => $organizations_addresses], 200);
    }

    public function getAddress($countNeed, $currentPage, $nameFilter, $newFilter)
    {
        if (Auth::guard('api')->check()) {
            /**
             * После проверки уже можешь получать любое свойство модели
             * пользователя через фасад Auth, например id
             */
            $id = Auth::guard('api')->user()->id;
        }else {
            return response()->json(['success' => false], 200);
        }

        //get two tables in one row from organization and organization's addresses
        $organizations_collection = DB::table('organizations')
            ->where('user_id', $id)
            ->join('addresses', 'organizations.id', '=', 'addresses.organization_id')
            ->select('organization_id as id_organization', 'addresses.id as id_address', 'name_organization', 'name', 'inn',
                'phone_work', 'phone_mobile', 'phone_more', 'type as type_person', 'position',
                'city', 'street', 'home', 'corpus', 'structure', 'house_type', 'apartment', 'addresses.created_at');



        if($nameFilter !== "null" || $newFilter !== "null") {
            if($nameFilter !== "null" && $newFilter !== "null") {
                $organizations = $organizations_collection->orderBy("name_organization", $nameFilter)->orderBy("created_at", $newFilter)->get();
            }else {
                if($nameFilter !== "null") {
                    $organizations = $organizations_collection->orderBy("name_organization", $nameFilter)->get();
                }
                if($newFilter !== "null") {
                    $organizations = $organizations_collection->orderBy("created_at", $newFilter)->get();
                }
            }
        }else {
            $organizations = $organizations_collection->get();
        }


        $count = count($organizations);
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

        //get options necesaries
        for($i = $from, $j = 0; $i < count($organizations) && $i < $to; $i++, $j++) {
            $city = 'г. ' . $organizations[$i]->city . ', ';
            $street = 'ул. ' . $organizations[$i]->street . ', ';
            $home = $organizations[$i]->home? 'д. ' . $organizations[$i]->home . ', ' : '';
            $corpus = $organizations[$i]->corpus? 'корп. ' . $organizations[$i]->corpus . ', ' : '';
            $structure = $organizations[$i]->structure? 'строение ' . $organizations[$i]->structure . ', ' : '';
            $apartment = $organizations[$i]->apartment? 'кв. ' . $organizations[$i]->apartment . ', ' : '';


            $phone_work = $organizations[$i]->phone_work? $organizations[$i]->phone_work . ', ' : "";
            $phone_mobile = $organizations[$i]->phone_mobile? $organizations[$i]->phone_mobile . ', ' : "";
            $phone_more = 'доп. ' . $organizations[$i]->phone_more? $organizations[$i]->phone_more . ', ' : "";

            $data[$j]['id_organization'] = $organizations[$i]->id_organization;
            $data[$j]['id_address'] = $organizations[$i]->id_address;
            $data[$j]['name_organization'] = $organizations[$i]->name_organization;
            $data[$j]['name'] = $organizations[$i]->name;
            $data[$j]['phone'] = trim($phone_work . $phone_mobile . $phone_more, ', ');

            $data[$j]['inn'] = $organizations[$i]->inn? $organizations[$i]->inn : null;

            //address from dadata
            $full_address = trim($city . $street . $home . $corpus . $structure . $apartment, ', ');
            $query = new QueryController();
            $response = $query->getCorrectAddress($full_address);

            if($response->suggestions) {
                $data[$j]['address'] = $response->suggestions[0]->value;
            }else {
                $data[$j]['address'] = "";
            }



            $data[$j]['city'] = $organizations[$i]->city;
            $data[$j]['street'] = $organizations[$i]->street;
            $data[$j]['home'] = $organizations[$i]->home;
            $data[$j]['corpus'] = $organizations[$i]->corpus;
            $data[$j]['structure'] = $organizations[$i]->structure;
            $data[$j]['house_type'] = $organizations[$i]->house_type;
            $data[$j]['apartment'] = $organizations[$i]->apartment;

            $data[$j]['phone_mobile'] = $organizations[$i]->phone_mobile;
            $data[$j]['phone_work'] = $organizations[$i]->phone_work;
            $data[$j]['phone_more'] = $organizations[$i]->phone_more;
            $data[$j]['position'] = $organizations[$i]->position;
            $data[$j]['type_person'] = $organizations[$i]->type_person;
        }

        return response()->json(['success' => true, 'data' => $data, 'count' => $count], 200);
    }
}
