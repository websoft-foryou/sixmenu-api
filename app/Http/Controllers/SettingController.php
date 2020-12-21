<?php

namespace App\Http\Controllers;

use App\Notifications\SendActivationEmail;
use App\Traits\CaptureIpTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use Auth;
use DB;


class SettingController extends Controller
{
    private $now;
    public function __construct()
    {
        $this->middleware('auth');
        $this->now = date('Y-m-d H:i:s');
    }

    public function update_email(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'cur_email' => ['required', 'string', 'email', 'max:255'],
            'new_email' => ['required', 'string', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
        }

        if ($user->email != $request->cur_email) {
            return response()->json(['success' => false, 'result' => 'Current email is not match.' ]);
        }

        $users = DB::table('users')->where('id', '!=', $user->id)->where('email', $request->new_email)->first();
        if (! empty($users)) {
            return response()->json(['success' => false, 'result' => 'New email already exist.' ]);
        }

        $client_info = new CaptureIpTrait();
        $client_ipaddress = $client_info->getClientIp();
        $client_country = $client_info->getClientCountry();

        $token_period_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $email_token = Str::random(64);

        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Email Setting', 'page_url'=>'admin/setting', 'user_action'=>'Update',
            'org_value'=>$user->email, 'new_value'=>$request->new_email, 'created_at'=>$this->now]);

        DB::table('users')->where('id', $user->id)->update(['email'=>$request->new_email, 'email_verified_at'=>null, 'email_token'=>$email_token,
            'token_period_at'=>$token_period_at, 'ip_address'=>$client_ipaddress, 'country'=>$client_country, 'updated_at'=>$this->now]);



        $user->notify(new SendActivationEmail($email_token, 'email-reactivate'));

        return response()->json([ 'success' => true, 'result' => 'OK!' ]);
    }


    public function update_password(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'cur_password' => ['required', 'string', 'max:100'],
            'new_password' => ['min:6', 'required', 'max:100'],
            'confirm_password' => ['same:new_password']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
        }

        if (! Hash::check($request->cur_password, $user->password)) {
            return response()->json(['success' => false, 'result' => 'Incorrect currnet password.' ]);
        }

        $new_password = Hash::make($request->new_password);
        DB::table('users')->where('id', $user->id)->update(['password'=>$new_password]);

        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Password Setting', 'page_url'=>'admin/setting', 'user_action'=>'Update', 'created_at'=>$this->now]);

        return response()->json([ 'success' => true, 'result' => 'OK!' ]);
    }
}
