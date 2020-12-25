<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Auth;
use DB;
use DateTime;
use Mail;


class DashboardController extends Controller
{
    private $now;
    public function __construct()
    {
        $this->middleware('auth');
        $this->now = date('Y-m-d H:i:s');
    }

    public function send_qrcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
        }

        $user = Auth::user();
        $restaurant = DB::table('restaurants')->where('user_id', $user->id)->first();

        $qrcode = $request->qrcode;
        $receiver_email = $request->email;
        $receiver_name = '';
        $sender_name = 'Administrator@atarit.com';

        $data = ['qrcode'=>$request->qrcode, 'restaurant'=>$restaurant->name_en];
        Mail::send(['html' => 'normal_email'], $data, function($message) use($receiver_email, $receiver_name, $sender_name, $qrcode ) {
            $message->to($receiver_email, $receiver_name)->subject('Our QRCode');
            $message->from(env('MAIL_FROM_ADDRESS'), $sender_name);

            $replace = substr($qrcode, 0, strpos($qrcode, ',')+1);
            $qrcode_image = str_replace($replace, '', $qrcode);
            $qrcode_image = str_replace(' ', '+', $qrcode_image);

            $message->attachData(base64_decode($qrcode_image), 'qrcode.png', ['mime'=>'image/png']);
        });

        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Dashboard', 'page_url'=>'admin/dashboard', 'user_action'=>'Send QRCode',
            'new_value'=>$request->email, 'created_at'=>$this->now]);

        return response()->json(['success' => true, 'result' => 'OK']);
    }

    public function get_recent_data(Request $request)
    {
        $user = Auth::user();
        $restaurant = DB::table('restaurants')->where('user_id', $user->id)->first();
        $restaurant_id = $restaurant->id;

        $today = date('Y-m-d');
        $today_visitors = DB::select("SELECT * FROM user_activities WHERE restaurant_id='$restaurant_id' AND user_action='Visit' AND LEFT(created_at, 10)='$today'");
        $total_visitors = DB::select("SELECT * FROM user_activities WHERE restaurant_id='$restaurant_id' AND user_action='Visit'");
        $total_incomes = DB::select("SELECT sum(amount) total_amount FROM sales WHERE restaurant_id='$restaurant_id' ");

        // last activies
        $last_activities = DB::select("SELECT id, user_action, description, created_at FROM user_activities WHERE restaurant_id='$restaurant_id' ORDER BY id DESC LIMIT 5");
        $activity_data = [];
        foreach($last_activities as $activity) {
            $data['id'] = $activity->id;
            $data['user_action'] = $activity->user_action;
            $data['description'] = $activity->description;
            $data['when'] = $this->time_diff_string($activity->created_at, date('Y-m-d H:i:s'));
            $activity_data[] = $data;
        }

        // income data
        $income_data = [];
        for ($i = 1; $i <= 12; $income_data[$i] = 0, $i ++);
        $incomes = DB::select("SELECT LEFT(created_at, 7) sale_date, SUM(amount) amount FROM sales WHERE restaurant_id='" . $restaurant_id ."'
            AND YEAR(created_at)='". date('Y') . "' GROUP BY LEFT(created_at, 7) ORDER BY LEFT(created_at, 7)");

        foreach ($incomes as $income) {
            $sale_date = $income->sale_date;
            $month = intval(substr($sale_date, 5, 2));
            $income_data[$month] = $income->amount;
        }

        // recent reviews
        $recent_reviews = $data = [];
        $reviews = DB::select("SELECT SUBSTRING(created_at, 5, 5) review_date, description FROM reviews
            WHERE restaurant_id='$restaurant_id' ORDER BY id DESC LIMIT 5" );
        foreach($reviews as $review) {
            $data['id'] = $review->id;
            $data['review_date'] = $review->review_date;
            $data['description'] = $review->description;
            $recent_reviews[] = $data;
        }

        // country_data
        $country_data = $data = [];
        $countries = DB::select("SELECT country, SUM(1) visitor_nums FROM user_activities WHERE restaurant_id='$restaurant_id' AND YEAR(created_at)='". date('Y') . "' GROUP BY country");
        foreach($countries as $country) {
            $data['country'] = $country->country;
            $data['visitor_nums'] = $country->visitor_nums;
            $country_data[] = $data;
        }

        // device data
        $device_data = $data = [];
        $devices = DB::select("SELECT device, SUM(1) visitor_nums FROM user_activities WHERE restaurant_id='$restaurant_id' AND YEAR(created_at)='". date('Y') . "' GROUP BY device");
        foreach($devices as $device) {
            $data['device'] = $device->device;
            $data['visitor_nums'] = $device->visitor_nums;
            $device_data[] = $data;
        }

        // browser data
        $browser_data = $data = [];
        $browsers = DB::select("SELECT browser, SUM(1) visitor_nums FROM user_activities WHERE restaurant_id='$restaurant_id' AND YEAR(created_at)='". date('Y') . "' GROUP BY browser");
        foreach($browsers as $browser) {
            $data['browser'] = $browser->browser;
            $data['visitor_nums'] = $browser->visitor_nums;
            $browser_data[] = $data;
        }

        // platform data
        $platform_data = $data = [];
        $platforms = DB::select("SELECT platform, SUM(1) visitor_nums FROM user_activities WHERE restaurant_id='$restaurant_id' AND YEAR(created_at)='". date('Y') . "' GROUP BY platform");
        foreach($platforms as $platform) {
            $data['platform'] = $platform->platform;
            $data['visitor_nums'] = $platform->visitor_nums;
            $platform_data[] = $data;
        }


        return response()->json(['success' => true, 'result' => array(
            'today_visitors' => count($today_visitors),
            'total_visitors' => count($total_visitors),
            'total_incomes' => $total_incomes[0]->total_amount == null ? 0 : $total_incomes[0]->total_amount,
            'last_activies' => $activity_data,
            'recent_reviews' => $recent_reviews,
            'income_data' => $income_data,
            'country_data' => $country_data,
            'device_data' => $device_data,
            'browser_data' => $browser_data,
            'platform_data' => $platform_data,
        ) ]);
    }

    private function time_diff_string($from, $to, $full = false) {
        $from = new DateTime($from);
        $to = new DateTime($to);
        $diff = $to->diff($from);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day',
            'h' => 'hour', 'i' => 'minute', 's' => 'second');
        $string = array(
            'y' => 'Y', 'm' => 'M', 'w' => 'W', 'd' => 'D',
            'h' => 'h', 'i' => 'm', 's' => 's');
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                //$v = $diff->$k . '' . $v . ($diff->$k > 1 ? 's' : '');
                $v = $diff->$k . '' . $v . ($diff->$k > 1 ? '' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}
