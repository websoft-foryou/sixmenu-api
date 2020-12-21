<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use DateTime;

class DashboardController extends Controller
{
    private $now;
    public function __construct()
    {
        $this->middleware('auth');
        $this->now = date('Y-m-d H:i:s');
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

        // recent visitor
        $recent_data = $data = [];
        $recent_visitors = DB::select("SELECT SUBSTRING(created_at, 5, 5) activity_date, ip_address, country FROM user_activities
            WHERE restaurant_id='$restaurant_id' AND user_action='Visit' ORDER BY id DESC LIMIT 5" );
        foreach($recent_visitors as $visitor) {
            $data['id'] = $activity->id;
            $data['activity_date'] = $visitor->activity_date;
            $data['description'] = $visitor->country . '(' . $visitor->ip_address . ')';
            $recent_data[] = $data;
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
            'recent_visitors' => $recent_data,
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
