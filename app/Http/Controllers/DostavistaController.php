<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DostavistaController extends Controller
{
    private $callback_token = '2C00F9B4BE6885224CE37304CE475875EBA64EDB';

    public function get(Request $request)
    {
        if (!isset($_SERVER['HTTP_X_DV_SIGNATURE'])) {
            echo 'Error: Signature not found';
            exit;
        }

        $data = file_get_contents('php://input');

        $signature = hash_hmac('sha256', $data, $this->callback_token);
        if ($signature != $_SERVER['HTTP_X_DV_SIGNATURE']) {
            echo 'Error: Signature is not valid';
            exit;
        }

        if($request->event_type === "order_created") {
            $data_order = $request->order;
            $dostavista_id = $data_order['order_id'];

            $order = Order::where('id_of_dostavista',$dostavista_id )->first();

            $order->update([
                'status' => $data_order['status'],
                'name_cargo' => $data_order['matter'],
                'weight' => $data_order['total_weight_kg'],
                'price' => $data_order['payment_amount'],
                'value_client' => $data_order['insurance_amount'],
                'address_from' => $data_order['points'][0]['address'],
                'address_to' => $data_order['points'][1]['address'],
                'track_number' => $data_order['points']['tracking_url'],
            ]);
        }

        if($request->event_type === "delivery_created") {
            $data_order = $request->delivery;
            $dostavista_id = $data_order['order_id'];

            $order = Order::where('id_of_dostavista',$dostavista_id )->first();

            $order->update([
                'status' => $data_order['status'],
                'price' => $data_order['order_payment_amount'],
            ]);
        }

        return response()->json([], 200);
    }
}
