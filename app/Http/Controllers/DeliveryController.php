<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Contact;
use App\Models\Doc;
use App\Http\Controllers\query\QueryController;
use Illuminate\Support\Facades\Auth;


class DeliveryController extends Controller
{
    public function create(Request $request)
    {
        if(Auth::guard('api')->check()) {
            $user_id = Auth::guard('api')->user()->id;
        }else {
            $user_id = 0;//if without auth, then nobody's order
        }

        $address_from = $request->input('address_dispatch');//from
        $address_to = $request->input('address_delivery');//to
        $persons_data_dispatch = $request->input('dispatchData');//from
        $persons_data_delivery = $request->input('deliveryData');//to
        $type_of_cargo = $request->input('cargo_type');// docs or cargo
        $name_cargo = $request->input('name_cargo');
        // for docs before 100, before 400, after 400
        // for cargo before 1, before 5, before 10, before 15 or exactly
        $weight = ceil($request->input('weight'));

        $length = null;
        $width = null;
        $height = null;
        $size = null;

        if($type_of_cargo !== "docs") {
            $is_oversize = $request->input('size_cancel');
            if(!$is_oversize) {
                $length = $request->input('length');
                $width = $request->input('width');
                $height = $request->input('height');
            }
            $is_size_exact = $request->input('size_exact');
            if($is_size_exact) {
                $size = $request->input('size');
            }
        }
        $date_take = $request->input('date_dispatch');
        $date_delivery = $request->input('date_delivery');

        $time = $request->input('time');

        $value_client = $request->input('val');
        $pay_type = $request->input('payment');

        //save in sql start data
        $order = Order::create([
            'id_of_dostavista' => null,
            'status' => null,
            'user_id' => $user_id,
            'name_cargo' => $type_of_cargo === "docs"? "документы" : $name_cargo,
            'address_from' => $address_from,
            'address_to' => $address_to,
            'type' => $type_of_cargo,//$type_of_cargo,//change type for BD
            'weight' => $type_of_cargo === "docs"? 1 : $weight,
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'size' => $size,
            'date_take' => $date_take,
            'date_delivery' => $date_delivery,
            'value_client' => $value_client,
            'pay_type' => $pay_type,
            'price' => null,
            'track_number' => null,
            'time' => json_encode($time),
        ]);

        //save in sql contacts (names, phones)
        $this->add_contacts( $persons_data_dispatch, "from", $order->id);
        $this->add_contacts( $persons_data_delivery, "to", $order->id);

        //create class of query
        $query = new QueryController();

        //data for query
        $data = [
            'matter' => $type_of_cargo === "docs"? "документы" : $name_cargo,//$type_of_cargo,
            'total_weight_kg' => $weight? floor($weight) : 1,
            'insurance_amount' => $value_client,
            'payment_method' => 'cash',//$pay_type,
            'points' => [
                [
                    'address' => $address_from,
                    'contact_person' => [
                        'name' => $persons_data_dispatch['names'][0],
                        'phone' => $persons_data_dispatch['phones'][0][0],
                    ],
                    'client_order_id' => $order->id,
                    'required_start_datetime' => $time['delivery_from'],
                    'required_finish_datetime' => $time['dispatch_to'],
                    'note' => null,
                    'building_number' => null,
                    'entrance_number' => null,
                    'floor_number' => null,
                    'apartment_number' => null,
                ],
                [
                    'address' => $address_to,
                    'contact_person' => [
                        'name' => $persons_data_delivery['names'][0],
                        'phone' => $persons_data_delivery['phones'][0][0],
                    ],
                    'client_order_id' => $order->id,
                    'required_start_datetime' => $time['delivery_from'],
                    'required_finish_datetime' => $time['delivery_to'],
                    'note' => null,
                    'building_number' => null,
                    'entrance_number' => null,
                    'floor_number' => null,
                    'apartment_number' => null,
                ],
            ],

        ];

        //set new order in dostavista
        $response = $query->setOrder($data);

        if($response->is_successful) {
            $price = floor($response->order->payment_amount);
            $id_from_api = $response->order->order_id;
            $status = $response->order->status;
            //set data from dostavista to sql
            $order->price = $price;
            $order->id_of_dostavista = $id_from_api;
            $order->status = $status;
            $order->save();

            return response()->json(['success' => true, 'data' => $response], 200);
        }else {
            $order->errors = json_encode($response);
            $order->save();
            return response()->json(['success' => false, 'error' => $response], 200);
        }
    }

