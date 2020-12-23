<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;

use Auth;
use DB;

class MembershipController extends Controller
{
    private $now;
    public function __construct()
    {
        $this->middleware('auth');
        $this->now = date('Y-m-d H:i:s');

        $this->gateway = Omnipay::create('PayPal_Rest');
        $this->gateway->setClientId(env('PAYPAL_CLIENT_ID'));
        $this->gateway->setSecret(env('PAYPAL_CLIENT_SECRET'));
        $this->gateway->setTestMode(true); //set it to 'false' when go live
    }

    public function get_membership()
    {
        $user = Auth::user();
        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Membership', 'page_url'=>'admin/pricing', 'user_action'=>'Browse', 'created_at'=>$this->now]);
        $user = DB::table('users')->where('id', $user->id)->first();
        if ($user->membership == '0') {
            return response()->json([ 'success' => true, 'result' => 'freemium' ]);
        }
        else {
            return response()->json([ 'success' => true, 'result' => 'premium' ]);
        }

    }

    public function charge_paypal(Request $request)
    {
        try {
            $response = $this->gateway->purchase(array(
                'amount' => $request->amount,
                'currency' => env('PAYPAL_CURRENCY'),
                'returnUrl' => url('payment_success'),
                'cancelUrl' => url('payment_error'),
            ))->send();

            if ($response->isRedirect()) {
                return response()->json([ 'success' => true, 'result' => $response->getRedirectUrl()]);
            } else {
                // not successful
                return response()->json([ 'success' => false, 'result' => $response->getMessage()]);
            }

        } catch(Exception $e) {
            return response()->json([ 'success' => false, 'result' => $e->getMessage()]);
        }
    }

    public function complete_payment(Request $request)
    {
        $user = Auth::user();
        $payment_id = $request->paymentId;
        $payer_id = $request->payerId;

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

            DB::table('payments')->insert(['user_id'=>$user->id, 'payment_id'=>$arr_body['id'], 'payer_id'=>$arr_body['payer']['payer_info']['payer_id'],
                'payer_email'=>$payer_email, 'amount'=>$amount, 'payment_status'=>$payment_status, 'created_at'=>$this->now, 'updated_at'=>$this->now]);
            DB::table('users')->where('id', $user->id)->update(['membership'=>'1', 'membership_created_at'=>$this->now]);
            DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Membership', 'page_url'=>'admin/pricing', 'user_action'=>'Upgrade with Paypal', 'created_at'=>$this->now]);

            return response()->json([ 'success' => true, 'result' => $arr_body['id']]);

        } else
            return response()->json([ 'success' => false, 'result' => $response->getMessage()]);

    }

    public function charge_card(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'holder_name' => ['required'],
            'card_number' => ['required'],
            'expire_month' => ['required'],
            'expire_year' => ['required'],
            'cvc_number' => ['required'],
            'billing_country' => ['required'],
            'address' => ['required'],
            'city' => ['required'],
            'post_code' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'result' => $validator->errors()->first() ]);
        }

        $formData = array(
            'name' => $request->holder_name,
            'number' => $request->card_number,
            'expiryMonth' => $request->expire_month,
            'expiryYear' => $request->expire_year,
            'cvv' => $request->cvc_number,
            'address1' => $request->address,
            'country' => $request->billing_country,
            'city' => $request->city,
            'postcode' => $request->post_code,
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
                DB::table('users')->where('id', $user->id)->update(['membership'=>'1', 'membership_created_at'=>$this->now]);
                DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Membership', 'page_url'=>'admin/pricing', 'user_action'=>'Upgrade with Card', 'created_at'=>$this->now]);

                return response()->json([ 'success' => true, 'result' => 'OK']);
            } else {
                // Payment failed
                return response()->json([ 'success' => false, 'result' => $response->getMessage()]);
            }
        } catch(Exception $e) {
            return response()->json([ 'success' => false, 'result' => $e->getMessage()]);
        }
    }

    public function downgrade_freemium(Request $request)
    {
        $user = Auth::user();
        DB::table('users')->where('id', $user->id)->update(['membership'=>'0', 'membership_created_at'=>$this->now]);
        DB::table('site_histories')->insert(['user_id' => $user->id, 'page_name'=>'Membership', 'page_url'=>'admin/pricing', 'user_action'=>'Downgrade', 'created_at'=>$this->now]);

        return response()->json([ 'success' => true, 'result' => 'OK']);
    }
}
