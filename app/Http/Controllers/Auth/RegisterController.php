<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendActivationEmail;
use App\Traits\CaptureIpTrait;
use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;

use App\Models\User;
use DB;
use Mail;

class RegisterController extends Controller
{
    private $now;
    public function __construct()
    {
        $this->now = date('Y-m-d H:i:s');
        $this->gateway = Omnipay::create('PayPal_Rest');
        $this->gateway->setClientId(env('PAYPAL_CLIENT_ID'));
        $this->gateway->setSecret(env('PAYPAL_CLIENT_SECRET'));
        $this->gateway->setTestMode(true); //set it to 'false' when go live
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

        if ($request->membership == '1' && $request->payment_method == 'card') {
            $validator = Validator::make($request->all(), [
                'holder_name' => ['required'],
                'card_number' => ['required'],
                'expire_month' => ['required'],
                'expire_year' => ['required'],
                'cvc_number' => ['required'],
                'billing_country' => ['required'],
                'billing_address' => ['required'],
                'billing_city' => ['required'],
                'billing_post_code' => ['required'],
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
            }
        }

        $client_info = new CaptureIpTrait();
        $client_ipaddress = $client_info->getClientIp();
        $client_country = $client_info->getClientCountry();

        $token_period_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $email_token = Str::random(64);

        $user = User::create([ 'name' => '', 'email' => $request->email, 'phone_number' => $request->phone_number,  'password' => Hash::make($request->password),
            'ip_address'=>$client_ipaddress, 'country'=>$client_country, 'token_period_at'=>$token_period_at, 'email_token'=>$email_token,
            'membership_created_at' =>$this->now, 'membership'=>strval($request->membership) ]);

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

        if ($request->membership == '0') {
            $user->notify(new SendActivationEmail($email_token));
            return response()->json(['success' => true, 'result' => 'User registered!']);
        }
        else {

            if ($request->payment_method == 'paypal') {
                // Pay via paypal
                try {
                    $response = $this->gateway->purchase(array(
                        'amount' => $request->amount,
                        'currency' => env('PAYPAL_CURRENCY'),
                        'returnUrl' => url('payment_success_signup'),
                        'cancelUrl' => url('payment_error_signup'),
                    ))->send();

                    if ($response->isRedirect()) {
                        return response()->json([ 'success' => true, 'result' => ['redirect_url' => $response->getRedirectUrl(), 'user_id'=>$user->id]]);
                    } else {
                        // not successful
                        return response()->json([ 'success' => false, 'result' => $response->getMessage()]);
                    }

                } catch(Exception $e) {
                    return response()->json([ 'success' => false, 'result' => $e->getMessage()]);
                }
            }
            else {
                // Pay via Card
                $formData = array(
                    'name' => $request->holder_name,
                    'number' => $request->card_number,
                    'expiryMonth' => $request->expire_month,
                    'expiryYear' => $request->expire_year,
                    'cvv' => $request->cvc_number,
                    'address1' => $request->billing_address,
                    'country' => $request->billing_country,
                    'city' => $request->billing_city,
                    'postcode' => $request->billing_post_code,
                );

                $card = new CreditCard($formData);
                try {
                    // Send purchase request
                    $response = $this->gateway->purchase([
                        'amount' => $request->amount,
                        'currency' => env('PAYPAL_CURRENCY'),
                        'card' => $card
                    ])->send();

                    // Process response
                    if ($response->isSuccessful()) {
                        // Payment was successful
                        $arr_body = $response->getData();

                        DB::table('payments')->insert(['user_id'=>$user->id, 'payment_id'=>$arr_body['id'], 'payer_id'=>$request->card_number,
                            'payer_email'=>$user->email, 'amount'=>$request->amount, 'payment_status'=>$arr_body['state'], 'created_at'=>$this->now, 'updated_at'=>$this->now]);

                        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Membership', 'page_url'=>'admin/pricing', 'user_action'=>'Upgrade with Card', 'created_at'=>$this->now]);

                        $user->notify(new SendActivationEmail($email_token));
                        return response()->json([ 'success' => true, 'result' => 'OK']);
                    } else {
                        // Payment failed
                        return response()->json([ 'success' => false, 'result' => $response->getMessage()]);
                    }
                } catch(Exception $e) {
                    return response()->json([ 'success' => false, 'result' => $e->getMessage()]);
                }
            }

        }
    }

    public function complete_payment(Request $request)
    {
        $payment_id = $request->paymentId;
        $payer_id = $request->payerId;
        $user_id = $request->userId;

        $transaction = $this->gateway->completePurchase(array(
            'payer_id'             => $payer_id,
            'transactionReference' => $payment_id,
        ));
        $response = $transaction->send();

        if ($response->isSuccessful())
        {
            // The customer has successfully paid.
            $arr_body = $response->getData();
            $payer_email = $arr_body['payer']['payer_info']['email'];
            $amount = $arr_body['transactions'][0]['amount']['total'];
            $payment_status = $arr_body['state'];

            $user = User::find($user_id);
            DB::table('payments')->insert(['user_id'=>$user->id, 'payment_id'=>$arr_body['id'], 'payer_id'=>$arr_body['payer']['payer_info']['payer_id'],
                'payer_email'=>$payer_email, 'amount'=>$amount, 'payment_status'=>$payment_status, 'created_at'=>$this->now, 'updated_at'=>$this->now]);

            DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Signup', 'page_url'=>'admin/signup', 'user_action'=>'Upgrade with Paypal', 'created_at'=>$this->now]);

            $user->notify(new SendActivationEmail($user->email_token));
            return response()->json([ 'success' => true, 'result' => $arr_body['id']]);

        } else
            return response()->json([ 'success' => false, 'result' => $response->getMessage()]);

    }

    public function verify_email(Request $request)
    {
        //header("Access-Control-Allow-Origin: ");

        if ($request->verify_type == 'newverify') {
            $user = DB::table('users')->where('email_token', $request->token)->first();
            if (empty($user))
                return response()->json(['success' => false, 'result' => 'The user is not exist. Please check your email again.']);

            if ($user->email_verified_at != null)
                return response()->json(['success' => false, 'result' => 'The user already verified.']);

            if ($user->token_period_at < date('Y-m-d H:i:s')) {
                $restaurant = DB::table('restaurants')->where('user_id', $user->id)->first();
                if (!empty($restaurant)) {
                    DB::table('restaurant_images')->where('restaurant_id', $restaurant->id)->delete();
                    DB::table('restaurants')->where('id', $restaurant->id)->delete();
                }
                DB::table('users')->where('id', $user->id)->delete();

                return response()->json(['success' => false, 'result' => 'The token period is over. Please register again.']);
            }

            DB::table('users')->where('id', $user->id)->update(['email_verified_at' => date('Y-m-d H:i:s')]);

            $login_result = Auth::loginUsingId($user->id, false);

            if ($login_result)
                return response()->json(['success' => true, 'result' => 'Your email account was verified successfully.']);
            else
                return response()->json(['success' => false, 'result' => 'Email verification was failure. Please check your token again.']);
        }

        if ($request->verify_type == 'reverify') {

            $user = DB::table('users')->where('email_token', $request->token)->first();
            if (empty($user))
                return response()->json(['success' => false, 'result' => 'The user is not exist. Please check your email again.']);

            if ($user->email_verified_at != null)
                return response()->json(['success' => false, 'result' => 'The user already verified.']);

            if ($user->token_period_at < date('Y-m-d H:i:s')) {

                return response()->json(['success' => false, 'result' => 'The token period is over. Please register again.']);
            }

            DB::table('users')->where('id', $user->id)->update(['email_verified_at' => date('Y-m-d H:i:s')]);

            $login_result = Auth::loginUsingId($user->id, false);

            if ($login_result)
                return response()->json(['success' => true, 'result' => 'Your email account was verified successfully.']);
            else
                return response()->json(['success' => false, 'result' => 'Email verification was failure. Please check your token again.']);
        }

    }

}
