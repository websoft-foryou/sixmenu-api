<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    private $now;
    public function __construct()
    {
        $this->middleware('auth');
        $this->now = date('Y-m-d H:i:s');
    }

    public function get_products()
    {
        $user = Auth::user();

        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Products', 'page_url'=>'admin/product', 'user_action'=>'Browse', 'created_at'=>$this->now]);

        $products = DB::select("SELECT P.id, P.name_en, P.name_hb, P.description_en, P.description_hb, P.price, P.product_type, C.id category_id, C.name_en category_name_en,
                GROUP_CONCAT(PI.file_name SEPARATOR '$$$') product_file_names, GROUP_CONCAT(PI.image_data SEPARATOR '$$$') product_images
            FROM products P LEFT JOIN categories C ON P.category_id=C.id LEFT JOIN product_images PI ON P.id=PI.product_id
            WHERE P.user_id=:user_id
            GROUP BY P.id, P.name_en, P.name_hb, P.description_en, P.description_hb, P.price, P.product_type, C.id, C.name_en", ['user_id'=>$user->id]);

        $data = [];
        foreach($products as $product) {
            $product_data = [];
            $product_data['product_id'] = $product->id;
            $product_data['category_id'] = $product->category_id;
            $product_data['category_name'] = $product->category_name_en;
            $product_data['product_name_en'] = $product->name_en;
            $product_data['product_name_hb'] = $product->name_hb;
            $product_data['product_description_en'] = $product->description_en;
            $product_data['product_description_hb'] = $product->description_hb;
            $product_data['product_image'] = explode('$$$', $product->product_images);
            $product_data['product_price'] = $product->price;
            $product_data['product_type'] = explode(',', $product->product_type);

            $data[] = $product_data;
        }
        return response()->json(['success' => true, 'result' => $data ]);
    }

    public function add_product(Request $request)
    {
        if ($request->is_premium == true)
            $validator = Validator::make($request->all(), [
                'category_id' => ['required'],
                'product_name_en' => ['required'],
                'product_name_hb' => ['required'],
                'product_description_en' => ['required'],
                'product_description_hb' => ['required'],
                'product_image' => ['required'],
                'product_price' => ['required', 'numeric'],
                'product_type' => ['required'],
            ]);
        if ($request->is_freemium == true)
            $validator = Validator::make($request->all(), [
                'category_id' => ['required'],
                'product_name_en' => ['required'],
                'product_description_en' => ['required'],
                'product_price' => ['required', 'numeric'],
                'product_type' => ['required'],
            ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
        }

        $user = Auth::user();
        $product_image = $image_name = '';
        $product_name_en = $request->product_name_en == null ? '' : $request->product_name_en;
        $product_name_hb = $request->product_name_hb == null ? '' : $request->product_name_hb;
        $product_description_en = $request->product_description_en == null ? '' : $request->product_description_en;
        $product_description_hb = $request->product_description_hb == null ? '' : $request->product_description_hb;

        $products_en_count = DB::table('products')->where(['user_id'=> $user->id, 'name_en'=> $product_name_en])->count();
        $products_hb_count = DB::table('products')->where(['user_id'=> $user->id, 'name_hb'=> $product_name_hb])->where('name_hb', '!=', '')->count();
        if ($products_en_count > 0 || $products_hb_count > 0) {
            return response()->json([ 'success' => false, 'result' => 'The product already existed. Please enter the another product name (EN or HB)' ]);
        }

        $product_type = '';
        foreach($request->product_type as $type) {
            $product_type .= $type . ",";
        }
        $product_type = substr($product_type, 0, -1);

        $product_id = DB::table('products')->insertGetId(['category_id'=> $request->category_id, 'user_id'=>$user->id, 'name_en'=>$product_name_en, 'name_hb'=>$product_name_hb,
            'description_en'=> $product_description_en, 'description_hb'=>$product_description_hb, 'price'=> $request->product_price, 'product_type' => $product_type,
            'created_at'=>$this->now, 'updated_at'=>$this->now]);

        foreach($request->product_image as $product_image) {
            $image_data_array = explode(';', $product_image);
            $name_data = explode('=', $image_data_array[1]);
            $image_name = $name_data[1];

            DB::table('product_images')->insert(['product_id'=>$product_id, 'file_name'=>$image_name, 'image_data'=>$product_image,
                'created_at'=>$this->now, 'updated_at'=>$this->now]);
        }

        DB::table('site_histories')->insert(['user_id'=>$user->id, 'page_name'=>'Products', 'page_url'=>'admin/product', 'user_action'=>'New',
            'new_value'=>$request->product_name_en, 'created_at'=>$this->now]);

        return response()->json([ 'success' => true, 'result' => 'Product registered!' ]);
    }

    public function update_product(Request $request)
    {
        if ($request->is_premium == true)
            $validator = Validator::make($request->all(), [
                'category_id' => ['required'],
                'product_name_en' => ['required'],
                'product_name_hb' => ['required'],
                'product_description_en' => ['required'],
                'product_description_hb' => ['required'],
                'product_image' => ['required'],
                'product_price' => ['required', 'numeric'],
                'product_type' => ['required'],
            ]);
        if ($request->is_freemium == true)
            $validator = Validator::make($request->all(), [
                'category_id' => ['required'],
                'product_name_en' => ['required'],
                'product_description_en' => ['required'],
                'product_price' => ['required', 'numeric'],
                'product_type' => ['required'],
            ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
        }

        $user = Auth::user();
        $product_id = $request->id;
        $product = DB::table('products')->where('id', $product_id)->first();
        $org_value = empty($product) ? '' : $product->name_en;

        $image_name = '';
        $product_name_en = $request->product_name_en == null ? '' : $request->product_name_en;
        $product_name_hb = $request->product_name_hb == null ? '' : $request->product_name_hb;
        $product_description_en = $request->product_description_en == null ? '' : $request->product_description_en;
        $product_description_hb = $request->product_description_hb == null ? '' : $request->product_description_hb;

        $products_en_count = DB::table('products')->where(['user_id'=> $user->id, 'name_en'=> $product_name_en])->where('id', '!=', $product_id)->count();
        $products_hb_count = DB::table('products')->where(['user_id'=> $user->id, 'name_hb'=> $product_name_hb])->where('name_hb', '!=', '')->where('id', '!=', $product_id)->count();
        if ($products_en_count > 0 || $products_hb_count > 0) {
            return response()->json([ 'success' => false, 'result' => 'The product already existed. Please enter the another product name (EN or HB)' ]);
        }

        $product_type = '';
        foreach($request->product_type as $type) {
            $product_type .= $type . ",";
        }
        $product_type = substr($product_type, 0, -1);

        DB::table('products')->where('id', $product_id)->update(['category_id'=> $request->category_id, 'name_en'=>$product_name_en, 'name_hb'=>$product_name_hb,
            'description_en'=> $product_description_en, 'description_hb'=>$product_description_hb, 'price'=> $request->product_price, 'product_type' => $product_type,
            'updated_at'=>$this->now]);
        DB::table('product_images')->where('product_id', $product_id)->delete();

        foreach($request->product_image as $product_image) {
            $image_data_array = explode(';', $product_image);
            $name_data = explode('=', $image_data_array[1]);
            $image_name = $name_data[1];

            DB::table('product_images')->insert(['product_id'=>$product_id, 'file_name'=>$image_name, 'image_data'=>$product_image,
                'created_at'=>$this->now, 'updated_at'=>$this->now]);
        }

        DB::table('site_histories')->insert(['user_id'=>$user->id, 'page_name'=>'Products', 'page_url'=>'admin/product', 'user_action'=>'Update',
            'org_value'=>$org_value,  'new_value'=>$request->product_name_en, 'created_at'=>$this->now]);

        return response()->json([ 'success' => true, 'result' => 'Product updated!' ]);
    }

    public function remove_product(Request $request)
    {
        $user = Auth::user();
        $product_id = $request->id;
        $product = DB::table('products')->where('id', $product_id)->first();
        $org_value = empty($product) ? '' : $product->name_en;

        DB::table('products')->where('id', $product_id)->delete();
        DB::table('product_images')->where('product_id', $product_id)->delete();
        DB::table('site_histories')->insert(['user_id'=>$user->id, 'page_name'=>'Products', 'page_url'=>'admin/product', 'user_action'=>'Delete',
            'org_value'=>$org_value, 'created_at'=>$this->now]);

        return response()->json([ 'success' => true, 'result' => 'Category removed!' ]);
    }
}
