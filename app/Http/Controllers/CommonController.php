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

    public function get_products(Request $request)
    {
		$user = Auth::user();

		if ($request->category_id == 0) {
			$products = DB::select("SELECT P.id, P.name_en, P.name_hb, P.description_en, P.description_hb, P.price, P.product_type, C.id category_id, C.name_en category_name_en,
                GROUP_CONCAT(PI.file_name SEPARATOR '$$$') product_file_names, GROUP_CONCAT(PI.image_data ORDER BY PI.id ASC SEPARATOR '$$$') product_images
            FROM products P LEFT JOIN categories C ON P.category_id=C.id LEFT JOIN product_images PI ON P.id=PI.product_id
            WHERE P.user_id=:user_id 
            GROUP BY P.id, P.name_en, P.name_hb, P.description_en, P.description_hb, P.price, P.product_type, C.id, C.name_en", ['user_id'=>$user->id]);

		}
		else {
			$products = DB::select("SELECT P.id, P.name_en, P.name_hb, P.description_en, P.description_hb, P.price, P.product_type, C.id category_id, C.name_en category_name_en,
                GROUP_CONCAT(PI.file_name SEPARATOR '$$$') product_file_names, GROUP_CONCAT(PI.image_data ORDER BY PI.id ASC SEPARATOR '$$$') product_images
            FROM products P LEFT JOIN categories C ON P.category_id=C.id LEFT JOIN product_images PI ON P.id=PI.product_id
            WHERE P.user_id=:user_id AND P.category_id=:category_id
            GROUP BY P.id, P.name_en, P.name_hb, P.description_en, P.description_hb, P.price, P.product_type, C.id, C.name_en", ['user_id'=>$user->id, 'category_id'=>$request->category_id]);
		}
		

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
}
