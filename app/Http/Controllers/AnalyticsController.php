<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class AnalyticsController extends Controller
{
    private $now;
    public function __construct()
    {
        $this->middleware('auth');
        $this->now = date('Y-m-d H:i:s');
    }

    public function get_user_daily_analytics_data(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $user = Auth::user();

        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'User Analytics', 'page_url'=>'admin/analytics', 'user_action'=>'Browse', 'created_at'=>$this->now]);

        $last_date = date('Y-m-t', strtotime($year . '-' . $month . '-01'));
        $last_date = substr($last_date, 8, 2);

        $data = [];
        for ($i = 1; $i <= intval($last_date); $data[$i] = 0, $i ++);

        $visitors = DB::select("SELECT LEFT(created_at, 10) visit_date, COUNT(1) visit_nums FROM user_logins WHERE user_id='" . $user->id ."'
            AND YEAR(created_at)='$year' AND MONTH(created_at)='$month' GROUP BY LEFT(created_at, 10)");

        foreach ($visitors as $visitor) {
            $visit_date = $visitor->visit_date;
            $date = intval(substr($visit_date, 8, 2));
            $data[$date] = $visitor->visit_nums;
        }
        return response()->json(['success' => true, 'result' => $data ]);
    }

    public function get_user_weekly_analytics_data(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $user = Auth::user();

        $last_date = date('Y-m-t', strtotime($year . '-' . $month . '-01'));
        $last_weeks = $this->get_weeks_from_date($last_date);

        $data = [];
        for ($i = 1; $i <= $last_weeks; $data[$i] = 0, $i ++);

        $visitors = DB::select("SELECT LEFT(created_at, 10) visit_date FROM user_logins WHERE user_id='" . $user->id ."'
            AND YEAR(created_at)='$year' AND MONTH(created_at)='$month' ORDER BY LEFT(created_at, 10)");

        foreach ($visitors as $visitor) {
            $visit_date = $visitor->visit_date;
            $week = $this->get_weeks_from_date($visit_date);
            $data[$week] += 1;
        }
        return response()->json(['success' => true, 'result' => $data ]);
    }


    public function get_user_monthly_analytics_data(Request $request)
    {
        $year = $request->year;
        $user = Auth::user();

        $data = [];
        for ($i = 1; $i <= 12; $data[$i] = 0, $i ++);

        $visitors = DB::select("SELECT LEFT(created_at, 7) visit_date, COUNT(1) visit_nums FROM user_logins WHERE user_id='" . $user->id ."'
            AND YEAR(created_at)='$year' GROUP BY LEFT(created_at, 7) ORDER BY LEFT(created_at, 7)");

        foreach ($visitors as $visitor) {
            $visit_date = $visitor->visit_date;
            $month = intval(substr($visit_date, 5, 2));
            $data[$month] = $visitor->visit_nums;
        }
        return response()->json(['success' => true, 'result' => $data ]);
    }

    public function get_income_daily_analytics_data(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $user = Auth::user();

        $last_date = date('Y-m-t', strtotime($year . '-' . $month . '-01'));
        $last_date = substr($last_date, 8, 2);

        $data = [];
        for ($i = 1; $i <= intval($last_date); $data[$i] = 0, $i ++);

        $incomes = DB::select("SELECT LEFT(created_at, 10) payment_date, COUNT(amount) amount FROM incomes WHERE user_id='" . $user->id ."'
            AND YEAR(created_at)='$year' AND MONTH(created_at)='$month' GROUP BY LEFT(created_at, 10)");

        foreach ($incomes as $income) {
            $payment_date = $income->payment_date;
            $date = intval(substr($payment_date, 8, 2));
            $data[$date] = $income->amount;
        }
        return response()->json(['success' => true, 'result' => $data ]);
    }

    public function get_income_weekly_analytics_data(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $user = Auth::user();

        $last_date = date('Y-m-t', strtotime($year . '-' . $month . '-01'));
        $last_weeks = $this->get_weeks_from_date($last_date);

        $data = [];
        for ($i = 1; $i <= $last_weeks; $data[$i] = 0, $i ++);

        $incomes = DB::select("SELECT LEFT(created_at, 10) payment_date, amount FROM incomes WHERE user_id='" . $user->id ."'
            AND YEAR(created_at)='$year' AND MONTH(created_at)='$month' ORDER BY LEFT(created_at, 10)");

        foreach ($incomes as $income) {
            $payment_date = $income->payment_date;
            $week = $this->get_weeks_from_date($payment_date);
            $data[$week] += $income->amount;
        }
        return response()->json(['success' => true, 'result' => $data ]);
    }


    public function get_income_monthly_analytics_data(Request $request)
    {
        $year = $request->year;
        $user = Auth::user();

        $data = [];
        for ($i = 1; $i <= 12; $data[$i] = 0, $i ++);

        $incomes = DB::select("SELECT LEFT(created_at, 7) payment_date, SUM(amount) amount FROM incomes WHERE user_id='" . $user->id ."'
            AND YEAR(created_at)='$year' GROUP BY LEFT(created_at, 7) ORDER BY LEFT(created_at, 7)");

        foreach ($incomes as $income) {
            $payment_date = $income->payment_date;
            $month = intval(substr($payment_date, 5, 2));
            $data[$month] = $income->amount;
        }
        return response()->json(['success' => true, 'result' => $data ]);
    }


    private function get_weeks_from_date($date)
    {
        $first_date = substr($date, 0, 7) . '-01';
        $week_of_first_date = date('W', strtotime($first_date));

        $week_of_now = date('W', strtotime($date));
        return $week_of_now - $week_of_first_date + 1;
    }

}
