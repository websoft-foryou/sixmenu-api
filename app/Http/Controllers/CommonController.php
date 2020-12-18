<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class CommonController extends Controller
{
    private $now;
    public function __construct()
    {
        $this->middleware('auth');
        $this->now = date('Y-m-d H:i:s');
    }

    public function get_categories()
    {
        $user = Auth::user();
        $categories = DB::table('categories')->where('user_id', $user->id)->get();

        $data = [];
        foreach($categories as $category) {
            $category_data = [];
            $category_data['id'] = $category->id;
            $category_data['image'] = $category->category_image;
            $category_data['name_en'] = $category->name_en;
            $category_data['name_hb'] = $category->name_hb;

            $data[] = $category_data;
        }

        return response()->json(['success' => true, 'result' => $data ]);
    }

    public function get_products()
    {
        return response()->json(['success' => true, 'result' => '' ]);
    }
}
