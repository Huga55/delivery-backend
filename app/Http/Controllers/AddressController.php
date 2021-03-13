<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\query\QueryController;

class AddressController extends Controller
{
    public function getAddress(Request $request)
    {
        $query = new QueryController();
        $data = $query->getCorrectAddress($request->input('address'));
        if(!isset($data->error)) {
            return response()->json(['success' => true, 'data' => $data], 200);
        }else {
            return response()->json(['success' => false, 'data' => $data], 200);
        }
    }

    public function fromCoordinates(Request $request)
    {
        $query = new QueryController();
        $coordinates = $request->input('coordinates');
        $lat = $coordinates[0];
        $lon = $coordinates[1];

        $data = $query->getCorrectAddressFromCoordinates(['lat' => $lat, 'lon' => $lon]);
        if(!isset($data->error)) {
            return response()->json(['success' => true, 'data' => $data], 200);
        }else {
            return response()->json(['success' => false, 'data' => $data], 200);
        }
    }
}
