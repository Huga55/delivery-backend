<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\query\QueryController;
use App\Models\Contact;
use App\Models\Doc;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function getAll(Request $request)
    {
        $countNeed = $request->countNeed;
        $currentPage = $request->currentPage;
        $nameFilter = $request->name;
        $dateStart = $request->dateStart;
        $dateFinish = $request->dateFinish;
        $searchFilter = $request->searchFilter;
        $user_id = $request->userId;

        if($user_id) {
            $orders_collection = Order::where('user_id', $user_id);
        }else {
            $orders_collection = Order::all();
        }

        if(!$orders_collection || $orders_collection->count() === 0) {
            return response()->json(['success' => true, 'data' => [ 'orders' => null, 'count' => 0 ] ], 200);
        }

        if($searchFilter && trim($searchFilter)) {
            $search_user = trim($searchFilter);

            $orders_collection_search = Order::where('orders.user_id', $id)
                ->leftJoin('contacts', 'orders.id', '=', 'contacts.order_id')
                ->leftJoin('docs', 'orders.id', '=', 'docs.order_id')
                ->select('orders.id as id');

            $orders_id = $orders_collection_search->where('name_cargo', 'LIKE', '%' . $search_user . '%')
                ->orWhere('address_from', 'LIKE', '%' . $search_user . '%')
                ->orWhere('address_to', 'LIKE', '%' . $search_user . '%')
                ->orWhere('price', 'LIKE', '%' . $search_user . '%')
                ->orWhere('contacts.name', 'LIKE', '%' . $search_user . '%')
                ->orWhere('phone', 'LIKE', '%' . $search_user . '%')
                ->orWhere('docs.name', 'LIKE', '%' . $search_user . '%')
                ->orWhere('doc_type', 'LIKE', '%' . $search_user . '%')
                ->groupBy('id')->get();

            $result_of_search = [];
            foreach ($orders_id as $result) {
                $result_of_search[] = $result['id'];
            }

            $orders_collection = Order::where('id', $result_of_search);
        }

        if($dateStart || $dateFinish || $nameFilter) {
            if($dateStart && $dateFinish) {
                $orders_collection = $orders_collection->whereDate('created_at', '>=', $dateStart)->whereDate('created_at', '<=', $dateFinish);
            }
            if($nameFilter) {
                $orders_collection = $orders_collection->orderBy('name_cargo', $nameFilter);
            }
        }

        if($user_id) {
            $orders = $orders_collection->get();
        }else {
            $orders = $orders_collection;
        }

        $count = $orders->count();

        if($count === 0) {
            return response()->json(['success' => true, 'data' => ['orders' => null, 'count' => $count] ], 200);
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

        for($i = $from, $j = 0; $i < $orders->count() && $i < $to; $i++, $j++) {
            $data[$j]['id'] = $orders[$i]['id'];
            $data[$j]['idDostavista'] = $orders[$i]['id_of_dostavista'];
            $data[$j]['dateCreate'] = date('d.m.Y', strtotime( $orders[$i]['created_at'] ));
            $data[$j]['nameCargo'] = $orders[$i]['name_cargo'];
            $data[$j]['type'] = $orders[$i]['type'];
            $data[$j]['nameDelivery'] = 'Достависта';//$orders[$i]['name_organization'];
            $data[$j]['trackNumber'] = $orders[$i]['track_number'];
            $data[$j]['price'] = $orders[$i]['price'];
            $data[$j]['status'] = $orders[$i]['status'];
            $data[$j]['addressDispatch'] = $orders[$i]['address_from'];
            $data[$j]['addressDelivery'] = $orders[$i]['address_to'];
            $data[$j]['docsCount'] = Doc::where('order_id', $orders[$i]['id'])->count();
            $data[$j]['trackNumber'] = $orders[$i]['track_number'];
            $data[$j]['to'] = [];
            $data[$j]['from'] = [];


            $contacts = Contact::where('order_id', $orders[$i]['id'])->get();

            if(count($contacts) > 0) {
                foreach ($contacts as $contact) {
                    $data[$j][$contact->type_contact][] = [
                        'name' => $contact->name,
                        'phone' => $contact->phone,
                    ];
                }
            }
        }

        return response()->json(['success' => true, 'data' => ['orders' => $data, 'count' => $count]], 200);
    }

    public function getOne($id)
    {
        $order_id = $id;

        $order = Order::find($order_id);

        $data['id'] = $order['id'];
        $data['idDostavista'] = $order['id_of_dostavista'];
        $data['dateCreate'] = date('d.m.Y', strtotime( $order['created_at'] ));
        $data['nameCargo'] = $order['name_cargo'];
        $data['type'] = $order['type'];
        $data['nameDelivery'] = 'Достависта';//$orders[$i]['name_organization'];
        $data['trackNumber'] = $order['track_number'];
        $data['price'] = $order['price'];
        $data['status'] = $order['status'];
        $data['addressDispatch'] = $order['address_from'];
        $data['addressDelivery'] = $order['address_to'];
        $data['docsCount'] = Doc::where('order_id', $order_id)->count();
        $data['trackNumber'] = $order['track_number'];
        $data['to'] = [];
        $data['from'] = [];
        $data['docs'] = [];


        $contacts = Contact::where('order_id', $order['id'])->get();

        if(count($contacts) > 0) {
            foreach ($contacts as $contact) {
                $data[$contact->type_contact][] = [
                    'name' => $contact->name,
                    'phone' => $contact->phone,
                ];
            }
        }

        $root_path = $_SERVER['DOCUMENT_ROOT'];
        $domain = 'https://' . $_SERVER['SERVER_NAME'];

        $docs = Doc::where('order_id', $order_id)->get();
        if(count($docs) > 0) {
            foreach ($docs as $doc) {
                $doc->path = $domain . '/public/' . $doc->path;
                $data['docs'][] = $doc;
            }
        }
        return response()->json(['success' => true, 'data' => $data], 200);
    }

    public function fileCreate(Request $request)
    {
        $doc_type = $request->doc_type;
        $doc_name = $request->doc_name;
        $order_id = $request->orderId;

        $root_path = $_SERVER['DOCUMENT_ROOT'];
        $domain = 'https://' . $_SERVER['SERVER_NAME'];

        if ($request->hasFile('file')) {
            $order = Order::find($order_id);
            $user_id = $order->user_id;
            $dostavista_id = $order->id_of_dostavista;

            $random = Str::random(5);

            //save file on the server
            $file = $request->file('file');
            $destinationPath = 'files/users/' . $user_id . '/'. $dostavista_id .'/';
            $filename = $doc_name . $random . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);

            Doc::create([
                'order_id' => $order_id,
                'dostavista_id' => $dostavista_id,
                'user_id' => $user_id,
                'path' => $destinationPath . $filename,
                'name' => $doc_name,
                'doc_type' => $doc_type,
                'type' => 'doc',
            ]);

            return response()->json(['success' => true], 200);
        }
        return response()->json(['success' => false] ,200);
    }

    public function fileDelete(Request $request)
    {
        $file_id = $request->id;
        $file = Doc::find($file_id);

        $root_path = $_SERVER['DOCUMENT_ROOT'];

        unlink($root_path . '/public/' . $file->path);

        $file->delete();

        return response()->json(['success' => true], 200);
    }

    public function delete(Request $request)
    {
        $order_id = $request->id;

        $order = Order::find($order_id);
        $dostavista_id = $order->id_of_dostavista;

        if($dostavista_id) {
            $query = new QueryController;

            $response = $query->cancelOrder($dostavista_id);

            if(!$response->is_successful) {
                return response()->json(['success' => false, 'data' => $response], 200);
            }
        }else {
            $order->delete();
        }

        return response()->json(['success' => true], 200);
    }
}
