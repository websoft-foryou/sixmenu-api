<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Notifications\SendActivationEmail;
use App\Traits\CaptureIpTrait;

use App\Models\User;
use DB;
use Mail;

class RegisterController extends Controller
{

    public function __construct()
    {
        //$this->middleware('guest');
    }


    public function user_register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_name_en' => ['required'],
            'restaurant_description_en' => ['required'],
            'restaurant_address_en' => ['required'],
            'restaurant_position' => ['required'],
            'restaurant_latitude' => ['required'],
            'restaurant_longitude' => ['required'],
            'restaurant_images' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
        }

        $client_info = new CaptureIpTrait();
        $client_ipaddress = $client_info->getClientIp();
        $client_country = $client_info->getClientCountry();

        $token_period_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $email_token = Str::random(64);
        $user = User::create([ 'name' => '', 'email' => $request->email, 'phone_number' => $request->phone_number,  'password' => Hash::make($request->password),
            'ip_address'=>$client_ipaddress, 'country'=>$client_country, 'token_period_at'=>$token_period_at, 'email_token'=>$email_token ]);

        $userid = $user->id;
        $restaurant_id = DB::table('restaurants')->insertGetId(['user_id'=>$userid, 'name_en'=>$request->restaurant_name_en, 'name_hb'=>$request->restaurant_name_hb,
            'description_en'=>$request->restaurant_description_en, 'description_hb'=>$request->restaurant_description_hb, 'address_en'=>$request->restaurant_address_en,
            'address_hb'=>$request->restaurant_address_hb, 'location'=>$request->restaurant_position, 'latitude'=>$request->restaurant_latitude,
            'longitude'=>$request->restaurant_longitude, 'created_at' => date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')]);

        foreach($request->restaurant_images as $restaurant_image) {
            $image_data_array = explode(';', $restaurant_image);
            $name_data = explode('=', $image_data_array[1]);
            $image_name = $name_data[1];
            $image_data = $image_data_array[2];

            DB::table('restaurant_images')->insert(['restaurant_id' => $restaurant_id, 'file_name' => $image_name, 'image_data'=>$image_data,
                'created_at' => date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')]);
        }

//        $data['email_content'] = $email_token;
//        $receiver = 'websoft4u@hotmail.com';
//        $receiver_name = 'USER';
//        Mail::send(['html' => 'normal_email'], $data, function($message) use($receiver, $receiver_name ) {
//            $message->to($receiver, $receiver_name)->subject('Course Confirmation');
//            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
//        });

        $user->notify(new SendActivationEmail($email_token));

        return response()->json([ 'success' => true, 'result' => 'User registered!' ]);
    }

    public function verify_email(Request $request)
    {
        $user = DB::table('users')->where('email_token', $request->token)->first();
        if (empty($user))
            return response()->json([ 'success' => false, 'result' => 'The user is not exist. Please check your email again.' ]);

        if ($user->email_verified_at != null)
            return response()->json([ 'success' => false, 'result' => 'The user already verified.' ]);

        if ($user->token_period_at < date('Y-m-d H:i:s')) {
            $restaurant = DB::table('restaurants')->where('user_id', $user->id)->first();
            if (!empty($restaurant)) {
                DB::table('restaurant_images')->where('restaurant_id', $restaurant->id)->delete();
                DB::table('restaurants')->where('id', $restaurant->id)->delete();
            }
            DB::table('users')->where('id', $user->id)->delete();

            return response()->json(['success' => false, 'result' => 'The token period is over. Please register again.']);
        }

        DB::table('users')->where('id', $user->id)->update(['email_verified_at'=> date('Y-m-d H:i:s')]);

        $login_result =  Auth::loginUsingId($user->id, false);

        if ($login_result)
            return response()->json(['success' => true, 'result' => 'Your email account was verified successfully.']);
        else
            return response()->json(['success' => false, 'result' => 'Email verification was failure. Please check your token again.']);

    }

}