    public function repeatOrder(Request $request)
    {
        $order_id = $request->order_id;
        $user_id = Auth::guard('api')->user()->id;

        $old_order = Order::find($order_id);

        //save in sql start data
        $order = Order::create([
            'id_of_dostavista' => null,
            'status' => null,
            'user_id' => $old_order->user_id,
            'name_cargo' => $old_order->name_cargo,
            'address_from' => $old_order->address_from,
            'address_to' => $old_order->address_to,
            'type' => $old_order->type,//$type_of_cargo,//change type for BD
            'weight' => $old_order->weight,
            'length' => $old_order->length,
            'width' => $old_order->width,
            'height' => $old_order->height,
            'size' => $old_order->size,
            'date_take' => $old_order->date_take,
            'date_delivery' => $old_order->date_delivery,
            'value_client' => $old_order->value_client,
            'pay_type' => $old_order->pay_type,
            'price' => null,
            'track_number' => null,
        ]);

        //create array of mans and phones
        $mans = $old_order->contacts()->get();
        foreach ($mans as $contact) {
            Contact::create([
                'order_id' => $order->id,
                'name' => $contact->name,
                'phone' => $contact->phone,
                'type_contact' => $contact->type_contact
            ]);
        }


        //create class of query
        $query = new QueryController();

        //data for query
        $data = [
            'matter' => $old_order->name_cargo,
            'total_weight_kg' => $old_order->weight,
            'insurance_amount' => $old_order->value_client,
            'payment_method' => 'cash',//$pay_type,
            'points' => [
                [
                    'address' => $old_order->address_from,
                    'contact_person' => [
                        'name' => $mans[0]->name,
                        'phone' => $mans[0]->phone,
                    ],
                    'client_order_id' => $order->id,
                    'required_start_datetime' => null,
                    'required_finish_datetime' => null,
                    'note' => null,
                    'building_number' => null,
                    'entrance_number' => null,
                    'floor_number' => null,
                    'apartment_number' => null,
                ],
                [
                    'address' => $old_order->address_to,
                    'contact_person' => [
                        'name' => $old_order->contacts()->where('type_contact', 'to')->get()[0]->name,
                        'phone' => $old_order->contacts()->where('type_contact', 'to')->get()[0]->phone,
                    ],
                    'client_order_id' => $order->id,
                    'required_start_datetime' => null,
                    'required_finish_datetime' => null,
                    'note' => null,
                    'building_number' => null,
                    'entrance_number' => null,
                    'floor_number' => null,
                    'apartment_number' => null,
                ],
            ],

        ];

        //set new order in dostavista
        $response = $query->setOrder($data);

        if($response->is_successful) {
            $price = floor($response->order->payment_amount);
            $id_from_api = $response->order->order_id;
            $status = $response->order->status;
            //set data from dostavista to sql
            $order->price = $price;
            $order->id_of_dostavista = $id_from_api;
            $order->status = $status;
            $order->save();

            return response()->json(['success' => true, 'data' => $response], 200);
        }else {
            $order->errors = json_encode($response);
            $order->save();
            return response()->json(['success' => false, 'error' => $response], 200);
        }
    }

    public function getOrders(Request $request)
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
        $countNeed = $request->countNeed;
        $currentPage = $request->currentPage;
        $dateStart = $request->dateStart;
        $dateFinish = $request->dateFinish;
        $statusFilter = $request->statusFilter;
        $priceFilter = $request->priceFilter;
        $searchFilter = $request->searchFilter;

        $orders_collection = Order::where('user_id', $id);

        //if user do not have orders
        if(!$orders_collection || count($orders_collection->get()) === 0) {
            return response()->json(['success' => true, 'data' => null, 'count' => 0], 200);
        }

