<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use DB;

class RestaurantController extends Controller
{
    private $now;
    public function __construct()
    {
        $this->middleware('auth');
        $this->now = date('Y-m-d H:i:s');
    }

    public function get_restaurant(Request $request)
    {
        $user = Auth::user();
        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Restaurants', 'page_url'=>'admin/restuarant', 'user_action'=>'Browse', 'created_at'=>$this->now]);
        $restaurant = DB::table('restaurants')->where('user_id', $user->id)->first();
        return response()->json(['success' => true, 'result' => $restaurant ]);
    }

    public function update_restaurant(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'open_day' => ['required'],
            'name_en' => ['required'],
            'name_hb' => ['required'],
            'address_en' => ['required'],
            'address_hb' => ['required'],
            'location' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
        }

        $mon_from = ($request->open_day['Mon']) ? $request->open_from['Mon'] : '';
        $mon_to   = ($request->open_day['Mon']) ? $request->open_to['Mon'] : '';
        $tue_from = ($request->open_day['Tue']) ? $request->open_from['Tue'] : '';
        $tue_to   = ($request->open_day['Tue']) ? $request->open_to['Tue'] : '';
        $wed_from = ($request->open_day['Wed']) ? $request->open_from['Wed'] : '';
        $wed_to   = ($request->open_day['Wed']) ? $request->open_to['Wed'] : '';
        $thu_from = ($request->open_day['Thu']) ? $request->open_from['Thu'] : '';
        $thu_to   = ($request->open_day['Thu']) ? $request->open_to['Thu'] : '';
        $fri_from = ($request->open_day['Fri']) ? $request->open_from['Fri'] : '';
        $fri_to   = ($request->open_day['Fri']) ? $request->open_to['Fri'] : '';
        $sat_from = ($request->open_day['Sat']) ? $request->open_from['Sat'] : '';
        $sat_to   = ($request->open_day['Sat']) ? $request->open_to['Sat'] : '';
        $sun_from = ($request->open_day['Sun']) ? $request->open_from['Sun'] : '';
        $sun_to   = ($request->open_day['Sun']) ? $request->open_to['Sun'] : '';

        DB::table('restaurants')->where('user_id', $user->id)->update(['name_en'=>$request->name_en, 'name_hb'=>$request->name_hb,
            'address_en'=>$request->address_en, 'address_hb'=>$request->address_hb, 'location'=>$request->location['position'],
            'latitude'=>$request->location['latitude'], 'longitude'=>$request->location['longitude'], 'facebook'=>$request->facebook, 'linkedin'=>$request->linkedin,
            'twitter'=>$request->twitter, 'telegram'=>$request->telegram, 'youtube'=>$request->youtube, 'mon_from'=>$mon_from, 'mon_to'=>$mon_to,
            'tue_from'=>$tue_from, 'tue_to'=>$tue_to, 'wed_from'=>$wed_from, 'wed_to'=>$wed_to, 'thu_from'=>$thu_from, 'thu_to'=>$thu_to,
            'fri_from'=>$fri_from, 'fri_to'=>$fri_to, 'sat_from'=>$sat_from, 'sat_to'=>$sat_to, 'sun_from'=>$sun_from, 'sun_to'=>$sun_to, 'updated_at'=>$this->now]);

        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Restaurants', 'page_url'=>'admin/restuarant', 'user_action'=>'Update', 'created_at'=>$this->now]);
        return response()->json(['success' => true, 'result' => 'Ok' ]);
    }
}
