<?php

namespace App\Http\Controllers\lk;

use App\Http\Controllers\Controller;
use App\Models\Doc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecordController extends Controller
{
    public function getRecords($countNeed, $currentPage, $dateStart, $dateFinish, $doc_type, $type)
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

        $doc_type_arr = [
            'other' => 'Другой',
            'accounting' => 'Бухгалтерский',
        ];

        //get value from english to russia
        $doc_type_rus = $doc_type !== 'null'? $doc_type_arr[$doc_type] : $doc_type;

        $record_collection = Doc::where('user_id', $id)->where('type', $type);

        if($dateStart !== 'null' || $dateFinish !== 'null' || $doc_type !== 'null') {
            if($dateStart !== 'null' && $dateFinish !== 'null') {
                $record_collection = $record_collection->whereDate('created_at', '>=', $dateStart)->whereDate('created_at', '<=', $dateFinish);
            }
            if($doc_type !== 'null') {
                $record_collection = $record_collection->where('doc_type', $doc_type_rus);
            }
        }

        $records = $record_collection->get();

        $count = count($records);

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

        for($i = $from, $j = 0; $i < count($records) && $i < $to; $i++, $j++) {
            $data[$j]['id'] = $records[$i]['id'];
            $data[$j]['order_id'] = $records[$i]['order_id'];
            $data[$j]['dostavista_id'] = $records[$i]['dostavista_id'];
            $data[$j]['path'] = $_SERVER['DOCUMENT_ROOT'] . '/public/' . $records[$i]['path'];
            $data[$j]['name'] = $records[$i]['name'];
            $data[$j]['doc_type'] = $records[$i]['doc_type'];
            $data[$j]['type'] = $records[$i]['type'];
            $data[$j]['dateCreate'] = date('d.m.Y', strtotime( $records[$i]['created_at'] ));
        }

        return response()->json(['success' => true, 'data' => $data, 'count' => $count], 200);
    }
}
