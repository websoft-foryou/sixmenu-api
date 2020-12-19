<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
class HistoryController extends Controller
{
    private $now;
    public function __construct()
    {
        $this->middleware('auth');
        $this->now = date('Y-m-d H:i:s');
    }

    public function get_history(Request $request)
    {
        $user = Auth::user();
        $histories = DB::table('site_histories')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        $data = [];
        foreach($histories as $history) {
            $history_data = [];
            $history_data['date_time'] = substr($history->created_at, 0, 10);
            $history_data['action'] = $history->user_action;
            $history_data['section'] = $history->page_name;
            $history_data['original_value'] = $history->org_value;
            $history_data['new_value'] = $history->new_value;
            $data[] = $history_data;
        }
        return response()->json(['success' => true, 'result' => $data ]);
    }
}