        if($searchFilter && trim($searchFilter)) {
            $search_user = trim($searchFilter);

            $orders_collection_search = Order::where('orders.user_id', $id)
                ->leftJoin('contacts', 'orders.id', '=', 'contacts.order_id')
                ->leftJoin('docs', 'orders.id', '=', 'docs.order_id')
                ->select('orders.id as id');
                /*->select('orders.id as id', 'orders.id_of_dostavista', 'orders.name_cargo', 'orders.address_from',
                'orders.address_to', 'orders.price', 'contacts.name', 'contacts.phone', 'docs.name as doc_name', 'docs.doc_type')*/;

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

        if($dateStart || $dateFinish || $statusFilter || $priceFilter) {
            if($dateStart && $dateFinish) {
                $orders_collection = $orders_collection->whereDate('created_at', '>=', $dateStart)->whereDate('created_at', '<=', $dateFinish);
            }
            if($statusFilter) {
                $orders_collection = $orders_collection->where('status', $statusFilter);
            }
            if($priceFilter) {
                $orders_collection = $orders_collection->orderBy('price', $priceFilter);
            }
        }

        $orders = $orders_collection->get();

        $count = count($orders);

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

        for($i = $from, $j = 0; $i < count($orders) && $i < $to; $i++, $j++) {
            $data[$j]['id'] = $orders[$i]['id'];
            $data[$j]['idDostavista'] = $orders[$i]['id_of_dostavista'];
            $data[$j]['dateCreate'] = date('d.m.Y', strtotime( $orders[$i]['created_at'] ));
            $data[$j]['nameCargo'] = $orders[$i]['name_cargo'];
            $data[$j]['type'] = $orders[$i]['type'];
            $data[$j]['nameOrganization'] = $orders[$i]->contacts()->where('type_contact', 'to')->get()[0]->name;
            $data[$j]['nameDelivery'] = 'Достависта';//$orders[$i]['name_organization'];
            $data[$j]['trackNumber'] = $orders[$i]['track_number'];
            $data[$j]['price'] = $orders[$i]['price'];
            $data[$j]['status'] = $orders[$i]['status'];
        }

        return response()->json(['success' => true, 'data' => $data, 'count' => $count], 200);
    }

    public function getDocsOfOrder($id)
    {
        $docs = Doc::where('dostavista_id', $id)->get();

        $data = [];
        for($i = 0; $i < count($docs); $i++) {
            $data[$i]['id'] = $docs[$i]['id'];
            $data[$i]['order_id'] = $docs[$i]['order_id'];
            $data[$i]['dostavista_id'] = $docs[$i]['dostavista_id'];
            $data[$i]['path'] = $_SERVER['DOCUMENT_ROOT'] . '/public/' . $docs[$i]['path'];
            $data[$i]['name'] = $docs[$i]['name'];
            $data[$i]['type_doc'] = $docs[$i]['doc_type'];
            $data[$i]['type'] = $docs[$i]['type'];
            $data[$i]['created_at'] = date('d.m.Y', strtotime( $docs[$i]['created_at']) );
        }

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    public function getPriceFromDostavista(Request $request)
    {
        $address_from = $request->address_dispatch;
        $address_to = $request->address_delivery;
        $cargo_type = $request->cargo_type;
        $matter = $cargo_type === "doc"? "Документы" : $request->name_cargo;
        $total_weight_kg = $cargo_type === "doc" || !$request->weight? 0 : $request->weight;
        $insurance_amount = $request->val;

        $data = [
            'matter' => $matter,
            'total_weight_kg' => $total_weight_kg,
            'insurance_amount' => $insurance_amount,
            'points' => [
                ['address' => $address_from],
                ['address' => $address_to],
            ]
        ];

        $query = new QueryController();
        $response = $query->getPrice($data);
        if($response->is_successful) {
            return response()->json(['success' => true, 'data' => $response], 200);
        }else {
            return response()->json(['success' => false, 'data' => $response], 200);
        }
    }

    public function lastData()
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

        $lastOrders = Order::where('user_id', $id)->orderby('id', 'desc')->limit(2)->get();

        $addresses = [];

        $contacts = [];

        foreach ($lastOrders as $lastOrder) {
            $addresses[] = [
                'addressDispatch' => $lastOrder->address_from? $lastOrder->address_from : null,
                'addressDelivery' => $lastOrder->address_to? $lastOrder->address_to : null,
            ];

            $last_contact_from = $lastOrder->contacts()->where('type_contact', 'from')->first();
            $last_contact_to = $lastOrder->contacts()->where('type_contact', 'to')->first();

            $contacts[] = [
                'contactDispatch' => $last_contact_from? [
                    'name' =>  $last_contact_from->name,
                    'phone' => $last_contact_from->phone,
                ] : null,
                'contactDelivery' => $last_contact_to? [
                    'name' => $last_contact_to->name,
                    'phone' => $last_contact_to->phone,
                ] : null,
            ];
        }

        return response()->json(['success' => true, 'data' => ['addresses' => $addresses, 'contacts' => $contacts] ], 200);
    }


    private function add_contacts($contacts, $type_contact, $order_id)
    {
        for($i = 0; $i < count($contacts['names']); $i++) {
            foreach ($contacts['phones'][$i] as $phone) {
                Contact::create([
                    'order_id' => $order_id,
                    'name' => $contacts['names'][$i],
                    'phone' => $phone,
                    'type_contact' => $type_contact,
                ]);
            }
        }
    }

}
