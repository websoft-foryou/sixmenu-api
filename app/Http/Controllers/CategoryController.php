<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
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
        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Categories', 'page_url'=>'admin/category', 'user_action'=>'Browse', 'created_at'=>$this->now]);

        $data = [];
        foreach($categories as $category) {
            $category_data = [];
            $category_data['image'] = $category->category_image;
            $category_data['category_name_en'] = $category->name_en;
            $category_data['category_name_hb'] = $category->name_hb;
            $category_data['category_id'] = $category->id;
            $data[] = $category_data;
        }
        return response()->json(['success' => true, 'result' => $data ]);
    }

    public function add_category(Request $request)
    {
        if ($request->is_premium == true)
            $validator = Validator::make($request->all(), [
                'category_name_en' => ['required'],
                'category_image' => ['required'],
            ]);
        if ($request->is_freemium == true)
            $validator = Validator::make($request->all(), [
                'category_name_en' => ['required'],
            ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
        }

        $user = Auth::user();
        $category_image = $image_name = '';
        $category_name_en = $request->category_name_en == null ? '' : $request->category_name_en;
        $category_name_hb = $request->category_name_hb == null ? '' : $request->category_name_hb;

        $categories_en_count = DB::table('categories')->where(['user_id'=> $user->id, 'name_en'=> $category_name_en])->count();
        $categories_hb_count = DB::table('categories')->where(['user_id'=> $user->id, 'name_hb'=> $category_name_hb])->where('name_hb', '!=', '')->count();
        if ($categories_en_count > 0 || $categories_hb_count > 0) {
            return response()->json([ 'success' => false, 'result' => 'The category already existed. Please enter the another category name (EN or HB)' ]);
        }

        if (isset($request->category_image) && count($request->category_image) > 0) {
            $category_image = $request->category_image[0];
            $image_data_array = explode(';', $category_image);
            $name_data = explode('=', $image_data_array[1]);
            $image_name = $name_data[1];
        }

        DB::table('categories')->insert(['user_id'=>$user->id, 'name_en'=>$category_name_en, 'name_hb'=>$category_name_hb,
            'file_name'=> $image_name, 'category_image'=>$category_image, 'created_at'=>$this->now, 'updated_at'=>$this->now]);

        DB::table('site_histories')->insert(['user_id'=>$user->id, 'page_name'=>'Categories', 'page_url'=>'admin/category', 'user_action'=>'New',
            'new_value'=>$request->category_name_en, 'created_at'=>$this->now]);

        return response()->json([ 'success' => true, 'result' => 'Category registered!' ]);
    }

    public function update_category(Request $request)
    {
        if ($request->is_premium == true)
            $validator = Validator::make($request->all(), [
                'category_name_en' => ['required'],
                'category_image' => ['required'],
            ]);
        if ($request->is_freemium == true)
            $validator = Validator::make($request->all(), [
                'category_name_en' => ['required'],
            ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
        }

        $user = Auth::user();
        $category_id = $request->id;
        $category = DB::table('categories')->where('id', $category_id)->first();
        $org_value = empty($category) ? '' : $category->name_en;

        $category_image = $image_name = '';
        $category_name_en = $request->category_name_en == null ? '' : $request->category_name_en;
        $category_name_hb = $request->category_name_hb == null ? '' : $request->category_name_hb;

        $categories_en_count = DB::table('categories')->where(['user_id'=> $user->id, 'name_en'=> $category_name_en])->where('id', '!=', $category_id)->count();
        $categories_hb_count = DB::table('categories')->where(['user_id'=> $user->id, 'name_hb'=> $category_name_hb])->where('name_hb', '!=', '')->where('id', '!=', $category_id)->count();
        if ($categories_en_count > 0 || $categories_hb_count > 0) {
            return response()->json([ 'success' => false, 'result' => 'The category already existed. Please enter the another category name (EN or HB)' ]);
        }

        if (isset($request->category_image) && count($request->category_image) > 0) {
            $category_image = $request->category_image[0];
            $image_data_array = explode(';', $category_image);
            $name_data = explode('=', $image_data_array[1]);
            $image_name = $name_data[1];
        }

        DB::table('categories')->where('id', $category_id)->update(['name_en'=>$category_name_en, 'name_hb'=>$category_name_hb,
            'file_name'=> $image_name, 'category_image'=>$category_image, 'updated_at'=>$this->now]);

        DB::table('site_histories')->insert(['user_id'=>$user->id, 'page_name'=>'Categories', 'page_url'=>'admin/category', 'user_action'=>'Update',
            'org_value'=>$org_value, 'new_value' => $request->category_name_en, 'created_at'=>$this->now]);

        return response()->json([ 'success' => true, 'result' => 'Category updated!' ]);
    }

    public function remove_category(Request $request)
    {
        $user = Auth::user();
        $category_id = $request->id;
        $category = DB::table('categories')->where('id', $category_id)->first();
        $org_value = empty($category) ? '' : $category->name_en;

        DB::table('categories')->where('id', $category_id)->delete();
        DB::table('site_histories')->insert(['user_id'=>$user->id, 'page_name'=>'Categories', 'page_url'=>'admin/category', 'user_action'=>'Delete',
            'org_value'=>$org_value, 'created_at'=>$this->now]);

        return response()->json([ 'success' => true, 'result' => 'Category removed!' ]);
    }
}
