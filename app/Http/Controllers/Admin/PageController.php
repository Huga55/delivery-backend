<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function get()
    {
        $page = Page::find(1);
        if(isset($page) && $page) {
            $data = [
                'name' => $page->name,
                'titleTop' => $page->title_top,
                'titleMain' => $page->title_main,
                'titleDoc' => $page->title_doc,
                'table' => json_decode($page->table_data),
                'posibilities' => json_decode($page->posibility),
                'services' => json_decode($page->services),
                'additional' => json_decode($page->addition),
            ];
            return response()->json(['success' => true, 'data' => $data], 200);
        }
        return response()->json(['success' => false, 'data' => null], 200);
    }

    public function change(Request $request)
    {
        $page = Page::find(1);
        if(isset($page) && $page) {
            $page->update([
                'name' => $request->name,
                'title_top' => $request->titleTop,
                'title_main' => $request->titleMain,
                'title_doc' => $request->titleDoc,
                'table_data' => json_encode($request->table),
                'posibility' => json_encode($request->posibilities),
                'services' => json_encode($request->services),
                'addition' => json_encode($request->additional),
            ]);
        }else {
            Page::create([
                'name' => $request->name,
                'title_top' => $request->titleTop,
                'title_main' => $request->titleMain,
                'title_doc' => $request->titleDoc,
                'table_data' => json_encode($request->table),
                'posibility' => json_encode($request->posibilities),
                'services' => json_encode($request->services),
                'addition' => json_encode($request->additional),
            ]);
        }
        return response()->json(['success' => true], 200);
    }

    public function delete()
    {

    }
}
